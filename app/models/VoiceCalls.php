<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 11/01/2018
 * Time: 11:33
 */
class VoiceCalls extends BaseModel
{
    /**
     * @type Users
     */
    private $_sender;

    /**
     * @type Users
     */
    private $_receiver;

    /**
     * @type Users
     */
    private $_user;

    /**
     * @type boolean
     */
    private $_is_send;

    static $sender_call_status = array(
        CALL_STATUS_WAIT => '等待通话',
        CALL_STATUS_NO_ANSWER => '无应答',
        CALL_STATUS_BUSY => '对方忙',
        CALL_STATUS_REFUSE => '对方已拒绝',
        CALL_STATUS_CANCEL => '已取消',
        CALL_STATUS_HANG_UP => '挂断',
        CALL_STATUS_ANSWERED => '接听',
    );

    static $receiver_call_status = array(
        CALL_STATUS_WAIT => '等待通话',
        CALL_STATUS_NO_ANSWER => '未接来电',
        CALL_STATUS_BUSY => '未接来电',
        CALL_STATUS_REFUSE => '已拒绝来电',
        CALL_STATUS_CANCEL => '未接来电',
        CALL_STATUS_HANG_UP => '挂断',
        CALL_STATUS_ANSWERED => '接听',
    );

    static $call_status = array(
        CALL_STATUS_WAIT => '等待通话',
        CALL_STATUS_NO_ANSWER => '无应答',
        CALL_STATUS_BUSY => '对方忙',
        CALL_STATUS_REFUSE => '对方拒绝',
        CALL_STATUS_CANCEL => '取消',
        CALL_STATUS_HANG_UP => '挂断',
        CALL_STATUS_ANSWERED => '接听',
    );

    static function getCacheEndpoint($id)
    {
        $config = self::di('config');
        $cache_dbs = explode(',', $config->cache_endpoint);
        return $cache_dbs[0];
    }

    function toSimpleJson()
    {
        return array(
            'user_id' => $this->user->id,
            'nickname' => $this->user->nickname,
            'avatar_url' => $this->user->avatar_small_url,
            'duration' => $this->duration,
            'created_at' => $this->created_at,
            'call_no' => $this->call_no,
            'call_status' => $this->call_status,
            'call_status_text' => $this->call_status_text
        );
    }

    static function createVoiceCall($sender, $receiver)
    {
        $voice_call = new \VoiceCalls();
        $voice_call->sender_id = $sender->id;
        $voice_call->receiver_id = $receiver->id;
        $voice_call->call_status = CALL_STATUS_WAIT;
        $voice_call->call_no = $voice_call->generateCallNo();
        if ($voice_call->isReceiverBusy()) {
            $voice_call->call_status = CALL_STATUS_BUSY;
        }
        if ($voice_call->create()) {
            debug("call_status: " . $voice_call->call_status);
            if (!$voice_call->isBusy()) {
                $voice_call->changeUserBusy();
                \VoiceCalls::delay(80)->asyncCheckCallStatus($voice_call->id);
            }
            return $voice_call;
        }
        return false;
    }

    static function asyncCheckCallStatus($voice_call_id)
    {
        $voice_call = \VoiceCalls::findById($voice_call_id);
        if ($voice_call) {
            if ($voice_call->isCallStatusWait() && (time() - $voice_call->created_at > 60)) {
                $voice_call->changeStatus(CALL_STATUS_NO_ANSWER);
            }
        }
    }

    static function findListByUser($reader, $page, $per_page)
    {
        $conds = array(
            'conditions' => 'sender_id = :sender_id: or receiver_id = :receiver_id:',
            'bind' => array(
                'sender_id' => $reader->id,
                'receiver_id' => $reader->id
            ),
            'order' => 'id desc'
        );

        $cache = \Users::getUserDb();
        $key = 'clear_voice_calls_user_' . $reader->id;
        $clear_time = $cache->get($key);
        if ($clear_time) {
            $conds['conditions'] .= ' and updated_at >' . $clear_time;
        }

        $voice_calls = \VoiceCalls::findPagination($conds, $page, $per_page);
        \VoiceCalls::assignUser($voice_calls, $reader);
        return $voice_calls;
    }

    static function assignUser($voice_calls, $reader)
    {
        $user_ids = array();
        $voice_call_hash = array();
        $user_hash = array();
        foreach ($voice_calls as $voice_call) {
            $user_id = $voice_call->sender_id == $reader->id ? $voice_call->receiver_id : $voice_call->sender_id;
            $voice_call_hash[$voice_call->id] = $user_id;
            $user_ids[] = $user_id;
        }
        $users = \Users::findByIds($user_ids);
        foreach ($users as $user) {
            $user_hash[$user->id] = $user;
        }
        foreach ($voice_calls as $voice_call) {
            $user_id = $voice_call_hash[$voice_call->id];
            if ($user_id) {
                $user = $user_hash[$user_id];
                $voice_call->user = $user;
                $voice_call->assignIsSend($reader);
            }
        }
    }

    function generateCallNo()
    {
        return 'CN' . strval($this->sender_id) . strval($this->receiver_id) . time() . mt_rand(100, 1000);
    }

    function changeStatus($call_status)
    {
        debug($call_status);

        $this->call_status = $call_status;
        if ($this->hasChanged('call_status') && $this->isHangUp()) {
            $this->duration = time() - $this->updated_at;

            debug($this->id, $this->call_status);
        }

        if ($this->update() && !$this->isCallStatusAnswered()) {

            debug($this->id, $this->call_status);

            $this->changeUserFree();
        }
    }

    function beforeUpdate()
    {
        if (!$this->isStatusValid()) {
            return true;
        }
        return false;
    }

    function isStatusValid()
    {
        return array_key_exists($this->call_status, \VoiceCalls::$call_status);
    }

    function isHangUp()
    {
        return $this->call_status == CALL_STATUS_HANG_UP;
    }

    function getDurationText()
    {
        $duration = intval($this->duration);
        $min = floor($duration / 60);
        $sec = $duration % 60;
        if ($sec < 10) {
            $sec = '0' . $sec;
        }
        $hour = floor($min / 60);
        if ($hour > 0) {
            $min = $min % 60;
        }
        if ($min < 60) {
            $min = '0' . $min;
        }
        $result = $min . ':' . $sec;
        if ($hour > 0) {
            if ($hour < 10) {
                $hour = '0' . $hour;
            }
            $result = $hour . ':' . $result;
        }
        return $result;
    }

    function getHangUpText()
    {
        return '通话时长' . $this->duration_text;
    }

    function getCallStatusText()
    {
        if ($this->isHangUp()) {
            return $this->hang_up_text;
        }
        if ($this->is_send) {
            $call_status_hash = \VoiceCalls::$sender_call_status;
        } else {
            $call_status_hash = \VoiceCalls::$receiver_call_status;
        }
        return fetch($call_status_hash, $this->call_status);
    }

    function assignIsSend($reader)
    {
        $this->is_send = $this->sender_id == $reader->id;
    }

    function isCallStatusWait()
    {
        return CALL_STATUS_WAIT == $this->call_status;
    }

    function isCallStatusAnswered()
    {
        return CALL_STATUS_ANSWERED == $this->call_status;
    }

    function isBusy()
    {
        return CALL_STATUS_BUSY == $this->call_status;
    }

    function isReceiverBusy()
    {
        $voice_call_id = self::getVoiceCallIdByUserId($this->receiver_id);
        return intval($voice_call_id) > 0;
    }

    static function userCacheKey($user_id)
    {
        return "voice_calling_" . $user_id;
    }

    static function userIsCalling($user_id)
    {
        $voice_call_id = self::getVoiceCallIdByUserId($user_id);
        return intval($voice_call_id) > 0;
    }

    static function getVoiceCallIdByUserId($user_id)
    {
        $redis = \VoiceCalls::getHotReadCache();
        $key = self::userCacheKey($user_id);

        return $redis->get($key);
    }

    static function getVoiceCallByUserId($user_id)
    {
        $voice_call_id = self::getVoiceCallIdByUserId($user_id);

        if (!$voice_call_id) {
            return null;
        }

        $voice_call = VoiceCalls::findFirstById($voice_call_id);

        return $voice_call;
    }

    static function userBusy($user_id, $voice_id)
    {
        $redis = \VoiceCalls::getHotWriteCache();
        $key = \VoiceCalls::userCacheKey($user_id);
        $redis->setex($key, 60 * 60 * 2, $voice_id);
    }

    static function freeUser($user_id)
    {
        $key = self::userCacheKey($user_id);
        $redis = \VoiceCalls::getHotWriteCache();
        $redis->del($key);
    }

    function changeUserBusy()
    {
        \VoiceCalls::userBusy($this->sender_id, $this->id);
        \VoiceCalls::userBusy($this->receiver_id, $this->id);
    }

    function changeUserFree()
    {
        \VoiceCalls::freeUser($this->sender_id);
        \VoiceCalls::freeUser($this->receiver_id);
    }

    static function pushHangupInfo($server, $user, $intranet_ip)
    {
        $hot_cache = self::getHotReadCache();
        $user_id = $user->id;
        $voice_call = VoiceCalls::getVoiceCallByUserId($user_id);

        if ($voice_call) {
            $call_sender_id = $voice_call->sender_id;
            $call_receiver_id = $voice_call->receiver_id;
            $voice_call->changeStatus(CALL_STATUS_HANG_UP);
            $receiver_id = $user->id == $call_sender_id ? $call_receiver_id : $call_sender_id;
            $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_id));
            $data = ['action' => 'hang_up', 'user_id' => $user_id, 'receiver_id' => $receiver_id, 'channel_name' => $voice_call->call_no];
            info("calling_hang_up_exce", $user->sid, $receiver_id, $receiver_fd, $data, $intranet_ip);
            //$payload = ['body' => $data, 'fd' => $fd, 'ip' => $intranet_ip];
            //$this->send('push', $payload);
            $server->push($receiver_fd, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }
}