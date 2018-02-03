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

    static $pay_status = [
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

    function getPayStatusText()
    {
        return fetch(\Payments::$pay_status, $this->pay_status);
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
                $user = \Users::findById($this->user_id);
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
            $attrs = $this->user->getStatAttrs();
            $attrs['add_value'] = $this->paid_amount;
            \Stats::delay()->record("user", "payment_success", $attrs);
            return $this->paySuccess();
        }
        return false;
    }

    function paySuccess()
    {
        if (!$this->isPaid()) {
            return true;
        }
        $order = $this->order;
        $order->status = ORDER_STATUS_SUCCESS;
        if ($order->save()) {
            $product = $order->product;
            if ($product->product_group->isDiamond()) {
                $opts = ['order_id' => $order->id, 'remark' => '购买钻石', 'mobile' => $order->mobile];
                \AccountHistories::changeBalance($this->user_id, ACCOUNT_TYPE_BUY_DIAMOND, $product->diamond, $opts);
            }
        }
        return false;
    }

    function getCnyAmount()
    {
        return $this->amount;
    }

}