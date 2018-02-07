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

    static $STATUS = [WITHDRAW_STATUS_WAIT => '提现中', WITHDRAW_STATUS_SUCCESS => '提现成功', WITHDRAW_STATUS_FAIL => '提现失败'];

    static function createWithdrawHistories($user, $opts)
    {
        $money = fetch($opts, 'money');
        $name = fetch($opts, 'name');
        $account = fetch($opts, 'account');

        $max_money = $user->hi_coins / 10;
        if ($money > $max_money) {
            return [ERROR_CODE_FAIL, '提现金额超过可提现最大值'];
        }

        $withdraw_history = WithdrawHistories::findFirst(
            [
                'conditions' => 'status = :status: and id != :id:',
                'bind' => ['status' => WITHDRAW_STATUS_WAIT, 'id' => $user->id],
                'order' => 'id desc'
            ]
        );


        if ($withdraw_history) {
            return [ERROR_CODE_FAIL, '有受理中的提现记录，不能再提现'];
        }


        $history = new WithdrawHistories();
        $history->user_id = $user->id;
        $history->user_name = $name;
        $history->alipay_account = $account;
        $history->product_channel_id = $user->product_channel_id;
        $history->amount = $money;

        $history->status = WITHDRAW_STATUS_WAIT;

        $history->save();

        return [ERROR_CODE_SUCCESS, '受理中'];
    }


    static function search($user, $page, $per_page = 10)
    {
        $cond = [
            'conditions' => ' id != :id: and product_channel_id = :product_channel_id:',
            'bind' => ['product_channel_id' => $user->product_channel_id, 'id' => $user->id],
            'order' => 'id desc'
        ];
        $withdraw_histories = self::findPagination($cond, $page, $per_page);
        return $withdraw_histories;
    }

    function toSimpleJson()
    {
        return [
            'amount' => $this->amount,
            'status_text' => $this->status_text,
            'created_at_date' => $this->created_at_date
        ];
    }
}