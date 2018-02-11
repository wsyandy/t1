<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 上午10:48
 */
class UserGifts extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Gifts
     */
    private $_gift;

    static function updateGiftNum($gift_order_id)
    {
        $gift_order = \GiftOrders::findById($gift_order_id);

        if (isBlank($gift_order) || !$gift_order->isSuccess()) {
            return false;
        }

        $lock_key = "user_gift_lock_" . $gift_order->user_id . '_' . $gift_order->gift_id;
        $lock = tryLock($lock_key);

        $user_gift = \UserGifts::findFirstOrNew(['user_id' => $gift_order->user_id, 'gift_id' => $gift_order->gift_id]);
        $gift = \Gifts::findFirstById($gift_order->gift_id);
        $gift_amount = $gift->amount;
        $gift_num = $gift_order->gift_num;

        $user_gift->gift_id = $gift->id;
        $user_gift->name = $gift->name;
        $user_gift->amount = $gift_amount;
        $user_gift->num = $gift_num + intval($user_gift->num);
        $user_gift->total_amount = $gift_amount * $gift_num + intval($user_gift->total_amount);
        $user_gift->pay_type = 'diamond';
        $user_gift->save();

        $user = $user_gift->user;
        $hi_coins = ($gift_amount * $gift_num) / 10;
        $user->hi_coins = $user->hi_coins + $hi_coins;
        $user->save();

        $user_gift->statSilentUserSendGiftNum($gift_order);

        unlock($lock);
        return $user_gift;
    }

    static function findListByUserId($user_id, $page, $per_page)
    {
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];
        return \UserGifts::findPagination($conditions, $page, $per_page);
    }

    function toJson()
    {
        return array(
            'gift_id' => $this->gift_id,
            'name' => $this->gift_name,
            'amount' => $this->amount,
            'pay_type' => $this->pay_type,
            'image_url' => $this->gift_image_url,
            'image_small_url' => $this->gift_image_small_url,
            'image_big_url' => $this->gift_image_big_url,
            'dynamic_image_url' => $this->gift_dynamic_image_url,
            'num' => $this->num
        );
    }

    //统计沉默用户送礼物个数
    function statSilentUserSendGiftNum($gift_order)
    {
        $sender = $gift_order->sender;

        info($gift_order->user_id, $gift_order->sender_id, $this->id);

        if ($sender->isActive()) {
            info("active_user_not_need_stat");
            return;
        }

        $current_room = $sender->current_room;
        $hot_cache = self::getHotWriteCache();

        if ($current_room) {

            info($current_room->id);

            $amount_day_key = $current_room->getStatGiftAmountKey();
            $amount_hour_key = $current_room->getStatGiftAmountKey(false);
            $user_num_day_key = $current_room->getStatGiftUserNumKey();
            $user_num_hour_key = $current_room->getStatGiftUserNumKey(false);
            $send_gift_rooms_key = date("Ymd") . "_user_send_gift_rooms_user_id_" . $sender->id;

            $hot_cache->incrby($amount_day_key, $gift_order->amount);
            $hot_cache->incrby($amount_hour_key, $gift_order->amount);
            $hot_cache->zadd($user_num_day_key, time(), $sender->id);
            $hot_cache->zadd($user_num_hour_key, time(), $sender->id);
            $hot_cache->zadd($send_gift_rooms_key, time(), $current_room->id);

            $day_expire = endOfDay() + 60 - time();
            $hour_expire = endOfHour() + 60 - time();

            $hot_cache->expire($amount_day_key, $day_expire);
            $hot_cache->expire($amount_hour_key, $hour_expire);
            $hot_cache->expire($user_num_day_key, $day_expire);
            $hot_cache->expire($user_num_hour_key, $hour_expire);
            $hot_cache->expire($send_gift_rooms_key, $day_expire);
        }
    }
}