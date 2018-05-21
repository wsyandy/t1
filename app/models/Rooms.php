<?php

class Rooms extends BaseModel
{
    use RoomEnumerations;
    use RoomAttrs;

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

    static $STATUS = [STATUS_OFF => '下架', STATUS_ON => '上架', STATUS_BLOCKED => '封闭'];
    static $USER_TYPE = [USER_TYPE_ACTIVE => '活跃', USER_TYPE_SILENT => '沉默'];
    static $THEME_TYPE = [ROOM_THEME_TYPE_NORMAL => '正常', ROOM_THEME_TYPE_BROADCAST => '电台', ROOM_THEME_TYPE_USER_BROADCAST => '个人电台'];
    static $ONLINE_STATUS = [STATUS_OFF => '离线', STATUS_ON => '在线'];
    static $HOT = [STATUS_OFF => '否', STATUS_ON => '是', STATUS_FORBIDDEN => '禁止上热门'];
    static $TOP = [STATUS_OFF => '否', STATUS_ON => '是'];
    static $NEW = [STATUS_OFF => '否', STATUS_ON => '是'];
    static $TYPES = ['gang_up' => '开黑', 'friend' => '交友', 'amuse' => '娱乐', 'sing' => '唱歌', 'broadcast' => '电台',
        'room_seat_sequence' => '麦序', 'male_gold' => '男女神', 'point_sing' => '点唱'];
    static $NOVICE = [STATUS_OFF => '否', STATUS_ON => '是']; //新手房间
    static $GREEN = [STATUS_OFF => '否', STATUS_ON => '是']; //绿色房间


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

    function isHot()
    {
        return $this->hot == STATUS_ON;
    }

    function isForbiddenHot()
    {
        $hot_cache = self::getHotReadCache();

        if ($hot_cache->get("room_forbidden_to_hot_room_id_" . $this->id) > 0) {
            return true;
        }

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

    function isShieldRoom()
    {

        if ($this->types) {
            $types = explode(",", $this->types);

            if (in_array('room_seat_sequence', $types) || in_array('male_gold', $types)) {
                return true;
            }
        }

        $keywords = ['男神', '女神', '男模', '女模', '野模', '捕鱼', '牛牛', '百捕', '千捕', '打地鼠', '金花', '赌', '嫖',
            '骚', '嫖娼', '黄片', '毛片', '聊骚', '涉黄', '阴毛', '性爱', '做爱', '交配', '阴道', '口交', '鸡巴', '性交',
            '性高潮', 'SM', '多P', '群交', '月经', '成人', '色情', '犯罪', '诈骗', '传销', '棋牌', '彩票', '假钞', '政治',
            '妈', '爸', '干你娘', '办理', '国家', '跪舔', '小婊砸', '我日', '超赚', '领导人', '作弊', '毒品', '淫秽', '异性',
            '私交', '涉嫌', '欺诈', '抢购', '招人', '跪求嫖', '艹', '操B', '艹B', '淫荡', '嫩模', '警察', '喘', '毒', '赌厅',
            '调情', '介绍所', '囚禁', '虐待', '包邮', '出售', '官方', '服务', '屁股', '搞基', '约炮', 'sao', '磕炮', '偷情',
            '系统小助手', '系统', '嫖', '客服小助手', '官方'
        ];

        foreach ($keywords as $keyword) {

            if (preg_match("/$keyword/i", $this->name)) {
                return true;
            }
        }

        return false;
    }

    function isInShieldRoomList()
    {
        $hot_shield_room_list_key = Rooms::generateShieldHotRoomListKey();
        $hot_cache = Rooms::getHotReadCache();
        return $hot_cache->zscore($hot_shield_room_list_key, $this->id) > 0;
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

    function toSimpleJson()
    {
        $user = $this->user;
        $data = ['id' => $this->id, 'uid' => $this->uid, 'name' => $this->name, 'topic' => $this->topic, 'chat' => $this->chat,
            'user_id' => $this->user_id, 'sex' => $user->sex, 'avatar_url' => $user->avatar_url, 'avatar_big_url' => $user->avatar_big_url,
            'avatar_small_url' => $user->avatar_small_url, 'avatar_100x100_url' => $user->avatar_100x100_url,
            'avatar_60x60_url' => $user->avatar_60x60_url, 'nickname' => $user->nickname, 'age' => $user->age,
            'monologue' => $user->monologue, 'channel_name' => $this->channel_name, 'online_status' => $this->online_status,
            'user_num' => $this->user_num, 'lock' => $this->lock, 'created_at' => $this->created_at, 'last_at' => $this->last_at, 'has_red_packet' => $this->has_red_packet
        ];

        $data['room_tag_names'] = $this->getRoomTagNamesText();

        return $data;
    }

    function mergeJson()
    {
        $room_seat_datas = $this->roomSeats();

        $user = $this->user;
        return ['channel_name' => $this->channel_name, 'user_num' => $this->user_num, 'sex' => $user->sex,
            'avatar_url' => $user->avatar_url, 'avatar_big_url' => $user->avatar_big_url,
            'avatar_small_url' => $user->avatar_small_url, 'avatar_100x100_url' => $user->avatar_100x100_url,
            'avatar_60x60_url' => $user->avatar_60x60_url,
            'nickname' => $user->nickname, 'age' => $user->age,
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
            'top_text' => $this->top_text,
            'user_uid' => $this->user_uid,
            'total_score_by_cache' => $this->total_score_by_cache
        ];

        return array_merge($opts, $this->toJson());
    }

    function toBasicJson()
    {
        return ['id' => $this->id, 'uid' => $this->uid, 'lock' => $this->lock, 'channel_name' => $this->channel_name, 'name' => $this->name];
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
        }

        $this->bindOnlineToken($user);
        $this->addUser($user);

        $this->save();
        $user->save();

        $this->updateLastAt();

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

            $user->current_room_id = 0;
            $user->current_room_seat_id = 0;
            $user->current_room_channel_name = '';
            $user->user_role = USER_ROLE_NO;
            $user->user_role_at = time();
            $user->save();
        }

        // 房主
        if ($this->user_id == $user->id) {
            $this->online_status = STATUS_OFF;
            $this->save();
        }

        $this->updateLastAt();

        //修复数据时,不需要解绑,防止用户在别的房间已经生成新的token
        if ($unbind) {
            $this->unbindOnlineToken($user);
        }
    }

    function updateLastAt()
    {
        $hot_cache = Users::getHotWriteCache();
        $key = 'room_active_last_at_list';
        $hot_cache->zadd($key, time(), $this->id);

        $total = $hot_cache->zcard($key);

        if ($total >= 1000) {
            $hot_cache->zremrangebyrank($key, 0, $total - 1000);
        }
    }

    static function getActiveRoomIdsByTime()
    {
        $start = time() - 3600;

        $end = time();
        $room_ids = [];

        $cond = [
            'conditions' => 'room_id > 0 and created_at >= :start: and created_at <= :end:',
            'bind' => ['start' => $start, 'end' => $end],
            'columns' => 'distinct room_id'];

        $gift_orders = GiftOrders::find($cond);

        $broadcast_room_cond = [
            "conditions" => 'types like :types: and online_status = :online_status: and status = :status:',
            'bind' => ['types' => "%broadcast%", 'online_status' => STATUS_ON, 'status' => STATUS_ON],
            'columns' => 'id'
        ];

        $broadcast_rooms = Rooms::find($broadcast_room_cond);

        foreach ($broadcast_rooms as $broadcast_room) {
            $room_ids[] = $broadcast_room->id;
        }


        foreach ($gift_orders as $gift_order) {
            $room_ids[] = $gift_order->room_id;
        }

        $hot_rooms = Rooms::find(['conditions' => 'status = :status:', 'bind' => ['status' => STATUS_ON]]);

        foreach ($hot_rooms as $hot_room) {
            $room_ids[] = $hot_room->id;
        }

        $room_ids = array_unique($room_ids);
        return $room_ids;
    }

    function getLastAtByCache()
    {
        $hot_cache = Users::getHotReadCache();
        $key = 'room_active_last_at_list';
        return $hot_cache->zscore($key, $this->id);
    }

    function kickingRoom($user, $time = 600)
    {
        $this->exitRoom($user);
        $this->forbidEnter($user, $time);
    }

    function getUserListKey()
    {
        return 'room_user_list_' . $this->id;
    }

    function getRealUserListKey()
    {
        return 'room_real_user_list_' . $this->id;
    }

    function addUser($user)
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $real_user_key = $this->getRealUserListKey();

        if (!$user->isSilent()) {
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
            $hot_cache->zrem($real_user_key, $user->id);
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

    function findUsers($page, $per_page)
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $total_entries = $hot_cache->zcard($key);

        $offset = $per_page * ($page - 1);

        $user_ids = $hot_cache->zrevrange($key, $offset, $offset + $per_page - 1);
        $users = Users::findByIds($user_ids);

//        foreach ($users as $user) {
//            if ($user->isManager($this) && USER_ROLE_MANAGER != $user->user_role) {
//                $user->user_role = USER_ROLE_MANAGER;
//                $user->update();
//            }
//        }

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
    function forbidEnter($user, $time = 600)
    {
        $hot_cache = Rooms::getHotWriteCache();

        if (isDevelopmentEnv()) {
            $time = 60;
        }

        $key = "room_forbid_user_room{$this->id}_user{$user->id}";

        $hot_cache->setex($key, $time, 1);
    }

    function isForbidEnter($user)
    {
        $hot_cache = Rooms::getHotReadCache();
        $key = "room_forbid_user_room{$this->id}_user{$user->id}";

        return $hot_cache->get($key) > 0;
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
        $db = Users::getUserDb();
        $key = $this->generateManagerListKey();
        return $db->zcard($key);
    }

    function addManager($user_id, $duration)
    {
        $db = Users::getUserDb();
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

        $db = Users::getUserDb();;
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
        $db = Users::getUserDb();
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

    function findManagers()
    {
        $this->freshManagerNum();
        $db = Users::getUserDb();
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

    function calculateUserDeadline($user_id)
    {
        $db = Users::getUserDb();
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

        $this->pushExitRoomMessage($user, $current_room_seat_id);
    }

    function pushBoomIncomeMessage($total_income, $cur_income, $status = STATUS_ON)
    {
        $body = [
            'action' => 'boom_gift',
            'boom_gift' => [
                'expire_at' => Rooms::getExpireAt($this->id),
                'client_url' => 'url://m/backpacks',
                'svga_image_url' => BoomHistories::getSvgaImageUrl(),
                'total_value' => (int)$total_income,
                'show_rank' => 1000000,
                'current_value' => (int)$cur_income,
                'render_type' => 'svga',
                'status' => $status,
                'image_color' => 'blue'
            ]
        ];

        debug($this->id, $body);

        $this->push($body, true);
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

    function pushTopTopicMessage($user, $content = "", $content_type = '')
    {
        if (!$content) {
            $messages = Rooms::$TOP_TOPIC_MESSAGES;
            $content = $messages[array_rand($messages)];
        }

        $body = ['action' => 'send_topic_msg', 'user_id' => $user->id, 'nickname' => $user->nickname, 'sex' => $user->sex,
            'avatar_url' => $user->avatar_url, 'avatar_small_url' => $user->avatar_small_url, 'content' => $content,
            'channel_name' => $this->channel_name, 'content_type' => $content_type
        ];

        $need_version_control = false;
        if ($content_type == 'red_packet') {
            $need_version_control = true;
        }

        $this->push($body, $need_version_control);
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
        $data['pay_type'] = $gift->pay_type;
        $data['total_amount'] = $gift_num * $gift->amount;

        $body = ['action' => 'send_gift', 'notify_type' => 'bc', 'channel_name' => $this->channel_name, 'gift' => $data];

        $this->push($body);
    }

    function pushRedPacketMessage($user, $num, $url, $notify_type = 'ptp')
    {
        $body = ['action' => 'red_packet', 'notify_type' => $notify_type, 'red_packet' => ['num' => $num, 'client_url' => $url]];
        info('推送红包信息', $body);
        if ($user->canReceiveBoomGiftMessage()) {
            $this->pushToUser($user, $body);
        }
    }

    function pushPkMessage($pk_history_datas)
    {
        $body = ['action' => 'pk', 'pk_history' => [
            'pk_type' => $pk_history_datas['pk_type'],
            'left_pk_user' => ['id' => $pk_history_datas['left_pk_user_id'], 'score' => $pk_history_datas[$pk_history_datas['left_pk_user_id']]],
            'right_pk_user' => ['id' => $pk_history_datas['right_pk_user_id'], 'score' => $pk_history_datas[$pk_history_datas['right_pk_user_id']]]
        ]
        ];
        $this->push($body, true);
    }


    function push($body, $check_user_version = false)
    {
        $users = $this->findTotalRealUsers();

        if (count($users) < 1) {

            if ($this->user) {
                debug($this->user->sid);
            }

            info("no_users", $this->id, $body);
            return;
        }

        foreach ($users as $user) {

            //推送校验新版本
            if ($check_user_version && !$user->canReceiveBoomGiftMessage()) {
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


    //新总的房间列表
    static function getTotalRoomListKey()
    {
        return "total_new_hot_room_list";
    }

    //新的热门房间列表
    static function getHotRoomListKey()
    {
        return "total_hot_rooms_list";
    }

    //新用户热门房间列表
    static function getNewUserHotRoomListKey()
    {
        return "new_user_hot_rooms_list";
    }

    //老用户充值热门房间列表
    static function getOldUserPayHotRoomListKey()
    {
        return "old_user_pay_hot_rooms_list";
    }

    //老用户未充值热门房间列表
    static function getOldUserNoPayHotRoomListKey()
    {
        return "old_user_no_pay_hot_rooms_list";
    }

    //总的屏蔽热门房间列表
    static function generateShieldHotRoomListKey()
    {
        return "hot_shield_room_list";
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
        //$new_user_hot_rooms_list_key = Rooms::getNewUserHotRoomListKey(); //新用户房间
        //$old_user_pay_hot_rooms_list_key = Rooms::getOldUserPayHotRoomListKey(); //充值老用户队列
        //$old_user_no_pay_hot_rooms_list_key = Rooms::getOldUserNoPayHotRoomListKey(); //未充值老用户队列

        $register_time = time() - $user->register_at;
        $time = 60 * 15;

        if (isProduction()) {
            $time = 86400;
        }

        if ($user->isShieldHotRoom()) {

            $hot_room_list_key = Rooms::getHotRoomListKey();

        } else {

            $hot_room_list_key = Rooms::getTotalRoomListKey(); //新的用户总的队列

//            if ($register_time <= $time) {
//                $hot_room_list_key = $new_user_hot_rooms_list_key;
//            } else {
//
//                if ($user->pay_amount > 0) {
//                    $hot_room_list_key = $old_user_pay_hot_rooms_list_key;
//                } else {
//                    $hot_room_list_key = $old_user_no_pay_hot_rooms_list_key;
//                }
//            }
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

        $rooms = Rooms::findByIds($room_ids);
        $pagination = new PaginationModel($rooms, count($room_ids), $page, $per_page);
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
        if ($room_id && $room && $room_id != $this->id && !$room->lock) {
            $client_url = 'app://m/rooms/detail?id=' . $room_id;
        }

        $body = ['action' => 'room_notice', 'channel_name' => $this->channel_name, 'expire_time' => $expire_time,
            'content' => $content, 'client_url' => $client_url];

        $this->push($body, true);
    }

    //全服通知
    static function asyncAllNoticePush($content, $opts = [])
    {
        $hot = fetch($opts, 'hot');
        $room_id = fetch($opts, 'room_id');
        $expire_time = fetch($opts, 'expire_time');
        $type = fetch($opts, 'type', 'notice');

        if ($hot) {

            $room = Rooms::findFirstById($room_id);

            //热门房间单独推送
            if (!$room->isInHotList()) {
                $room->pushRoomNoticeMessage($content, ['room_id' => $room_id, 'expire_time' => $expire_time]);
            }

            $hot_cache = Users::getHotWriteCache();
            $hot_room_list_key = Rooms::getHotRoomListKey();
            $hot_total_room_list_key = Rooms::getTotalRoomListKey(); //新的用户总的队列

            $hot_room_ids = $hot_cache->zrevrange($hot_room_list_key, 0, 9);
            $hot_total_room_ids = $hot_cache->zrevrange($hot_total_room_list_key, 0, 9);

            $room_ids = array_merge($hot_room_ids, $hot_total_room_ids);
            $room_ids = array_unique($room_ids);

            $rooms = Rooms::findByIds($room_ids);

        } else {
            $cond = ['conditions' => 'user_type = :user_type: and last_at >= :last_at:',
                'bind' => ['user_type' => USER_TYPE_ACTIVE, 'last_at' => time() - 10 * 3600], 'order' => 'last_at desc', 'limit' => 100];
            $rooms = Rooms::find($cond);
        }

        $system_user = Users::findFirstById(1);

        foreach ($rooms as $room) {

            if ('notice' == $type) {
                $room->pushRoomNoticeMessage($content, ['room_id' => $room_id, 'expire_time' => $expire_time]);
            } else {
                $room->pushTopTopicMessage($system_user, $content);
            }
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

    //房间贡献榜
    function generateRoomWealthRankListKey($list_type, $opts = [])
    {
        switch ($list_type) {
            case 'day':
                {
                    $date = fetch($opts, 'date', date("Ymd"));
                    $key = "room_wealth_rank_list_day_" . "room_id_{$this->id}_" . $date;
                    break;
                }
            case 'week':
                {
                    $start = fetch($opts, 'start', date("Ymd", beginOfWeek()));
                    $end = fetch($opts, 'end', date("Ymd", endOfWeek()));
                    $key = "room_wealth_rank_list_week_" . "room_id_{$this->id}_" . $start . '_' . $end;
                    break;
                }
            default:
                return '';
        }

        debug($key);

        return $key;
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
    static function statDayIncome($room, $income, $sender_id, $gift_num, $opts = [])
    {
        debug($income, $sender_id, $gift_num, $opts);

        if ($income > 0 && $room) {

            if (is_numeric($room)) {
                $room = Rooms::findFirstById($room);
            }

            if (!$room) {
                return;
            }

            $room_db = Users::getUserDb();
            $time = fetch($opts, 'time', time());
            $date = date("Ymd", $time);

            //房间流水统计
            $room_db->zincrby($room->generateStatIncomeDayKey($date), $income, $room->id);
            $room_db->zadd($room->generateSendGiftUserDayKey($date), time(), $sender_id);
            $room_db->zincrby($room->generateSendGiftNumDayKey($date), $gift_num, $room->id);

            //房间流水贡献榜统计
            $room_db->zincrby($room->generateRoomWealthRankListKey('day', ['date' => $date]), $income, $sender_id);
            $room_db->zincrby($room->generateRoomWealthRankListKey('week',
                ['start' => date("Ymd", beginOfWeek($time)), 'end' => date("Ymd", endOfWeek($time))]), $income, $sender_id);


            //统计时间段房间流水 10分钟为单位
            $hot_cache = Users::getHotWriteCache();
            $minutes = date("YmdHi");
            $interval = intval(intval($minutes) % 10);
            $minutes_start = $minutes - $interval;
            $minutes_end = $minutes + (10 - $interval);
            $minutes_stat_key = "room_stats_send_gift_amount_minutes_" . $minutes_start . "_" . $minutes_end . "_room_id" . $room->id;
            $hot_cache->incrby($minutes_stat_key, $income);
            $hot_cache->expire($minutes_stat_key, 3600 * 3);

            $minutes_num_stat_key = "room_stats_send_gift_num_minutes_" . $minutes_start . "_" . $minutes_end . "_room_id" . $room->id;
            $hot_cache->incrby($minutes_num_stat_key, 1);
            $hot_cache->expire($minutes_num_stat_key, 3600 * 3);

            // 爆礼物
            if (isDevelopmentEnv() || in_array($room->id, Rooms::getGameWhiteList())) {
                $room->statBoomIncome($income, $time);
            }

            debug($minutes_stat_key);
        }
    }

    /**
     * @param $room_id
     * @return false|int
     */
    static function getExpireAt($room_id)
    {
        $cache = self::getHotWriteCache();
        $room_sign_key = self::generateRoomBoomGiftSignKey($room_id);
        $time = $cache->get($room_sign_key);

        debug('boom_test', $room_id, $room_sign_key, $time);

        if (empty($time)) {
            return 0;
        }

        $time = strtotime('+3 minutes', $time);
        return $time;
    }


    /**
     * 记录爆礼物开始时间
     * @param $room_id
     * @return string
     */
    static function generateRoomBoomGiftSignKey($room_id)
    {
        return 'room_boom_gift' . $room_id;
    }

    // 爆礼物流水值记录
    public function statBoomIncome($income, $time)
    {
        $cache = self::getHotWriteCache();
        $room_id = $this->id;

        // 单位周期 房间当前流水值
        $cur_income_key = self::generateBoomCurIncomeKey($room_id);
        $cur_income = $cache->get($cur_income_key);

        $lock = tryLock($cur_income_key);

        // 房间爆礼物结束倒计时
        $room_boon_gift_sign_key = Rooms::generateRoomBoomGiftSignKey($this->id);

        $expire = endOfDay() - $time;

        $expire = 180;

        $boom_list_key = 'boom_gifts_list';
        $total_income = BoomHistories::getBoomTotalValue();

        // 判断房间是否在进行爆礼物活动
        if ($cache->exists($room_boon_gift_sign_key)) {

            ($cur_income != 0) && $cache->del($cur_income_key);

        } else {

            // 单位周期 截止目前房间总流水
            $cur_total_income = $cur_income + $income;

            if ($cur_total_income >= $total_income) {
                // 爆礼物
                $cache->setex($room_boon_gift_sign_key, 180, $time);
                $cache->del($cur_income_key);
                $cache->zrem($boom_list_key, $room_id);

                $this->pushBoomIncomeMessage($total_income, $cur_total_income);

                unlock($lock);

                return;
            }

            $res = $cache->setex($cur_income_key, $expire, $cur_total_income);

            if ($res && $cur_total_income >= BoomHistories::getBoomStartLine()) {

                if (!$cache->zscore($boom_list_key, $room_id)) {
                    $cache->zadd($boom_list_key, time(), $room_id);
                }
                $this->pushBoomIncomeMessage($total_income, $cur_total_income);
            }
        }

        unlock($lock);
    }

    function getCurrentBoomGiftValue()
    {
        $cache = \Rooms::getHotWriteCache();
        $cur_income_key = \Rooms::generateBoomCurIncomeKey($this->id);
        $room_boon_gift_sign_key = Rooms::generateRoomBoomGiftSignKey($this->id);

        if ($cache->exists($room_boon_gift_sign_key)) {
            return \BoomHistories::getBoomTotalValue();
        }

        $cur_income = $cache->get($cur_income_key);

        return $cur_income;
    }

    function hasBoomGift()
    {
        $cache = \Rooms::getHotWriteCache();
        $room_boon_gift_sign_key = Rooms::generateRoomBoomGiftSignKey($this->id);
        $cur_income = $this->getCurrentBoomGiftValue();

        if ($cur_income >= \BoomHistories::getBoomStartLine() || $cache->exists($room_boon_gift_sign_key)) {
            return true;
        }

        return false;
    }

    //按天统计房间进入人数
    static function statDayEnterRoomUser($room_id, $user_id)
    {
        $room_db = Users::getUserDb();
        $room = Rooms::findFirstById($room_id);

        if ($room) {
            $room_db->zadd($room->generateStatEnterRoomUserDayKey(date("Ymd")), time(), $user_id);
        }
    }

    //按天统计房间用户活跃时长
    static function statDayUserTime($action, $room_id, $time)
    {
        if ($time > 0) {
            $room_db = Users::getUserDb();
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

        $room_db = Users::getUserDb();
        $key = "room_stats_income_day_" . $stat_at;
        $total_entries = $room_db->zcard($key);
        $per_page = $total_entries;
        $page = 1;
        $offset = $per_page * ($page - 1);
        //$room_ids = $room_db->zrevrange($key, $offset, $offset + $per_page - 1);
        $room_ids = $room_db->zrevrangebyscore($key, 100000000, 1000);
        $rooms = Rooms::findByIds($room_ids);
        $pagination = new PaginationModel($rooms, $total_entries, $page, $per_page);
        $pagination->clazz = 'Rooms';
        return $pagination;
    }

    //按天的流水
    function getDayIncome($stat_at)
    {
        $room_db = Users::getUserDb();
        $val = $room_db->zscore($this->generateStatIncomeDayKey($stat_at), $this->id);
        return intval($val);
    }

    //按天的进入房间人数
    function getDayEnterRoomUser($stat_at)
    {
        $room_db = Users::getUserDb();
        return $room_db->zcard($this->generateStatEnterRoomUserDayKey($stat_at));
    }


    //按天的送礼物人数
    function getDaySendGiftUser($stat_at)
    {
        $room_db = Users::getUserDb();
        return $room_db->zcard($this->generateSendGiftUserDayKey($stat_at));
    }

    //按天的送礼物个数
    function getDaySendGiftNum($stat_at)
    {
        $room_db = Users::getUserDb();
        return $room_db->zscore($this->generateSendGiftNumDayKey($stat_at), $this->id);
    }


    //按天的主播时长 action audience broadcaster host_broadcaster
    function getDayUserTime($action, $stat_at)
    {
        $room_db = Users::getUserDb();
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

//        if (!$this->isBroadcast() && !$user->isIdCardAuth() && $user->pay_amount < 1) {
//            return false;
//        }

        if (!$this->checkRoomSeat()) {
            return false;
        }

        if ($this->getRealUserNum() < $least_user_num) {
            return false;
        }

        if ($this->isTop()) {
            return false;
        }

        if ($this->lock) {
            return false;
        }

        if ($this->isForbiddenHot()) {
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
                //$room->exitRoom($user, false);
                info($user->sid, $room_id, $user->current_room_id);
            }

            if ($room_seat && $user->current_room_seat_id != $room_seat_id && $room_seat->user_id == $user_id) {
                $room_seat->user_id = 0;
                $room_seat->update();

                info($user->current_room_seat_id, $room_seat_id);
            }

            Rooms::delUserIdInExitRoomByServerList($user_id);

            return;
        }

        $exce_exit_room_key = "exce_exit_room_id{$room->id}";
        $exce_exit_room_lock = tryLock($exce_exit_room_key, 1000);
        $current_room_seat_id = '';

        if ($room_seat) {
            $current_room_seat_id = $room_seat->id;
            $room_seat->down($user);
        }

//        if ($user->last_at <= time() - 10 * 60) {
//            info($user->sid, $room_id, $user->current_room_id, date('c', $user->last_at), date('c', time()));
//            $room->exitRoom($user);
//        }

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

    static function generateAbnormalExitRoomListKey()
    {
        return "abnormal_exit_room_list";
    }

    static function isInAbnormalExitRoomList($room_id, $user_id)
    {
        $hot_cache = Rooms::getHotReadCache();
        return $hot_cache->zscore(self::generateAbnormalExitRoomListKey(), $room_id . "_" . $user_id) > 0;
    }

    static function addAbnormalExitRoomUserId($room_id, $user_id)
    {
        if ($room_id && $user_id) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->zadd(self::generateAbnormalExitRoomListKey(), time(), $room_id . "_" . $user_id);
        }
    }

    static function delAbnormalExitRoomUserId($room_id, $user_id)
    {
        if (self::isInAbnormalExitRoomList($room_id, $user_id)) {
            $hot_cache = Rooms::getHotWriteCache();
            $hot_cache->zrem(self::generateAbnormalExitRoomListKey(), $room_id . "_" . $user_id);
        }
    }

    static function getAbnormalExitRoomList()
    {
        $hot_cache = Rooms::getHotReadCache();

        return $hot_cache->zrange(self::generateAbnormalExitRoomListKey(), 0, -1);
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

    static function findRoomsByCategoryType($type, $page, $per_page)
    {
        $hot_cache = Rooms::getHotWriteCache();
        $key = "room_category_type_{$type}_list";

        $offset = $per_page * ($page - 1);
        $res = $hot_cache->zrevrange($key, $offset, $offset + $per_page - 1, 'withscores');
        $room_ids = [];

        foreach ($res as $user_id => $time) {
            $room_ids[] = $user_id;
        }

        $rooms = Rooms::findByIds($room_ids);

        $total_entries = $hot_cache->zcard($key);

        $pagination = new PaginationModel($rooms, $total_entries, $page, $per_page);
        $pagination->clazz = 'Rooms';
        return $pagination;
    }

    function getGameHistory()
    {
        $game_history = \GameHistories::findFirst(['conditions' => 'room_id=:room_id: and status!=:status: and created_at>:created_at:',
            'bind' => ['room_id' => $this->id, 'status' => GAME_STATUS_END, 'created_at' => time() - 300], 'order' => 'id desc']);

        return $game_history;
    }

    function getPkHistory()
    {
        $game_history = \PkHistories::findFirst(['conditions' => 'room_id=:room_id: and status!=:status: and expire_at>:current_time:',
            'bind' => ['room_id' => $this->id, 'status' => STATUS_OFF, 'current_time' => time()], 'order' => 'id desc']);

        return $game_history;
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

    static function getGameWhiteList()
    {
        $hot_cache = Rooms::getHotWriteCache();
        return $hot_cache->zrange("room_game_white_list", 0, -1);
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

    function getRoomTagNamesText()
    {
        if ($this->room_tag_names) {
            return explode(',', $this->room_tag_names);
        }

        return [];
    }

    static function searchGangUpRooms($user, $page, $per_page)
    {
        $cond['conditions'] = "online_status = :online_status: and status = :status: and room_category_types like 
        :room_category_types: and lock = :lock:";
        $cond['bind'] = ['online_status' => STATUS_ON, 'status' => STATUS_ON, 'room_category_types' => "%,gang_up,%", 'lock' => 'false'];
        $cond['order'] = 'last_at desc';

        $shield_room_ids = $user->getShieldRoomIds();

        if ($shield_room_ids) {
            $cond['conditions'] .= " and id not in (" . implode(",", $shield_room_ids) . ")";
        }

        $gang_up_rooms = \Rooms::findPagination($cond, $page, $per_page);

        $gang_up_rooms_json = $gang_up_rooms->toJson('gang_up_rooms', 'toSimpleJson');

        return $gang_up_rooms_json;
    }

    function getRoomMenuConfig($user, $opts = [])
    {
        $root_host = fetch($opts, 'root_host');
        $menu_config = [];
        $is_host = false;

        if ($user->isRoomHost($this)) {
            $is_host = true;
        }

        if ($user->canReceiveBoomGiftMessage()) {

            if (isInternalIp($user->ip)) {

                $menu_config[] = ['show' => true, 'title' => '红包', 'type' => 'red_packet',
                    'url' => 'url://m/red_packets', 'icon' => $root_host . 'images/red_packet.png'];

            }

            if ($is_host) {
                $menu_config[] = ['show' => true, 'title' => 'PK', 'type' => 'pk', 'icon' => $root_host . 'images/pk.png'];
            }
        }

        if ($is_host) {
            $menu_config[] = ['show' => true, 'title' => '游戏', 'type' => 'game',
                'url' => 'url://m/games?room_id=' . $this->id, 'icon' => $root_host . 'images/room_menu_game.png'];
            $menu_config[] = ['show' => true, 'title' => 'cp', 'type' => 'game',
                'url' => 'url://m/couples?room_id=' . $this->id, 'icon' => $root_host . 'images/cp.png'];
        }

        return $menu_config;
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

    function getHotRoomScoreRatio()
    {
        $user_db = Users::getUserDb();
        $key = "hot_room_score_ratio_room_id_{$this->id}";
        $ratio = $user_db->get($key);
        if (!$ratio) {
            return 0;
        }
        return $ratio;
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

                if ($room->isHot()) {
                    $hot_room_ids[$room->id] = $total_score;
                } else {
                    $room_ids[$room->id] = $total_score;
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

//        $hot_room_num = count($hot_room_ids);
//
//        if ($hot_room_num > 0 && $hot_room_num <= 9) {
//
//            $diff_num = 9 - $hot_room_num;
//
//            if (count($room_ids) <= $diff_num) {
//
//                foreach ($hot_room_ids as $hot_room_id => $score) {
//                    $room_ids[$hot_room_id] = $score;
//                }
//
//            } else {
//
//                $room_ids = array_slice($room_ids, $diff_num, 1);
//
//                if (count($room_ids) > 1) {
//                    $tmp_score = $room_ids[0];
//
//                    foreach ($hot_room_ids as $hot_room_id => $score) {
//
//                        if ($score > $tmp_score) {
//                            $room_ids[$hot_room_id] = $score;
//                        } else {
//                            $room_ids[$hot_room_id] = $tmp_score += 10;
//                        }
//                    }
//                }
//
//            }
//        }


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

        info($shield_room_ids, $room_ids);

        foreach ($room_ids as $room_id => $score) {

            if (!in_array($room_id, $shield_room_ids)) {
                $hot_cache->zadd($hot_room_list_key, $score, $room_id);
            }

            $hot_cache->zadd($total_new_hot_room_list_key, $score, $room_id);
        }

//        $i = 1;
//        if (count($shield_room_ids) > 0) {
//
//            $i = 1;
//
//            foreach ($shield_room_ids as $shield_room_id => $score) {
//
//                if ($i <= $new_user_shield_room_num) {
//                    $hot_cache->zadd($new_user_hot_rooms_list_key, $score, $shield_room_id);
//                    $hot_cache->zadd($old_user_no_pay_hot_rooms_list_key, $score, $shield_room_id);
//                }
//
//                $hot_cache->zadd($old_user_pay_hot_rooms_list_key, $score, $shield_room_id);
//                $hot_cache->zadd($total_new_hot_room_list_key, $score, $shield_room_id);
//
//                $i++;
//            }
//        }

        unlock($lock);
    }

    static function iosAuthVersionRooms($user, $page, $per_page)
    {
        $key = Rooms::generateIosAuthRoomListKey();
        $hot_cache = Rooms::getHotWriteCache();

        $room_ids = $hot_cache->zrevrange($key, 0, -1);

        $cond['conditions'] = " (room_category_types like :room_category_types: and online_status = :online_status: 
        and status = :status:)";

        $cond['bind'] = ['room_category_types' => "%,broadcast,%", 'online_status' => STATUS_ON,
            'status' => STATUS_ON
        ];

        if ($room_ids) {
            $cond['conditions'] .= " or id in (" . implode(",", $room_ids) . ")";
        }

        $rooms = Rooms::findPagination($cond, $page, $per_page);
        return $rooms;
    }

// ios 审核期间队列
    static function generateIosAuthRoomListKey()
    {
        return "ios_auth_room_list";
    }

    function isIosAuthRoom()
    {
        $hot_cache = Rooms::getHotReadCache();
        return intval($hot_cache->zscore(Rooms::generateIosAuthRoomListKey(), $this->id)) > 0;
    }

    static public function generateBoomCurIncomeKey($room_id)
    {
        return 'boom_target_value_room_' . $room_id;
    }

    function getTimeForUserInRoom($user_id)
    {
        $hot_cache = self::getHotWriteCache();
        $real_user_key = $this->getRealUserListKey();
        $time = $hot_cache->zscore($real_user_key, $user_id);
        return $time;
    }

    function getNotDrawRedPacket($user)
    {
        $cache = \Users::getUserDb();
        //当前房间所有还在进行中的红包ids
        $underway_red_packet_list_key = \RedPackets::generateUnderwayRedPacketListKey($this->id);
        $underway_ids = $cache->zrange($underway_red_packet_list_key, 0, -1);

        //当前用户领取过的红包ids
        $user_get_red_packet_ids = \RedPackets::UserGetRedPacketIds($this->id, $user->id);
        $ids = array_diff($underway_ids, $user_get_red_packet_ids);
        $room_red_packets = \RedPackets::findByIds($ids);

        return $room_red_packets;
    }

    function getReadyCpInfo()
    {
        $cache = \Users::getHotWriteCache();
        $key = \Couples::generateReadyCpInfoKey($this->id);
        $data = $cache->hgetall($key);
        return $data;
    }

    function checkBroadcasters()
    {

        $product_channel = $this->product_channel;
        $channel_name = $this->channel_name;
        $app_id = $product_channel->getImAppId();

        $headers = array(
            'Cache-Control' => 'no-cache',
            'Authorization' => 'Basic YjA0NGUzZmIzM2FiNGYxMjlhZDBjZDlkZmQ3ZTlkNjU6OWVlYjhkYzU1NDNiNGRmN2IxYzgzMmQ4NDE5MjlmODE='
        );
        $url = "http://api.agora.io/dev/v1/channel/user/{$app_id}/{$channel_name}";

        $res = httpGet($url, [], $headers);
        $res_body = $res->raw_body;
        $res_body = json_decode($res_body, true);
        info($this->id, $res_body);
        if(fetch($res_body, 'success') !== true){
            info('Exce', $url, $res_body);
            return;
        }

        $data = fetch($res_body, 'data');
        $broadcaster_ids = fetch($data, 'broadcasters');

        $room_seats = RoomSeats::findPagination(['conditions' => 'room_id=:room_id:',
            'bind' => ['room_id' => $this->id], 'order' => 'rank asc'], 1, 8, 8);

        $user_ids = [];
        foreach ($room_seats as $room_seat) {
            if ($room_seat->user_id < 1) {
                continue;
            }

            $user_ids[] = $room_seat->user_id;
        }

        info($this->id, 'broadcaster_ids', $broadcaster_ids, 'user_ids', $user_ids);

        $hot_cache = Users::getHotWriteCache();
        $user_list_key = $this->getUserListKey();

        foreach ($broadcaster_ids as $broadcaster_id) {

            $this->checkBroadcaster($broadcaster_id);
            
            if (in_array($broadcaster_id, $user_ids)) {
                continue;
            }

            if ($hot_cache->zscore($user_list_key, $broadcaster_id)) {
                info('异常id 在房间', $this->id, 'broadcaster_id', $broadcaster_id);
            } else {
                info('异常id 不在房间', $this->id, 'broadcaster_id', $broadcaster_id);
            }
        }
    }

    function checkBroadcaster($user_id)
    {

        $product_channel = $this->product_channel;
        $channel_name = $this->channel_name;
        $app_id = $product_channel->getImAppId();

        $headers = array(
            'Cache-Control' => 'no-cache',
            'Authorization' => 'Basic YjA0NGUzZmIzM2FiNGYxMjlhZDBjZDlkZmQ3ZTlkNjU6OWVlYjhkYzU1NDNiNGRmN2IxYzgzMmQ4NDE5MjlmODE='
        );

        $url = "http://api.agora.io/dev/v1/channel/user/property/{$app_id}/{$user_id}/{$channel_name}";
        $res = httpGet($url, [], $headers);
        $res_body = $res->raw_body;
        $res_body = json_decode($res_body, true);
        if(fetch($res_body, 'success') !== true){
            info('Exce', $url, $res_body);
            return;
        }

        $data = fetch($res_body, 'data');
        info('data', $this->id, $data);
        $in_channel = fetch($data, 'in_channel', false);
        $role = fetch($data, 'role', 0);
        if($in_channel === false){
            info('离开频道', $this->id, $user_id);
            return;
        }


    }

    function kickingRule($app_id, $channel_name, $user_id)
    {

        $headers = array(
            'Cache-Control' => 'no-cache',
            'Authorization' => 'Basic YjA0NGUzZmIzM2FiNGYxMjlhZDBjZDlkZmQ3ZTlkNjU6OWVlYjhkYzU1NDNiNGRmN2IxYzgzMmQ4NDE5MjlmODE='
        );
        $url = "https://api.agora.io/dev/v1/kicking-rule/";
        $body = [
            'appid' => $app_id,
            'cname' => $channel_name,
            'uid' => $user_id,
            'time' => 60
        ];

        info($url);

        $res = httpPost($url, $body, $headers);
        info($res);
    }

}