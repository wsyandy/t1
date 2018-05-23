<?php

trait RoomStats
{
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

    static public function generateBoomCurIncomeKey($room_id)
    {
        return 'boom_target_value_room_' . $room_id;
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
}