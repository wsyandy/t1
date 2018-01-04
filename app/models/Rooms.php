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

    static $ONLINE_STATUS = [STATUS_OFF => '离线', STATUS_ON => '在线'];

    function toSimpleJson()
    {
        return ['id' => $this->id, 'name' => $this->name, 'channel_name' => $this->channel_name,
            'user_id' => $this->user_id, 'created_at' => $this->created_at, 'last_at' => $this->last_at
        ];
    }

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
        $room->online_status = STATUS_ON;
        $room->last_at = time();
        $room->save();

        $room->channel_name = $room->generateChannelName();
        $room->save();

        for ($i = 0; $i < 9; $i++) {
            $room_seat = new RoomSeats();
            $room_seat->room_id = $room->id;
            $room_seat->status = STATUS_ON;
            $room_seat->rank = $i;
            $room_seat->save();

            // 房主
            if ($i == 0) {
                $room->room_seat_id = $room_seat->id;
                $room->save();
            }
        }

        return $room;
    }

    function generateChannelName()
    {
        return $this->id . 'channel_name' . $this->user_id . '_' . mt_rand(10000, 99999);
    }

    function updateRoom($params)
    {
        $name = fetch($params, 'name');
        if (!isBlank($name)) {
            $this->name = $name;
        }

        $topic = fetch($params, 'topic');
        if (!isBlank($topic)) {
            $this->topic = $topic;
        }

        $this->update();
    }

    function userNum()
    {
        return 0;
    }

    function enterRoom($user)
    {
        $this->last_at = time();
        $this->online_status = STATUS_ON;
        $this->save();

        $user->room_id = $this->id;
        $user->user_role = USER_ROLE_AUDIENCE; // 旁听
        if ($this->user_id == $user->id) {
            $user->room_seat_id = $this->room_seat_id;
            $user->user_role = USER_ROLE_HOST_BROADCASTER; // 房主
        }

        $user->save();

    }

    function exitRoom($user)
    {

        $user->room_id = $this->id;
        $user->user_role = USER_ROLE_NO;
        $this->online_status = STATUS_OFF;
        $user->save();

        $room_seat = RoomSeats::findFirstById($user->room_seat_id);
        if($room_seat){
            $room_seat->user_id = 0;
            $room_seat->save();
        }

    }

}