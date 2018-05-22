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

        if ($gift->isDiamondPayType()) {
            return intval($this->diamond) >= $total_amount;
        }

        if ($gift->isGoldPayType()) {
            return intval($this->gold) >= $total_amount;
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
        return $this->isIosAuthVersion();
    }

    /**
     * iOS审核版本
     * @return bool
     */
    function isIosAuthVersion()
    {
        $result = $this->isIos() &&
            intval($this->version_code) > intval($this->product_channel->apple_stable_version);
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

    function canShareForH5()
    {
        if ($this->isIos()) {
            return $this->version_code >= 19;
        }

        return $this->version_code >= 8;
    }

    function canReceiveBoomGiftMessage()
    {
        if ($this->isIos()) {
            return $this->version_code > 19;
        }

        return $this->version_code > 8;
    }

    // 屏蔽热门房间
    function isShieldHotRoom()
    {
        if ($this->union_id || $this->isIdCardAuth()) {
            return false;
        }

        $province_ids = [1, 2];
        if (in_array($this->province_id, $province_ids)
            || in_array($this->ip_province_id, $province_ids) && isProduction()
            || in_array($this->geo_province_id, $province_ids) && isProduction()) {

            info($this->id, $this->province_id, $this->ip_province_id, $this->geo_province_id);
            return true;
        }

        $city_ids = [1, 2, 94, 192, 193];
        if (in_array($this->city_id, $city_ids)
            || in_array($this->ip_city_id, $city_ids) && isProduction()
            || in_array($this->geo_city_id, $city_ids) && isProduction()) {

            info($this->id, $this->city_id, $this->ip_city_id, $this->geo_city_id);
            return true;
        }

        return false;
    }

}