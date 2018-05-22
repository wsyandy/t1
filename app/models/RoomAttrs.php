<?php

trait RoomAttrs
{
    //计算送礼物钻石数热门分值
    function getRoomSendGiftAmountScore($opts = [])
    {
        $time = fetch($opts, 'time', time());

        $total_amount = 0;
        $hot_cache = self::getHotWriteCache();
        $percent = 0;

        for ($i = 0; $i < 12; $i++) {

            $minutes = date("YmdHi", $time);
            $interval = intval(intval($minutes) % 10);
            $minutes_start = $minutes - $interval;
            $minutes_end = $minutes + (10 - $interval);
            $minutes_stat_key = "room_stats_send_gift_amount_minutes_" . $minutes_start . "_" . $minutes_end . "_room_id" . $this->id;
            $amount = $hot_cache->get($minutes_stat_key);

            if ($percent > 0) {
                $amount = $amount * (1 - $percent / 100);
            }

            $percent += 8;

            if ($amount > 0) {
                $total_amount += $amount;
            }

            $time -= 600;

            info($this->id, $amount, $percent, $total_amount, $minutes_stat_key);
        }

        return $total_amount;
    }

    //计算送礼物次数热门分值
    function getRoomSendGiftNumScore($opts = [])
    {
        $time = fetch($opts, 'time', time());

        $total_num = 0;
        $hot_cache = self::getHotWriteCache();
        $percent = 0;

        for ($i = 0; $i < 12; $i++) {

            $time -= 600;

            $minutes = date("YmdHi", $time);
            $interval = intval(intval($minutes) % 10);
            $minutes_start = $minutes - $interval;
            $minutes_end = $minutes + (10 - $interval);
            $minutes_num_stat_key = "room_stats_send_gift_num_minutes_" . $minutes_start . "_" . $minutes_end . "_room_id" . $this->id;
            $num = $hot_cache->get($minutes_num_stat_key);

            if ($percent > 0) {
                $num = $num * (1 - $percent / 100);
            }

            $percent += 8;

            if ($num > 0) {
                $total_num += $num;
            }

            info($this->id, $num, $percent, $total_num, $minutes_num_stat_key);
        }

        return $total_num;
    }

    function getRealUserPayScore()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getRealUserListKey();
        $user_ids = $hot_cache->zrange($key, 0, -1);
        $user_num = count($user_ids);
        $score = 0;

        //可优化
        if ($user_num > 0) {
            $score = $user_num * 10;
        }

        return $score;
    }

    function getRoomHostScore()
    {
        $user = $this->user;
        $day_charm_value = $user->getDayCharmValue();
        $day_wealth_value = $user->getDayWealthValue();

        $wealth_score = $day_wealth_value / 50;
        $charm_score = $day_charm_value / 100;

        $total_score = $wealth_score + $charm_score;

        info($this->id, $total_score);

        return $total_score;
    }

    function getIdCardAuthUsersScore()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getRealUserListKey();
        $user_ids = $hot_cache->zrange($key, 0, -1);

        $score = 0;

        $user_ids = array_diff($user_ids, [$this->user_id]);

        //可优化
        if (count($user_ids) > 0) {
            $users = Users::findByIds($user_ids);

            foreach ($users as $user) {

                if ($user->isIdCardAuth()) {
                    $day_charm_value = $user->getDayCharmValue();
                    $day_wealth_value = $user->getDayWealthValue();
                    $score += $day_wealth_value / 500;
                    $score += $day_charm_value / 1000;
                }
            }
        }

        info($this->id, $score);
        return $score;
    }

    //停留时间分值
    function getRealUserStayTimeScore()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getRealUserListKey();

        $user_num = $hot_cache->zcount($key, '-inf', time() - 15 * 60);

        info($this->id, $user_num);

        return intval($user_num);
    }

    //热门房间总分值
    function getTotalScore()
    {
        $is_shield = 0;

        if ($this->isShieldRoom()) {

            $is_shield = 1;

            $send_gift_amount_score_rate = 0.5;
            $send_gift_num_score_rate = 0.02;
            $real_user_pay_score_rate = 0.45;
            $real_user_stay_time_score_rate = 0.01;
            $room_host_score_rate = 0.01;
            $id_card_auth_users_score_rate = 0.01;

        } else {

            $send_gift_amount_score_rate = 0.5;
            $send_gift_num_score_rate = 0.02;
            $real_user_pay_score_rate = 0.45;
            $real_user_stay_time_score_rate = 0.01;
            $room_host_score_rate = 0.01;
            $id_card_auth_users_score_rate = 0.01;
        }

        $send_gift_amount_score = $this->getRoomSendGiftAmountScore() * $send_gift_amount_score_rate;
        $send_gift_num_score = $this->getRoomSendGiftNumScore() * $send_gift_num_score_rate;
        $real_user_pay_score = $this->getRealUserPayScore() * $real_user_pay_score_rate;
        $real_user_stay_time_score = $this->getRealUserStayTimeScore() * $real_user_stay_time_score_rate;
        $room_host_score = $this->getRoomHostScore() * $room_host_score_rate;
        $id_card_auth_users_score = $this->getIdCardAuthUsersScore() * $id_card_auth_users_score_rate;


        $total_score = $send_gift_amount_score + $send_gift_num_score + $real_user_pay_score + $real_user_stay_time_score
            + $room_host_score + $id_card_auth_users_score;

        $total_score = intval($total_score);

        $ratio = $this->getHotRoomScoreRatio();

        if ($ratio) {
            $total_score = $total_score * $ratio;
        }

        $user_db = Users::getUserDb();

        if (!$is_shield) {
            $total_score = $total_score * 1.5;
        }

//        if (preg_match("/broadcast/i", $this->types)) {
//            $total_score = $total_score * 5;
//        }

        $data = [
            'send_gift_amount_score' => $send_gift_amount_score, 'send_gift_num_score' => $send_gift_num_score,
            'real_user_pay_score' => $real_user_pay_score, 'real_user_stay_time_score' => $real_user_stay_time_score,
            'room_host_score' => $room_host_score, 'id_card_auth_users_score' => $id_card_auth_users_score, 'total_score' => $total_score,
            'is_shield' => $is_shield, 'time' => time()
        ];

        $user_db->hmset("hot_room_score_record_room_id_{$this->id}", $data);

        info($this->id, $send_gift_amount_score, $send_gift_num_score, $real_user_pay_score, $real_user_stay_time_score, $room_host_score,
            $id_card_auth_users_score, $ratio, $total_score);

        return $total_score;
    }

    function getTotalScoreByCache()
    {
        $user_db = Users::getUserDb();
        $total_score = $user_db->hget("hot_room_score_record_room_id_{$this->id}", 'total_score');

        return intval($total_score);
    }

    //用户总人数
    function getUserNum()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        return $hot_cache->zcard($key);
    }

    //真实用户人数
    function getRealUserNum()
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getRealUserListKey();
        return $hot_cache->zcard($key);
    }

    //沉默用户人数
    function getSilentUserNum()
    {
        $num = $this->getUserNum() - $this->getRealUserNum();
        return $num;
    }

    //获取房间扶持分值
    function getHotRoomScoreRatio()
    {
        $user_db = Users::getUserDb();
        $key = "hot_room_score_ratio_room_id_{$this->id}";
        return intval($user_db->get($key));
    }

    /**
     * @param $room_id
     * @return false|int
     */
    static function getBoomGiftExpireAt($room_id)
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

    //获取房间收益
    function getTotalAmount()
    {
        $db = Users::getUserDb();
        return $db->zscore("stat_room_income_list", $this->id);
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

    function isIosAuthRoom()
    {
        $hot_cache = Rooms::getHotReadCache();
        return intval($hot_cache->zscore(Rooms::generateIosAuthRoomListKey(), $this->id)) > 0;
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
            'monologue' => $user->monologue, 'room_seats' => $room_seat_datas, 'managers' => $this->findManagers(true),
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

    function generateManagerListKey()
    {
        return "room_manager_list_id" . $this->id;
    }

    function generateManagerCacheKey()
    {
        $key = "room_manager_cache_room_id_" . $this->id;
        return $key;
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

    function getRoomTagNamesText()
    {
        if ($this->room_tag_names) {
            return explode(',', $this->room_tag_names);
        }

        return [];
    }

    function getChannelName()
    {
        return $this->id . 'c' . md5($this->id . 'u' . $this->user_id);
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

            if (in_array($this->id, \Rooms::getGameWhiteList()) || isInternalIp($user->ip)) {

                $menu_config[] = ['show' => true, 'title' => '红包', 'type' => 'red_packet',
                    'url' => 'url://m/red_packets?room_id=' . $this->id, 'icon' => $root_host . 'images/red_packet.png'];

            }

            if ($is_host) {
                $menu_config[] = ['show' => true, 'url' => 'app://users/pk', 'title' => 'PK', 'type' => 'pk', 'icon' => $root_host . 'images/pk.png'];
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

    static function generateAbnormalExitRoomListKey()
    {
        return "abnormal_exit_room_list";
    }

    static function getAbnormalExitRoomList()
    {
        $hot_cache = Rooms::getHotReadCache();

        return $hot_cache->zrange(self::generateAbnormalExitRoomListKey(), 0, -1);
    }

    static function isInAbnormalExitRoomList($room_id, $user_id)
    {
        $hot_cache = Rooms::getHotReadCache();
        return $hot_cache->zscore(self::generateAbnormalExitRoomListKey(), $room_id . "_" . $user_id) > 0;
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

    static function getGameWhiteList()
    {
        $hot_cache = Rooms::getHotWriteCache();
        return $hot_cache->zrange("room_game_white_list", 0, -1);
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

    function isInHotList()
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();

        return $hot_cache->zscore($hot_room_list_key, $this->id) > 0;
    }

    function generateFilterUserKey($user_id)
    {
        return "filter_user_" . $this->id . "and" . $user_id;
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

    function isForbidEnter($user)
    {
        $hot_cache = Rooms::getHotReadCache();
        $key = "room_forbid_user_room{$this->id}_user{$user->id}";

        return $hot_cache->get($key) > 0;
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

    function findUsers($page, $per_page)
    {
        $hot_cache = self::getHotWriteCache();
        $key = $this->getUserListKey();
        $total_entries = $hot_cache->zcard($key);

        $offset = $per_page * ($page - 1);

        $user_ids = $hot_cache->zrevrange($key, $offset, $offset + $per_page - 1);
        $users = Users::findByIds($user_ids);

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

    function getUserListKey()
    {
        return 'room_user_list_' . $this->id;
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

    function getRealUserListKey()
    {
        return 'room_real_user_list_' . $this->id;
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

    //是否为电台房间
    function isBroadcast()
    {
        return ROOM_THEME_TYPE_BROADCAST == $this->theme_type || ROOM_THEME_TYPE_USER_BROADCAST == $this->theme_type;
    }
}