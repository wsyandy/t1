<?php

/**
 * Created by PhpStorm.
 * User: administrator
 * Date: 2018/4/13
 * Time: 下午8:03
 */
trait GiftOrderInternational
{

    static function giveToByInternational($sender_id, $receiver_id, $gift, $gift_num)
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
            $opts = ['gift_order_id' => $gift_order->id, 'remark' => $remark];

            //扣除国际版金币
            if ($gift->isIGoldPayType()) {
                $result = \IGoldHistories::changeBalance($gift_order->sender_id, I_GOLD_HISTORY_FEE_TYPE_BUY_GIFT, $gift_order->amount, $opts);
                if ($result) {

                    //如果赠送者是公司人员，并且接受者不是公司人员，则将其赠送的钻石金额加入到缓存中
                    if ($sender->isCompanyUser() && !$receiver->isCompanyUser()) {
                        $sender->addCompanyUserSendNumber($gift_order->amount);
                    }

                    $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;
                    $gift_order->update();
                    $gift_order->updateUserGiftData($gift);

                }else{

                    $gift_order->status = GIFT_ORDER_STATUS_WAIT;
                    $gift_order->update();

                }

                return $result;
            }

            return false;

        }

        info("send_gift_fail", $sender->sid, $receiver->sid, $sender->diamond, $gift->id, $gift_num);
        return false;
    }
}