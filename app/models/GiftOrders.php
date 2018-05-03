<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 上午10:48
 */
class GiftOrders extends BaseModel
{

    use GiftOrderInternational;

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
        GIFT_ORDER_STATUS_FAIL => '支付失败',
        GIFT_ORDER_STATUS_FREEZE => '冻结'
    ];

    static $TYPE = [GIFT_ORDER_TYPE_USER_SEND => '用户赠送', GIFT_ORDER_TYPE_USER_BUY => '购买',
        GIFT_ORDER_TYPE_SYSTEM_SEND => '系统赠送', GIFT_ORDER_TYPE_ACTIVITY_LUCKY_DRAW => '抽奖赠送'];

    function afterCreate()
    {

    }

    function afterUpdate()
    {
        if ($this->hasChanged('status') && $this->status == GIFT_ORDER_STATUS_SUCCESS && $this->pay_type == GIFT_PAY_TYPE_DIAMOND) {

            //当礼物订单状态为支付成功，并且礼物订单类型为钻石支付的时候，才进行推送
            \DataCollection::syncData('gift_order', 'give_to_success', ['gift_order' => $this->toPushDataJson()]);
        }
    }

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

    function toPushDataJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_name' => $this->getGiftUser($this->user_id)->nickname,
            'sender_name' => $this->getGiftUser($this->sender_id)->nickname,
            'amount' => $this->amount,
            'gift_num' => $this->gift_num,
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

    static function giveTo($sender_id, $receiver_ids, $gift, $gift_num, $total_gift_num)
    {

        if (!is_array($receiver_ids)) {
            $receiver_ids = explode(',', $receiver_ids);
        }

        $sender = Users::findFirstById($sender_id);

        if (!$sender->isSilent() && !$sender->canGiveGift($gift, $total_gift_num)) {
            return false;
        }

        $receivers = Users::findByIds($receiver_ids);
        $error_code = true;

        foreach ($receivers as $receiver) {

            $receiver_id = $receiver->id;

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
            $gift_order->sender_country_id = $sender->country_id;
            $gift_order->receiver_country_id = $receiver->country_id;
            $gift_order->product_channel_id = $receiver->product_channel_id;

            if ($sender_id == $receiver_id) {
                $gift_order->type = GIFT_ORDER_TYPE_USER_BUY;
            } else {
                $gift_order->type = GIFT_ORDER_TYPE_USER_SEND;
            }

            // 在房间里送里面
            if ($sender->current_room_id && $receiver->current_room_id && $sender->current_room_id == $receiver->current_room_id) {
                $gift_order->room_id = $sender->current_room_id;
                $gift_order->room_union_id = $sender->current_room->union_id;
                $gift_order->room_union_type = $sender->current_room->union_type;
            }

            if ($gift_order->create()) {
                $remark = '购买礼物(' . $gift->name . ')' . $gift_num . '个, 花费' . $gift_order->pay_type_text . $gift_order->amount;
                $opts = ['gift_order_id' => $gift_order->id, 'remark' => $remark, 'mobile' => $sender->mobile, 'target_id' => $gift_order->id];

                //扣除钻石
                if ($gift->isDiamondPayType()) {
                    $result = \AccountHistories::changeBalance($gift_order->sender_id, ACCOUNT_TYPE_BUY_GIFT, $gift_order->amount, $opts);
                    if ($result) {
                        //如果赠送者是公司人员，并且接受者不是公司人员，则将其赠送的钻石金额加入到缓存中
                        if ($sender->isCompanyUser() && !$receiver->isCompanyUser()) {
                            $sender->addCompanyUserSendNumber($gift_order->amount);
                        }
                    }
                } else {
                    //扣除金币
                    $result = \GoldHistories::changeBalance($gift_order->sender_id, GOLD_TYPE_BUY_GIFT, $gift_order->amount, $opts);
                }

                if ($result) {
                    $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;

                    if ($gift_order->update()) {
                        $gift_order->updateUserGiftData($gift);
                    } else {
                        $error_code = false;
                        break;
                    }

                } else {
                    $gift_order->status = GIFT_ORDER_STATUS_WAIT;
                    $gift_order->update();

                    $error_code = false;
                    break;
                }
            } else {
                $error_code = false;
                break;
            }
        }

        if (!$error_code) {
            info("Exce send_gift_fail", $sender->sid, $receiver->sid, $sender->diamond, $gift->id, $gift_num);
        }

        return $error_code;
    }

    /**
     * @param $sender_id
     * @param $receiver_ids
     * @param $gift
     * @param $gift_num
     */
    static function sendGift($sender, $receiver_ids, $gift, $gift_num)
    {
        if (!is_array($receiver_ids)) {
            $receiver_ids = explode(",", $receiver_ids);
        }

        $receiver_num = count($receiver_ids);

        if ($receiver_num < 1) {
            info("param error", $sender->id);
            return false;
        }

        //送礼物总个数
        $total_gift_num = $receiver_num * $gift_num;
        $total_amount = intval($gift->amount) * $total_gift_num;

        if (!$sender->canGiveGift($gift, $total_gift_num)) {
            return false;
        }

        $opts = ['mobile' => $sender->mobile];

        $receiver_id = $receiver_ids[0];
        $receiver = Users::findFirstById($receiver_id);

        if ($gift->isDiamondPayType()) {
            $remark = '送礼物消费' . $total_amount . '钻石,礼物总个数' . $total_gift_num . ",礼物id" . $gift->id . ",接收礼物人数" . $receiver_num;
            $opts['remark'] = $remark;

            if ($sender->isCompanyUser()) {
                $sender->addCompanyUserSendNumber($total_amount);
            }

            $target = \AccountHistories::changeBalance($sender->id, ACCOUNT_TYPE_BUY_GIFT, $total_amount, $opts);
        } else {
            $remark = '送礼物消费' . $total_amount . '金币,礼物总个数' . $total_gift_num . ",礼物id" . $gift->id . ",接收礼物人数" . $receiver_num;
            $opts['remark'] = $remark;
            $target = \GoldHistories::changeBalance($sender->id, GOLD_TYPE_BUY_GIFT, $total_amount, $opts);
        }

        if ($target) {

            $opts = ['gift_num' => $gift_num, 'sender_current_room_id' => $sender->current_room_id,
                'receiver_current_room_id' => $receiver->current_room_id, 'target_id' => $target->id, 'time' => $target->created_at];

            self::delay()->asyncCreateGiftOrder($sender->id, $receiver_ids, $gift->id, $opts);

            $opts['async_verify_data'] = 1;
            self::delay(15)->asyncCreateGiftOrder($sender->id, $receiver_ids, $gift->id, $opts);
            self::delay(30)->asyncCreateGiftOrder($sender->id, $receiver_ids, $gift->id, $opts);
            return true;
        }

        return false;
    }

    static function asyncCreateGiftOrder($sender_id, $receiver_ids, $gift_id, $opts = [])
    {
        if (!is_array($receiver_ids)) {
            $receiver_ids = explode(",", $receiver_ids);
        }

        $sender = Users::findFirstById($sender_id);
        $gift = Gifts::findFirstById($gift_id);
        $gift_num = fetch($opts, 'gift_num', 1);
        $sender_current_room_id = fetch($opts, 'sender_current_room_id');
        $receiver_current_room_id = fetch($opts, 'receiver_current_room_id');
        $time = fetch($opts, 'time', time());
        $target_id = fetch($opts, 'target_id');
        $async_verify_data = fetch($opts, 'async_verify_data');

        if ($async_verify_data) {

            $cond = [
                'conditions' => 'target_id = :target_id: and pay_type = :pay_type:',
                'bind' => ['target_id' => $target_id, 'pay_type' => $gift->pay_type],
                'order' => 'id desc'
            ];

            $gift_order = GiftOrders::findFirst($cond);

            if ($gift_order) {
                info("gift_already_save", $sender_id, $receiver_ids, $gift_id, $opts);
                return;
            }

            info("Exce already_save_fail", $sender_id, $receiver_ids, $gift_id, $opts);
        }

        $receivers = Users::findByIds($receiver_ids);

        foreach ($receivers as $receiver) {

            $receiver_id = $receiver->id;

            $gift_order = new GiftOrders();
            $gift_order->sender_id = $sender_id;
            $gift_order->user_id = $receiver_id;
            $gift_order->gift_id = $gift->id;
            $gift_order->amount = $gift->amount * $gift_num;
            $gift_order->name = $gift->name;
            $gift_order->pay_type = $gift->pay_type;
            $gift_order->gift_type = $gift->type;
            $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;
            $gift_order->gift_num = $gift_num;
            $gift_order->receiver_user_type = $receiver->user_type;
            $gift_order->sender_user_type = $sender->user_type;
            $gift_order->receiver_union_id = $receiver->union_id;
            $gift_order->sender_union_id = $sender->union_id;
            $gift_order->receiver_union_type = $receiver->union_type;
            $gift_order->sender_union_type = $sender->union_type;
            $gift_order->sender_country_id = $sender->country_id;
            $gift_order->receiver_country_id = $receiver->country_id;
            $gift_order->target_id = $target_id;
            $gift_order->product_channel_id = $receiver->product_channel_id;

            if ($sender_id == $receiver_id) {
                $gift_order->type = GIFT_ORDER_TYPE_USER_BUY;
            } else {
                $gift_order->type = GIFT_ORDER_TYPE_USER_SEND;
            }

            // 在房间里送里面
            if ($sender_current_room_id && $receiver_current_room_id && $sender_current_room_id == $receiver_current_room_id) {
                $sender_current_room = Rooms::findFirstById($sender_current_room_id);
                $gift_order->room_id = $sender_current_room_id;
                $gift_order->room_union_id = $sender_current_room->union_id;
                $gift_order->room_union_type = $sender_current_room->union_type;
            }

            if ($gift_order->create()) {
                $gift_order->updateUserGiftData($gift, ['time' => $time]);
            } else {
                info("Exce gift_order_create_fail", $sender_id, $receiver_ids, $gift_id, $opts);
            }
        }
    }

    function updateUserGiftData($gift, $opts = [])
    {
        $time = fetch($opts, 'time', time());

        if ($gift->isCar()) {
            \UserGifts::delay()->updateGiftExpireAt($this->id);
        } else {
            \UserGifts::delay()->updateGiftNum($this->id);

            if ($gift->isDiamondPayType()) {
                //座驾不增加hi币
                \HiCoinHistories::delay()->createHistory($this->user_id, ['gift_order_id' => $this->id, 'time' => $time]);
                //防止异步丢任务
                \HiCoinHistories::delay(15)->createHistory($this->user_id, ['gift_order_id' => $this->id, 'time' => $time, 'async_verify_data' => 1]);
            }
        }

        if ($gift->isDiamondPayType()) {
            $this->updateUserData($opts);
        }
    }

    function updateUserData($opts = [])
    {
        $time = $time = fetch($opts, 'time', time());

        $params = ['time' => $time];

        //统计房间收益
        if ($this->room) {

            if (!$this->gift->isCar()) {
                $this->room->statIncome($this->amount);

                if (!$this->sender->isSilent()) {
                    Rooms::delay()->statDayIncome($this->room_id, $this->amount, $this->sender_id, $this->gift_num, $params);
                }
            }

            if ($this->sender_id != $this->user_id) {
                //推送全局消息
                Rooms::allNoticePush($this);
            }
        }


        \Users::delay()->updateExperience($this->id, $params);
        \Users::delay()->updateCharm($this->id, $params);
    }

    static function giveCarBySystem($receiver_id, $operator_id, $gift, $content, $gift_num = 1)
    {
        $sender_id = SYSTEM_ID;

        $sender = Users::findFirstById($sender_id);


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
        $gift_order->remark = "系统赠送";
        $gift_order->operator_id = $operator_id;
        $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;
        $gift_order->type = GIFT_ORDER_TYPE_SYSTEM_SEND;
        $gift_order->product_channel_id = $receiver->product_channel_id;
        $gift_order->save();

        \UserGifts::delay()->updateGiftExpireAt($gift_order->id, ['content' => $content]);

        return true;
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

    function isGoldPayType()
    {
        return GIFT_PAY_TYPE_GOLD == $this->pay_type;
    }

    function isCar()
    {
        return GIFT_TYPE_CAR == $this->gift_type;
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