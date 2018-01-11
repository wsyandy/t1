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
        CALL_STATUS_HANG_UP => '挂断'
    );

    static $receiver_call_status = array(
        CALL_STATUS_WAIT => '等待通话',
        CALL_STATUS_NO_ANSWER => '未接来电',
        CALL_STATUS_BUSY => '未接来电',
        CALL_STATUS_REFUSE => '已拒绝来电',
        CALL_STATUS_CANCEL => '未接来电',
        CALL_STATUS_HANG_UP => '挂断'
    );

    static $call_status = array(
        CALL_STATUS_WAIT => '等待通话',
        CALL_STATUS_NO_ANSWER => '无应答',
        CALL_STATUS_BUSY => '对方忙',
        CALL_STATUS_REFUSE => '对方拒绝',
        CALL_STATUS_CANCEL => '取消',
        CALL_STATUS_HANG_UP => '挂断'
    );

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
        if ($voice_call->create()) {
            return $voice_call;
        }
        return false;
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
        return 'CN' . strval($this->sender_id) . strval($this->receiver_id) . time();
    }

    function changeStatus($call_status)
    {
        $this->call_status = $call_status;
        $this->update();
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
        $min = $duration / 60;
        $sec = $duration % 60;
        if ($sec < 10) {
            $sec = '0' . $sec;
        }
        $hour = $min / 60;
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
        var_dump($this->is_send);
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

}