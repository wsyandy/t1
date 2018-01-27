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
        if (self::lock($gift_order)) {
            $user_gift = \UserGifts::findFirstOrNew(['user_id' => $gift_order->user_id, 'gift_id' => $gift_order->gift_id]);
            $gift = \Gifts::findById($gift_order->gift_id);
            $user_gift->gift_id = $gift->id;
            $user_gift->name = $gift->name;
            $user_gift->gift_num = intval($user_gift->gift_num) + $gift_order->gift_num;
            $user_gift->amount = $gift->amount;
            $user_gift->total_amount = $user_gift->amount * $user_gift->gift_num + intval($user_gift->total_amount);
            $user_gift->num = $gift_order->gift_num + intval($user_gift->num);
            $user_gift->pay_type = 'diamond';
            $user_gift->save();
            self::unlock($gift_order);
            return $user_gift;
        } else {
            return \UserGifts::delay()->updateGiftNum($gift_order_id);
        }
    }

    static function lock($gift_order)
    {
        $hot_db = self::getHotWriteCache();
        $expire_secs = 60 * 2;
        return $hot_db->set(self::lockKey($gift_order), $gift_order->id, array('nx', 'ex' => $expire_secs));
    }

    static function lockKey($gift_order)
    {
        $lock_key = "user_gift_lock_" . $gift_order->user_id . '_' . $gift_order->gift_id;
        debug("lock_key: " . $lock_key);
        return $lock_key;
    }

    static function unlock($gift_order)
    {
        $hot_db = self::getHotWriteCache();
        $lock_key = self::lockKey($gift_order);
        if ($hot_db->get($lock_key) == $gift_order->id) {
            $hot_db->del($lock_key);
        }
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
}