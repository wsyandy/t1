<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/2
 * Time: 下午7:52
 */
class RoomSeats extends BaseModel
{

    /**
     * @type Rooms
     */
    private $_room;
    /**
     * @type Users
     */
    private $_user;

    static $STATUS = [STATUS_OFF => '封闭', STATUS_ON => '解封'];


    function beforeCreate()
    {

    }

    function afterCreate()
    {

    }

    function beforeUpdate()
    {

    }

    function afterUpdate()
    {

    }

    function mergeJson()
    {
        $data = [];
        $user = $this->user;
        if ($user) {
            $data = ['sex' => $user->sex, 'avatar_small_url' => $user->avatar_small_url, 'nickname' => $user->nickname];
        }

        return $data;
    }

    function toSimpleJson()
    {
        $user = $this->user;
        $json = [];

        if ($user) {
            $json = [
                'sex' => $user->sex,
                'avatar_url' => $user->avatar_url,
                'avatar_small_url' => $user->avatar_small_url,
                'nickname' => $user->nickname,
            ];
        }

        $json['id'] = $this->id;
        $json['status'] = $this->status;
        $json['microphone'] = $this->microphone;
        $json['rank'] = $this->rank;
        $json['room_id'] = $this->room_id;
        $json['user_id'] = $this->user_id;

        return $json;
    }

    function toOnlineJson()
    {
        $json['id'] = $this->id;
        $json['status'] = $this->status;
        $json['microphone'] = $this->microphone;
        return $json;
    }

    function bindOnlineToken($user)
    {
        //绑定用户的onlinetoken 长连接使用
        $online_token = $user->online_token;

        info($online_token, $user->sid, $this->id);

        if ($online_token) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->set("room_seat_token_" . $online_token, $this->id);
        }
    }

    function unbindOnlineToken($user)
    {
        //解绑用户的onlinetoken 长连接使用
        $online_token = $user->online_token;

        info($online_token, $user->sid, $this->id);

        if ($online_token) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->del("room_seat_token_" . $online_token);
        }
    }

    //根据onlinetoken查找房间 异常退出时使用
    static function findRoomSeatByOnlineToken($token)
    {
        $hot_cache = Rooms::getHotWriteCache();
        $room_seat_id = $hot_cache->get("room_seat_token_" . $token);

        if (!$room_seat_id) {
            return null;
        }

        $room_seat = RoomSeats::findFirstById($room_seat_id);

        return $room_seat;
    }


    // 上麦
    function up($user, $other_user = null)
    {
        $res = $this->canUp($user, $other_user);

        list($error_code, $error_reason) = $res;

        if (ERROR_CODE_FAIL == $error_code) {
            info($user->sid, $error_reason);
            return $res;
        }

        // 抱用户上麦
        if ($other_user) {

            info($user->sid, $other_user->sid, $this->id, $this->room_id);

            if ($this->status == STATUS_OFF) {
                $this->open();
            }

            $this->user_id = $other_user->id;
            $other_user->current_room_id = $this->room_id;
            $other_user->current_room_seat_id = $this->id;
            if ($other_user->user_role > USER_ROLE_BROADCASTER) {
                $other_user->user_role = USER_ROLE_BROADCASTER; // 主播
            }
            $other_user->update();
            $this->room->updateUserRank($other_user);
            $object = $other_user;
        } else {
            info($user->sid, $this->id, $this->room_id);
            //当前用户已在麦位
            $current_room_seat = $user->current_room_seat;

            if ($current_room_seat) {
                $current_room_seat->down($user);
                info("change_room_seat", $user->sid, $current_room_seat->id);
            }

            // 自己上麦
            $this->user_id = $user->id;

            $user->current_room_id = $this->room_id;
            $user->current_room_seat_id = $this->id;
            if ($other_user->user_role > USER_ROLE_BROADCASTER) {
                $other_user->user_role = USER_ROLE_BROADCASTER; // 主播
            }
            $user->update();
            $this->room->updateUserRank($user);
            $object = $user;
        }

        $this->update();
        $this->bindOnlineToken($object);
        return $res;
    }

    // 下麦
    function down($user, $other_user = null)
    {
        $this->user_id = 0;

        // 设为旁听
        if ($other_user) {
            info($user->sid, $other_user->sid, $this->id, $this->room_id);
            $other_user->current_room_seat_id = 0;
            if ($other_user->user_role == USER_ROLE_BROADCASTER) {
                $other_user->user_role = USER_ROLE_AUDIENCE; // 旁听
            }
            $other_user->update();
            $this->room->updateUserRank($other_user, false);
            $object = $other_user;
        } else {
            info($user->sid, $this->id, $this->room_id);
            // 自己下麦
            $user->current_room_seat_id = 0;
            if ($other_user->user_role == USER_ROLE_BROADCASTER) {
                $other_user->user_role = USER_ROLE_AUDIENCE; // 旁听
            }
            $user->update();
            $this->room->updateUserRank($user, false);
            $object = $user;
        }

        $this->update();
        $this->unbindOnlineToken($object);
    }

    // 封麦
    function close()
    {
        $room_user = $this->room->user;
        $user = $this->user;
        info($room_user->sid, $this->id);

        if ($user) {
            info($user->sid, $this->id);
            // 下麦
            $this->down($room_user, $this->user);
        }

        $this->status = STATUS_OFF;
        $this->microphone = true; // 清空设置
        $this->save();
    }

    // 解封
    function open()
    {
        $room_user = $this->room->user;
        info($room_user->sid, $this->id);

        $this->status = STATUS_ON;
        $this->save();
    }

    function getMicrophoneText()
    {
        $microphone_text = "禁止";

        if ($this->microphone == true) {
            $microphone_text = "允许";
        }

        return $microphone_text;
    }

    function isClose()
    {
        return STATUS_OFF == $this->status;
    }

    function isOpen()
    {
        return STATUS_ON == $this->status;
    }

    //能否上麦
    function canUp($user, $other_user = null)
    {
        if ($this->user_id) {
            return [ERROR_CODE_FAIL, '麦位已存在用户'];
        }

        $room = $this->room;

        if ($other_user) {

            if (!$user->canManagerRoom($room)) {
                return [ERROR_CODE_FAIL, '您无此权限'];
            }

            //不能抱自己上麦
            if ($other_user->id === $user->id) {
                return [ERROR_CODE_FAIL, '不能抱自己上麦'];
            }

            //当前用户不在房间
            if (!$other_user->isInRoom($room)) {
                return [ERROR_CODE_FAIL, '用户不在房间'];
            }

            //当前用户已在麦位
            if ($other_user->current_room_seat_id) {
                return [ERROR_CODE_FAIL, '用户已在麦位'];
            }

        } else {

            //当前用户不在房间
            if (!$user->isInRoom($room)) {
                return [ERROR_CODE_FAIL, '用户不在房间'];
            }

            if ($this->isClose()) {
                return [ERROR_CODE_FAIL, '麦位已被封'];
            }

            //房主不能上自己的麦位
            if ($room->user_id === $user->id) {
                return [ERROR_CODE_FAIL, '房主不能上自己的麦位'];
            }
        }

        return [ERROR_CODE_SUCCESS, ''];
    }
}