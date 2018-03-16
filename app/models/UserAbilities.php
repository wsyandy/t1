<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 17:11
 */

trait UserAbilities
{
    function canGiveGift($gift, $gift_num)
    {
        $total_amount = intval($gift->amount) * $gift_num;
        info($this->diamond, $total_amount, $gift_num, $gift->id);
        return intval($this->diamond) >= $total_amount;
    }

    /**
     * 是否不通过h5页面由客户端直接支付
     * @return bool
     */
    function isNativePay()
    {
        if ($this->isAndroid()) {
            return false;
        }
        return $this->isAuthVersion();
    }

    /**
     * iOS审核版本
     * @return bool
     */
    function isAuthVersion()
    {
        $result = $this->isIos() &&
            intval($this->version_code) >= intval($this->product_channel->apple_stable_version);
        return $result;
    }

    function canShowProductFullName()
    {
        return $this->isAndroid();
    }
}