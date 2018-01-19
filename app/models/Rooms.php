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


    static $STATUS = [STATUS_OFF => '下架', STATUS_ON => '上架', STATUS_BLOCKED => '封闭'];

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
            'lock' => $this->lock, 'created_at' => $this->created_at, 'last_at' => $this->last_at
        ];
    }

    function mergeJson()
    {
        $room_seats = RoomSeats::find(['conditions' => 'room_id=:room_id:', 'bind' => ['room_id' => $this->id], 'order' => 'rank asc']);
        $room_seat_datas = [];
        foreach ($room_seats as $room_seat) {
            $room_seat_datas[] = $room_seat->to_json;
        }

        return ['channel_name' => $this->channel_name, 'user_num' => $this->user_num, 'sex' => $this->user->sex, 'avatar_small_url' => $this->user->avatar_small_url,
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

    function bindOnlineToken($user)
    {
        //绑定用户的onlinetoken 长连接使用
        $online_token = $user->online_token;

        debug($online_token, $user->id, $this->id);

        if ($online_token) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->set("room_token_" . $online_token, $this->id);
        }
    }

    function unbindOnlineToken($user)
    {
        //解绑用户的onlinetoken 长连接使用
        $online_token = $user->online_token;

        debug($online_token, $user->id, $this->id);

        if ($online_token) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->del("room_token_" . $online_token);
        }
    }

    //根据onlinetoken查找房间 异常退出时使用
    static function findRoomByOnlineToken($token)
    {
        $hot_cache = Rooms::getHotWriteCache();
        $room_id = $hot_cache->get("room_token_" . $token);

        if (!$room_id) {
            return null;
        }

        $room = Rooms::findFirstById($room_id);

        return $room;
    }

    function enterRoom($user)
    {
        $user->current_room_id = $this->id;
        $user->user_role = USER_ROLE_AUDIENCE; // 旁听

        //如果有麦位id 为主播
        if ($user->current_room_seat_id) {
            $user->user_role = USER_ROLE_BROADCASTER; // 主播
        }

        // 房主
        if ($this->user_id == $user->id) {
            $user->user_role = USER_ROLE_HOST_BROADCASTER; // 房主

            $this->last_at = time();
            $this->online_status = STATUS_ON; // 主播是否在线
            $this->save();
        }

        $user->save();

        $this->bindOnlineToken($user);
        $this->addUser($user);
    }

    function exitRoom($user)
    {

        // 麦位
        $room_seat = RoomSeats::findFirstById($user->current_room_seat_id);

        if (!$room_seat) {
            $room_seat = RoomSeats::findFirstByUserId($user->id);
        }

        if ($room_seat) {
            $room_seat->user_id = 0;
            $room_seat->save();
        }

        debug($user->current_room_seat_id);
        $user->current_room_id = 0;
        $user->current_room_seat_id = 0;
        $user->user_role = USER_ROLE_NO;
        $user->save();

        // 房主
        if ($this->user_id == $user->id) {
            $this->online_status = STATUS_OFF;
            $this->save();
        }

        $this->unbindOnlineToken($user);
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
            $hot_cache->zadd($key, time() + 86400 * 7, $user->id);
        } elseif (USER_ROLE_BROADCASTER == $user->user_role) {
            $hot_cache->zadd($key, time() + 86400 * 3, $user->id);
        } else {
            $hot_cache->zadd($key, time(), $user->id);
        }

        if ($this->user_num == 1) {
            $this->status = STATUS_ON;
            $this->update();
        }
    }

    function remUser($user)
    {
        $hot_cache = self::getHotWriteCache();
        $key = 'room_user_list_' . $this->id;
        $hot_cache->zrem($key, $user->id);

        if ($this->user_num < 1) {
            $this->status = STATUS_OFF;
            $this->update();
        }
    }

    function updateUserRank($user, $asc = true)
    {
        $hot_cache = self::getHotWriteCache();
        $key = 'room_user_list_' . $this->id;

        $time = time();

        if ($asc) {
            $time += 3 * 86400;
        }

        $hot_cache->zadd($key, $time, $user->id);

        debug($hot_cache->zscore($key, $user->id));
    }

    function findUsers($page, $per_page)
    {
        $hot_cache = self::getHotWriteCache();
        $key = 'room_user_list_' . $this->id;
        $total_entries = $hot_cache->zcard($key);

        $offset = $per_page * ($page - 1);

        $user_ids = $hot_cache->zrevrange($key, $offset, $offset + $per_page - 1);
        $objects = Users::findByIds($user_ids);

        $users = [];

        foreach ($objects as $object) {
            if ($object->current_room_id != $this->id) {
//                $hot_cache->zrem($key, $object->id);
                continue;
            }

            $users[] = $object;
        }

        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }


    function lock($password)
    {
        $this->password = $password;
        $this->lock = true;
        $this->update();
    }

    function unlock()
    {
        $this->password = '';
        $this->lock = false;
        $this->update();
    }

    function getLockText()
    {
        $lock_text = "无锁";

        if ($this->lock) {
            $lock_text = "有锁";
        }

        return $lock_text;
    }

    function getChatText()
    {
        $chat_text = "禁止聊天";

        if ($this->chat == true) {
            $chat_text = "可以聊天";
        }

        return $chat_text;
    }

    //禁止 踢出房间 禁止用户在10分钟内禁入
    function forbidEnter($user)
    {
        $hot_cache = Rooms::getHotWriteCache();
        $time = 600;

        if (isDevelopmentEnv()) {
            $time = 60;
        }

        $key = "room_forbid_user_room{$this->id}_user{$user->id}";

        info($key);

        $hot_cache->setex($key, $time, 1);
    }

    function isForbidEnter($user)
    {
        $hot_cache = Rooms::getHotReadCache();
        $key = "room_forbid_user_room{$this->id}_user{$user->id}";

        return $hot_cache->get($key) > 0;
    }
}