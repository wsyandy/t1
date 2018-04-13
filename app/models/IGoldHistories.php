<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 14:47
 */
class IGoldHistories extends BaseModel
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
     * @type GiftOrders
     */
    private $_gift_order;

    /**
     * @type Countries
     */
    private $_country;

    static $FEE_TYPE = [
        I_GOLD_HISTORY_FEE_TYPE_BUY_GOLD => '购买金币',
        I_GOLD_HISTORY_FEE_TYPE_BUY_GIFT => '购买礼物'
    ];

    function beforeCreate()
    {
        return $this->checkBalance();
    }

    function afterCreate()
    {
        $user = $this->user;
        $user->i_gold = $this->balance;
        $user->update();

//        $user_attrs = $user->getStatAttrs();
//        $user_attrs['add_value'] = abs($this->amount);
//        $action = $this->getStatActon();
//        \Stats::delay()->record('user', $action, $user_attrs);

        //钻石消费记录
//        \DataCollection::syncData('account_history', 'change_balance', ['account_history' => $this->toJson()]);
    }

    static function changeBalance($user_id, $fee_type, $amount, $opts = [])
    {
        $user = Users::findFirstById($user_id);

        if (!$user) {
            info($user_id);
            return false;
        }

        $i_gold_history = new \IgoldHistories();
        $i_gold_history->user_id = $user_id;
        $i_gold_history->fee_type = $fee_type;
        $i_gold_history->amount = $amount;
        $i_gold_history->country_id = $user->country_id;

        foreach (['order_id', 'gift_order_id', 'remark', 'operator_id'] as $column) {
            $value = fetch($opts, $column);
            if ($value) {
                $i_gold_history->$column = $value;
            }
        }

        if ($i_gold_history->save()) {
            return true;
        }

        info($user->sid, $fee_type, $amount, $opts);
        return false;
    }

    function checkBalance()
    {
        $change_amount = abs($this->amount);
        if ($this->isCostIGold()) {
            $change_amount = -$change_amount;
            $this->amount = $change_amount;
        }

        $old_i_gold_history = self::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $this->user_id],
            'order' => 'id desc']);

        $old_balance = intval($this->balance);
        if ($old_i_gold_history) {
            $old_balance = intval($old_i_gold_history->balance);
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
    function isCostIGold()
    {
        return in_array($this->fee_type, [I_GOLD_HISTORY_FEE_TYPE_BUY_GIFT]);
    }

    function getStatActon()
    {
        if ($this->fee_type == ACCOUNT_TYPE_GIVE) {
            return "diamond_recharge_give";
        }

        if ($this->isCostIGold()) {
            return "diamond_cost";
        }

        return "diamond_recharge";
    }

}