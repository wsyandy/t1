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
        ACCOUNT_TYPE_CREATE_UNION => '创建公会',
        ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND => 'Hi币兑钻石',
        ACCOUNT_TYPE_GAME_INCOME => '游戏收入',
        ACCOUNT_TYPE_GAME_EXPENSES => '游戏支出',
        ACCOUNT_TYPE_DEDUCT => '系统扣除'
    ];

    function beforeCreate()
    {
        return $this->checkBalance();
    }

    function afterCreate()
    {
        $user = $this->user;
        $user->diamond = $this->balance;
        // 系统赠送
        if ($this->fee_type == ACCOUNT_TYPE_GIVE && $user->organisation != USER_ORGANISATION_COMPANY) {
            $user->organisation = USER_ORGANISATION_COMPANY;
        }
        $user->update();

        $user_attrs = $user->getStatAttrs();
        $user_attrs['add_value'] = abs($this->amount);
        $action = $this->getStatActon();
        \Stats::delay()->record('user', $action, $user_attrs);

        //钻石消费记录
        \DataCollection::syncData('account_history', 'change_balance', ['account_history' => $this->toJson()]);
    }

    static function changeBalance($user_id, $fee_type, $amount, $opts = [])
    {
        $user = Users::findFirstById($user_id);

        if (!$user) {
            info($user_id);
            return false;
        }

        $account_history = new \AccountHistories();
        $account_history->user_id = $user_id;
        $account_history->fee_type = $fee_type;
        $account_history->amount = $amount;
        $account_history->union_id = $user->union_id;
        $account_history->union_type = $user->union_type;
        $account_history->country_id = $user->country_id;

        foreach (['order_id', 'gift_order_id', 'hi_coin_history_id', 'remark', 'operator_id', 'mobile'] as $column) {
            $value = fetch($opts, $column);
            if ($value) {
                $account_history->$column = $value;
            }
        }

        if ($account_history->save()) {
            return true;
        }

        info($user->sid, $fee_type, $amount, $opts);
        return false;
    }

    function checkBalance()
    {
        $change_amount = abs($this->amount);
        if ($this->isCostDiamond()) {
            $change_amount = -$change_amount;
            $this->amount = $change_amount;
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
        if ($this->balance < 0 && $this->user->isActive()) {
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
            || $this->fee_type == ACCOUNT_TYPE_GAME_EXPENSES || $this->fee_type == ACCOUNT_TYPE_DEDUCT;
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