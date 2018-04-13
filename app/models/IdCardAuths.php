<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/19
 * Time: 下午7:29
 */
class IdCardAuths extends BaseModel
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
     * @type AccountBanks
     */
    private $_account_bank;

    static $AUTH_STATUS = [AUTH_SUCCESS => '审核成功', AUTH_FAIL => '审核失败', AUTH_WAIT => '等待审核'];


    function afterCreate()
    {
        $this->updateUserIdCardAuth();
    }

    function afterUpdate()
    {
        if ($this->hasChanged('auth_status')) {
            $this->updateUserIdCardAuth();
        }
    }

    function updateUserIdCardAuth()
    {
        if (AUTH_SUCCESS == $this->auth_status) {
            Chats::sendTextSystemMessage($this->user->id, '您的主持认证通过了,赶紧开房间连麦吧~');
        }

        if (AUTH_FAIL == $this->auth_status) {
            Chats::sendTextSystemMessage($this->user->id, "很遗憾,您的主持认证申请未通过");
        }

        $this->auth_at = time();
        $this->update();

        $user = $this->user;
        $user->id_card_auth = $this->auth_status;
        $user->update();
    }


    static function createIdCardAuth($user, $opts = [])
    {
        if (AUTH_WAIT == $user->id_card_auth || AUTH_SUCCESS == $user->id_card_auth) {
            return [ERROR_CODE_FAIL, '请勿重复认证'];
        }

        $id_no = fetch($opts, 'id_no');
        $id_name = fetch($opts, 'id_name');
        $mobile = fetch($opts, 'mobile');
        $bank_account = fetch($opts, 'bank_account');

        $id_card_auth = IdCardAuths::findFirstByUserId($user->id);

        if (!$id_card_auth) {
            $id_card_auth = new IdCardAuths();
            $id_card_auth->user_id = $user->id;
            $id_card_auth->product_channel_id = $user->product_channel_id;
        }

        $id_card_auth->id_name = $id_name;
        $id_card_auth->mobile = $mobile;
        $id_card_auth->id_no = $id_no;
        $id_card_auth->bank_account = $bank_account;
        $id_card_auth->auth_status = AUTH_WAIT;

        if ($id_card_auth->save()) {
            return [ERROR_CODE_SUCCESS, '认证成功,请等待审核'];
        }

        return [ERROR_CODE_FAIL, '认证失败'];
    }

    static function checkBankAccount($bank_account)
    {
        $card_number_array = str_split($bank_account);
        $last_n = $card_number_array[count($card_number_array) - 1];
        krsort($card_number_array);
        $i = 1;
        $total = 0;
        foreach ($card_number_array as $n) {
            if ($i % 2 == 0) {
                $ix = $n * 2;
                if ($ix >= 10) {
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                } else {
                    $total += $ix;
                }
            } else {
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if ($x == $last_n) {
            return true;
        } else {
            return false;
        }
    }

}