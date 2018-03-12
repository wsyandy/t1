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

    static $STATUS = [
        GIFT_ORDER_STATUS_WAIT => '等待支付',
        GIFT_ORDER_STATUS_SUCCESS => '支付成功',
        GIFT_ORDER_STATUS_FAIL => '支付失败'
    ];

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

        if (!$sender->isSilent() && !$sender->canGiveGift($gift, $gift_num)) {
            return false;
        }

        $receiver = Users::findFirstById($receiver_id);

        if (!$receiver) {
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
        $gift_order->gift_num = $gift_num;
        $gift_order->receiver_user_type = $receiver->user_type;
        $gift_order->sender_user_type = $sender->user_type;
        if ($gift_order->create()) {
            $remark = '购买礼物(' . $gift->name . ')' . $gift_num . '个, 花费钻石' . $gift_order->amount;
            $opts = ['gift_order_id' => $gift_order->id, 'remark' => $remark, 'mobile' => $sender->mobile];
            $result = \AccountHistories::changeBalance($gift_order->sender_id, ACCOUNT_TYPE_BUY_GIFT, $gift_order->amount, $opts);
            if ($result) {
                $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;
                $gift_order->update();
                \UserGifts::delay()->updateGiftNum($gift_order->id);
            } else {
                $gift_order->status = GIFT_ORDER_STATUS_WAIT;
                $gift_order->update();
            }
            return $result;
        }
        return false;
    }

    static function findOrderListByUser($user_id, $page, $per_page)
    {
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => [
                'user_id' => $user_id
            ],
            'order' => 'id desc'
        ];
        return \GiftOrders::findPagination($conditions, $page, $per_page);
    }

    function isSuccess()
    {
        return GIFT_ORDER_STATUS_SUCCESS == $this->status;
    }

    function getReceiverUserTypeText()
    {
        return fetch(Users::$USER_TYPE, $this->receiver_user_type);
    }

    function getSenderUserTypeText()
    {
        return fetch(Users::$USER_TYPE, $this->sender_user_type);
    }
}