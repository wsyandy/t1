<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 10:56
 */

class PaymentChannels extends BaseModel
{

    static $payment_type = array(
        'weixin_sdk' => 'weixin_sdk', 'weixin_h5' => 'weixin_h5',
        'alipay_sdk' => 'alipay_sdk', 'alipay_h5' => 'alipay_h5',
        'apple' => 'apple',
    );

    static $clazz = array(
        'WeixinSdk' => 'WeixinSdk', 'WeixinH5' => 'WeixinH5',
        'alipaySdk' => 'alipaySdk', 'alipayH5' => 'alipayH5',
        'apple' => 'Apple',
    );

    static $status = array(STATUS_ON => '有效', STATUS_OFF => '无效');

    function toJson()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'mer_no' => $this->mer_no,
            'mer_name' => $this->mer_name,
            'status_text' => $this->status_text
        );
    }

    function getStatusText()
    {
        return fetch(\PaymentChannels::$status, $this->status);
    }

    function supportProductChannel($product_channel)
    {
        return in_array($product_channel->id, $this->product_channel_ids);
    }

    function getProductChannelIds()
    {
        $pcpcs = \PaymentChannelProductChannels::findByConditions(array('payment_channel_id' => $this->id));
        $ids = array();
        foreach ($pcpcs as $pcpc) {
            $ids[] = $pcpc->product_channel_id;
        }
        debug("ids: " . json_encode($ids, JSON_UNESCAPED_UNICODE));
        return $ids;
    }
}