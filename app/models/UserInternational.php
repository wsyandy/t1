<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午3:21
 */
trait UserInternational
{
    static function searchByInternational($user, $page, $per_page, $opts = [])
    {
        $user_id = fetch($opts, 'user_id');
        $nickname = fetch($opts, 'nickname');
        $uid = fetch($opts, 'uid');
        $province_id = fetch($opts, 'province_id');
        $city_id = fetch($opts, 'city_id');
        $filter_ids = fetch($opts, 'filter_ids');

        if ($user_id) {
            $cond = ['conditions' => 'id = :user_id:', 'bind' => ['user_id' => $user_id]];
        } else {
            $cond = ['conditions' => 'id <> ' . $user->id];
        }

        //用户检索
        if ($uid && $nickname) {
            $cond['conditions'] .= ' and (uid = :uid: or nickname like :nickname:) ';
            $cond['bind']['uid'] = $uid;
            $cond['bind']['nickname'] = "%{$nickname}%";
        }else {
            if ($nickname){
                $cond['conditions'] .= ' and (nickname like :nickname:) ';
                $cond['bind']['nickname'] = "%{$nickname}%";
            }
        }

        if ($city_id) {
            $cond['conditions'] .= ' and (city_id=:city_id: or geo_city_id=:geo_city_id: or ip_city_id=:ip_city_id:)';
            $cond['bind']['city_id'] = $city_id;
            $cond['bind']['geo_city_id'] = $city_id;
            $cond['bind']['ip_city_id'] = $city_id;
        }

        if ($province_id) {
            $cond['conditions'] .= ' and (province_id=:province_id: or geo_province_id=:geo_province_id: or ip_province_id=:ip_province_id:)';
            $cond['bind']['province_id'] = $province_id;
            $cond['bind']['geo_province_id'] = $province_id;
            $cond['bind']['ip_province_id'] = $province_id;
        }

        $user_type = fetch($opts, 'user_type', USER_TYPE_ACTIVE);
        if ($user_type) {
            $cond['conditions'] .= " and user_type = " . $user_type;
        }

        if ($filter_ids) {
            $cond['conditions'] .= " and id not in ({$filter_ids})";
        }

        $cond['conditions'] .= " and id != " . SYSTEM_ID . " and avatar_status = " . AUTH_SUCCESS . ' and (user_status = ' . USER_STATUS_ON .
            ' or user_status = ' . USER_STATUS_LOGOUT . ')';
        $cond['order'] = 'last_at desc,id desc';

        info($user->id, $cond);

        $users = Users::findPagination($cond, $page, $per_page);

        return $users;
    }

    function calculateLevelByInternational()
    {
        $level = $this->level;
        $experience = $this->experience;

        if ($experience < 1) {
            return 0;
        } elseif ($experience >= 116000) {
            return 30;
        }

        $level_ranges = [0, 100, 200, 300, 400, 500, 600, 700, 800, 900,
            1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000,
            11000, 16000, 21000, 26000, 31000, 36000, 56000, 76000, 96000, 116000
        ];

        foreach ($level_ranges as $index => $level_range) {

            if (isset($level_ranges[$index + 1]) && $experience >= $level_range &&
                $experience < $level_ranges[$index + 1]
            ) {
                $level = $index;
                break;
            }

        }

        return $level;
    }

    //国际版段位
    function calculateSegmentByInternational()
    {
        $levels = [1, 6, 11, 16, 21, 26, 31];
        $user_level = $this->level;

        $i_segment_enum = array_keys(self::$I_SEGMENT);

        if ($user_level < 1) {
            return '';
        } elseif ($user_level >= 30) {
            return 'vip';
        }

        $i_segment = '';

        foreach ($levels as $index => $level) {

            if (isset($levels[$index + 1]) && $user_level >= $level && $user_level < $levels[$index + 1]) {
                $i_segment = $i_segment_enum[$index];
            }
        }

        return $i_segment;
    }

    //更新用户等级/经验/财富值
    static function updateExperienceByInternational($gift_order_id)
    {
        $gift_order = \GiftOrders::findById($gift_order_id);

        if (isBlank($gift_order) || !$gift_order->isSuccess()) {
            return false;
        }

        $lock_key = "update_user_level_lock_" . $gift_order->sender_id;
        $lock = tryLock($lock_key);

        $sender = $gift_order->sender;
        $amount = $gift_order->amount;
        $sender_experience = 0.02 * $amount;
        $wealth_value = $amount;

        if ($sender) {

            $sender->experience += $sender_experience;
            $sender->level = $sender->calculateLevelByInternational();
            $sender->i_segment = $sender->calculateSegmentByInternational();
            $sender->wealth_value += $wealth_value;

            if (!$sender->isCompanyUser()) {
                Users::updateFiledRankList($sender->id, 'wealth', $wealth_value);
            }

            $union = $sender->union;

            if (isPresent($union) && $union->type == UNION_TYPE_PRIVATE) {
                $sender->union_wealth_value += $wealth_value;
                Unions::delay()->updateFameValue($wealth_value, $union->id);
            }

            $sender->update();
        }

        unlock($lock);
    }

}
