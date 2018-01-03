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


    static $STATUS = [STATUS_OFF => '封闭', STATUS_ON => '正常'];

    function mergeJson()
    {
        $room_seats = RoomSeats::find(['conditions' => 'room_id=:room_id:', 'bind' => ['room_id' => $this->id], 'order' => 'rank asc']);
        $room_seat_datas = [];
        foreach ($room_seats as $room_seat) {
            $room_seat_datas[] = $room_seat->to_json;
        }

        return ['user_num' => $this->userNum(), 'speaker' => $this->user->speaker,
            'microphone' => $this->user->microphone, 'room_seats' => $room_seat_datas];
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

        for ($i = 0; $i < 9; $i++) {
            $room_seat = new RoomSeats();
            $room_seat->room_id = $room->id;
            $room_seat->status = STATUS_ON;
            $room_seat->rank = $i;
            $room_seat->save();
        }

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