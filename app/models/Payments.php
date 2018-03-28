<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 17:07
 */
class Payments extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Orders
     */
    private $_order;

    /**
     * @type PaymentChannels
     */
    private $_payment_channel;

    static $PAY_STATUS = [
        PAYMENT_PAY_STATUS_WAIT => '等待支付',
        PAYMENT_PAY_STATUS_SUCCESS => '支付成功',
        PAYMENT_PAY_STATUS_FAIL => '支付失败'
    ];

    function toJson()
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'user_avatar_url' => $this->user->avatar_small_url,
            'payment_type' => $this->payment_type,
            'amount' => $this->amount,
            'pay_status_text' => $this->pay_status_text,
            'paid_at' => $this->paid_at_text,
            'payment_channel_name' => $this->payment_channel_name,
            'created_at_text' => $this->created_at_text
        ];
    }

    static function createPayment($user, $order, $payment_channel)
    {
        $payment = new \Payments();
        $payment->user_id = $user->id;
        $payment->order_id = $order->id;
        $payment->payment_channel_id = $payment_channel->id;
        $payment->amount = $order->amount;
        $payment->payment_type = $payment_channel->payment_type;
        $payment->payment_no = $payment->generatePaymentNo();
        $payment->pay_status = PAYMENT_PAY_STATUS_WAIT;

        if ($payment->create()) {

            \Stats::delay()->record('user', 'create_payment', $user->getStatAttrs());

            return $payment;
        }

        return false;
    }

    function generatePaymentNo()
    {
        return strtoupper(substr($this->payment_type, 0, 2)) . date('YmdHis') . mt_rand(10000, 99999);
    }

    function isPaid()
    {
        return PAYMENT_PAY_STATUS_SUCCESS == $this->pay_status;
    }

    function validResult($opts, $body)
    {
        $gateway = $this->payment_channel->gateway();
        $result = 'error';
        # 订单已经支付完成
        if (!$this->isPaid()) {
            if ('apple' == $this->payment_type) {
                $user = \Users::findFirstById($this->user_id);
                if ($user) {
                    $opts['apple_share_secret'] = $user->product_channel->apple_share_secret;
                }
            }

            if ($gateway->validSign($opts, $body)) {
                $order = \Orders::findById($this->order_id);
                if ($order) {
                    $opts['order_amount'] = $order->amount;
                }
                $result = $gateway->validResult($this, $opts, $body);
            } else {
                debug("[NOTIFY] 支付验证通知失败");
            }
        }

        return $result;
    }

    function beforeUpdate()
    {
        if ($this->hasChanged('pay_status') && $this->isPaid()) {
            $fee = 1 - (double)($this->payment_channel->fee);
            $this->paid_amount = sprintf("%0.2f", ($this->amount * $fee));
        }
    }

    function afterUpdate()
    {
        if ($this->hasChanged('pay_status') && $this->isPaid()) {

            $lock_key = 'order_update_lock_' . $this->order_id;
            $hot_cache = self::getHotWriteCache();
            if (!$hot_cache->set($lock_key, 1, ['NX', 'EX' => 1])) {
                info('payment_id', $this->id, '支付重复通知，获取锁失败', 'payment_type', $this->payment_type);
                return;
            }

            if (!$this->paySuccess()) {
                return;
            }

            $attrs = $this->user->getStatAttrs();
            $attrs['add_value'] = round($this->paid_amount);
            info('stat', $this->id, $this->payment_type, $this->amount, $this->paid_amount, round($this->paid_amount));
            \Stats::delay()->record("user", "payment_success", $attrs);
            return;
        }
    }

    function paySuccess()
    {

        $order = Orders::findFirstById($this->order_id);
        if ($order->status == ORDER_STATUS_SUCCESS) {
            info('payment_id', $this->id, '支付重复通知！', 'payment_type', $this->payment_type);
            return false;
        }

        $order->payment_id = $this->id;
        $order->status = ORDER_STATUS_SUCCESS;
        if (!$order->save()) {
            return false;
        }

        $product = $order->product;
        $opts = ['order_id' => $order->id, 'remark' => '购买' . $product->full_name, 'mobile' => $order->mobile];
        AccountHistories::changeBalance($this->user_id, ACCOUNT_TYPE_BUY_DIAMOND, $product->diamond, $opts);
        if ($product->gold) {
            GoldHistories::changeBalance($this->user_id, GOLD_TYPE_BUY_GOLD, $product->gold, ['order_id' => $order->id, 'remark' => '购买金币']);
        }


        return true;
    }

    function getCnyAmount()
    {
        return $this->amount;
    }

}