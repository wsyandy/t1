<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 上午10:48
 */

class GiftOrders extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Gifts
     */
    private $_gift;

    /**
     * @type Users
     */
    private $_sender;

    static $status = array(
        GIFT_ORDER_STATUS_WAIT => '等待支付',
        GIFT_ORDER_STATUS_SUCCESS => '支付成功',
        GIFT_ORDER_STATUS_FAIL => '支付失败'
    );

    /**
     * @param $sender_id
     * @param $receiver_id
     * @param $gift
     * @param $gift_num
     * @return bool
     */

    static function giveTo($sender_id, $receiver_id, $gift, $gift_num)
    {
        $sender = \Users::findById($sender_id);
        if (!$sender->canGiveGift($gift, $gift_num)) {
            return false;
        }

        $gift_order = new \GiftOrders();
        $gift_order->sender_id = $sender_id;
        $gift_order->user_id = $receiver_id;
        $gift_order->gift_id = $gift->id;
        $gift_order->amount = $gift->amount * $gift_num;
        $gift_order->name = $gift->name;
        $gift_order->pay_type = 'diamond';
        $gift_order->status = GIFT_ORDER_STATUS_WAIT;
        if ($gift_order->create()) {
            $result = \AccountHistories::changeBalance(
                $gift_order->sender_id,
                ACCOUNT_TYPE_BUY_GIFT,
                $gift_order->amount,
                array(
                    'gift_order_id' => $gift_order->id,
                    'remark' => '购买礼物(' . $gift->name .')' . $gift_num . '个, 花费钻石' . $gift_order->amount
                )
            );
            if ($result) {
                $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;
                \UserGifts::delay()->freshGiftNum($gift_order->user_id, $gift->id, $gift_num);
            } else {
                $gift_order->status = GIFT_ORDER_STATUS_WAIT;
            }
            $gift_order->update();
            return $result;
        }
        return false;
    }

    static function findOrderListByUser($user_id, $page, $per_page)
    {
        $conditions = array(
            'conditions' => 'user_id = :user_id:',
            'bind' => array(
                'user_id' => $user_id
            ),
            'order' => 'id desc'
        );
        return \GiftOrders::findPagination($conditions, $page, $per_page);
    }

    function getStatusText()
    {
        return fetch(\GiftOrders::$status, $this->status);
    }

}