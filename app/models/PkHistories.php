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
            $pk_history_existed = \PkHistories::findFirst(['conditions' => 'room_id=:room_id: and status !=:status:',
                'bind' => ['room_id' => $room_id, 'status' => STATUS_OFF],
                'order' => 'id desc'
            ]);
            if ($pk_history_existed) {
                $pk_history_existed->status = STATUS_OFF;
                $pk_history_existed->update();
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

            self::delay($pk_time)->asyncFinishPk($pk_history);
            return [$pk_history, ERROR_CODE_SUCCESS, '创建成功'];
        }

        return [null, ERROR_CODE_FAIL, '创建失败'];
    }


    static function asyncFinishPk($pk_history)
    {
        if (isPresent($pk_history) && $pk_history->status != STATUS_OFF) {
            $pk_history->status = STATUS_OFF;
            $pk_history->update();
        }
    }

    static function generatePkListKey()
    {
        return 'pk_histories_list';
    }

    function toSimpleJson()
    {
        $left_pk_user = $this->left_pk_user;
        $right_pk_user = $this->right_pk_user;
        $left_pk_user_score = $this->getPkUserScore($left_pk_user->id) ? $this->getPkUserScore($left_pk_user->id) : $this->left_pk_user_score;
        $right_pk_user_score = $this->getPkUserScore($right_pk_user->id) ? $this->getPkUserScore($right_pk_user->id) : $this->right_pk_user_score;

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

    static function updatePkHistories($sender, $total_amount, $receiver_id, $pay_type)
    {
        $pk_history_datas = self::updatePkHistoryInfo($sender, $total_amount, $receiver_id, $pay_type);
        $room = \Rooms::findFirstById($sender->current_room_id);

        info('更新', $sender, $total_amount, $receiver_id, $pay_type);
        if (isPresent($pk_history_datas)) {
            $room->pushPkMessage($pk_history_datas);
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
        $body = ['left_pk_user_id' => $this->left_pk_user_id, 'right_pk_user_id' => $this->right_pk_user_id, $this->left_pk_user_id => 0, $this->right_pk_user_id => 0, 'pk_type' => $this->pk_type];
        $cache->hmset($key, $body);
        info('初始化pk数据', $key, $body);

        $cache->expire($key, $this->expire_at - time() + 3600);
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

    static function updatePkHistoryInfo($sender, $total_amount, $receiver_id, $pay_type)
    {
        $cache = self::getHotWriteCache();
        $key = self::generatePkHistoryInfoKey($sender->current_room_id);
        if ($cache->hexists($key, $receiver_id)) {
            $current_score = $cache->hget($key, $receiver_id);
            $pk_type = $cache->hget($key, 'pk_type');
            switch ($pk_type) {
                case 'send_gift_user':
                    $current_score = self::checkSendGiftUser($sender, $receiver_id, $current_score);
                    break;
                case 'send_gift_amount':
                    if ($pay_type == GIFT_PAY_TYPE_DIAMOND) {
                        $current_score = $current_score + $total_amount;
                    }
                    break;
            }
            $cache->hmset($key, [$receiver_id => $current_score]);
        }
        $datas = $cache->hgetall($key);
        info('更新pk记录', $key, $datas, $current_score);

        return $datas;
    }

    function delPkHistoryInfo()
    {
        $room_id = $this->room_id;
        $cache = self::getHotWriteCache();
        $key = self::generatePkHistoryInfoKey($room_id);
        $datas = $cache->hgetall($key);
        info('pk_history_info=>', $datas, $key);

        $left_pk_user_score = $datas[$datas['left_pk_user_id']];
        $right_pk_user_score = $datas[$datas['right_pk_user_id']];

        $this->left_pk_user_score = $left_pk_user_score;
        $this->right_pk_user_score = $right_pk_user_score;

        $send_gift_left_user_key = self::generatePkForUserInRoom($room_id, $datas['left_pk_user_id']);
        $send_gift_right_user_key = self::generatePkForUserInRoom($room_id, $datas['right_pk_user_id']);
        if ($this->update()) {
            $cache->del($key);
            $cache->del($send_gift_left_user_key);
            $cache->del($send_gift_right_user_key);
        }
    }

    static function checkPkHistoryForUser($room_id)
    {
        $cache = self::getHotWriteCache();
        $key = self::generatePkListKey();
        $score = $cache->zscore($key, $room_id);
        info('所有pk房间', $cache->zrange($key, 0, -1), '当前房间ID', $room_id);
        if ($score) {
            return true;
        }
        return false;
    }

    static function generatePkForUserInRoom($room_id, $receiver_id)
    {
        return 'pk_send_gift_user_for_' . $receiver_id . '_in_' . $room_id;
    }

    static function checkSendGiftUser($sender, $receiver_id, $current_score)
    {
        $cache = self::getHotWriteCache();
        $key = self::generatePkForUserInRoom($sender->current_room_id, $receiver_id);
        $ids = $cache->zrange($key, 0, -1);
        info('赠送者ID', $sender->id, '曾经送过的ID', $ids);
        if (!in_array($sender->id, $ids)) {
            $cache->zadd($key, time(), $sender->id);
            return $current_score + 1;
        }
        return $current_score;
    }

    function getPkUserScore($user_id)
    {
        $cache = self::getHotWriteCache();
        $key = self::generatePkHistoryInfoKey($this->room_id);
        $score = $cache->hget($key, $user_id);
        return $score;
    }
}
