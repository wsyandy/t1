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
            'sender_id' => $this->sender_id,
            'pay_type' => $this->pay_type,
            'pay_type_text' => $this->getPayTypeText()
        ];
    }

    function mergeJson()
    {
        return [
            'user_name' => $this->getGiftUser($this->user_id)->nickname,
            'sender_name' => $this->getGiftUser($this->sender_id)->nickname,
            'image_url' => $this->gift_image_url,
            'image_small_url' => $this->gift_image_small_url,
            'image_big_url' => $this->gift_image_big_url,
            'gift_type_text' => $this->getGiftTypeText(),
            'pay_type_text' => $this->getPayTypeText(),
        ];
    }


    function getGiftTypeText()
    {
        return fetch(Gifts::$TYPE, $this->gift_type);
    }

    function getPayTypeText()
    {
        return fetch(Gifts::$PAY_TYPE, $this->pay_type);
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

        $sender = Users::findFirstById($sender_id);
        if (!$sender->isSilent() && !$sender->canGiveGift($gift, $gift_num)) {
            return false;
        }

        $receiver = Users::findFirstById($receiver_id);
        if (!$receiver) {
            return false;
        }

        $gift_order = new GiftOrders();
        $gift_order->sender_id = $sender_id;
        $gift_order->user_id = $receiver_id;
        $gift_order->gift_id = $gift->id;
        $gift_order->amount = $gift->amount * $gift_num;
        $gift_order->name = $gift->name;
        $gift_order->pay_type = $gift->pay_type;
        $gift_order->gift_type = $gift->type;
        $gift_order->status = GIFT_ORDER_STATUS_WAIT;
        $gift_order->gift_num = $gift_num;
        $gift_order->receiver_user_type = $receiver->user_type;
        $gift_order->sender_user_type = $sender->user_type;
        $gift_order->receiver_union_id = $receiver->union_id;
        $gift_order->sender_union_id = $sender->union_id;
        $gift_order->receiver_union_type = $receiver->union_type;
        $gift_order->sender_union_type = $sender->union_type;

        // 在房间里送里面
        if ($sender->current_room_id && $receiver->current_room_id && $sender->current_room_id == $receiver->current_room_id) {
            $gift_order->room_id = $sender->current_room_id;
            $gift_order->room_union_id = $sender->current_room->union_id;
            $gift_order->room_union_type = $sender->current_room->union_type;
        }

        if ($gift_order->create()) {
            $remark = '购买礼物(' . $gift->name . ')' . $gift_num . '个, 花费钻石' . $gift_order->amount;
            $opts = ['gift_order_id' => $gift_order->id, 'remark' => $remark, 'mobile' => $sender->mobile];

            //扣除钻石
            if ($gift->isDiamondPayType()) {
                $result = \AccountHistories::changeBalance($gift_order->sender_id, ACCOUNT_TYPE_BUY_GIFT, $gift_order->amount, $opts);
            } else {
                //扣除金币
                $result = \GoldHistories::changeBalance($gift_order->sender_id, GOLD_TYPE_BUY_GIFT, $gift_order->amount, $opts);
            }

            if ($result) {

                $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;
                $gift_order->update();

                if ($gift->isCar()) {
                    \UserGifts::delay()->updateGiftExpireAt($gift_order->id);
                } else {
                    \UserGifts::delay()->updateGiftNum($gift_order->id);

                    if ($gift->isDiamondPayType()) {
                        //座驾不增加hi币
                        \HiCoinHistories::delay()->createHistory($gift_order->user_id, ['gift_order_id' => $gift_order->id]);
                    }
                }

                if ($gift->isDiamondPayType()) {
                    $gift_order->updateUserData();
                }

            } else {
                $gift_order->status = GIFT_ORDER_STATUS_WAIT;
                $gift_order->update();
            }

            return $result;
        }

        info("send_gift_fail", $sender->sid, $receiver->sid, $sender->diamond, $gift->id, $gift_num);
        return false;
    }

    function updateUserData()
    {
        //统计房间收益
        if ($this->room) {
            $this->room->statIncome($this->amount);

            if ($this->sender_id != $this->user_id) {
                //推送全局消息
                Rooms::allNoticePush($this);
            }
        }

        \Users::delay()->updateExperience($this->id);
        \Users::delay()->updateCharm($this->id);
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

    function isDiamondPayType()
    {
        return GIFT_PAY_TYPE_DIAMOND == $this->pay_type;
    }

    function allNoticePushContent()
    {
        $user = $this->user;
        $sender = $this->sender;
        $gift_num = $this->gift_num;
        $name = $this->name;
        $content = "<p style='font-size: 14px;text-align: left'><span style='color: #F5DF00'>{$user->nickname}</span><span style='color: white'>收到</span><span style='color: #F5DF00'>{$sender->nickname}</span><span style='color: white'>送的</span><span style='color: #F5DF00'>{$name}×{$gift_num}</span><span style='color: white'>,感动全场，求掌声，求祝福</span></p>";

        return $content;
    }
}