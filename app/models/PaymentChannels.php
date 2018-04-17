<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 10:56
 */
class PaymentChannels extends BaseModel
{

    static $PAYMENT_TYPE = [
        'weixin' => 'weixin', 'weixin_h5' => 'weixin_h5',
        'alipay_sdk' => 'alipay_sdk', 'alipay_h5' => 'alipay_h5',
        'apple' => 'apple', 'weixin_js' => 'weixin_js', 'google' => 'google'
    ];

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $PLATFORMS = ['client_ios' => '客户端ios', 'client_android' => '客户端安卓', 'weixin_ios' => '微信ios',
        'weixin_android' => '微信安卓', 'touch_ios' => 'H5ios', 'touch_android' => 'H5安卓'];

    function toJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mer_no' => $this->mer_no,
            'mer_name' => $this->mer_name,
            'status_text' => $this->status_text,
            'rank' => $this->rank
        ];
    }

    function toApiJson()
    {
        return [
            'id' => $this->id,
            'payment_type' => $this->payment_type
        ];
    }

    static function getGatewayClasses()
    {
        return \paygateway\Base::getGatewayNames();
    }

    function gateway()
    {
        $clazz = '\paygateway\\' . $this->clazz;
        $gateway = new $clazz($this);

        return $gateway;
    }

    function supportProductChannel($product_channel)
    {
        return in_array($product_channel->id, $this->product_channel_ids);
    }

    function getProductChannelIds()
    {
        $pcpcs = \PaymentChannelProductChannels::findByConditions(['payment_channel_id' => $this->id]);
        $ids = [];
        foreach ($pcpcs as $pcpc) {
            $ids[] = $pcpc->product_channel_id;
        }
        debug("ids: " . json_encode($ids, JSON_UNESCAPED_UNICODE));
        return $ids;
    }

    /**
     * 线下测试支持所有通道
     * @param $user
     * @return bool
     */
    function match($user)
    {

        debug("user: ", $user->id, $user->platform, $this->payment_type);
        if ($user->isWxPlatform()) {

            if (in_array($this->payment_type, ['weixin_js'])) {
                return true;
            }

            return false;
        }

        $version_code = $user->version_code;
        if ($user->isAndroid()) {

            if ($this->android_version_code && $user->isAndroid() && $this->android_version_code > $version_code) {
                return false;
            }

            if (in_array($this->payment_type, ['weixin', 'alipay_sdk', 'google'])) {
                return true;
            }

            return false;
        }

        if ($user->isIos()) {

            if ($this->ios_version_code && $user->isIos() && $this->ios_version_code > $version_code) {
                return false;
            }

            return $this->isApple();
        }

        return false;
    }

    function isWeixinH5()
    {
        return 'weixin_h5' == $this->payment_type;
    }

    function isApple()
    {
        return 'apple' == $this->payment_type;
    }

    function isValid()
    {
        return STATUS_ON == $this->status;
    }

    static function selectByUser($user, $format = null)
    {
        $payment_channel_ids = \PaymentChannelProductChannels::findPaymentChannelIdsByProductChannelId($user->product_channel_id);
        $payment_channels = \PaymentChannels::findByIds($payment_channel_ids);
        $selected = [];
        foreach ($payment_channels as $payment_channel) {
            if ($payment_channel->isValid() && $payment_channel->match($user)) {
                if (isPresent($format) && $payment_channel->isResponseTo($format)) {
                    $selected[] = $payment_channel->$format();
                } else {
                    $selected[] = $payment_channel;
                }
            }
        }

        return $selected;
    }

}