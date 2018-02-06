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
        $gift = \Gifts::findById($gift_order->gift_id);
        $user_gift->gift_id = $gift->id;
        $user_gift->name = $gift->name;
        $user_gift->gift_num = intval($user_gift->gift_num) + $gift_order->gift_num;
        $user_gift->amount = $gift->amount;
        $user_gift->total_amount = $user_gift->amount * $user_gift->gift_num + intval($user_gift->total_amount);
        $user_gift->num = $gift_order->gift_num + intval($user_gift->num);
        $user_gift->pay_type = 'diamond';
        $user_gift->save();

        $user = $user_gift->user;
        $hi_coins = ($gift->amount * $gift_order->gift_num) / 10;
        $user->hi_coins = $user->hi_coins + $hi_coins;
        $user->save();

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
}