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

    function toUserJson()
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
}