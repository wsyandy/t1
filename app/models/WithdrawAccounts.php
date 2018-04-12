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
        $data = [];
        if($this->account){
            $account_text = substr_replace($this->account,'*',0,-3,$this->account);
            $data = ['account_text'=>$account_text];
        }
        return $data;
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
}