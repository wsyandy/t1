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

    /**
     * @type HiCoinHistories
     */
    private $_hi_coin_history;

    static $FEE_TYPE = [GOLD_TYPE_SIGN_IN => '用户签到',
        GOLD_TYPE_BUY_GIFT => "购买礼物",
        GOLD_TYPE_SHARE_WORK => '分享任务',
        GOLD_TYPE_BUY_GOLD => '购买金币',
        GOLD_TYPE_HI_COIN_EXCHANGE_DIAMOND => 'Hi币兑钻石获金币',
        GOLD_TYPE_GIVE => '系统赠送',
        GOLD_TYPE_GAME_INCOME => '游戏收入',
        GOLD_TYPE_GAME_EXPENSES => '游戏支出',
        GOLD_TYPE_ACTIVITY_LUCKY_DRAW => '活动抽奖赠送',
        GOLD_TYPE_BIND_MOBILE => '绑定手机号码',
    ];


    function beforeCreate()
    {
        return $this->checkBalance();
    }

    function afterCreate()
    {
        $user = $this->user;
        $user->gold = $this->balance;
        $user->update();
    }

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
        $gold_history->country_id = $user->country_id;

        foreach (['order_id', 'gift_order_id', 'hi_coin_history_id', 'remark', 'operator_id', 'activity_id'] as $column) {

            $value = fetch($opts, $column);
            if ($value) {
                $gold_history->$column = $value;
            }
        }

        if ($gold_history->save()) {
            $stat_attrs = array_merge($user->getStatAttrs(), ['add_value' => $amount]);
            //消耗金币统计
            if ($gold_history->isCostGold()) {
                \Stats::delay()->record('user', 'gold_cost', $stat_attrs);
            } else {
                //获取金币统计
                \Stats::delay()->record('user', 'gold_obtain', $stat_attrs);
            }

            return true;
        }

        info('Exce', $user->sid, $fee_type, $amount, $opts);
        return false;
    }

    function checkBalance()
    {
        $change_amount = abs($this->amount);

        if ($this->isCostGold()) {
            $change_amount = -$change_amount;
            $this->amount = $change_amount;
        }
        $old_gold_history = self::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $this->user_id],
            'order' => 'id desc']);

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

    /**
     * 用户消耗
     */
    function isCostGold()
    {
        return $this->fee_type == GOLD_TYPE_BUY_GIFT || $this->fee_type == GOLD_TYPE_GAME_EXPENSES;
    }

    /**
     * 系统赠送
     */
    function isSystemGive()
    {
        return $this->fee_type == GOLD_TYPE_GIVE;
    }

}