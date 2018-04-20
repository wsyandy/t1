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

        if ($gift->isDiamondPayType()) {
            return intval($this->diamond) >= $total_amount;
        }

        if ($gift->isGoldPayType()) {
            return intval($this->gold) >= $total_amount;
        }

        if ($gift->isIGoldPayType()) {
            return intval($this->i_gold) >= $total_amount;
        }

        return false;
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

    function canShowGoldGift()
    {
        $product_channel = $this->product_channel;

        if (1 != $product_channel->id) {
            return true;
        }

        if ($this->isIos()) {
            return $this->version_code > 11;
        }

        return $this->version_code > 4;
    }

    function canShowRoomActivity()
    {
        if (1 != $this->product_channel_id) {
            return false;
        }

        if ($this->isIos()) {
            return $this->version_code >= 15;
        }

        return $this->version_code >= 6;
    }
}