<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/21
 * Time: 下午1:35
 */
class GoldHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Operators
     */
    private $_operator;

    static $FEE_TYPE = [GOLD_TYPE_SIGN_IN => '用户签到', GOLD_TYPE_BUY_GIFT => "购买礼物", GOLD_TYPE_SHARE_WORK => '分享任务'];

    static function changeBalance($user_id, $fee_type, $amount, $opts = [])
    {
        $user = Users::findFirstById($user_id);

        if (isBlank($user)) {
            info($user_id);
            return false;
        }

        $gold_history = new GoldHistories();
        $gold_history->user_id = $user_id;
        $gold_history->product_channel_id = $user->product_channel_id;
        $gold_history->fee_type = $fee_type;
        $gold_history->amount = $amount;

        foreach (['order_id', 'gift_order_id', 'remark', 'operator_id', 'mobile'] as $column) {
            $value = fetch($opts, $column);

            if ($value) {
                $gold_history->$column = $value;
            }
        }

        if ($gold_history->save()) {
            return true;
        }

        info($user->sid, $fee_type, $amount, $opts);
        return false;
    }

    function beforeCreate()
    {
        $this->checkBalance();
    }

    function checkBalance()
    {
        $change_amount = abs($this->amount);

        if ($this->isCostGold()) {
            $change_amount = -$change_amount;
            $this->amount = $change_amount;
        }
        $old_gold_history = self::findUserLast($this->user_id);
        $old_balance = intval($this->balance);

        if ($old_gold_history) {
            $old_balance = intval($old_gold_history->balance);
        }

        $this->balance = $old_balance + $change_amount;

        if ($this->balance < 0 && $this->user->isActive()) {
            return true;
        }

        return false;
    }

    function isCostGold()
    {
        return $this->fee_type == GOLD_TYPE_BUY_GIFT;
    }

    function afterCreate()
    {
        $user = \Users::findById($this->user_id);
        $user->gold = $this->balance;
        $user->update();
    }

    static function findUserLast($user_id)
    {
        $gold_histories = self::findGoldList($user_id, 1, 1);

        if (count($gold_histories) > 0) {
            return $gold_histories[0];
        }

        return null;
    }


    static function findGoldList($user_id, $page, $per_page)
    {
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];
        return self::findPagination($conditions, $page, $per_page);
    }
}