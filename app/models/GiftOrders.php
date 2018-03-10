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

    /**
     * @type Rooms
     */
    private $_room;

    static $STATUS = [
        GIFT_ORDER_STATUS_WAIT => '等待支付',
        GIFT_ORDER_STATUS_SUCCESS => '支付成功',
        GIFT_ORDER_STATUS_FAIL => '支付失败'
    ];

    function toDetailJson()
    {
        return [
            'name' => $this->name,
            'user_name' => $this->getGiftUser($this->user_id)->nickname,
            'sender_name' => $this->getGiftUser($this->sender_id)->nickname,
            'user_avatar_small_url' => $this->getGiftUser($this->user_id)->avatar_small_url,
            'sender_avatar_small_url' => $this->getGiftUser($this->sender_id)->avatar_small_url,
            'amount' => $this->amount,
            'gift_num' => $this->gift_num,
            'image_url' => $this->gift_image_url,
            'image_small_url' => $this->gift_image_small_url,
            'image_big_url' => $this->gift_image_big_url,
            'created_at_text' => $this->created_at_text,
            'user_id' => $this->user_id,
            'sender_id' => $this->sender_id
        ];
    }

    function mergeJson()
    {
        return [
            'user_name' => $this->getGiftUser($this->user_id)->nickname,
            'sender_name' => $this->getGiftUser($this->sender_id)->nickname,
            'image_url' => $this->gift_image_url,
            'image_small_url' => $this->gift_image_small_url,
            'image_big_url' => $this->gift_image_big_url
        ];
    }

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

        if ($sender->current_room_id && $receiver->current_room_id && $sender->current_room_id == $receiver->current_room_id) {
            $gift_order->room_id = $sender->current_room_id;
        }

        if ($gift_order->create()) {
            $remark = '购买礼物(' . $gift->name . ')' . $gift_num . '个, 花费钻石' . $gift_order->amount;
            $opts = ['gift_order_id' => $gift_order->id, 'remark' => $remark, 'mobile' => $sender->mobile];
            $result = \AccountHistories::changeBalance($gift_order->sender_id, ACCOUNT_TYPE_BUY_GIFT, $gift_order->amount, $opts);
            if ($result) {
                $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;
                \UserGifts::delay()->updateGiftNum($gift_order->id);
                \Users::delay()->updateExperience($gift_order->id);

                //统计房间收益
                if ($gift_order->room) {
                    $gift_order->room->statIncome($gift_order->amount);
                }

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

    function getGiftUser($id)
    {
        $user = \Users::findFirstById($id);
        return $user;
    }
}