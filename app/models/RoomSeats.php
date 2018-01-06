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
        if ($this->user) {
            $data = ['sex' => $this->user->sex, 'avatar_small_url' => $this->user->avatar_small_url,
                'nickname' => $this->user->nickname];
        }

        return $data;
    }

    function toSimpleJson()
    {
        $user = $this->user;
        $json = [];

        if ($user) {
            $json = [
                'user_id' => $user->id,
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

        return $json;
    }

    // 上麦
    function up($user, $other_user = null)
    {

        // 抱用户上麦
        if ($other_user) {

            if ($this->status == STATUS_OFF) {
                $this->open();
            }

            $this->user_id = $other_user->id;

            $other_user->current_room_id = $this->room_id;
            $other_user->current_room_seat_id = $this->id;
            $other_user->user_role = USER_ROLE_BROADCASTER; // 主播
            $other_user->update();
        } else {

            // 自己上麦
            $this->user_id = $user->id;

            $user->current_room_id = $this->room_id;
            $user->current_room_seat_id = $this->id;
            $user->user_role = USER_ROLE_BROADCASTER;
            $user->update();
        }

        $this->update();
    }

    // 下麦
    function down($user, $other_user = null)
    {

        $this->user_id = 0;

        // 设为旁听
        if ($other_user) {
            $other_user->current_room_seat_id = 0;
            $other_user->user_role = USER_ROLE_AUDIENCE; // 旁听
            $other_user->update();
        } else {
            // 自己下麦
            $user->current_room_seat_id = 0;
            $user->user_role = USER_ROLE_AUDIENCE; // 旁听
            $user->update();
        }

        $this->update();
    }

    // 封麦
    function close()
    {

        if ($this->user) {
            // 下麦
            $this->down($this->room->user, $this->user);
        }

        $this->status = STATUS_OFF;
        $this->microphone = true; // 清空设置
        $this->save();
    }

    // 解封
    function open()
    {
        $this->status = STATUS_ON;
        $this->save();
    }


}