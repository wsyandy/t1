<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/26
 * Time: 下午3:25
 */
class HiCoinHistories extends BaseModel
{
    /**
     * @type ProductChannels
     */
    private $_product_channel;


    /**
     * @type Users
     */
    private $_user;

    /**
     * @type GiftOrders
     */
    private $gift_order;

    /**
     * @type Products
     */
    private $_product;

    static $FEE_TYPE = [HI_COIN_FEE_TYPE_RECEIVE_GIFT => '接收礼物', HI_COIN_FEE_TYPE_HOST_REWARD => '主播奖励',
        HI_COIN_FEE_TYPE_UNION_HOST_REWARD => '家族长奖励', HI_COIN_FEE_TYPE_WITHDRAW => '提现', HI_COIN_FEE_TYPE_ROOM_REWARD => '房间流水奖励',
        HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND => 'Hi币兑钻石'];


    function beforeCreate()
    {
        return $this->checkBalance();
    }

    function afterCreate()
    {
        if ($this->isExchange()) {
            $user = \Users::findFirstById($this->user_id);
            $user_attrs = $user->getStatAttrs();
            $user_attrs['add_value'] = abs($this->hi_coins);
            \Stats::delay()->record('user', 'hi_coin_cost', $user_attrs);
        }
    }

    function mergeJson()
    {
        return [
            'fee_type_text' => $this->fee_type_text,
            'user_nickname' => $this->user->nickname,
        ];
    }

    function checkBalance()
    {
        $change_amount = abs($this->hi_coins);

        if ($this->isCost()) {
            $change_amount = -$change_amount;
            $this->hi_coins = $change_amount;
        }

        $old_hi_coin_history = \HiCoinHistories::findUserLast($this->user_id);
        $old_balance = $this->balance;

        if ($old_hi_coin_history) {
            $old_balance = $old_hi_coin_history->balance;
        }

        info($old_balance + $change_amount);

        $this->balance = $old_balance + $change_amount;

        if ($this->balance < 0 && $this->user->isActive()) {
            return true;
        }

        return false;
    }

    static function findUserLast($user_id)
    {
        $hi_coin_histories = \HiCoinHistories::findHiCoinHistoryList($user_id, 1, 1);

        if (count($hi_coin_histories) > 0) {
            return $hi_coin_histories[0];
        }

        return null;
    }

    static function findHiCoinHistoryList($user_id, $page, $per_page)
    {
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];

        return \HiCoinHistories::findPagination($conditions, $page, $per_page);
    }

    static function createHistory($user_id, $opts = [])
    {
        $gift_order_id = fetch($opts, 'gift_order_id');
        $withdraw_history_id = fetch($opts, 'withdraw_history_id');
        $operator_id = fetch($opts, 'operator_id');
        $hi_coins = fetch($opts, 'hi_coins');
        $remark = fetch($opts, 'remark');
        $fee_type = fetch($opts, 'fee_type');

        $user = Users::findFirstById($user_id);

        if (!$user) {
            info($user_id);
            return;
        }

        info($user_id, $gift_order_id);

        $lock_key = "update_user_hi_coins_lock_" . $user_id;
        $lock = tryLock($lock_key);

        $hi_coin_history = new HiCoinHistories();
        $hi_coin_history->user_id = $user_id;

        if ($fee_type) {
            $hi_coin_history->hi_coins = $hi_coins;
            $hi_coin_history->fee_type = $fee_type;
            $hi_coin_history->remark = $remark;
        }

        if ($gift_order_id) {

            $gift_order = GiftOrders::findFirstById($gift_order_id);

            if (!$gift_order) {
                info($gift_order_id);
                return;
            }

            $hi_coin_history->gift_order_id = $gift_order_id;
            $amount = $gift_order->amount;
            $hi_coins = $amount * $user->rateOfDiamondToHiCoin();
            $hi_coins = intval($hi_coins * 10000) / 10000;
            $hi_coin_history->hi_coins = $hi_coins;
            $hi_coin_history->fee_type = HI_COIN_FEE_TYPE_RECEIVE_GIFT;
            $hi_coin_history->remark = "接收礼物总额: $amount 收益:" . $hi_coins;
        }

        if ($withdraw_history_id) {

            $withdraw_history = WithdrawHistories::findFirstById($withdraw_history_id);

            if (!$withdraw_history) {
                info($withdraw_history_id);
                return;
            }

            $hi_coin_history->withdraw_history_id = $withdraw_history_id;
            $amount = $withdraw_history->amount;
            $hi_coin_history->hi_coins = $amount;
            $hi_coin_history->fee_type = HI_COIN_FEE_TYPE_WITHDRAW;
            $hi_coin_history->remark = "提现金额:" . $amount;
        }

        if ($operator_id) {
            $hi_coin_history->operator_id = $operator_id;
            $hi_coin_history->hi_coins = $hi_coins;
            $hi_coin_history->fee_type = HI_COIN_FEE_TYPE_ROOM_REWARD;
            $hi_coin_history->remark = $remark;
        }

        $hi_coin_history->product_channel_id = $user->product_channel_id;
        $hi_coin_history->union_id = $user->union_id;
        $hi_coin_history->union_type = $user->union_type;
        $hi_coin_history->save();

        $user->hi_coins = $hi_coin_history->balance;
        $user->update();

        //有礼物更新hi币榜单 自己给自己送座驾不加hi币贡献榜
        if ($gift_order_id) {
            $user->updateHiCoinRankList($gift_order->sender_id, $hi_coin_history->hi_coins);
        }

        unlock($lock);

        return $hi_coin_history;
    }

    function isCost()
    {
        return in_array($this->fee_type, [HI_COIN_FEE_TYPE_WITHDRAW, HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND]);
    }

    function isExchange()
    {
        return in_array($this->fee_type, [HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND]);
    }

    //Hi币转钻石记录
    static function hiCoinExchangeDiamondHiCoinHistory($user_id, $opts = [])
    {

        $product_id = fetch($opts, 'product_id');
        $gold = fetch($opts, 'gold');
        $diamond = fetch($opts, 'diamond');
        $hi_coins = fetch($opts, 'hi_coins');

        info('user_id', $user_id, 'gold', $gold, 'diamond', $diamond, 'hi_coins', $hi_coins);
        $user = Users::findFirstById($user_id);

        if (!$user) {
            info($user_id);
            return;
        }

        $remark = "Hi币兑钻石 Hi币: {$hi_coins} 钻石:{$diamond} 金币:{$gold}";
        $lock_key = "update_user_hi_coins_lock_" . $user_id;
        $lock = tryLock($lock_key);

        $hi_coin_history = new HiCoinHistories();
        $hi_coin_history->user_id = $user_id;

        $hi_coin_history->fee_type = HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND;
        $hi_coin_history->remark = $remark;
        $hi_coin_history->hi_coins = $hi_coins;
        $hi_coin_history->diamond = $diamond;
        $hi_coin_history->gold = $gold;
        $hi_coin_history->product_id = $product_id;


        $hi_coin_history->product_channel_id = $user->product_channel_id;
        $hi_coin_history->union_id = $user->union_id;
        $hi_coin_history->union_type = $user->union_type;
        $hi_coin_history->save();

        $opts = ['remark' => $remark, 'hi_coin_history_id' => $hi_coin_history->id];
        info('user_id', $user->id, $opts);

        if ($hi_coin_history->gold > 0) {
            \GoldHistories::changeBalance($user->id, GOLD_TYPE_HI_COIN_EXCHANGE_DIAMOND, $gold, $opts);
        }

        if ($hi_coin_history->diamond > 0) {
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND, $diamond, $opts);
        }

        $user->hi_coins = $hi_coin_history->balance;
        $user->update();


        unlock($lock);

        return $hi_coin_history;

    }
}