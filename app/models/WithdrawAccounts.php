<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/11
 * Time: 下午3:43
 */
class WithdrawAccounts extends BaseModel
{
    static $TYPE = [1 => "支付宝", 2 => "银行卡"];

    static $STATUS = [STATUS_PROGRESS => "创建中", STATUS_ON => "正常", STATUS_OFF => "关闭"];

    /**
     * @type Users
     */
    private $_user;

    /**
     * @type AccountBanks
     */
    private $_account_bank;

    static function createWithdrawAccount($user, $mobile)
    {
        //暂时只支持添加一张银行卡
        $old_withdraw_account = self::findFirstWithdrawAccount($user);
        if (isPresent($old_withdraw_account)) {
            return $old_withdraw_account->id;
        }

        $withdraw_account = \WithdrawAccounts::findFirstOrNew(['user_id' => $user->id, 'mobile' => $mobile, 'status' => STATUS_PROGRESS]);
        $withdraw_account->save();
        return $withdraw_account->id;
    }

    function updateProfile($opts)
    {
        foreach ($opts as $key => $value) {
            $this->$key = $value;
        }
        if ($this->status == STATUS_PROGRESS) {
            $this->status = STATUS_ON;
        }

        $this->update();
    }

    function mergeJson()
    {
        $data = ['account_text' => $this->account_text];
        return $data;
    }

    function getAccountText()
    {
        if (!$this->account) {
            return '';
        }
        $arr = str_split($this->account, 4);
        $str = implode(' ', $arr);
        return $str;
    }

    //解除绑定
    function unbind($user)
    {
        if ($user->id != $this->user_id) {
            return false;
        }

        if ($this->status != STATUS_OFF) {
            $this->status = STATUS_OFF;
        }

        $this->update();

        return true;
    }

    static function getDefaultWithdrawAccount($user)
    {
        $user_db = \Users::getUserDb();
        $key = 'selected_withdraw_account_' . $user->id;
        $withdraw_account_id = $user_db->get($key);

        $selected_withdraw_account = \WithdrawAccounts::findFirstById($withdraw_account_id);

        if (isBlank($selected_withdraw_account) || $selected_withdraw_account->status != STATUS_ON) {

            $last_withdraw_history = \WithdrawHistories::findLastWithdrawHistory($user->id);
            $last_withdraw_account = $last_withdraw_history->withdraw_account;
            if (isPresent($last_withdraw_account) && $last_withdraw_account->status == STATUS_ON) {
                return $last_withdraw_account;
            }

        } else {
            return $selected_withdraw_account;
        }

        return 0;
    }

    static function findFirstWithdrawAccount($user)
    {
        $withdraw_account = WithdrawAccounts::findFirst(
            [
                'conditions' => "status = " . STATUS_ON . " and user_id = $user->id"
            ]
        );

        return $withdraw_account;
    }
}