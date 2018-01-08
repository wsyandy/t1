<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 10:56
 */

class PaymentChannels extends BaseModel
{

    static $payment_type = [
        'weixin' => 'weixin', 'weixin_h5' => 'weixin_h5',
        'alipay_sdk' => 'alipay_sdk', 'alipay_h5' => 'alipay_h5',
        'apple' => 'apple'
    ];

    static $clazz = [
        'Weixin' => 'Weixin', 'WeixinH5' => 'WeixinH5',
        'AlipaySdk' => 'AlipaySdk', 'AlipayH5' => 'AlipayH5',
        'Apple' => 'Apple',
    ];

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    function toJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mer_no' => $this->mer_no,
            'mer_name' => $this->mer_name,
            'status_text' => $this->status_text
        ];
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

    function match($user)
    {
        debug("user: " . $user->platform);
        if ($user->isIos() && !$this->isApple()) {
            return false;
        }
        return true;
    }

    function isApple()
    {
        return 'apple' == $this->payment_type;
    }

    function isValid()
    {
        return STATUS_ON == $this->status;
    }

    static function selectByUser($user)
    {
        $payment_channel_ids = \PaymentChannelProductChannels::findPaymentChannelIdsByProductChannelId($user->product_channel_id);
        $payment_channels = \PaymentChannels::findByIds($payment_channel_ids);
        $selected = [];
        foreach ($payment_channels as $payment_channel) {
            if ($payment_channel->isValid() && $payment_channel->match($user)) {
                $selected[] = $payment_channel;
            }
        }
        return $selected;
    }
}