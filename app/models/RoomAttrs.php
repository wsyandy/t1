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

        $score = 0;

        //可优化
        if (count($user_ids) > 0) {
            $pay_user_num = Users::count([
                'conditions' => '(pay_amount > 0 or pay_amount is not null) and id in (' . implode(',', $user_ids) . ")",
                'columns' => 'id']);

            $no_pay_user_num = $this->getRealUserNum() - $pay_user_num;

            $score += $pay_user_num * 10;
            $score += $no_pay_user_num * 1;
        }

        info($this->id, $score);

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

            $send_gift_amount_score_rate = 0.7;
            $send_gift_num_score_rate = 0.05;
            $real_user_pay_score_rate = 0.1;
            $real_user_stay_time_score_rate = 0.05;
            $room_host_score_rate = 0.05;
            $id_card_auth_users_score_rate = 0.05;

        } else {

            $send_gift_amount_score_rate = 0.1;
            $send_gift_num_score_rate = 0.05;
            $real_user_pay_score_rate = 0.6;
            $real_user_stay_time_score_rate = 0.1;
            $room_host_score_rate = 0.1;
            $id_card_auth_users_score_rate = 0.05;
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
        $user_db = Rooms::getRoomDb();
        $key = "hot_room_score_ratio_room_id_{$this->id}";
        return intval($user_db->get($key));
    }
}