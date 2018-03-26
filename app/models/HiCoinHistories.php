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

    static $FEE_TYPE = [HI_COIN_FEE_TYPE_RECEIVE_GIFT => '接收礼物', HI_COIN_FEE_TYPE_HOST_REWARD => '主播奖励',
        HI_COIN_FEE_TYPE_UNION_HOST_REWARD => '家族长奖励'];


    function beforeCreate()
    {
        return $this->checkBalance();
    }

    function checkBalance()
    {
        $change_amount = abs($this->hi_coins);
        $old_hi_coin_history = \HiCoinHistories::findUserLast($this->user_id);
        $old_balance = $this->balance;

        if ($old_hi_coin_history) {
            $old_balance = intval($old_hi_coin_history->balance);
        }

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

        return \AccountHistories::findPagination($conditions, $page, $per_page);
    }

    static function createHistory($user_id, $gift_order_id)
    {
        $user = Users::findFirstById($user_id);
        $gift_order = GiftOrders::findFirstById($gift_order_id);

        if (!$user || ($gift_order_id && !$gift_order)) {
            info($user_id, $gift_order_id);
            return;
        }

        info($user_id, $gift_order_id);

        $lock_key = "update_user_hi_coins_lock_" . $user_id;
        $lock = tryLock($lock_key);

        $hi_coin_history = new HiCoinHistories();
        $hi_coin_history->user_id = $user_id;

        if ($gift_order_id) {
            $hi_coin_history->gift_order_id = $gift_order_id;
            $amount = $hi_coin_history->amount;
            $hi_coins = $amount / $user->rateOfDiamondToHiCoin();
            $hi_coin_history->hi_coins = $hi_coins;
            $hi_coin_history->fee_type = HI_COIN_FEE_TYPE_RECEIVE_GIFT;
            $hi_coin_history->remark = "接收礼物总额: $amount 收益:" . $hi_coins;
        }

        $hi_coin_history->product_channel_id = $user->product_channel_id;
        $hi_coin_history->union_id = $user->union_id;
        $hi_coin_history->union_type = $user->union_type;
        $hi_coin_history->save();

        $user->hi_coins += $hi_coin_history->hi_coins;
        $user->update();

        unlock($lock);
    }
}