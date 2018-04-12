<?php

class Rooms extends BaseModel
{
    use RoomEnumerations;
    use RoomInternational;

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

    static $STATUS = [STATUS_OFF => '下架', STATUS_ON => '上架', STATUS_BLOCKED => '封闭'];
    static $USER_TYPE = [USER_TYPE_ACTIVE => '活跃', USER_TYPE_SILENT => '沉默'];
    static $THEME_TYPE = [ROOM_THEME_TYPE_NORMAL => '正常', ROOM_THEME_TYPE_BROADCAST => '电台', ROOM_THEME_TYPE_USER_BROADCAST => '个人电台'];
    static $ONLINE_STATUS = [STATUS_OFF => '离线', STATUS_ON => '在线'];
    static $HOT = [STATUS_OFF => '否', STATUS_ON => '是', STATUS_FORBIDDEN => '禁止上热门'];
    static $TOP = [STATUS_OFF => '否', STATUS_ON => '是'];
    static $NEW = [STATUS_OFF => '否', STATUS_ON => '是'];
    static $TYPES = ['gang_up' => '开黑', 'friend' => '交友', 'amuse' => '娱乐', 'sing' => '唱歌'];
    static $NOVICE = [STATUS_OFF => '否', STATUS_ON => '是']; //新手房间
    static $GREEN = [STATUS_OFF => '否', STATUS_ON => '是']; //绿色房间

    function beforeCreate()
    {

    }

    function afterCreate()
    {
        if (!$this->uid) {
            $this->uid = $this->generateUid();
            $this->update();
        }
    }

    function beforeUpdate()
    {

    }

    function afterUpdate()
    {

    }

    /**
     * 产生 UID
     */
    function generateUid()
    {
        if (isDevelopmentEnv()) {
            return $this->id + 100000;
        }

        return $this->id;
    }

    function isHot()
    {
        return $this->hot == STATUS_ON;
    }

    function isForbiddenHot()
    {
        return $this->hot == STATUS_FORBIDDEN;
    }

    function isBlocked()
    {
        return $this->status == STATUS_BLOCKED;
    }

    function isNoviceRoom()
    {
        return STATUS_ON == $this->novice;
    }

    function isGreenRoom()
    {
        return STATUS_ON == $this->green;
    }

    function toSimpleJson()
    {
        $user = $this->user;

        return ['id' => $this->id, 'uid' => $this->uid, 'name' => $this->name, 'topic' => $this->topic, 'chat' => $this->chat,
            'user_id' => $this->user_id, 'sex' => $user->sex, 'avatar_small_url' => $user->avatar_small_url,
            'avatar_url' => $user->avatar_url, 'avatar_big_url' => $user->avatar_big_url, 'nickname' => $user->nickname, 'age' => $user->age,
            'monologue' => $user->monologue, 'channel_name' => $this->channel_name, 'online_status' => $this->online_status,
            'user_num' => $this->user_num, 'lock' => $this->lock, 'created_at' => $this->created_at, 'last_at' => $this->last_at
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
        return ['channel_name' => $this->channel_name, 'user_num' => $this->user_num, 'sex' => $user->sex,
            'avatar_small_url' => $user->avatar_small_url, 'nickname' => $user->nickname, 'age' => $user->age,
            'monologue' => $user->monologue, 'room_seats' => $room_seat_datas, 'managers' => $this->findManagers(),
            'theme_image_url' => $this->theme_image_url, 'uid' => $this->uid
        ];
    }

    function toDetailJson()
    {
        $opts = [
            'audio_id' => $this->audio_id,
            'user_nickname' => $this->user->nickname,
            'user_sex_text' => $this->user->sex_text,
            'user_mobile' => $this->user->mobile,
            'status_text' => $this->status_text,
            'online_status_text' => $this->online_status_text,
            'user_type_text' => $this->user->type_text,
            'last_at_text' => $this->last_at_text,
            'chat_text' => $this->chat_text,
            'lock_text' => $this->lock_text,
            'hot_text' => $this->hot_text,
            'user_agreement_num' => $this->user->agreement_num,
            'union_id' => $this->union_id,
            'union_name' => $this->union_name,
            'type_text' => $this->union_type_text,
            'theme_type' => $this->theme_type,
            'top_text' => $this->top_text
        ];

        return array_merge($opts, $this->toJson());
    }

    function toBasicJson()
    {
        return ['id' => $this->id, 'uid' => $this->uid, 'lock' => $this->lock, 'channel_name' => $this->channel_name, 'name' => $this->name];
    }

    function toInternationalDetailJson()
    {

        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'name' => $this->name,
            'topic' => $this->topic,
            'chat' => $this->chat,
            'online_status' => $this->online_status,
            'channel_name' => $this->channel_name,
            'lock' => $this->lock,
            'created_at' => $this->created_at,
            'last_at' => $this->last_at,
            'user_num' => $this->user_num,
            'theme_type' => $this->theme_type,
            'audio_id' => $this->audio_id,
            'theme_image_url' => $this->theme_image_url,
            'room_theme_id' => $this->room_theme_id,
            'user_info' => $this->userInfo(),
            'room_seats' => $this->roomSeats(),
            'managers' => $this->findManagers()
        ];
    }

    function userInfo()
    {

        $user = $this->user;

        return [
            'id' => $user->id,
            'uid' => $user->uid,
            'nickname' => $user->nickname,
            'avatar_small_url' => $user->avatar_small_url,
            'sex' => $user->sex,
            'age' => $user->age,
            'monologue' => $user->monologue
        ];
    }

    function roomSeats()
    {
        $room_seats = RoomSeats::find([
            'conditions' => 'room_id=:room_id:',
            'bind' => ['room_id' => $this->id],
            'order' => 'rank asc'
        ]);

        $data = $room_seats->toJson('room_seats', 'toJson');
        return $data['room_seats'];
    }

    static function createRoom($user, $name)
    {
        $room = new Rooms();
        $room->name = $name;
        $room->user_id = $user->id;
        $room->user = $user;
        $room->status = STATUS_ON;
        $room->product_channel_id = $user->product_channel_id;
        $room->user_type = $user->user_type;
        $room->union_id = $user->union_id;
        $room->union_type = $user->union_type;
        $room->country_id = $user->country_id;
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
            $room_seat->country_id = $user->country_id;
            $room_seat->save();
        }

        return $room;
    }

    //是否为电台房间
    function isBroadcast()
    {
        return ROOM_THEME_TYPE_BROADCAST == $this->theme_type || ROOM_THEME_TYPE_USER_BROADCAST == $this->theme_type;
    }

    function getChannelName()
    {
        return $this->id . 'c' . md5($this->id . 'u' . $this->user_id);
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
        $room_online_token = "room_token_" . $online_token;

        $hot_cache = Rooms::getHotWriteCache();
        $room_id = $hot_cache->get($room_online_token);

        info($online_token, $user->sid, $this->id, 'user_room_id', $room_id);
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
            info($token);
            return null;
        }

        info($room_id);
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
        }

        $this->bindOnlineToken($user);
        $this->addUser($user);

        $this->save();
        $user->save();

        if (!$user->isSilent()) {
            Rooms::delay()->statDayEnterRoomUser($this->id, $user->id);
        }

        info($this->id, $this->user_num, $user->sid, $user->current_room_seat_id);
    }

    function exitRoom($user, $unbind = true)
    {
        $this->remUser($user);

        $current_room_seat_id = $user->current_room_seat_id;

        // 房间相同才清除用户信息
        if ($this->id == $user->current_room_id) {

            // 退出所有麦位
            $room_seats = RoomSeats::findByUserId($user->id);
            foreach ($room_seats as $room_seat) {
                $room_seat->user_id = 0;
                $room_seat->save();
            }

            $user->current_room_id = 0;
            $user->current_room_seat_id = 0;
            $user->user_role = USER_ROLE_NO;
            $user->user_role_at = time();
            $user->save();
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

        info($this->id, $this->user_num, $user->sid, $current_room_seat_id);
    }

    function kickingRoom($user)
    {
        info($this->user->sid, $user->sid);
        $this->exitRoom($user);
        $this->forbidEnter($user);
    }

    function getUserListKey()
    {
        return 'room_user_list_' . $this->id;
    }

    function getRealUserListKey()
    {
        return 'room_real_user_list_' . $this->id;
    }

    function getUserNum()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        return $hot_cache->zcard($key);
    }

    function getRealUserNum()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getRealUserListKey();
        return $hot_cache->zcard($key);
    }

    function getSilentUserNum()
    {
        $num = $this->getUserNum() - $this->getRealUserNum();
        return $num;
    }

    function addUser($user)
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $real_user_key = $this->getRealUserListKey();

        if (!$user->isSilent()) {
            info("not silent", $user->sid, $this->id);
            $hot_cache->zadd($real_user_key, time(), $user->id);
        }

        if ($this->user_id == $user->id) {
            $hot_cache->zadd($key, time() + 86400 * 7, $user->id);
        } elseif (USER_ROLE_BROADCASTER == $user->user_role) {
            $hot_cache->zadd($key, time() + 86400 * 3, $user->id);
        } else {
            $hot_cache->zadd($key, time(), $user->id);
        }

        $hot_cache->zadd(Rooms::getTotalRoomUserNumListKey(), $this->user_num, $this->id);

        info($user->sid, $this->id, $key, $real_user_key);

        if ($this->user_num > 0 && $this->status == STATUS_OFF && !$this->isBlocked()) {
            $this->status = STATUS_ON;
            $this->update();
        }
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

        if (!$user->isSilent()) {
            info("not silent", $user->sid, $this->id);
            $hot_cache->zrem($real_user_key, $user->id);
        }

        $hot_cache->zrem($key, $user->id);

        info($user->sid, $this->id, $key, $real_user_key);

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

    function findUsers($page, $per_page)
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $total_entries = $hot_cache->zcard($key);

        $offset = $per_page * ($page - 1);

        $user_ids = $hot_cache->zrevrange($key, $offset, $offset + $per_page - 1);
        $users = Users::findByIds($user_ids);

        foreach ($users as $user) {
            if ($user->isManager($this) && USER_ROLE_MANAGER != $user->user_role) {
                $user->user_role = USER_ROLE_MANAGER;
                $user->update();
            }
        }

        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }

    //随机一个用户
    function findRandomUser($filter_user_ids = [])
    {
        if ($this->getUserNum() < 1) {
            return null;
        }

        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $user_ids = $hot_cache->zrange($key, 0, -1);
        $user_ids = array_diff($user_ids, $filter_user_ids);
        $user_id = $user_ids[array_rand($user_ids)];

        if (!$user_id) {
            return null;
        }

        $user = Users::findFirstById($user_id);

        return $user;
    }

    function findTotalUsers()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $user_ids = $hot_cache->zrange($key, 0, -1);
        $users = Users::findByIds($user_ids);

        return $users;
    }

    function findSilentUsers()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $real_user_key = $this->getRealUserListKey();
        $user_ids = $hot_cache->zrange($key, 0, -1);
        $real_user_ids = $hot_cache->zrange($real_user_key, 0, -1);
        $silent_user_ids = array_diff($user_ids, $real_user_ids);
        $users = Users::findByIds($silent_user_ids);
        return $users;
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

    function getThemeImageUrl()
    {
        if (!$this->room_theme_id) {
            return '';
        }
        $room_theme = $this->room_theme;
        return $room_theme->theme_image_url;
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
    }

    function deleteManager($user_id)
    {
        $user = Users::findFirstById($user_id);

        if (!$user) {
            return;
        }

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
        info($this->user->sid, $user_id, $this->id);
        $db = Rooms::getRoomDb();
        $manager_list_key = $this->generateManagerListKey();
        $total_manager_key = self::generateTotalManagerKey();
        $user_manager_list_key = self::generateUserManagerListKey($user_id);
        $time = $duration * 3600;
        if (isDevelopmentEnv()) {
            $time = $duration * 60;
        }
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

    function findManagers()
    {
        $this->freshManagerNum();
        $db = Rooms::getRoomDb();
        $manager_list_key = $this->generateManagerListKey();
        $user_ids = $db->zrevrange($manager_list_key, 0, -1);
        $users = Users::findByIds($user_ids);
        $users = $this->initRoomManagerInfo($users);
        $managers = [];

        foreach ($users as $user) {
            $managers[] = $user->toRoomManagerJson();
        }

        return $managers;
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

    function calculateUserDeadline($user_id)
    {
        $db = Rooms::getRoomDb();
        $manager_list_key = $this->generateManagerListKey();
        $deadline = $db->zscore($manager_list_key, $user_id);
        return $deadline;
    }

    //获取沉默房间过期时间
    function getExpireTime()
    {
        $hot_cache = self::getHotWriteCache();
        $key = self::getOnlineSilentRoomKey();
        return $hot_cache->zscore($key, $this->id);
    }

    //1到5分钟占50%，5到10分钟占30%,10分钟到30分钟占20%
    function calculateExpireTime()
    {
        $rand_num = mt_rand(1, 100);

        if ($rand_num <= 50) {
            $time = mt_rand(1, 5);
        } elseif (50 < $rand_num && $rand_num <= 80) {
            $time = mt_rand(5, 10);
        } else {
            $time = mt_rand(10, 30);
        }

        return time() + $time * 60;
    }

    static function getOnlineSilentRoomKey()
    {
        return "online_silent_room_list_key";
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

    static function getOfflineSilentRooms()
    {
        $orders = ['id asc', 'id desc', 'created_at asc', 'created_at desc', 'updated_at asc', 'updated_at desc',
            'user_id asc', 'user_id desc'];

        $rank = array_rand($orders);
        $order = $orders[$rank];

        $limit = mt_rand(1, 2);

        if (isDevelopmentEnv()) {
            $limit = mt_rand(1, 7);
        }

        $cond['conditions'] = 'user_type = :user_type: and (online_status = :online_status: or online_status is null)';
        $cond['bind'] = ['user_type' => USER_TYPE_SILENT, 'online_status' => STATUS_OFF];
        $cond['order'] = $order;
        $cond['limit'] = $limit;
        $rooms = Rooms::find($cond);
        return $rooms;
    }

    static function getExpireOnlineSilentRooms()
    {
        $key = self::getOnlineSilentRoomKey();
        $hot_cache = self::getHotWriteCache();

        if (self::getOnlineSilentRoomNum() < 1) {
            return [];
        }

        $room_ids = $hot_cache->zrangebyscore($key, '-inf', time());
        info($room_ids);
        $rooms = Rooms::findByIds($room_ids);
        return $rooms;
    }

    static function getOnlineSilentRooms()
    {
        $key = self::getOnlineSilentRoomKey();
        $hot_cache = self::getHotWriteCache();

        if (self::getOnlineSilentRoomNum() < 1) {
            return [];
        }

        $room_ids = $hot_cache->zrange($key, 0, -1);
        info($room_ids);
        $rooms = Rooms::findByIds($room_ids);
        return $rooms;
    }

    static function getOnlineSilentRoomNum()
    {
        $key = self::getOnlineSilentRoomKey();
        $hot_cache = self::getHotWriteCache();
        return $hot_cache->zcard($key);
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

        info($room_id, $user->id);

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
        info($room_id, $user_id);
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

        info($this->id, $user->sid, $user->current_room_seat_id);

        $current_room_seat_id = $user->current_room_seat_id;

        $this->exitRoom($user);

        if ($user->isRoomHost($this)) {
            $this->rmOnlineSilentRoom();
        }

        $this->pushExitRoomMessage($user, $current_room_seat_id);
    }

    function pushEnterRoomMessage($user)
    {

        $body = ['action' => 'enter_room', 'user_id' => $user->id, 'nickname' => $user->nickname, 'sex' => $user->sex,
            'avatar_url' => $user->avatar_url, 'avatar_small_url' => $user->avatar_small_url, 'channel_name' => $this->channel_name,
            'segment' => $user->segment, 'segment_text' => $user->segment_text
        ];

        $user_car_gift = $user->getUserCarGift();

        if ($user_car_gift) {
            $body['user_car_gift'] = $user_car_gift->toSimpleJson();
        }

        $this->push($body);
    }

    function pushExitRoomMessage($user, $current_room_seat_id = '', $to_self = false)
    {
        $body = ['action' => 'exit_room', 'user_id' => $user->id, 'channel_name' => $this->channel_name];

        if ($current_room_seat_id) {
            $current_room_seat = RoomSeats::findFirstById($current_room_seat_id);

            if ($current_room_seat) {
                $body['room_seat'] = $current_room_seat->toSimpleJson();
            }
        }

        //指定用户
        if ($to_self) {
            $this->pushToUser($user, $body);
        } else {
            $this->push($body);
        }
    }

    function pushTopTopicMessage($user, $content = "")
    {
        if (!$content) {
            $messages = Rooms::$TOP_TOPIC_MESSAGES;
            $content = $messages[array_rand($messages)];
        }

        $body = ['action' => 'send_topic_msg', 'user_id' => $user->id, 'nickname' => $user->nickname, 'sex' => $user->sex,
            'avatar_url' => $user->avatar_url, 'avatar_small_url' => $user->avatar_small_url, 'content' => $content,
            'channel_name' => $this->channel_name
        ];

        $this->push($body);
    }

    function pushUpMessage($user, $current_room_seat)
    {
        $body = ['action' => 'up', 'channel_name' => $this->channel_name, 'room_seat' => $current_room_seat->toSimpleJson()];
        $this->push($body);
    }

    function pushDownMessage($user, $current_room_seat)
    {
        $body = ['action' => 'down', 'channel_name' => $this->channel_name, 'room_seat' => $current_room_seat->toSimpleJson()];

        $this->push($body);
    }

    function pushGiftMessage($user, $receiver, $gift, $gift_num)
    {
        $sender_nickname = $user->nickname;
        $receiver_nickname = $receiver->nickname;

        if (isDevelopmentEnv()) {
            $sender_nickname .= $user->id;
            $receiver_nickname .= $receiver->id;

        }

        $data = $gift->toSimpleJson();
        $data['num'] = $gift_num;
        $data['sender_id'] = $user->id;
        $data['sender_nickname'] = $sender_nickname;
        $data['sender_room_seat_id'] = $user->current_room_seat_id;
        $data['receiver_id'] = $receiver->id;
        $data['receiver_nickname'] = $receiver_nickname;
        $data['receiver_room_seat_id'] = $receiver->current_room_seat_id;

        $body = ['action' => 'send_gift', 'notify_type' => 'bc', 'channel_name' => $this->channel_name, 'gift' => $data];

        $this->push($body);
    }

    function push($body, $check_user_version = false)
    {
        $users = $this->findTotalRealUsers();

        if (count($users) < 1) {

            if ($this->user) {
                debug($this->user->sid);
            }

            info("no_users", $body, $this->id);
            return;
        }

        foreach ($users as $user) {

            //推送校验新版本
            if ($check_user_version && !$user->isHignVersion()) {
                info("old_version_user", $user->sid);
                continue;
            }

            $res = $this->pushToUser($user, $body);

            if ($res) {
                break;
            }
        }
    }

    //指定用户推送消息
    function pushToUser($user, $body)
    {
        $intranet_ip = $user->getIntranetIp();
        $receiver_fd = $user->getUserFd();
        $payload = ['body' => $body, 'fd' => $receiver_fd];

        if (!$intranet_ip) {
            info("user_already_close", $user->id, $user->sid, $this->id, $payload, $this->user->sid);
            return false;
        }

        $res = \services\SwooleUtils::send('push', $intranet_ip, self::config('websocket_local_server_port'), $payload);

        if ($res) {
            info($user->id, $user->sid, $this->id, $payload, $this->user->sid);
            return true;
        }

        info("Exce", $user->id, $user->sid, $this->id, $payload, $this->user->sid);

        return false;
    }

    function findRealUser()
    {
        if ($this->getRealUserNum() < 1) {
            info("user_real_num < 1");
            return null;
        }

        $hot_cache = self::getHotReadCache();
        $key = $this->getRealUserListKey();
        $user_ids = $hot_cache->zrange($key, 0, -1);
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
        $user_ids = $hot_cache->zrange($key, 0, -1);
        $users = Users::findByIds($user_ids);

        return $users;
    }

    function isSilent()
    {
        return USER_TYPE_SILENT == $this->user_type;
    }

    function isActive()
    {
        return USER_TYPE_ACTIVE == $this->user_type;
    }

    function canEnter($user)
    {
        if ($this->isForbidEnter($user)) {
            return false;
        }

        return true;
    }

    static function activeRoom($room_id)
    {
        return;
        $room = Rooms::findFirstById($room_id);

        if (!$room) {
            return;
        }

        $silent_users = $room->findSilentUsers();

        if (count($silent_users) > 0) {
            foreach ($silent_users as $silent_user) {
                $silent_user->activeRoom($room);
            }
        }

        if ($room->isSilent()) {
            $room->addSilentUsers();
        }
    }

    function addSilentUsers()
    {
        if ($this->lock) {
            info("room_is_lock", $this->id);
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
            info($filter_user_ids);
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

    static function getWaitEnterSilentRoomUserIds()
    {
        $hot_cache = Rooms::getHotWriteCache();
        $user_ids = $hot_cache->zrange('wait_enter_silent_room_list', 0, -1);
        return $user_ids;
    }

    function isOnline()
    {
        return $this->online_status == STATUS_ON;
    }

    function canSetAudio()
    {
        if ($this->theme_type == ROOM_THEME_TYPE_BROADCAST || $this->audio_id || $this->user_type != USER_TYPE_SILENT) {
            debug($this->id);
            return false;
        }
        return true;
    }

    static function addUserAgreement($room_id)
    {
        $room = Rooms::findFirstById($room_id);

        if (!$room || $room->user_agreement_num < 1) {
            return;
        }

        $users = $room->selectSilentUsers($room->user_agreement_num);

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
        }

        info($room->id, $room->user_agreement_num, count($users));
    }

    static function deleteUserAgreement($room_id)
    {
        $room = Rooms::findFirstById($room_id);

        if (!$room) {
            return;
        }

        $silent_users = $room->findSilentUsers();

        foreach ($silent_users as $user) {

            $delay_time = mt_rand(1, 120);

            if (isDevelopmentEnv()) {
                $delay_time = mt_rand(1, 30);
            }

            Rooms::delay($delay_time)->asyncExitSilentRoom($room->id, $user->id);
        }
    }

    //总的房间列表
    static function generateTotalRoomListKey()
    {
        return "total_room_list";
    }

    //总的热门房间列表
    static function generateHotRoomListKey()
    {
        return "hot_room_list";
    }

    //新手热门房间列表
    static function generateNoviceHotRoomListKey()
    {
        return "novice_hot_room_list";
    }

    //绿色热门房间列表
    static function generateGreenHotRoomListKey()
    {
        return "green_hot_room_list";
    }

    function generateFilterUserKey($user_id)
    {
        return "filter_user_" . $this->id . "and" . $user_id;
    }

    function addFilterUser($user_id)
    {
        $db = Rooms::getRoomDb();
        $expire = 2;
        $db->setex($this->generateFilterUserKey($user_id), $expire, time());
    }

    function checkFilterUser($user_id)
    {
        $db = Rooms::getRoomDb();

        $key = $this->generateFilterUserKey($user_id);
        if ($db->get($key)) {
            return true;
        }
        return false;
    }

    static function searchHotRooms($user, $page, $per_page)
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $green_hot_room_list_key = Rooms::generateGreenHotRoomListKey();
        $novice_hot_room_list_key = Rooms::generateNoviceHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();

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
        }

        $total_room_ids = $hot_cache->zrange($hot_room_list_key, 0, -1);
        $total_user_num_key = Rooms::getTotalRoomUserNumListKey();

        foreach ($total_room_ids as $room_id) {

            if ($hot_cache->zscore($total_user_num_key, $room_id) < 1) {
                $hot_cache->zrem($hot_room_list_key, $room_id);
            }
        }

        $total_entries = $hot_cache->zcard($hot_room_list_key);

        $offset = $per_page * ($page - 1);
        if ($offset > $total_entries - 1) {
            $offset = $total_entries - 1;
        }

        $room_ids = $hot_cache->zrevrange($hot_room_list_key, $offset, $offset + $per_page - 1);
        $rooms = Rooms::findByIds($room_ids);

        $pagination = new PaginationModel($rooms, $total_entries, $page, $per_page);
        $pagination->clazz = 'Rooms';

        return $pagination;
    }

    function isInHotList()
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();

        return $hot_cache->zscore($hot_room_list_key, $this->id) > 0;
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

    function pushRoomNoticeMessage($content, $opts = [])
    {
        $room_id = fetch($opts, 'room_id');
        $expire_time = fetch($opts, 'expire_time');
        $client_url = '';
        $room = Rooms::findFirstById($room_id);

        //当前房间不带client_url
        if ($room_id != $this->id && !$room->lock) {
            $client_url = 'app://m/rooms/detail?id=' . $room_id;
        }

        $body = ['action' => 'room_notice', 'channel_name' => $this->channel_name, 'expire_time' => $expire_time, 'content' => $content
            , 'client_url' => $client_url];

        info($body, $this->id, $this->user->sid);

        $this->push($body, true);
    }

    //全服通知
    static function asyncAllNoticePush($content, $opts = [])
    {
        $hot = fetch($opts, 'hot');
        $room_id = fetch($opts, 'room_id');
        $expire_time = fetch($opts, 'expire_time');

        if ($hot) {

            $room = Rooms::findFirstById($room_id);

            //热门房间单独推送
            if (!$room->isInHotList()) {
                $room->pushRoomNoticeMessage($content, ['room_id' => $room_id, 'expire_time' => $expire_time]);
            }

            $rooms = Rooms::searchHotRooms(null, 1, 100);
        } else {
            $cond = ['conditions' => 'user_type = :user_type: and last_at >= :last_at:',
                'bind' => ['user_type' => USER_TYPE_ACTIVE, 'last_at' => time() - 10 * 3600], 'order' => 'last_at desc', 'limit' => 100];
            $rooms = Rooms::find($cond);
        }

        foreach ($rooms as $room) {
            $room->pushRoomNoticeMessage($content, ['room_id' => $room_id, 'expire_time' => $expire_time]);
        }
    }

    //全服通知
    static function allNoticePush($gift_order)
    {

        $opts = ['room_id' => $gift_order->room_id];

        $max_amount = 131400;
        $min_amount = 52000;

        if (isDevelopmentEnv()) {
            $max_amount = 1000;
            $min_amount = 500;
        }

        $push = false;
        $expire_time = 5;

        if ($gift_order->amount >= $max_amount) {
            $expire_time = 10;
            $push = true;
        }

        if ($gift_order->amount >= $min_amount && $gift_order->amount < $max_amount) {
            $opts['hot'] = 1;
            $expire_time = 6;
            $push = true;
        }

        if ($push) {
            $opts['expire_time'] = $expire_time;
            info($gift_order->id, $gift_order->sender_id, $gift_order->user_id, $gift_order->amount, $opts);
            Rooms::delay()->asyncAllNoticePush($gift_order->allNoticePushContent(), $opts);
        }
    }


    //沉默用户送礼物按天统计
    function getDayGiftAmountBySilentUser()
    {
        $hot_cache = self::getHotReadCache();
        $amount = $hot_cache->get($this->getStatGiftAmountKey());
        return intval($amount);
    }

    //沉默用户送礼物按小时统计
    function getHourGiftAmountBySilentUser()
    {
        $hot_cache = self::getHotReadCache();
        $amount = $hot_cache->get($this->getStatGiftAmountKey(false));
        return intval($amount);
    }

    //沉默用户送礼物按天统计
    function getDayGiftUserNumBySilentUser()
    {
        $hot_cache = self::getHotReadCache();
        $num = $hot_cache->zcard($this->getStatGiftUserNumKey());
        return intval($num);
    }

    //沉默用户送礼物按小时统计
    function getHourGiftUserNumBySilentUser()
    {
        $hot_cache = self::getHotReadCache();
        $num = $hot_cache->zcard($this->getStatGiftUserNumKey(false));
        return intval($num);
    }

    //沉默用户送礼物金额
    function getStatGiftAmountKey($day = true)
    {
        if ($day) {
            $time = date("Ymd");
        } else {
            $time = date("YmdH");
        }

        return $time . "_silent_user_send_gift_amount_room_id" . $this->id;
    }

    //沉默用户送礼物金额key
    function getStatGiftUserNumKey($day = true)
    {
        if ($day) {
            $time = date("Ymd");
        } else {
            $time = date("YmdH");
        }

        return $time . "_silent_user_send_gift_user_num_room_id" . $this->id;
    }

    //获取指定时间的房间收益 只有支付类型为钻石 礼物类型为普通礼物的才计算为收益
    function getDayAmount($start_at, $end_at)
    {
        $cond = [
            'conditions' => "room_id = :room_id: and status = :status: and created_at >=:start_at: and created_at <=:end_at: and pay_type = :pay_type:" .
                " and gift_type = :gift_type:",
            'bind' => ['room_id' => $this->id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'start_at' => $start_at, 'end_at' => $end_at,
                'pay_type' => GIFT_PAY_TYPE_DIAMOND, 'gift_type' => GIFT_TYPE_COMMON],
            'column' => 'amount'
        ];

        $amount = GiftOrders::sum($cond);
        return $amount;
    }

    //房间收益统计 总的
    function statIncome($amount)
    {
        $db = Users::getUserDb();

        if ($amount) {
            $db->zincrby("stat_room_income_list", $amount, $this->id);
        }
    }

    //获取房间收益
    function getAmount()
    {
        $db = Users::getUserDb();
        return $db->zscore("stat_room_income_list", $this->id);
    }

    //房间收益列表
    static function roomIncomeList($page, $per_page, $cond)
    {
        $db = Users::getUserDb();
        $key = "stat_room_income_list";
        $total_entries = $db->zcard($key);
        $offset = $per_page * ($page - 1);
        $room_ids = $db->zrevrange($key, $offset, $offset + $per_page - 1);
        $room_ids = implode(',', $room_ids);

        if (isPresent($cond)) {
            debug($cond);
            $rooms = self::find($cond);
        } else {
            $rooms = self::findByIds($room_ids);
        }

        $pagination = new PaginationModel($rooms, $total_entries, $page, $per_page);

        $pagination->clazz = 'Rooms';

        return $pagination;
    }

    function generateStatIncomeDayKey($stat_at)
    {
        if (!$stat_at) {
            $stat_at = date('Ymd');
        }

        $key = "room_stats_income_day_" . $stat_at;

        return $key;
    }

    function generateSendGiftUserDayKey($stat_at)
    {
        if (!$stat_at) {
            $stat_at = date('Ymd');
        }

        $key = "room_stats_send_gift_user_day_" . $stat_at . "_room_id_{$this->id}";

        return $key;
    }

    function generateSendGiftNumDayKey($stat_at)
    {
        if (!$stat_at) {
            $stat_at = date('Ymd');
        }

        $key = "room_stats_send_gift_num_day_" . $stat_at;

        return $key;
    }


    function generateStatEnterRoomUserDayKey($stat_at)
    {
        if (!$stat_at) {
            $stat_at = date('Ymd');
        }

        $key = "room_stats_enter_room_user_day_" . $stat_at . "_room_id_{$this->id}";

        return $key;
    }


    function generateStatTimeDayKey($action, $stat_at)
    {
        if (!$stat_at) {
            $stat_at = date('Ymd');
        }

        $key = "room_stats_{$action}_time_day_" . $stat_at;

        return $key;
    }

    //按天统计房间收益和送礼物人数,送礼物个数
    static function statDayIncome($room_id, $income, $sender_id, $gift_num)
    {
        if ($income > 0) {
            info($room_id, $income, $sender_id);
            $room_db = Rooms::getRoomDb();
            $room = Rooms::findFirstById($room_id);

            if ($room) {
                $room_db->zincrby($room->generateStatIncomeDayKey(date("Ymd")), $income, $room_id);
                $room_db->zadd($room->generateSendGiftUserDayKey(date("Ymd")), time(), $sender_id);
                $room_db->zincrby($room->generateSendGiftNumDayKey(date("Ymd")), $gift_num, $room_id);
            }
        }
    }

    //按天统计房间进入人数
    static function statDayEnterRoomUser($room_id, $user_id)
    {
        info($user_id, $room_id);
        $room_db = Rooms::getRoomDb();
        $room = Rooms::findFirstById($room_id);

        if ($room) {
            $room_db->zadd($room->generateStatEnterRoomUserDayKey(date("Ymd")), time(), $user_id);
        }
    }

    //按天统计房间用户活跃时长
    static function statDayUserTime($action, $room_id, $time)
    {
        if ($time > 0) {
            info($action, $room_id, $time);
            $room_db = Rooms::getRoomDb();
            $room = Rooms::findFirstById($room_id);

            if ($room) {
                $room_db->zincrby($room->generateStatTimeDayKey($action, date("Ymd")), $time, $room_id);
            }
        }
    }

    //按天统计房间收益的id
    static function dayStatRooms($stat_at = '')
    {
        if (!$stat_at) {
            $stat_at = date('Ymd');
        }

        $room_db = Rooms::getRoomDb();
        $key = "room_stats_income_day_" . $stat_at;
        $total_entries = $room_db->zcard($key);
        $per_page = $total_entries;
        $page = 1;
        $offset = $per_page * ($page - 1);
        $room_ids = $room_db->zrevrange($key, $offset, $offset + $per_page - 1);
        $rooms = Rooms::findByIds($room_ids);
        $pagination = new PaginationModel($rooms, $total_entries, $page, $per_page);
        $pagination->clazz = 'Rooms';
        return $pagination;
    }

    //按天的流水
    function getDayIncome($stat_at)
    {
        $room_db = Rooms::getRoomDb();
        return $room_db->zscore($this->generateStatIncomeDayKey($stat_at), $this->id);
    }

    //按天的进入房间人数
    function getDayEnterRoomUser($stat_at)
    {
        $room_db = Rooms::getRoomDb();
        return $room_db->zcard($this->generateStatEnterRoomUserDayKey($stat_at));
    }


    //按天的送礼物人数
    function getDaySendGiftUser($stat_at)
    {
        $room_db = Rooms::getRoomDb();
        return $room_db->zcard($this->generateSendGiftUserDayKey($stat_at));
    }

    //按天的送礼物个数
    function getDaySendGiftNum($stat_at)
    {
        $room_db = Rooms::getRoomDb();
        return $room_db->zscore($this->generateSendGiftNumDayKey($stat_at), $this->id);
    }


    //按天的主播时长 action audience broadcaster host_broadcaster
    function getDayUserTime($action, $stat_at)
    {
        $room_db = Rooms::getRoomDb();
        return $room_db->zscore($this->generateStatTimeDayKey($action, $stat_at), $this->id);
    }

    //平均送礼物个数
    function daySendGiftAverageNum()
    {
        $avg = 0;

        if ($this->day_send_gift_user > 0) {
            $avg = intval($this->day_send_gift_num * 100 / $this->day_send_gift_user) / 100;
        }

        return $avg;
    }

    //总的平均送礼物个数
    function totalSendGiftAverageNum()
    {
        $avg = 0;

        if ($this->total_send_gift_user > 0) {
            $avg = intval($this->total_send_gift_num * 100 / $this->total_send_gift_user) / 100;
        }

        return $avg;
    }

    function isTop()
    {
        return STATUS_ON == $this->top;
    }

    //是否能上热门
    function canToHot($least_user_num)
    {
        $user = $this->user;

        if (!$this->isBroadcast() && !$user->isIdCardAuth() && $user->pay_amount < 1) {
            info("user_no_pay_amount", $user->id, $user->pay_amount, $this->id);
            return false;
        }

        if (!$this->checkRoomSeat()) {
            info("room_seat_is_null", $this->id);
            return false;
        }

        if ($this->getUserNum() < $least_user_num) {
            info("room_no_user", $this->id);
            return false;
        }

        if ($this->lock) {
            info("room_seat_is_lock", $this->id);
            return false;
        }

        if ($this->isForbiddenHot()) {
            info("isForbiddenHot", $this->id);
            return false;
        }

        if ($this->isBlocked()) {
            info("isBlocked", $this->id);
            return false;
        }

        if ($user->isCompanyUser()) {
            info("isCompanyUser", $this->id);
            return false;
        }

        return true;
    }

    static function searchRooms($opts, $page, $per_page)
    {
        $country_id = fetch($opts, 'country_id');
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

        if ($country_id) {
            $cond['conditions'] .= " and country_id = :country_id: ";
            $cond['bind']['country_id'] = $country_id;
        }

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

    //服务端控制用户退出房间
    static function exitRoomByServer($user_id, $room_id, $room_seat_id)
    {
        info($user_id, $room_id, $room_seat_id);

        $room = Rooms::findFirstById($room_id);
        $user = Users::findFirstById($user_id);

        if (!$room || !$user) {
            Rooms::delUserIdInExitRoomByServerList($user_id);
            info("param error");
            return;
        }

        if (!$user->current_room_id) {
            Rooms::delUserIdInExitRoomByServerList($user_id);
            info("user_not_in_room", $user_id, $room_id, $room_seat_id);
            return;
        }

        $room_seat = RoomSeats::findFirstById($room_seat_id);

        //用户重连不踢出用户
        if ($user->getUserFd()) {

            //如果用户已经连接并且不在被踢的房间 则只清楚房间信息 不发踢人websocket
            if ($room_id && $user->current_room_id != $room_id) {
                $room->exitRoom($user, false);
            }

            if ($room_seat && $user->current_room_seat_id != $room_seat_id && $room_seat->user_id == $user_id) {
                $room_seat->user_id = 0;
                $room_seat->update();
            }

            Rooms::delUserIdInExitRoomByServerList($user_id);
            info("user_re_connect", $user_id);
            return;
        }

        $exce_exit_room_key = "exce_exit_room_id{$room->id}";
        $exce_exit_room_lock = tryLock($exce_exit_room_key, 1000);
        $current_room_seat_id = '';

        if ($room_seat) {
            $current_room_seat_id = $room_seat->id;
            $room_seat->down($user);
        }

        $room->exitRoom($user);
        //$room->pushExitRoomMessage($user, $current_room_seat_id);
        Rooms::delUserIdInExitRoomByServerList($user_id);
        unlock($exce_exit_room_lock);
    }

    static function generateExitRoomByServerListKey()
    {
        return "exit_room_by_server_by_server_list";
    }

    static function isInExitRoomByServerList($user_id)
    {
        $hot_cache = Rooms::getHotReadCache();
        return $hot_cache->zscore(self::generateExitRoomByServerListKey(), $user_id) > 0;
    }

    static function addUserIdInExitRoomByServerList($user_id)
    {
        $hot_cache = Rooms::getHotWriteCache();
        $hot_cache->zadd(self::generateExitRoomByServerListKey(), time(), $user_id);
    }

    static function delUserIdInExitRoomByServerList($user_id)
    {
        $hot_cache = Rooms::getHotWriteCache();
        $hot_cache->zrem(self::generateExitRoomByServerListKey(), $user_id);
    }
}