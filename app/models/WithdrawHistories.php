<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/6
 * Time: 上午11:58
 */
class WithdrawHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type ProductChannels
     */
    private $_product_channel;

    /**
     * @type Unions
     */
    private $_union;

    static $TYPE = [WITHDRAW_TYPE_USER => '用户体体现', WITHDRAW_TYPE_UNION => '公会体现'];

    function afterUpdate()
    {
        if ($this->hasChanged('status') && WITHDRAW_STATUS_SUCCESS == $this->status) {
            $user = $this->user;
            $product_channel = $this->product_channel;
            $rate = $product_channel->rateOfHiCoinToMoney();
            debug($user->id, $this->amount, $rate);
            $user->hi_coins = $user->hi_coins - $this->amount * $rate;
            $user->save();
        }
    }

    static $STATUS = [WITHDRAW_STATUS_WAIT => '提现中', WITHDRAW_STATUS_SUCCESS => '提现成功', WITHDRAW_STATUS_FAIL => '提现失败'];

    static function createWithdrawHistories($user, $opts)
    {
        $amount = fetch($opts, 'money');
        $user_name = fetch($opts, 'name');
        $alipay_account = fetch($opts, 'account');

        $max_amount = $user->withdraw_amount;

        if ($amount > $max_amount) {
            return [ERROR_CODE_FAIL, '提现金额超过可提现最大值'];
        }

        if (self::hasWaitedHistoryByUser($user)) {
            return [ERROR_CODE_FAIL, '您有受理中的提现记录，不能再提现'];
        }


        $history = new WithdrawHistories();
        $history->user_id = $user->id;
        $history->user_name = $user_name;
        $history->alipay_account = $alipay_account;
        $history->product_channel_id = $user->product_channel_id;
        $history->amount = $amount;
        $history->status = WITHDRAW_STATUS_WAIT;
        $history->type = WITHDRAW_TYPE_USER;
        $history->save();

        return [ERROR_CODE_SUCCESS, '受理中'];
    }

    static function createUnionWithdrawHistories($union, $opts)
    {
        $amount = fetch($opts, 'amount');
        $alipay_account = fetch($opts, 'alipay_account');

        if ($amount > $union->amount) {
            return [ERROR_CODE_FAIL, '提现金额超过可提现最大值'];
        }

        if (self::hasWaitedHistoryByUser($union)) {
            return [ERROR_CODE_FAIL, '您有受理中的提现记录，不能再提现'];
        }


        $history = new WithdrawHistories();
        $history->union_id = $union->id;
        $history->alipay_account = $alipay_account;
        $history->product_channel_id = $union->product_channel_id;
        $history->amount = $amount;
        $history->status = WITHDRAW_STATUS_WAIT;
        $history->type = WITHDRAW_TYPE_UNION;
        $history->save();

        return [ERROR_CODE_SUCCESS, '受理中'];
    }


    static function search($user, $page, $per_page = 10)
    {
        $cond = [
            'conditions' => ' user_id = :user_id: and product_channel_id = :product_channel_id:',
            'bind' => ['product_channel_id' => $user->product_channel_id, 'user_id' => $user->id],
            'order' => 'id desc'
        ];
        $withdraw_histories = self::findPagination($cond, $page, $per_page);
        return $withdraw_histories;
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status_text' => $this->status_text,
            'created_at_date' => $this->created_at_date,
            'created_at_text' => $this->created_at_text,
        ];
    }

    static function hasWaitedHistoryByUser($user)
    {
        $withdraw_history = WithdrawHistories::findFirst(
            [
                'conditions' => 'status = :status: and user_id = :user_id: and product_channel_id = :product_channel_id: and type = :type:',
                'bind' => ['status' => WITHDRAW_STATUS_WAIT, 'user_id' => $user->id, 'product_channel_id' => $user->product_channel_id, 'type' => WITHDRAW_TYPE_USER],
                'order' => 'id desc'
            ]
        );

        if ($withdraw_history) {
            return true;
        }

        return false;
    }

    static function hasWaitedHistoryByUnion($union)
    {
        $withdraw_history = WithdrawHistories::findFirst(
            [
                'conditions' => 'status = :status: and union_id = :union_id: and product_channel_id = :product_channel_id: and type = :type:',
                'bind' => ['status' => WITHDRAW_STATUS_WAIT, 'union_id' => $union->id, 'product_channel_id' => $union->product_channel_id, 'type' => WITHDRAW_TYPE_UNION],
                'order' => 'id desc'
            ]
        );

        if ($withdraw_history) {
            return true;
        }

        return false;
    }
}