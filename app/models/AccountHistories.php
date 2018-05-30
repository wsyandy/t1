<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 14:47
 */
class AccountHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Operators
     */
    private $_operator;

    /**
     * @type HiCoinHistories
     */
    private $_hi_coin_history;

    static $FEE_TYPE = [
        ACCOUNT_TYPE_BUY_DIAMOND => '购买钻石',
        ACCOUNT_TYPE_BUY_GIFT => '购买礼物',
        ACCOUNT_TYPE_GIVE => '系统赠送',
        ACCOUNT_TYPE_CREATE_UNION => '创建家族',
        ACCOUNT_TYPE_CREATE_UNION_REFUND => '创建家族返还',
        ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND => 'Hi币兑钻石',
        ACCOUNT_TYPE_GAME_INCOME => '游戏收入',
        ACCOUNT_TYPE_GAME_EXPENSES => '游戏支出',
        ACCOUNT_TYPE_DEDUCT => '系统扣除',
        ACCOUNT_TYPE_DISTRIBUTE_REGISTER => '分销注册',
        ACCOUNT_TYPE_DISTRIBUTE_PAY => '分销充值',
        ACCOUNT_TYPE_DISTRIBUTE_EXCHANGE => '分销兑换',
        ACCOUNT_TYPE_DRAW_INCOME => '转盘抽奖收入',
        ACCOUNT_TYPE_DRAW_EXPENSES => '转盘抽奖支出',
        ACCOUNT_TYPE_GUARD_WISH_EXPENSES => '守护愿望支出',
        ACCOUNT_TYPE_RED_PACKET_EXPENSES => '红包支出',
        ACCOUNT_TYPE_RED_PACKET_INCOME => '红包收入',
        ACCOUNT_TYPE_RED_PACKET_RESTORATION => '红包余额返还',
        ACCOUNT_TYPE_IN_BOOM => '爆礼物获取'
    ];

    function beforeCreate()
    {
        return $this->checkBalance();
    }

    function afterCreate()
    {
        $user = $this->user;
        $user->diamond = $this->balance;
        $user->update();

        if ($user->isCompanyUser() && $this->isCostDiamond()) {
            info($user->id, $this->amount);
            $user->addCompanyUserSendNumber($this->amount);
        }

        $user_attrs = $user->getStatAttrs();
        $user_attrs['add_value'] = abs($this->amount);
        $action = $this->getStatActon();
        \Stats::delay()->record('user', $action, $user_attrs);

        //钻石消费记录
        \DataCollection::syncData('account_history', 'change_balance', ['account_history' => $this->toJson()]);
    }

    // 分销
    function toDistributeJson()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at_text,
            'amount' => $this->amount,
            'user_nickname' => $this->target->nickname,
            'user_avatar_url' => $this->target->avatar_small_url
        ];
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at_text,
            'amount' => $this->amount
        ];
    }

    static function changeBalance($user_id, $fee_type, $amount, $opts = [])
    {
        if (is_numeric($user_id)) {
            $user = Users::findFirstById($user_id);
        } else {
            $user = $user_id;
            $user_id = $user->id;
        }

        if (!$user) {
            info('Exce', $user_id);
            return null;
        }

        $account_history = new \AccountHistories();
        $account_history->user_id = $user->id;
        $account_history->fee_type = $fee_type;
        $account_history->amount = $amount;
        $account_history->union_id = $user->union_id;
        $account_history->union_type = $user->union_type;

        //'order_id', 'gift_order_id', 'hi_coin_history_id',
        foreach (['remark', 'operator_id', 'mobile', 'target_id'] as $column) {
            $value = fetch($opts, $column);
            if ($value) {
                $account_history->$column = $value;
            }
        }

        if ($account_history->save()) {
            return $account_history;
        }

        return null;
    }

    function checkBalance()
    {
        $change_amount = abs($this->amount);
        if ($this->isCostDiamond()) {
            $change_amount = -$change_amount;
            $this->amount = $change_amount;
            $user = $this->user;
            $can_consume_diamond = $user->canConsumeDiamond($change_amount);
            if (!$can_consume_diamond) {
                return true;
            }
        }

        $old_account_history = self::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $this->user_id],
            'order' => 'id desc']);

        $old_balance = intval($this->balance);
        if ($old_account_history) {
            $old_balance = intval($old_account_history->balance);
        }
        $this->balance = $old_balance + $change_amount;

        if ($this->balance < 0 && $this->user->isActive() && $this->fee_type != ACCOUNT_TYPE_DEDUCT) {
            return true;
        }
        return false;
    }

    /**
     * 用户消耗
     */
    function isCostDiamond()
    {
        return $this->fee_type == ACCOUNT_TYPE_BUY_GIFT || $this->fee_type == ACCOUNT_TYPE_CREATE_UNION
            || $this->fee_type == ACCOUNT_TYPE_GAME_EXPENSES || $this->fee_type == ACCOUNT_TYPE_DEDUCT
            || $this->fee_type == ACCOUNT_TYPE_DRAW_EXPENSES || $this->fee_type == ACCOUNT_TYPE_GUARD_WISH_EXPENSES
            || $this->fee_type == ACCOUNT_TYPE_RED_PACKET_EXPENSES;
    }

    function getStatActon()
    {
        if ($this->fee_type == ACCOUNT_TYPE_GIVE) {
            return "diamond_recharge_give";
        }

        if ($this->isCostDiamond()) {
            return "diamond_cost";
        }

        return "diamond_recharge";
    }

}