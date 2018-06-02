<?php

class Rooms extends BaseModel
{
    use RoomEnumerations;
    use RoomAttrs;
    use RoomStats;
    use RoomMessages;

    /**
     * @type ProductChannels
     */
    private $_product_channel;
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Audios
     */
    private $_audio;

    /**
     * @type RoomThemes
     */
    private $_room_theme;

    /**
     * @type Unions
     */
    private $_union;

    /**
     * @type Countries
     */
    private $_country;

    /**
     * @type integer
     */
    private $_boom_config_id;


    static function getCacheEndpoint($id)
    {
        return self::config('room_db');
    }

    static function getRoomDb()
    {
        $endpoint = self::config('room_db');
        return XRedis::getInstance($endpoint);
    }

    function beforeCreate()
    {
        $this->uid = $this->generateUid();
    }

    function afterCreate()
    {
        if (!$this->uid) {
            $this->uid = $this->generateUid();
            $this->update();
        }

        if ($this->name && $this->theme_type != ROOM_THEME_TYPE_BROADCAST) {
            self::delay()->updateRoomTypes($this->id);
        }
    }

    function beforeUpdate()
    {

    }

    function afterUpdate()
    {
        if ($this->hasChanged('name') || $this->hasChanged('types')) {

            if ($this->theme_type != ROOM_THEME_TYPE_BROADCAST) {
                self::delay()->updateRoomTypes($this->id);
            }

            self::delay()->updateShieldRoomList($this->id);
        }


    }

    /**
     * 产生 UID
     */
    function generateUid()
    {

        for ($i = 0; $i < 10; $i++) {
            $uid = $this->randUid();
            if (!$uid) {
                continue;
            }
            $lock_key = 'lock_generate_room_uid_' . $uid;
            $hot_cache = self::getHotWriteCache();
            if (!$hot_cache->setnx($lock_key, $uid)) {
                debug('加锁失败', $lock_key);
                continue;
            }
            $hot_cache->expire($lock_key, 3);
            debug('加锁成功', $lock_key);

            return $uid;
        }

        return $this->id;
    }

    function randUid()
    {

        $user_db = Users::getUserDb();
        $not_good_no_uid = 'room_not_good_no_uid_list';
        $offset = mt_rand(0, 100000);
        $uid = $user_db->zrange($not_good_no_uid, $offset, $offset);
        $uid = current($uid);
        if (!$user_db->zrem($not_good_no_uid, $uid)) {
            $user_db->zrem($not_good_no_uid, $uid);
        }

        return $uid;
    }

    static function updateShieldRoomList($room_id)
    {
        $room = Rooms::findFirstById($room_id);

        if ($room->isShieldRoom()) {
            $hot_shield_room_list_key = Rooms::generateShieldHotRoomListKey();
            $hot_cache = self::getHotWriteCache();
            $hot_cache->zrem($hot_shield_room_list_key, $room->id);
        }
    }

    function roomSeats()
    {
        $room_seats = RoomSeats::findPagination(['conditions' => 'room_id=:room_id:',
            'bind' => ['room_id' => $this->id], 'order' => 'rank asc'], 1, 8, 8);

        $data = $room_seats->toJson('room_seats', 'toJson');
        return $data['room_seats'];
    }

    static function createRoom($user, $opts)
    {
        $name = fetch($opts, 'name');
        $room_tag_ids = fetch($opts, 'room_tag_ids');

        $room = new Rooms();
        $room->name = $name;

        //还要判断是否符合规则
        if (isPresent($room_tag_ids)) {

            $room_tags = RoomTags::findByIds($room_tag_ids);
            if (count($room_tags)) {
                $room->room_tag_ids = $room_tag_ids;
                $room_tag_names = [];
                foreach ($room_tags as $room_tag) {
                    $room_tag_names[] = $room_tag->name;
                }

                $room->room_tag_names = implode(',', $room_tag_names);
            }
        }


        $room->user_id = $user->id;
        $room->user = $user;
        $room->status = STATUS_ON;
        $room->product_channel_id = $user->product_channel_id;
        $room->user_type = $user->user_type;
        $room->union_id = $user->union_id;
        $room->union_type = $user->union_type;
        $room->last_at = time();
        $room->chat = true;
        $room->save();

        $user->room_id = $room->id;
        $user->save();

        // 麦位
        for ($i = 1; $i <= 8; $i++) {
            $room_seat = new RoomSeats();
            $room_seat->room_id = $room->id;
            $room_seat->status = STATUS_ON;
            $room_seat->rank = $i;
            $room_seat->microphone = true;
            $room_seat->save();
        }

        return $room;
    }

    function updateRoom($params)
    {
        $name = fetch($params, 'name');

        if (!isBlank($name)) {

            list($res, $name) = BannedWords::checkWord($name);

            if ($res) {
                Chats::sendTextSystemMessage($this->user_id, "您设置的房间名称违反规则,请及时修改");
            }

            $this->name = $name;

        }


        $topic = fetch($params, 'topic');

        if (!isBlank($topic)) {

            list($res, $topic) = BannedWords::checkWord($topic);

            if ($res) {
                Chats::sendTextSystemMessage($this->user_id, "您设置的房间话题违反规则,请及时修改");
            }

            $this->topic = $topic;
        }

        $room_tag_ids = fetch($params, 'room_tag_ids');

        //还要判断是否符合规则
        if (isPresent($room_tag_ids)) {
            $room_tags = RoomTags::findByIds($room_tag_ids);

            if (count($room_tags)) {
                $this->room_tag_ids = $room_tag_ids;


                $room_tag_names = [];
                foreach ($room_tags as $room_tag) {
                    $room_tag_names[] = $room_tag->name;
                }

                $this->room_tag_names = implode(',', $room_tag_names);
            }
        }

        $this->update();
    }

    function bindOnlineToken($user)
    {
        //绑定用户的onlinetoken 长连接使用
        $online_token = $user->online_token;

        if ($online_token) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->setex("room_token_" . $online_token, 7 * 24 * 3600, $this->id);
        }
    }

    function unbindOnlineToken($user)
    {
        //解绑用户的onlinetoken 长连接使用
        $online_token = $user->online_token;
        $room_online_token = "room_token_" . $online_token;

        $hot_cache = Rooms::getHotWriteCache();
        $room_id = $hot_cache->get($room_online_token);

        // 房间相同
        if ($online_token && $this->id == $room_id) {
            $hot_cache->del($room_online_token);
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
        //用户有可能在房间时进入房间
        if ($user->user_role != USER_ROLE_HOST_BROADCASTER) {
            $user->user_role_at = time();
        }

        $user->current_room_id = $this->id;
        $user->current_room_channel_name = $this->channel_name;
        $user->user_role = USER_ROLE_AUDIENCE; // 旁听
        $this->last_at = time();

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
            $this->online_status = STATUS_ON; // 主播是否在线
            if (!$this->isBlocked()) {
                $this->status = STATUS_ON;
            }
        }

        $this->bindOnlineToken($user);
        $this->addUser($user);

        $this->save();
        $user->save();

        if (!$user->isSilent()) {
            Rooms::delay()->statDayEnterRoomUser($this->id, $user->id);
        }
    }

    function exitRoom($user, $unbind = true)
    {

        $this->remUser($user);

        // 房间相同才清除用户信息
        if ($this->id == $user->current_room_id) {

            // 退出所有麦位
            $room_seats = RoomSeats::findByUserId($user->id);
            foreach ($room_seats as $room_seat) {
                $room_seat->user_id = 0;
                $room_seat->save();
            }

            $current_room_seat_id = $user->current_room_seat_id;

            $user->current_room_id = 0;
            $user->current_room_seat_id = 0;
            $user->current_room_channel_name = '';
            $user->user_role = USER_ROLE_NO;
            $user->user_role_at = time();
            $user->save();

            $this->pushExitRoomMessage($user, $current_room_seat_id);
        }

        // 房主
        if ($this->user_id == $user->id) {
            $this->online_status = STATUS_OFF;
            $this->save();
        }

        //修复数据时,不需要解绑,防止用户在别的房间已经生成新的token
        if ($unbind) {
            $this->unbindOnlineToken($user);
        }
    }

    function updateLastAt($user = null)
    {

        $hot_cache = Users::getHotWriteCache();

        // 活跃房间列表
        $key = 'room_active_last_at_list';
        $hot_cache->zadd($key, time(), $this->id);
        $total = $hot_cache->zcard($key);
        if ($total >= 1000) {
            $hot_cache->zremrangebyrank($key, 0, $total - 1000);
        }

        // 活跃用户列表
        $real_user_key = $this->getRealUserListKey();
        if ($user && !$user->isSilent()) {
            $hot_cache->zadd($real_user_key, time(), $user->id);
        }

        // 房间活跃时间
        if (time() - $this->last_at > 15) {
            $this->last_at = time();
            $this->update();
        }
    }

    function kickingRoom($user, $time = 600)
    {
        $this->exitRoom($user);
        $this->forbidEnter($user, $time);
    }

    function addUser($user)
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $real_user_key = $this->getRealUserListKey();
        $enter_key = $this->getEnterRoomUserListKey();

        if (!$user->isSilent()) {
            $hot_cache->zadd($real_user_key, time(), $user->id);
            $hot_cache->zadd($enter_key, time(), $user->id);
        }

        if ($this->user_id == $user->id) {
            $hot_cache->zadd($key, time() + 86400 * 7, $user->id);
        } elseif (USER_ROLE_BROADCASTER == $user->user_role) {
            $hot_cache->zadd($key, time() + 86400 * 3, $user->id);
        } else {
            $hot_cache->zadd($key, time(), $user->id);
        }

        $hot_cache->zadd(Rooms::getTotalRoomUserNumListKey(), $this->user_num, $this->id);
    }

    static function getTotalRoomUserNumListKey()
    {
        return "total_room_user_num_list";
    }

    function remUser($user)
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $real_user_key = $this->getRealUserListKey();
        $enter_key = $this->getEnterRoomUserListKey();

        if (!$user->isSilent()) {
            $hot_cache->zrem($real_user_key, $user->id);
            $hot_cache->zrem($enter_key, $user->id);
        }

        $hot_cache->zrem($key, $user->id);
        if ($this->user_num < 1) {
            $hot_cache->zrem(Rooms::getTotalRoomUserNumListKey(), $this->id);

            if (!$this->isBlocked()) {
                $this->status = STATUS_OFF;
                $this->update();
            }
        } else {
            $hot_cache->zadd(Rooms::getTotalRoomUserNumListKey(), $this->user_num, $this->id);
        }
    }

    function updateUserRank($user, $asc = true)
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();

        $time = time();

        if ($asc) {
            $time += 3 * 86400;
        }

        if (!$hot_cache->zscore($key, $user->id)) {
            info("user_not_in_list", $user->id, $this->id, $key);
            return;
        }

        $hot_cache->zadd($key, $time, $user->id);
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

    //禁止 踢出房间 禁止用户在10分钟内禁入
    function forbidEnter($user, $time = 600)
    {
        $hot_cache = Rooms::getHotWriteCache();

        if (isDevelopmentEnv()) {
            $time = 60;
        }

        $key = "room_forbid_user_room{$this->id}_user{$user->id}";

        $hot_cache->setex($key, $time, 1);
    }

    function addManager($user, $duration)
    {
        $user_id = $user->id;
        $db = Users::getUserDb();
        $manager_list_key = $this->generateManagerListKey();
        $total_manager_key = self::generateTotalManagerKey();
        $user_manager_list_key = self::generateUserManagerListKey($user_id);
        $time = time() + $duration * 3600;
        $is_permanent = false;

        //-1 为永久
        if (-1 == $duration) {
            $time = time() + 86400 * 10000;
            $is_permanent = true;
        } else {

            if (isDevelopmentEnv()) {
                if (1 == $duration || 3 == $duration) {
                    $time = time() + $duration * 60;
                } elseif (24 == $duration) {
                    $time = time() + 5 * 60;
                }
            }

            $db->zadd($total_manager_key, $time, $this->generateRoomManagerKey($user_id));
        }

        $db->zadd($manager_list_key, $time, $user_id);
        $db->zadd($user_manager_list_key, $time, $this->id);

        $room_manager_cache_key = $this->generateManagerCacheKey();

        $db->hset($room_manager_cache_key, $user_id, json_encode(['user_id' => $user_id, 'is_permanent' => $is_permanent,
            'deadline' => $this->calculateUserDeadline($user_id)], JSON_UNESCAPED_UNICODE));

        if ($user && $user->isInRoom($this)) {
            $user_role = USER_ROLE_MANAGER;
            $user->user_role = $user_role;
            $user->update();
        }
    }

    function deleteManager($user_id)
    {
        $user = Users::findFirstById($user_id);

        if (!$user) {
            return;
        }

        $db = Users::getUserDb();;
        $key = $this->generateManagerListKey();
        $total_manager_key = self::generateTotalManagerKey();
        $user_manager_list_key = self::generateUserManagerListKey($user_id);
        $db->zrem($key, $user_id);
        $db->zrem($user_manager_list_key, $this->id);
        $room_manager_key = $this->generateRoomManagerKey($user_id);
        $room_manager_cache_key = $this->generateManagerCacheKey();

        $db->hdel($room_manager_cache_key, $user_id);

        if ($db->zscore($total_manager_key, $room_manager_key)) {
            $db->zrem($total_manager_key, $room_manager_key);
        }

        if ($user->isInRoom($this)) {
            $user_role = USER_ROLE_AUDIENCE;

            if ($user->current_room_seat_id) {
                $user_role = USER_ROLE_BROADCASTER;
            }

            $user->user_role = $user_role;
            $user->update();
        }
    }

    function updateManager($user_id, $duration)
    {
        $db = Users::getUserDb();
        $manager_list_key = $this->generateManagerListKey();
        $total_manager_key = self::generateTotalManagerKey();
        $user_manager_list_key = self::generateUserManagerListKey($user_id);
        $room_manager_cache_key = $this->generateManagerCacheKey();
        $room_manager_key = $this->generateRoomManagerKey($user_id);
        $time = $duration * 3600;

        if (isDevelopmentEnv()) {
            $time = $duration * 60;
        }

        $db->zincrby($manager_list_key, $time, $user_id);
        $db->zincrby($user_manager_list_key, $time, $this->id);

        if ($db->zscore($total_manager_key, $room_manager_key)) {
            $db->zincrby($total_manager_key, $time, $room_manager_key);
        }

        $cache = $db->hget($room_manager_cache_key, $user_id);

        if ($cache) {

            $cache = json_decode($cache, true);
            $cache['deadline'] = $this->calculateUserDeadline($user_id);

            $db->hset($room_manager_cache_key, $user_id, json_encode($cache, JSON_UNESCAPED_UNICODE));
        }
    }

    function freshManagerNum()
    {
        $db = Users::getUserDb();
        $manager_list_key = $this->generateManagerListKey();
        $manager_ids = $db->zrangebyscore($manager_list_key, '-inf', time());

        if (count($manager_ids) < 1) {
            return;
        }

        foreach ($manager_ids as $manager_id) {
            $this->deleteManager($manager_id);
        }
    }

    function findManagers($is_simple = false)
    {
        $this->freshManagerNum();
        $db = Users::getUserDb();
        $manager_list_key = $this->generateManagerListKey();
        $user_ids = $db->zrevrange($manager_list_key, 0, -1);
        $users = Users::findByIds($user_ids);
        $users = $this->initRoomManagerInfo($users);
        $managers = [];

        foreach ($users as $user) {

            if ($is_simple) {
                $managers[] = $user->toRoomManagerSimpleJson();
            } else {
                $managers[] = $user->toRoomManagerJson();
            }
        }

        return $managers;
    }

    function initRoomManagerInfo($users)
    {
        $db = Users::getUserDb();
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

    function addOnlineSilentRoom()
    {
        $hot_cache = self::getHotWriteCache();
        $key = self::getOnlineSilentRoomKey();
        $time = $this->calculateExpireTime();
        debug($time, $this->id);
        $hot_cache->zadd($key, $time, $this->id);
    }

    function rmOnlineSilentRoom()
    {
        $hot_cache = self::getHotWriteCache();
        $key = self::getOnlineSilentRoomKey();
        debug($this->id);
        $hot_cache->zrem($key, $this->id);
    }

    function updateOnlineSilentRoom($time)
    {
        $hot_cache = self::getHotWriteCache();
        $key = self::getOnlineSilentRoomKey();
        debug($this->id);
        $hot_cache->zadd($key, $time, $this->id);
    }

    static function enterSilentRoom($room_id, $user_id)
    {
        $room = Rooms::findFirstById($room_id);
        $user = Users::findFirstById($user_id);

        if (!$room || !$user) {
            Rooms::deleteWaitEnterSilentRoomList($user_id);
            info("Exce", $room_id, $user_id);
            return false;
        }

        if ($user->isInAnyRoom()) {
            Rooms::deleteWaitEnterSilentRoomList($user_id);
            info("user_in_other_room", $user->id, $user->current_room_id, $room_id);
            return false;
        }

        if ($user->isRoomHost($room)) {
            $room->addOnlineSilentRoom();
        } elseif ($room->isActive() && ($room->getRealUserNum() < 1 || $room->user_agreement_num < 1)) {

            if (isProduction()) {
                Rooms::deleteWaitEnterSilentRoomList($user_id);
                info("room_no_real_user", $room_id, $user_id, $room->getRealUserNum(), $room->user_agreement_num);
                return false;
            }

        }

        $room->enterRoom($user);
        Rooms::deleteWaitEnterSilentRoomList($user_id);

        $room->pushEnterRoomMessage($user);
    }

    static function asyncExitSilentRoom($room_id, $user_id)
    {
        $room = Rooms::findFirstById($room_id);
        $user = Users::findFirstById($user_id);

        if (!$user || !$room) {
            info("no_user", $room_id, $user_id);
            return;
        }

        $room->exitSilentRoom($user);
    }

    function exitSilentRoom($user)
    {

        if (!$user) {
            info("Exce", $this->id, $user->sid);
            return false;
        }

        $current_room_seat_id = $user->current_room_seat_id;

        $this->exitRoom($user);

        if ($user->isRoomHost($this)) {
            $this->rmOnlineSilentRoom();
        }

        ////$this->pushExitRoomMessage($user, $current_room_seat_id);
    }

    function findRealUser()
    {
        if ($this->getRealUserNum() < 1) {
            info("user_real_num < 1");
            return null;
        }

        $hot_cache = self::getHotReadCache();
        $key = $this->getRealUserListKey();
        $user_ids = $hot_cache->zrevrange($key, 0, -1);
        $index = array_rand($user_ids);
        $user_id = $user_ids[$index];
        $user = Users::findFirstById($user_id);

        return $user;
    }

    function findTotalRealUsers()
    {
        if ($this->getRealUserNum() < 1) {
            info("user_real_num < 1");
            return [];
        }

        $hot_cache = self::getHotReadCache();
        $key = $this->getRealUserListKey();
        $user_ids = $hot_cache->zrevrange($key, 0, -1);
        $users = Users::findByIds($user_ids);

        return $users;
    }

    static function autoActiveRoom($room_id)
    {

        $room = Rooms::findFirstById($room_id);
        if (!$room) {
            return;
        }

        $silent_users = $room->findSilentUsers();
        if (count($silent_users) > 0) {
            foreach ($silent_users as $silent_user) {
                $silent_user->autoActiveRoom($room);
            }
        }

        if ($room->isSilent()) {
            $room->addSilentUsers();
        }
    }

    function addSilentUsers()
    {
        if ($this->lock) {
            return;
        }

        if ($this->isSilent() && $this->getExpireTime() <= time() + 10) {
            info("silent_room_already_expire", $this->id, date("Ymd h:i:s", $this->getExpireTime()));
            return;
        }

        $real_user_num = $this->getRealUserNum();
        $user_num = $this->getUserNum();

        if (!$this->isOnline() && $real_user_num < 1) {
            info("room_is_offline", $this->id);
            return;
        }

        if (($real_user_num <= 5 && $user_num >= 10 || $real_user_num > 5 && $user_num >= 30) &&
            $real_user_num < 20
        ) {
            info("user_is_full", $real_user_num, $user_num);
            return;
        }

        $rand = $real_user_num <= 5 ? 5 : 8;

        $limit = mt_rand(1, $rand);
        $users = $this->selectSilentUsers($limit);

        foreach ($users as $user) {

            if (!$this->canEnter($user)) {
                info("user_can_not_enter_room", $this->id, $user->id);
                continue;
            }

            if ($user->isInAnyRoom()) {
                info("user_in_other_room", $user->id, $user->current_room_id, $this->id);
                continue;
            }

            $delay_time = mt_rand(1, 60);
            info($this->id, $user->id, $delay_time);
            Rooms::addWaitEnterSilentRoomList($user->id);
            Rooms::delay($delay_time)->enterSilentRoom($this->id, $user->id);
        }

        info($this->id, $limit, count($users));

    }

    function selectSilentUsers($limit)
    {
        $cond['conditions'] = "(current_room_id = 0 or current_room_id is null) and user_type = :user_type: 
        and id <> :user_id: and avatar_status = :avatar_status:";
        $cond['bind'] = ['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS,
            'user_id' => $this->user_id];
        $cond['limit'] = $limit;

        $filter_user_ids = Rooms::getWaitEnterSilentRoomUserIds();

        if (count($filter_user_ids) > 0) {
            $cond['conditions'] .= " and id not in (" . implode(',', $filter_user_ids) . ')';
        }

        $users = Users::find($cond);

        return $users;
    }

    //记录沉默用户进入房间 异步进入后在队列中删除
    static function addWaitEnterSilentRoomList($user_id)
    {
        $hot_cache = self::getHotWriteCache();
        $hot_cache->zadd('wait_enter_silent_room_list', time(), $user_id);
    }

    static function deleteWaitEnterSilentRoomList($user_id)
    {
        $hot_cache = self::getHotWriteCache();
        $hot_cache->zrem('wait_enter_silent_room_list', $user_id);
    }

    static function addUserAgreement($room_id, $user_agreement_num = 0)
    {

        $room = Rooms::findFirstById($room_id);
        if (!$room || $room->user_agreement_num < 1) {
            return;
        }

        $enter_num = 0;
        $users = $room->selectSilentUsers($user_agreement_num + 100);
        foreach ($users as $user) {

            if ($user->isInAnyRoom()) {
                info("user_in_other_room", $user->id, $user->current_room_id, $room->id);
                continue;
            }

            $delay_time = mt_rand(1, 120);
            if (isDevelopmentEnv()) {
                $delay_time = mt_rand(1, 30);
            }

            info($room->id, $user->id, $delay_time);
            Rooms::addWaitEnterSilentRoomList($user->id);
            Rooms::delay($delay_time)->enterSilentRoom($room->id, $user->id);
            $enter_num++;

            if ($enter_num >= $user_agreement_num) {
                break;
            }
        }

        info($room->id, $room->user_agreement_num);
    }

    static function deleteUserAgreement($room_id, $delete_num = 0)
    {
        $room = Rooms::findFirstById($room_id);
        if (!$room) {
            return;
        }

        $silent_users = $room->findSilentUsers();
        if (!$delete_num) {
            $delete_num = count($silent_users);
        }

        $num = 0;
        foreach ($silent_users as $user) {

            $delay_time = mt_rand(1, 180);
            if (isDevelopmentEnv()) {
                $delay_time = mt_rand(1, 30);
            }

            Rooms::delay($delay_time)->asyncExitSilentRoom($room->id, $user->id);
            $num++;
            if ($num >= $delete_num) {
                break;
            }

        }
    }

    function addFilterUser($user_id)
    {
        $db = Users::getUserDb();
        $expire = 2;
        $db->setex($this->generateFilterUserKey($user_id), $expire, time());
    }

    function checkFilterUser($user_id)
    {
        $db = Users::getUserDb();

        $key = $this->generateFilterUserKey($user_id);
        if ($db->get($key)) {
            return true;
        }
        return false;
    }

    static function newSearchHotRooms($user, $page, $per_page)
    {
        $register_time = time() - $user->register_at;
        $time = 60 * 15;
        if (isProduction()) {
            $time = 86400;
        }

        if ($user->isShieldHotRoom()) {

            $hot_room_list_key = Rooms::getHotRoomListKey();
        } else {

            $hot_room_list_key = Rooms::getTotalRoomListKey(); //新的用户总的队列
            if ($register_time <= $time) {
                $hot_room_list_key = Rooms::getNewUserHotRoomListKey(); //新用户房间
            }
        }

        $hot_cache = Users::getHotWriteCache();
        $room_ids = $hot_cache->zrevrange($hot_room_list_key, 0, -1);
        $shield_room_ids = $user->getShieldRoomIds();

        if ($shield_room_ids) {
            $room_ids = array_diff($room_ids, $shield_room_ids);
        }

        if ($user && $user->isIosAuthVersion()) {
            return Rooms::search($user, $page, $per_page, ['filter_ids' => $room_ids]);
        }

        $total_entries = count($room_ids);
        $offset = $per_page * ($page - 1);
        $room_ids = array_slice($room_ids, $offset, $per_page);
        $rooms = Rooms::findByIds($room_ids);
        $pagination = new PaginationModel($rooms, $total_entries, $page, $per_page);
        $pagination->clazz = 'Rooms';

        return $pagination;
    }

    static function searchHotRooms($user, $page, $per_page)
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();

        $green_hot_room_list_key = Rooms::generateGreenHotRoomListKey();
        $novice_hot_room_list_key = Rooms::generateNoviceHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();
        $shield_room_ids = [];

        if (isPresent($user)) {

            $register_time = time() - $user->register_at;
            $start_at = 60 * 15;
            $end_at = 60 * 20;

            if (isProduction()) {
                $start_at = 3600;
                $end_at = 86400;
            }

            if ($register_time <= $start_at) {
                $hot_room_list_key = $green_hot_room_list_key;
            } elseif ($register_time > $start_at && $register_time <= $end_at) {
                $hot_room_list_key = $novice_hot_room_list_key;
            }

            if ($user->isShieldHotRoom()) {
                $hot_room_list_key = Rooms::generateShieldHotRoomListKey();
            }

            $shield_room_ids = $user->getShieldRoomIds();
        }

        $total_room_ids = $hot_cache->zrange($hot_room_list_key, 0, -1);
        $total_user_num_key = Rooms::getTotalRoomUserNumListKey();

        foreach ($total_room_ids as $room_id) {

            if ($hot_cache->zscore($total_user_num_key, $room_id) < 1) {
                $hot_cache->zrem($hot_room_list_key, $room_id);
            }
        }

        if ($user && $user->isIosAuthVersion()) {
            $rooms = \Rooms::iosAuthVersionRooms($user, $page, $per_page);
            return $rooms;
        }

        $total_entries = $hot_cache->zcard($hot_room_list_key);

        $offset = $per_page * ($page - 1);
        if ($offset > $total_entries - 1) {
            $offset = $total_entries - 1;
        }

        $room_ids = $hot_cache->zrevrange($hot_room_list_key, 0, -1);

        if ($shield_room_ids) {
            $room_ids = array_diff($room_ids, $shield_room_ids);
        }

        $rooms = Rooms::findByIds($room_ids);

        $pagination = new PaginationModel($rooms, $total_entries, $page, $per_page);
        $pagination->clazz = 'Rooms';

        return $pagination;
    }

    //判断麦位上没有用户
    function checkRoomSeat()
    {
        if ($this->isBroadcast()) {
            return true;
        }

        $room_seat = RoomSeats::findFirst(['conditions' => 'room_id = :room_id: and user_id > 0',
            'bind' => ['room_id' => $this->id]]);

        if ($room_seat) {
            return true;
        }

        return false;
    }

    static function searchRooms($opts, $page, $per_page)
    {

        $product_channel_id = fetch($opts, 'product_channel_id');
        $uid = fetch($opts, 'uid');
        $name = fetch($opts, 'name');
        $new = fetch($opts, 'new');
        $hot = fetch($opts, 'hot');

        //限制搜索条件
        $cond = [
            'conditions' => 'online_status = ' . STATUS_ON . ' and status = ' . STATUS_ON . ' and product_channel_id = :product_channel_id:',
            'bind' => ['product_channel_id' => $product_channel_id],
            'order' => 'last_at desc, user_type asc'
        ];

        if ($new == STATUS_ON) {
            $cond['conditions'] .= " and new = " . STATUS_ON;
        }

        if ($hot == STATUS_ON) {
            $cond['conditions'] .= " and hot = " . STATUS_ON;
        }

        if ($uid) {
            $cond['conditions'] .= " and (uid = :uid:) ";
            $cond['bind']['uid'] = $uid;
        }

        if ($name) {
            $cond['conditions'] .= " and (name like :name:) ";
            $cond['bind']['name'] = "%{$name}%";
        }

        debug($cond);


        $rooms = Rooms::findPagination($cond, $page, $per_page);

        return $rooms;
    }

    static function updateRoomTypes($room_id)
    {
        $room_category_words = RoomCategoryKeywords::find(['order' => 'id desc']);
        $room_categories = RoomCategories::find(['conditions' => "status = " . STATUS_ON, 'order' => 'id desc']);

        $room_category_word_names = [];
        $room_category_names = [];

        if ($room_category_words) {
            foreach ($room_category_words as $room_category_word) {
                $room_category_word_names[$room_category_word->id] = $room_category_word->name;
            }
        }

        if ($room_categories) {
            foreach ($room_categories as $room_category) {
                $room_category_names[$room_category->id] = $room_category->name;
            }
        }

        debug($room_category_word_names, $room_category_names);


        $room = Rooms::findFirstById($room_id);

        $name = $room->name;


        $room_category_ids = [];
        $select_room_category_names = [];
        $select_room_category_types = [];
        $parent_room_category_ids = [];

        if ($room_category_names) {
            foreach ($room_category_names as $room_category_id => $room_category_name) {

                $room_category_name = preg_replace('/\./', '', $room_category_name);

                if (!$room_category_name) {
                    continue;
                }


                if (preg_match("/$room_category_name/i", $name)) {

                    $room_category = RoomCategories::findFirstById($room_category_id);

                    $room_category_ids[] = $room_category->id;
                    $select_room_category_names[] = $room_category->name;
                    $select_room_category_types[] = $room_category->type;

                    $parent_room_category_id = $room_category->parent_id;

                    if (!in_array($parent_room_category_id, $room_category_ids) && $parent_room_category_id) {
                        $select_room_category_types[] = $room_category->parent->type;
                        $select_room_category_names[] = $room_category->parent->name;
                        $room_category_ids[] = $parent_room_category_id;
                        $parent_room_category_ids[] = $parent_room_category_id;
                    }
                }
            }
        }

        if ($room_category_word_names) {

            foreach ($room_category_word_names as $room_category_word_id => $room_category_word_name) {

                $room_category_word_name = preg_replace('/\./', '', $room_category_word_name);

                if (!$room_category_word_name) {
                    continue;
                }


                if (preg_match("/$room_category_word_name/i", $name)) {
                    $room_category_word = RoomCategoryKeywords::findFirstById($room_category_word_id);
                    $room_category = $room_category_word->room_category;

                    $parent_room_category_id = $room_category->parent_id;

                    if (!in_array($room_category->id, $room_category_ids)) {
                        $room_category_ids[] = $room_category->id;
                        $select_room_category_names[] = $room_category->name;
                        $select_room_category_types[] = $room_category->type;
                    }

                    if (!in_array($parent_room_category_id, $room_category_ids) && $parent_room_category_id) {
                        $room_category_ids[] = $parent_room_category_id;
                        $select_room_category_names[] = $room_category->parent->name;
                        $select_room_category_types[] = $room_category->parent->type;
                        $parent_room_category_ids[] = $parent_room_category_id;
                    }
                }
            }
        }

        $room_category_ids = array_unique($room_category_ids);
        $select_room_category_names = array_filter(array_unique($select_room_category_names));
        $select_room_category_types = array_filter(array_unique($select_room_category_types));
        $parent_room_category_ids = array_filter(array_unique($parent_room_category_ids));


        $room_category_ids = implode(',', $room_category_ids);
        $select_room_category_types = implode(',', $select_room_category_types);
        $select_room_category_names = implode(',', $select_room_category_names);

        if ($room_category_ids) {
            $room_category_ids = ',' . $room_category_ids . ",";
        }

        if ($select_room_category_names) {
            $select_room_category_names = ',' . $select_room_category_names . ',';
        }

        if ($select_room_category_types) {
            $select_room_category_types = ',' . $select_room_category_types . ',';
        }

        $parent_room_categories = RoomCategories::findByIds($parent_room_category_ids);

        if ($parent_room_categories) {

            foreach ($parent_room_categories as $parent_room_category) {
                $room->saveRoomIdsByCategoryType($parent_room_category->type);
            }

            foreach ($room_categories as $room_category) {

                if (!in_array($room_category->id, $parent_room_category_ids)) {
                    $room->delRoomIdsByCategoryType($room_category->type);
                }
            }

        } else {

            foreach ($room_categories as $room_category) {
                $room->delRoomIdsByCategoryType($room_category->type);
            }
        }

        info($select_room_category_names, $select_room_category_types);
        $room->room_category_ids = $room_category_ids;
        $room->room_category_names = $select_room_category_names;
        $room->room_category_types = $select_room_category_types;
        $room->update();
    }

    function saveRoomIdsByCategoryType($type)
    {
        $hot_cache = Rooms::getHotWriteCache();
        $key = "room_category_type_{$type}_list";
        $hot_cache->zadd($key, time(), $this->id);
    }

    function delRoomIdsByCategoryType($type)
    {
        if (!$type) {
            return;
        }

        $hot_cache = Rooms::getHotWriteCache();
        $key = "room_category_type_{$type}_list";

        if ($hot_cache->zscore($key, $this->id)) {
            $hot_cache->zrem($key, $this->id);
        }
    }

    static function search($user, $page, $per_page, $opts = [])
    {
        $user_id = $user->id;
        $new = intval(fetch($opts, 'new', 0));
        $broadcast = intval(fetch($opts, 'broadcast', 0));
        $follow = intval(fetch($opts, 'follow', 0));
        $filter_ids = fetch($opts, 'filter_ids', []);

        debug($user->id, $page, $per_page, $opts);

        //限制搜索条件
        $cond = [
            'conditions' => 'online_status = :online_status: and status = :status: and user_id <> :user_id:',
            'bind' => ['online_status' => STATUS_ON, 'status' => STATUS_ON, 'user_id' => $user_id],
            'order' => 'last_at desc, user_type asc'
        ];

        if (STATUS_ON == $broadcast) {
            $theme_types = ROOM_THEME_TYPE_BROADCAST . ',' . ROOM_THEME_TYPE_USER_BROADCAST;
            $cond['conditions'] .= " and theme_type in ($theme_types)";
        }

        if (STATUS_ON == $follow) {

            $user_ids = $user->followUserIds();
            if (count($user_ids) > 0) {
                $cond['conditions'] .= " and user_id in (" . implode(',', $user_ids) . ") ";
            }
        }

        if (!$new && !$broadcast && !$follow) {
            $search_type = '';

            foreach (\Rooms::$TYPES as $key => $value) {

                $type_value = fetch($opts, $key);

                if (STATUS_ON == $type_value) {
                    $search_type = $key;
                    break;
                }
            }

            if ($search_type) {
                $cond['conditions'] .= " and room_category_types like :types:";
                $cond['bind']['types'] = "%" . $search_type . "%";

            }
        }

        $shield_room_ids = $user->getShieldRoomIds();
        if ($shield_room_ids) {
            $filter_ids = array_unique(array_merge($filter_ids, $shield_room_ids));
        }

        if (count($filter_ids) > 0) {
            $cond['conditions'] .= " and id not in (" . implode(',', $filter_ids) . ")";
            return \Rooms::findPagination($cond, $page, $per_page);
        }


        $rooms = \Rooms::findPagination($cond, $page, $per_page);

        if (!isDevelopmentEnv() && $rooms->total_entries < 2) {

            $cond = [
                'conditions' => 'online_status = ' . STATUS_ON . ' and status = ' . STATUS_ON,
                'order' => 'last_at desc, user_type asc'
            ];

            $rooms = \Rooms::findPagination($cond, $page, $per_page);
        }

        return $rooms;
    }

    static function searchTopRoom()
    {
        $cond = ['conditions' => 'top = :top:', 'bind' => ['top' => STATUS_ON]];
        $rooms = Rooms::findPagination($cond, 1, 2);
        return $rooms;
    }

    static function addGameWhiteList($room_id)
    {
        if ($room_id) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->zadd("room_game_white_list", time(), $room_id);
        }
    }

    static function deleteGameWhiteList($room_id)
    {
        if ($room_id) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->zrem("room_game_white_list", $room_id);
        }
    }

    static function searchGangUpRooms($user, $page, $per_page)
    {
        $cond['conditions'] = "online_status = :online_status: and status = :status: and room_category_types like :room_category_types: and lock = :lock:";
        $cond['bind'] = ['online_status' => STATUS_ON, 'status' => STATUS_ON, 'room_category_types' => "%,gang_up,%", 'lock' => 'false'];
        $cond['order'] = 'last_at desc';

        $shield_room_ids = $user->getShieldRoomIds();
        if ($shield_room_ids) {
            $cond['conditions'] .= " and id not in (" . implode(",", $shield_room_ids) . ")";
        }

        $gang_up_rooms = \Rooms::findPagination($cond, $page, $per_page);
        \Users::findBatch($gang_up_rooms);

        $gang_up_rooms_json = $gang_up_rooms->toJson('gang_up_rooms', 'toSimpleJson');

        return $gang_up_rooms_json;
    }

    static function remHotRoomList($room)
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $green_hot_room_list_key = Rooms::generateGreenHotRoomListKey();
        $novice_hot_room_list_key = Rooms::generateNoviceHotRoomListKey();

        $hot_cache = Users::getHotWriteCache();

        $hot_cache->zrem($hot_room_list_key, $room->id);
        $hot_cache->zrem($green_hot_room_list_key, $room->id);
        $hot_cache->zrem($novice_hot_room_list_key, $room->id);
    }

    static function addForbiddenList($room, $opts = [])
    {
        $forbidden_time = fetch($opts, 'forbidden_time');
        $forbidden_reason = fetch($opts, 'forbidden_reason');
        $operator = fetch($opts, 'operator');

        $hot_cache = Rooms::getHotWriteCache();
        $user_db = Users::getUserDb();
        $key = "room_forbidden_to_hot_list";
        $record_key = "room_forbidden_records_room_id_" . $room->id;
        $time = time();

        $hot_cache->zadd($key, $time, $room->id);

        if ($forbidden_time) {

            $expire = $forbidden_time * 3600;

            if (isDevelopmentEnv()) {
                $expire = $forbidden_time * 10;
            }

            $hot_cache->setex("room_forbidden_to_hot_room_id_{$room->id}", $expire, $time);

            $record = date("Y-m-d H:i:s", $time) . "禁止上热门原因:" . $forbidden_reason . ";操作者:" . $operator->username . ";禁止时长:" . $forbidden_time . "小时";
            $user_db->zadd($record_key, $time, $record);

        } else {
            $room->hot = STATUS_FORBIDDEN;
            $room->update();

            $record = date("Y-m-d H:i:s", $time) . "禁止上热门原因:" . $forbidden_reason . ";操作者:" . $operator->username . ";禁止时长:永久禁止";
            $user_db->zadd($record_key, $time, $record);
        }

        Rooms::remHotRoomList($room);
    }

    static function remForbiddenList($room, $opts = [])
    {
        $operator = fetch($opts, 'operator');

        $hot_cache = Rooms::getHotWriteCache();
        $user_db = Users::getUserDb();
        $key = "room_forbidden_to_hot_list";
        $time = time();
        $record_key = "room_forbidden_records_room_id_" . $room->id;

        $hot_cache->zrem($key, $room->id);

        if ($operator) {
            $record = date("Y-m-d H:i:s", $time) . "取消禁止上热门;操作者:" . $operator->username;
            $user_db->zadd($record_key, $time, $record);
        }
    }

    function setHotRoomScoreRatio($ratio)
    {
        $ratio = floatval($ratio);
        $user_db = Users::getUserDb();
        $key = "hot_room_score_ratio_room_id_{$this->id}";

        if (!$ratio) {

            $user_db->del($key);

            return;
        }

        $user_db->set($key, $ratio);
    }

    static function updateHotRoomList($all_room_ids, $opts = [])
    {
        $hot_cache = Rooms::getHotWriteCache();
        $hot_room_list_key = Rooms::getHotRoomListKey(); //正常房间
        $new_user_hot_rooms_list_key = Rooms::getNewUserHotRoomListKey(); //新用户房间
        $old_user_pay_hot_rooms_list_key = Rooms::getOldUserPayHotRoomListKey(); //充值老用户队列
        $old_user_no_pay_hot_rooms_list_key = Rooms::getOldUserNoPayHotRoomListKey(); //未充值老用户队列
        $total_new_hot_room_list_key = Rooms::getTotalRoomListKey(); //新的用户总的队列

        $room_ids = [];
        $shield_room_ids = [];
        $hot_room_ids = [];
        $new_user_room_ids = [];
        $total_num = count($all_room_ids);
        $per_page = 100;
        if (isDevelopmentEnv()) {
            $per_page = 3;
        }

        $loop_num = ceil($total_num / $per_page);
        $offset = 0;

        for ($i = 0; $i < $loop_num; $i++) {

            $slice_ids = array_slice($all_room_ids, $offset, $per_page);
            info($total_num, $offset, $per_page, $slice_ids);
            $offset += $per_page;
            $rooms = Rooms::findByIds($slice_ids);

            foreach ($rooms as $room) {

                if (!$room->canToHot(2)) {
                    continue;
                }

                $total_score = $room->getTotalScore();

                if ($total_score < 1 && !$room->isHot()) {
                    continue;
                }

                $room_ids[$room->id] = $total_score;

                if ($room->isNoviceRoom()) {
                    $new_user_room_ids[$room->id] = $total_score;
                }

                if ($room->isShieldRoom()) {
                    $shield_room_ids[] = $room->id;
                }

                if (isDevelopmentEnv()) {
                    $room_score_key = "hot_room_score_list_room_id{$room->id}";
                    $hot_cache->zadd($room_score_key, time(), date("Y-m-d Hi") . "得分:" . $total_score);
                    $hot_cache->expire($room_score_key, 3600 * 3);
                }
            }
        }

        uksort($room_ids, function ($a, $b) use ($room_ids) {

            if ($room_ids[$a] > $room_ids[$b]) {
                return -1;
            }

            return 1;
        });


        $shield_room_num = 30;
        $total_room_num = 30;
        $new_user_shield_room_num = 3;

        if (isDevelopmentEnv()) {
            $shield_room_num = 2;
            $new_user_shield_room_num = 1;
        }

        //$shield_room_ids = array_slice($shield_room_ids, 0, $shield_room_num, true);
        $room_ids = array_slice($room_ids, 0, $total_room_num, true);

        $lock = tryLock($hot_room_list_key, 1000);

        $hot_cache->zclear($hot_room_list_key);
        $hot_cache->zclear($new_user_hot_rooms_list_key);
        $hot_cache->zclear($old_user_pay_hot_rooms_list_key);
        $hot_cache->zclear($old_user_no_pay_hot_rooms_list_key);
        $hot_cache->zclear($total_new_hot_room_list_key);
        $max_score = 0;

        if (isPresent($room_ids)) {
            $max_score = max($room_ids);
        }

        foreach ($room_ids as $room_id => $score) {

            $hot_cache->zadd($total_new_hot_room_list_key, $score, $room_id);

            if (!in_array($room_id, $shield_room_ids)) {
                $hot_cache->zadd($hot_room_list_key, $score, $room_id);
            }

            if (array_key_exists($room_id, $new_user_room_ids)) {
                $score = $score + $max_score;
            }

            debug($max_score, $score);
            $hot_cache->zadd($new_user_hot_rooms_list_key, $score, $room_id);
        }

        unlock($lock);
    }


}