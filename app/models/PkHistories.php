<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/5/7
 * Time: 下午6:10
 */

class PkHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_left_pk_user;

    /**
     * @type Users
     */
    private $_right_pk_user;


    static $STATUS = [STATUS_ON => '创建成功', STATUS_PROGRESS => 'PK中', STATUS_OFF => 'PK结束'];

    function beforeCreate()
    {

    }

    function afterCreate()
    {
        $this->updatePkHistoryListForCache('add');
    }

    function afterUpdate()
    {
        if ($this->hasChanged('status') && $this->status == STATUS_OFF) {
            $this->updatePkHistoryListForCache('del');
        }
    }


    static function createHistory($user, $opts = [])
    {
        $room_id = fetch($opts, 'room_id');
        $left_pk_user_id = fetch($opts, 'left_pk_user_id');
        $right_pk_user_id = fetch($opts, 'right_pk_user_id');
        $pk_type = fetch($opts, 'pk_type');
        $pk_time = fetch($opts, 'pk_time');
        $cover = fetch($opts, 'cover', 0);
        $result = self::checkPkHistoryInfo($room_id);

        if ($cover) {
            if ($result) {
                return [null, ERROR_CODE_FORM, '创建失败'];
            }
        } else {
            $pk_history = \PkHistories::findFirst(['conditions' => 'room_id=:room_id: and status !=:status:',
                'bind' => ['room_id' => $room_id, 'status' => STATUS_OFF],
                'order' => 'id desc'
            ]);
            if ($pk_history) {
                $pk_history->status = STATUS_OFF;
                $pk_history->update();
            }
        }

        $pk_history = new PkHistories();
        $pk_history->room_id = $room_id;
        $pk_history->user_id = $user->id;
        $pk_history->left_pk_user_id = $left_pk_user_id;
        $pk_history->right_pk_user_id = $right_pk_user_id;
        $pk_history->pk_type = $pk_type;
        $pk_history->expire_at = time() + $pk_time;
        $pk_history->status = STATUS_PROGRESS;

        if ($pk_history->save()) {
            return [$pk_history, ERROR_CODE_SUCCESS, '创建成功'];
        }

        return [null, ERROR_CODE_FAIL, '创建失败'];
    }

    static function generatePkListKey()
    {
        return 'pk_histories_list';
    }

    function toSimpleJson()
    {
        $left_pk_user = $this->left_pk_user;
        $right_pk_user = $this->right_pk_user;
        $left_pk_user_score = $this->left_pk_user_score;
        $right_pk_user_score = $this->right_pk_user_score;

        return [
            'id' => $this->id,
            'pk_type' => $this->pk_type,
            'expire_at' => $this->expire_at,
            'created_at' => $this->created_at,
            'created_at_text' => $this->created_at_text,

            'left_pk_user' => [
                'id' => $left_pk_user->id,
                'nickname' => $left_pk_user->nickname,
                'score' => $left_pk_user_score,
                'avatar_small_url' => $left_pk_user->avatar_small_url
            ],

            'right_pk_user' => [
                'id' => $right_pk_user->id,
                'nickname' => $right_pk_user->nickname,
                'score' => $right_pk_user_score,
                'avatar_small_url' => $right_pk_user->avatar_small_url
            ]
        ];
    }

    static function updatePkHistories($sender, $total_amount, $receiver_ids)
    {
        $pk_history_datas = self::updatePkHistoryInfo($sender->room_id, $total_amount, $receiver_ids);

        if (isPresent($pk_history_datas)) {
            $body = ['action' => 'pk', 'pk_history' => [
                'left_pk_user' => ['id' => $pk_history_datas['left_pk_user_id'], 'score' => $pk_history_datas[$pk_history_datas['left_pk_user_id']]],
                'right_pk_user' => ['id' => $pk_history_datas['right_pk_user_id'], 'score' => $pk_history_datas[$pk_history_datas['right_pk_user_id']]]
            ]
            ];

            $intranet_ip = $sender->getIntranetIp();
            $receiver_fd = $sender->getUserFd();

            $result = \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);

            info('推送结果:', $result, '主体信息：', $body);
        }

    }

    function updatePkHistoryListForCache($type)
    {
        $cache = self::getHotWriteCache();
        $key = self::generatePkListKey();
        switch ($type) {
            case 'add':
                $cache->zadd($key, time(), $this->room_id);
                $this->savePkHistoryInfo();
                break;
            case 'del':
                $cache->zrem($key, $this->room_id);
                $this->delPkHistoryInfo();
                break;
        }

    }

    function savePkHistoryInfo()
    {
        $cache = self::getHotWriteCache();
        $key = self::generatePkHistoryInfoKey($this->room_id);
        $body = ['left_pk_user_id' => $this->left_pk_user_id, 'right_pk_user_id' => $this->right_pk_user_id, $this->left_pk_user_id => 0, $this->right_pk_user_id => 0];
        $cache->hmset($key, $body);

        $cache->expire($key, 3 * 65);
    }

    static function generatePkHistoryInfoKey($room_id)
    {
        return 'pk_history_info_' . $room_id;
    }

    static function checkPkHistoryInfo($room_id)
    {
        $cache = self::getHotWriteCache();
        $key = self::generatePkHistoryInfoKey($room_id);
        return $cache->hget($key, 'room_id');
    }

    static function updatePkHistoryInfo($room_id, $total_amount, $receiver_ids)
    {
        $cache = self::getHotWriteCache();
        $key = self::generatePkHistoryInfoKey($room_id);

        foreach ($receiver_ids as $receiver_id) {

            if ($cache->hexists($key, $receiver_id)) {
                $score = $cache->hget($key, $receiver_id);
                $current_score = $score + $total_amount;
                $cache->hmset($key, [$receiver_id => $current_score]);
            }
        }

        return $cache->hgetall($key);
    }

    function delPkHistoryInfo()
    {
        $room_id = $this->room_id;
        $cache = self::getHotWriteCache();
        $key = self::generatePkHistoryInfoKey($room_id);
        $datas = $cache->hgetall($key);

        $left_pk_user_score = $datas[$datas['left_pk_user_id']];
        $right_pk_user_score = $datas[$datas['right_pk_user_id']];

        $this->left_pk_user_score = $left_pk_user_score;
        $this->right_pk_user_score = $right_pk_user_score;
        if ($this->update()) {
            $cache->del($key);
        }
    }
}
