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

    static function freshGiftNum($gift_order_id)
    {
        $gift_order = \GiftOrders::findById($gift_order_id);
        if (isBlank($gift_order) || !$gift_order->isSuccess()) {
            return false;
        }
        $user_gift = \UserGifts::findFirstOrNew(array('user_id' => $gift_order->user_id));
        $gift = \Gifts::findById($gift_order->gift_id);
        $user_gift->gift_id = $gift->id;
        $user_gift->name = $gift->name;
        $user_gift->gift_num = intval($user_gift->gift_num) + $gift_order->gift_num;
        $user_gift->amount = $gift->amount;
        $user_gift->total_amount = $user_gift->amount * $user_gift->gift_num + intval($user_gift->total_amount);
        $user_gift->num = $gift_order->gift_num + intval($user_gift->num);
        $user_gift->pay_type = 'diamond';
        return $user_gift->save();
    }

    static function findListByUserId($user_id, $page, $per_page)
    {
        $conditions = array(
            'conditions' => 'user_id = :user_id:',
            'bind' => array('user_id' => $user_id),
            'order' => 'id desc'
        );
        return \UserGifts::findPagination($conditions, $page, $per_page);
    }
}