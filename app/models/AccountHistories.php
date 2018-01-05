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

    static $fee_types = array(
        ACCOUNT_TYPE_BUY_DIAMOND => '购买钻石',
        ACCOUNT_TYPE_BUY_GIFT => '购买礼物',
        ACCOUNT_TYPE_GIVE => '系统赠送'
    );

    /**
     * @return mixed
     */
    static function getCacheEndPoint()
    {
        $config = self::di('config');
        $endpoints = explode(',', $config->user_db_endpoints);
        return $endpoints[0];
    }

    static function changeBalance($user_id, $fee_type, $amount, $opts = array())
    {
        $account_history = new \AccountHistories();
        $account_history->user_id = $user_id;
        $account_history->fee_type = $fee_type;
        $account_history->amount = $amount;

        foreach (['order_id', 'gift_order_id', 'remark'] as $column) {
            $account_history->$column = fetch($opts, $column);
        }
        if ($account_history->create()) {
            return $account_history->balance;
        }
        return false;
    }

    function beforeCreate()
    {
        $this->checkBalance();
    }

    function checkBalance()
    {
        $change_amount = abs($this->amount);
        if ($this->isCostDiamond()) {
            $change_amount = -$change_amount;
        }
        $old_account_history = \AccountHistories::findUserLast($this->user_id);
        $old_balance = intval($this->balance);
        if ($old_account_history) {
            $old_balance = intval($old_account_history->balance);
        }
        $this->balance = $old_balance + $change_amount;
    }

    function isCostDiamond()
    {
        return $this->fee_type == ACCOUNT_TYPE_BUY_GIFT;
    }

    function afterCreate()
    {
        if ($this->hasChanged('balance')) {
            $user = \Users::findById($this->user_id);
            $user->diamond = $this->balance;
            $user->update();
        }
    }

    function getFeeTypeText()
    {
        return fetch(\AccountHistories::$fee_types, $this->fee_type);
    }

    static function findUserLast($user_id)
    {
        $account_histories = \AccountHistories::findAccountList($user_id, 1, 1);
        return $account_histories[0];
    }

    static function findAccountList($user_id, $page, $per_page)
    {
        $conditions = array(
            'conditions' => 'user_id = :user_id:',
            'bind' => array('user_id' => $user_id),
            'order' => 'id desc'
        );
        return \AccountHistories::findPagination($conditions, $page, $per_page);
    }
}