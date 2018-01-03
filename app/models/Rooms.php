<?php

class Rooms extends BaseModel
{
    /**
     * @type ProductChannels
     */
    private $_product_channel;
    /**
     * @type Users
     */
    private $_user;


    static $STATUS = [STATUS_OFF => '封闭', STATUS_ON => '解封'];

    function mergeJson()
    {
        return ['user_num' => $this->userNum(), 'speaker' => $this->user->speaker, 'microphone' => $this->user->microphone];
    }

    static function createRoom($user, $name)
    {
        $room = new Rooms();
        $room->name = $name;
        $room->user_id = $user->id;
        $room->user = $user;
        $room->product_channel_id = $user->product_channel_id;
        $room->status = STATUS_ON;
        $room->last_at = time();
        $room->save();
        return $room;
    }

    function generateChannelName()
    {
        return $this->id . 'channel_name' . $this->user_id;
    }

    function updateRoom($param = [])
    {
        $name = fetch($param, 'name');
        $topic = fetch($param, 'topic');

        if (!isBlank($name)) {
            $this->name = $name;
        }

        if (!isBlank($topic)) {
            $this->topic = $topic;
        }

        $this->update();
    }

    function userNum()
    {
        return 0;
    }

}