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

        $user = $this->user;
        return ['channel_name' => $this->channel_name, 'user_num' => $this->user_num, 'sex' => $user->sex, 'avatar_small_url' => $user->avatar_small_url,
            'nickname' => $user->nickname, 'age' => $user->age, 'monologue' => $user->monologue, 'room_seats' => $room_seat_datas];
    }

    function toBasicJson()
    {
        return ['id' => $this->id, 'lock' => $this->lock, 'channel_name' => $this->channel_name, 'name' => $this->name];
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

        info($online_token, $user->sid, $this->id);

        if ($online_token) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->set("room_token_" . $online_token, $this->id);
        }
    }

    function unbindOnlineToken($user)
    {
        //解绑用户的onlinetoken 长连接使用
        $online_token = $user->online_token;

        info($online_token, $user->sid, $this->id);

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

        if ($user->isManager($this)) {
            $user->user_role = USER_ROLE_MANAGER; //管理员
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
        info($this->id, $this->user_num, $user->sid, $user->current_room_seat_id);
    }

    function exitRoom($user, $unbind = true)
    {
        $current_room_seat_id = $user->current_room_seat_id;
        // 麦位
        $room_seat = RoomSeats::findFirstById($current_room_seat_id);

        if (!$room_seat) {
            $room_seat = RoomSeats::findFirstByUserId($user->id);
        }

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

        //修复数据时,不需要解绑,防止用户在别的房间已经生成新的token
        if ($unbind) {
            $this->unbindOnlineToken($user);
        }

        $this->remUser($user);

        info($this->id, $this->user_num, $user->sid, $current_room_seat_id);
    }

    function kickingRoom($user)
    {
        info($this->user->sid, $user->sid);
        $this->exitRoom($user);
        $this->forbidEnter($user);
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

        if ($this->user_num > 0 && $this->status == STATUS_OFF) {
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

    static function getRoomDb()
    {
        $user_db = Users::getUserDb();
        return $user_db;
    }

    function generateManagerListKey()
    {
        return "room_manager_list_id" . $this->id;
    }

    static function generateTotalManagerKey()
    {
        return "total_room_manager_list";
    }

    function generateRoomManagerKey($user_id)
    {
        return "room_id{$this->id}_user_id{$user_id}";
    }

    static function generateUserManagerListKey($user_id)
    {
        return "user_manager_room_list_id" . $user_id;
    }

    function getManagerNum()
    {
        $this->freshManagerNum();
        $db = Rooms::getRoomDb();
        $key = $this->generateManagerListKey();
        return $db->zcard($key);
    }

    function addManager($user_id, $duration)
    {
        info($this->user->sid, $user_id, $this->id);
        $db = Rooms::getRoomDb();
        $manager_list_key = $this->generateManagerListKey();
        $total_manager_key = self::generateTotalManagerKey();
        $user_manager_list_key = self::generateUserManagerListKey($user_id);
        $time = time() + $duration * 3600;

        //-1 为永久
        if (-1 == $duration) {
            $time = time() + 86400 * 10000;
        } else {
            $db->zadd($total_manager_key, $time, $this->generateRoomManagerKey($user_id));
        }

        $db->zadd($manager_list_key, $time, $user_id);
        $db->zadd($user_manager_list_key, $time, $this->id);
    }

    function deleteManager($user_id)
    {
        info($this->user->sid, $user_id, $this->id);
        $db = Rooms::getRoomDb();;
        $key = $this->generateManagerListKey();
        $total_manager_key = self::generateTotalManagerKey();
        $user_manager_list_key = self::generateUserManagerListKey($user_id);
        $db->zrem($key, $user_id);
        $db->zrem($user_manager_list_key, $this->id);
        $room_manager_key = $this->generateRoomManagerKey($user_id);
        if ($db->zscore($total_manager_key, $room_manager_key)) {
            $db->zrem($total_manager_key, $room_manager_key);
        }


    }

    function updateManager($user_id, $duration)
    {
        info($this->user->sid, $user_id, $this->id);
        $db = Rooms::getRoomDb();
        $manager_list_key = $this->generateManagerListKey();
        $total_manager_key = self::generateTotalManagerKey();
        $user_manager_list_key = self::generateUserManagerListKey($user_id);
        $time = $duration * 3600;
        $db->zincrby($manager_list_key, $time, $user_id);
        $db->zincrby($user_manager_list_key, $time, $this->id);
        $room_manager_key = $this->generateRoomManagerKey($user_id);
        if ($db->zscore($total_manager_key, $room_manager_key)) {
            $db->zincrby($total_manager_key, $time, $room_manager_key);
        }
    }

    function freshManagerNum()
    {
        $db = Rooms::getRoomDb();
        $manager_list_key = $this->generateManagerListKey();
        $manager_ids = $db->zrangebyscore($manager_list_key, '-inf', time());

        if (count($manager_ids) < 1) {
            return;
        }

        info($this->user->sid, $manager_ids, $this->id);
        foreach ($manager_ids as $manager_id) {
            $this->deleteManager($manager_id);
        }
    }

    function findManagers($page, $per_page)
    {
        $this->freshManagerNum();
        $db = Rooms::getRoomDb();
        $manager_list_key = $this->generateManagerListKey();
        $total_entries = $db->zcard($manager_list_key);
        $offset = $per_page * ($page - 1);
        $user_ids = $db->zrevrange($manager_list_key, $offset, $offset + $per_page - 1);
        $users = Users::findByIds($user_ids);
        $users = $this->initRoomManagerInfo($users);
        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';
        return $pagination;
    }

    function initRoomManagerInfo($users)
    {
        $db = Rooms::getRoomDb();
        $manager_list_key = $this->generateManagerListKey();

        foreach ($users as $user) {

            $is_permanent = true;
            $deadline = 0;

            if (!$user->isPermanentManager($this)) {
                $deadline = $db->zscore($manager_list_key, $user->id);
                $is_permanent = false;
            }

            $user->deadline = $deadline;
            $user->is_permanent = $is_permanent;
        }

        return $users;
    }

}