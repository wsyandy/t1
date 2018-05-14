<?php

class WishHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;
    /**
     * @type ProductChannels
     */
    private $_product_channel;


    function toSimpleJson()
    {
        $guarded_number = \WishHistories::getGuardedNumber($this->product_channel_id, $this->id);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'wish_text' => $this->wish_text,
            'user_nickname' => $this->user_nickname,
            'user_uid' => $this->user->uid,
            'user_avatar_url' => $this->user->avatar_url,
            'guarded_number' => $guarded_number,
            'age' => $this->user->age
        ];
    }

    static function createWishHistories($opts)
    {
        $user_id = fetch($opts, 'user_id');
        $wish_text = fetch($opts, 'wish_text');
        $product_channel_id = fetch($opts, 'product_channel_id');

        $wish_histories = new \WishHistories();
        $wish_histories->user_id = $user_id;
        $wish_histories->wish_text = $wish_text;
        $wish_histories->product_channel_id = $product_channel_id;
        if ($wish_histories->save()) {
            $user_db = \Users::getUserDb();
            $guard_wish_key = \WishHistories::getGuardWishKey($product_channel_id);
            $user_db->zadd($guard_wish_key, 0, $wish_histories->id);

            $lucky_user_key = self::generateLuckyUserList($product_channel_id);
            $lucky_user_ids = $user_db->zrange($lucky_user_key, 0, -1);

            //如果发布愿望的用户已经在幸运用户集合当中，即中过奖，则不在加入集合中
            if (!in_array($wish_histories->user_id, $lucky_user_ids)) {
                $guard_wish_user_list_key = \WishHistories::generateNoLuckyUserList($product_channel_id);
                $user_db->zadd($guard_wish_user_list_key, time(), $wish_histories->user_id);
            }

            return $wish_histories->id;
        }
    }

    static function getGuardWishKey($product_channel_id)
    {
        return 'guarded_wish_for_product_channel_' . $product_channel_id;
    }

    static function generateNoLuckyUserList($product_channel_id)
    {
        return 'no_lucky_user_list_' . $product_channel_id;
    }

    //获取愿望分页列表
    static function findByRelationsForWish($relations_key, $per_page)
    {
        $user_db = \Users::getUserDb();
        $total_entries = $user_db->zcard($relations_key);
        $total_page = ceil($total_entries / $per_page);
        $page = mt_rand(1, $total_page);

        $offset = $per_page * ($page - 1);
        $res = $user_db->zrevrange($relations_key, $offset, $offset + $per_page - 1, 'withscores');
        $wish_history_ids = [];
        foreach ($res as $wish_history_id => $guarded_number) {
            $wish_history_ids[] = $wish_history_id;
        }
        if (!$wish_history_ids) {
            return null;
        }

        $wish_histories = self::findByIds($wish_history_ids);

        $total_entries = $user_db->zcard($relations_key);
        $pagination = new PaginationModel($wish_histories, $total_entries, $page, $per_page);
        $pagination->clazz = 'WishHistories';

        return $pagination;
    }

    static function getGuardedNumber($product_channel_id, $id)
    {
        $user_db = \Users::getUserDb();
        $key = self::getGuardWishKey($product_channel_id);
        $guarded_number = $user_db->zscore($key, $id);
        info('当前愿望的守护数', $guarded_number);
        return $guarded_number;
    }

    static function getUserIdGuarded($relations_key, $limit)
    {
        $user_db = \Users::getUserDb();

        $res = $user_db->zrevrange($relations_key, 0, $limit - 1, 'withscores');
        $wish_history_info = [];
        foreach ($res as $wish_history_id => $guarded_number) {
            $info = self::findById($wish_history_id);
            $wish_history_info[] = array(
                'user_id' => $info->user_id,
                'guarded_number' => $guarded_number
            );
        }
        if (!$wish_history_info) {
            return null;
        }

        return $wish_history_info;
    }

    static function getRand($product_channel_id)
    {
        $user_db = \Users::getUserDb();
        $key = self::generateNoLuckyUserList($product_channel_id);
        $ids = $user_db->zrange($key, 0, -1);
        $lucky_user_key = self::generateLuckyUserList($product_channel_id);

        if (6 > count($ids)) {
            foreach ($ids as $id) {
                self::afterWin($key, $lucky_user_key, $id);
            }
        }

        $lucky_ids = [];

        for ($i = 0; $i < 5; $i++) {
            $index = array_rand($ids);
            $lucky_ids[] = $ids[$index];
            unset($ids[$index]);
        }
        info($lucky_ids);
        //中奖的用户加入幸运用户集合中，并从no_lucky的集合中删除
        foreach ($lucky_ids as $lucky_id) {
            self::afterWin($key, $lucky_user_key, $lucky_id);
        }
    }

    static function generateLuckyUserList($product_channel_id)
    {
        return 'lucky_user_list_' . $product_channel_id;
    }

    static function afterWin($key, $lucky_user_key, $lucky_id)
    {
        $user_db = \Users::getUserDb();
        $user_db->zrem($key, $lucky_id);
        $user_db->zadd($lucky_user_key, time(), $lucky_id);

        $time = date('Ymd195959');
        $current_day_lucky_key = self::generateCurrentLuckyKey($time);
        $user_db->zadd($current_day_lucky_key, time(), $lucky_id);
    }

    static function generateCurrentLuckyKey($time)
    {
        return 'current_day_lucky_user_list_' . $time;
    }

    static function getLuckyUserList()
    {
        $db = \Users::getUserDb();
        $time = time() < strtotime(date('Y-m-d 19:59:59')) ? date('Ymd195959', time() - 24 * 3600) : date('Ymd195959');

        $lucky_user_key = self::generateCurrentLuckyKey($time);
        $lucky_user_ids = $db->zrange($lucky_user_key, 0, -1);
//        $lucky_user_key = self::generateLuckyUserList($product_channel_id);
//        $start_at = strtotime(date('Y-m-d 19:59:59', time()));
////        $start_at = endOfHour(strtotime('Y-m-d 19:59:59'));
//        $stop_at = strtotime(date('Y-m-d 19:59:59', time() + 24 * 60 * 60));
////        $stop_at = endOfHour(strtotime('Y-m-d 19:59:59', '+1 day'));
//        $lucky_user_ids = $db->zrangebyscore($lucky_user_key, $start_at, $stop_at);
//        if (isBlank($lucky_user_ids)) {
//            $start_at = strtotime(date('Y-m-d 19:59:59', time() - 24 * 60 * 60));
//            $stop_at = strtotime(date('Y-m-d 19:59:59', time()));
//            $lucky_user_ids = $db->zrangebyscore($lucky_user_key, $start_at, $stop_at);
//        }
        info($time, $lucky_user_key, $lucky_user_ids);
        $lucky_users = \Users::findByIds($lucky_user_ids);
        $lucky_names = [];
        foreach ($lucky_users as $index => $lucky_user) {
            $lucky_names[$index]['uid'] = $lucky_user->uid;
            $lucky_names[$index]['nickname'] = $lucky_user->nickname;
        }
        return $lucky_names;
    }
}