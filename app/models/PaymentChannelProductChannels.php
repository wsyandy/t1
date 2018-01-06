<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 12:03
 */

class PaymentChannelProductChannels extends BaseModel
{
    /**
     * @type ProductChannels
     */
    private $_product_channel;

    /**
     * @type PaymentChannels;
     */
    private $_payment_channel;

    static function fresh($payment_channel_id, $product_channel_ids)
    {
        $conditions = array('payment_channel_id' => $payment_channel_id);
        $payment_channel_product_channels = \PaymentChannelProductChannels::findByConditions($conditions);
        $exist_product_channel_ids = array();
        foreach ($payment_channel_product_channels as $payment_channel_product_channel) {
            if (in_array($payment_channel_product_channel->product_channel_id, $product_channel_ids)) {
                $exist_product_channel_ids[] = $payment_channel_product_channel->product_channel_id;
            } else {
                $payment_channel_product_channel->delete();
            }
        }
        foreach ($product_channel_ids as $product_channel_id) {
            if (!in_array($product_channel_id, $exist_product_channel_ids)) {
                $payment_channel_product_channel = new \PaymentChannelProductChannels();
                $payment_channel_product_channel->product_channel_id = $product_channel_id;
                $payment_channel_product_channel->payment_channel_id = $payment_channel_id;
                $payment_channel_product_channel->create();
            }
        }
    }
}