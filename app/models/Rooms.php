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

    function toSimpleJson()
    {
        return ['id' => $this->id, 'name' => $this->name, 'topic' => $this->topic, 'chat' => $this->chat,
            'user_id' => $this->user_id, 'sex' => $this->user->sex, 'avatar_small_url' => $this->user->avatar_small_url,
            'nickname' => $this->user->nickname, 'age' => $this->user->age, 'monologue' => $this->user->monologue,
            'channel_name' => $this->channel_name, 'online_status' => $this->online_status, 'user_num' => $this->user_num,
            'lock' => $this->lock, 'created_at' => $this->created_at, 'last_at' => $this->last_at,
            'distance' => strval(mt_rand(1, 10) / 10) . 'km'
        ];
    }

    function mergeJson()
    {
        $room_seats = RoomSeats::find(['conditions' => 'room_id=:room_id:', 'bind' => ['room_id' => $this->id], 'order' => 'rank asc']);
        $room_seat_datas = [];
        foreach ($room_seats as $room_seat) {
            $room_seat_datas[] = $room_seat->to_json;
        }

        return ['user_num' => $this->user_num, 'sex' => $this->user->sex, 'avatar_small_url' => $this->user->avatar_small_url,
            'nickname' => $this->user->nickname, 'age' => $this->user->age, 'monologue' => $this->user->monologue, 'room_seats' => $room_seat_datas];
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

        $user->room_id = $room->id;
        $user->save();

        // 麦位
        for ($i = 1; $i <= 8; $i++) {
            $room_seat = new RoomSeats();
            $room_seat->room_id = $room->id;
            $room_seat->status = STATUS_ON;
            $room_seat->rank = $i;
            $room_seat->save();
        }

        return $room;
    }

    function getChannelName()
    {
        return $this->id . 'c' . md5($this->id . 'u' . $this->user_id);
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

    function enterRoom($user)
    {

        $user->current_room_id = $this->id;
        $user->user_role = USER_ROLE_AUDIENCE; // 旁听

        // 房主
        if ($this->user_id == $user->id) {
            $user->user_role = USER_ROLE_HOST_BROADCASTER; // 房主

            $this->last_at = time();
            $this->online_status = STATUS_ON;
            $this->save();
        }

        $user->save();

        $this->addUser($user);
    }

    function exitRoom($user)
    {

        // 麦位
        $room_seat = RoomSeats::findFirstById($user->current_room_seat_id);
        if ($room_seat) {
            $room_seat->user_id = 0;
            $room_seat->save();
        }

        $user->current_room_id = 0;
        $user->current_room_seat_id = 0;
        $user->user_role = USER_ROLE_NO;
        $user->save();

        // 房主
        if ($this->user_id == $user->id) {
            $this->online_status = STATUS_OFF;
            $this->save();
        }

        $this->remUser($user);
    }

    function getUserNum()
    {
        $hot_cache = self::getHotWriteCache();
        $key = 'room_user_list_' . $this->id;
        return $hot_cache->zcard($key);
    }

    function addUser($user)
    {

        $hot_cache = self::getHotWriteCache();
        $key = 'room_user_list_' . $this->id;
        if ($this->user_id == $user->id) {
            $hot_cache->zadd($key, time() + 86400, $user->id);
        } else {
            $hot_cache->zadd($key, time(), $user->id);
        }
    }

    function remUser($user)
    {
        $hot_cache = self::getHotWriteCache();
        $key = 'room_user_list_' . $this->id;
        $hot_cache->zrem($key, $user->id);
    }

    function findUsers($page, $per_page)
    {
        $hot_cache = self::getHotWriteCache();
        $key = 'room_user_list_' . $this->id;
        $total_entries = $hot_cache->zcard($key);

        $offset = $per_page * ($page - 1);

        $user_ids = $hot_cache->zrevrange($key, $offset, $offset + $per_page - 1);
        $users = Users::findByIds($user_ids);

        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }

}