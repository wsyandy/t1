<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/5
 * Time: 下午2:11
 */

class ActivityHistories extends BaseModel
{
    /**
     * @type Activities
     */
    private $_activity;

    /**
     * @type Users
     */
    private $_user;

    static $AUTH_STATUS = [AUTH_SUCCESS => '审核成功', AUTH_FAIL => '审核失败', AUTH_WAIT => '等待审核'];

    //奖品类型
    function prizeTypeText()
    {
        return fetch(Activities::$ACTIVITY_PRIZE_TYPE, $this->prize_type);
    }

    function toSimpleJson()
    {
        return [
            'prize_type_text' => $this->prize_type_text,
            'user_nickname' => $this->user_nickname,
            'created_text' => $this->created_text
        ];
    }


    static function createHistory($activity_id, $opts = [])
    {
        $user_id = fetch($opts, 'user_id');
        $prize_type = fetch($opts, 'prize_type');
        $activity_history = new ActivityHistories();
        $activity_history->activity_id = $activity_id;
        $activity_history->user_id = $user_id;
        $activity_history->auth_status = AUTH_SUCCESS;
        $activity_history->auth_status = AUTH_SUCCESS;
        $activity_history->prize_type = $prize_type;

        info($activity_id, $opts);

        if ($activity_history->save()) {

            //靓号奖励需要人功审核
            if (in_array($prize_type, [2, 4])) {
                $activity_history->auth_status = AUTH_WAIT;
            } elseif (in_array($prize_type, [1, 3, 5])) {

                //金币奖励
                switch ($prize_type) {
                    case $prize_type == 1:
                        $gold = 10000;
                        break;
                    case $prize_type == 3:
                        $gold = 1000;
                        break;
                    case $prize_type == 5:
                        $gold = 1000;
                        break;
                }

                GoldHistories::changeBalance($user_id, GIFT_ORDER_TYPE_ACTIVITY_LUCKY_DRAW, $gold, [
                    'remark' => "活动" . $activity_history->activity->title . "奖励" . $gold . "金币", 'activity_id' => $activity_id]);
                $activity_history->gold = $gold;
                $activity_history->update();
            } elseif (in_array($prize_type, [6, 7, 8])) {
                $gift_map = [6 => 31, 7 => 37, 8 => 32];

                if (isDevelopmentEnv()) {
                    $gift_map = [6 => 52, 7 => 68, 8 => 49];
                }

                $gift_id = fetch($gift_map, $prize_type);


                $sender_id = SYSTEM_ID;

                $sender = Users::findFirstById($sender_id);


                $receiver = Users::findFirstById($user_id);
                if (!$receiver) {
                    return false;
                }

                $gift = Gifts::findFirstById($gift_id);

                if ($gift) {

                    $activity_history->gift_id = $gift_id;
                    $activity_history->update();


                    $gift_order = new GiftOrders();
                    $gift_order->sender_id = $sender_id;
                    $gift_order->user_id = $user_id;
                    $gift_order->gift_id = $gift->id;
                    $gift_order->amount = $gift->amount * 1;
                    $gift_order->name = $gift->name;
                    $gift_order->pay_type = $gift->pay_type;
                    $gift_order->gift_type = $gift->type;
                    $gift_order->gift_num = 1;
                    $gift_order->type = GIFT_ORDER_TYPE_ACTIVITY_LUCKY_DRAW;
                    $gift_order->activity_id = $activity_id;
                    $gift_order->receiver_user_type = $receiver->user_type;
                    $gift_order->sender_user_type = $sender->user_type;
                    $gift_order->receiver_union_id = $receiver->union_id;
                    $gift_order->sender_union_id = $sender->union_id;
                    $gift_order->receiver_union_type = $receiver->union_type;
                    $gift_order->sendersave_union_type = $sender->union_type;
                    $gift_order->remark = "活动" . $activity_history->activity->title . "赠送" . $gift->name;
                    $gift_order->status = GIFT_ORDER_STATUS_SUCCESS;
                    $gift_order->type = GIFT_ORDER_TYPE_SYSTEM_SEND;
                    $gift_order->save();

                    if ($gift->isCar()) {
                        \UserGifts::delay()->updateGiftExpireAt($gift_order->id, ['content' => '恭喜']);
                    } else {
                        \UserGifts::delay()->updateGiftNum($gift_order->id);
                    }
                }
            }

            return true;
        }

        return false;
    }
}