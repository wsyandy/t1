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

    function mergeJson()
    {
        return ['sex' => $this->user->sex, 'avatar_small_url' => $this->user->avatar_small_url,
            'nickname' => $this->user->nickname];
    }

}