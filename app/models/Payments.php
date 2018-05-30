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

    /**
     * @type Operators
     */
    private $_operator;

    static $PAY_STATUS = [
        PAYMENT_PAY_STATUS_WAIT => '等待支付',
        PAYMENT_PAY_STATUS_SUCCESS => '支付成功',
        PAYMENT_PAY_STATUS_FAIL => '支付失败'
    ];

    function beforeUpdate()
    {
        if ($this->hasChanged('pay_status') && $this->isPaid()) {
            if (!$this->isManualRecharge()) {
                $fee = 1 - (double)($this->payment_channel->fee);
                $this->paid_amount = sprintf("%0.2f", ($this->amount * $fee));
            }
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

            if ($this->isApple()) {
                $this->statDayPayAmount();
            }

            $attrs = $this->user->getStatAttrs();
            $attrs['add_value'] = round($this->paid_amount);
            info('stat', $this->id, $this->payment_type, $this->amount, $this->paid_amount, round($this->paid_amount));
            \Stats::delay()->record("user", "payment_success", $attrs);


            // 分销奖励
            if ($this->user->share_parent_id) {
                \SmsDistributeHistories::delay()->checkPay($this->user_id, $this->order->product->diamond);
            }

            //当支付成功后，推送消息
            \DataCollection::syncData('payment', 'pay_success', ['payment' => $this->toPushDataJson()]);
            return;
        }
    }

    function toPushDataJson()
    {
        return array_merge(['product_diamond_number' => $this->diamond], $this->toJson());
    }

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

    static function createPayment($user, $order, $payment_channel, $opts = [])
    {
        $payment = new \Payments();
        $payment->user_id = $user->id;
        $payment->order_id = $order->id;
        $payment->payment_channel_id = $payment_channel->id;
        $payment->amount = $order->amount;
        $payment->payment_type = $payment_channel->payment_type;
        $payment->payment_no = $payment->generatePaymentNo();
        $payment->pay_status = PAYMENT_PAY_STATUS_WAIT;

        if ($payment->isManualRecharge()) {
            $diamond = fetch($opts, 'diamond');
            $gold = fetch($opts, 'gold');
            $paid_amount = fetch($opts, 'paid_amount');

            $payment->operator_id = $order->operator_id;
            $payment->paid_amount = $paid_amount;
            $payment->temp_data = json_encode(['diamond' => $diamond, 'gold' => $gold], JSON_UNESCAPED_UNICODE);
        }

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

    function statDayPayAmount()
    {
        if ($this->user->device) {
            $hot_cache = Payments::getHotWriteCache();
            $user_db = Users::getUserDb();

            $user = $this->user;

            $key = "stat_apple_day_total_pay_amount_list_" . date("Ymd");
            $total_key = "stat_apple_total_pay_amount_device_id_" . $user->device_id;

            $user_db->incrby($total_key, $this->amount);
            $hot_cache->zincrby($key, $this->amount, $user->device_id);
            $hot_cache->expire($key, endOfDay() - time());
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

        $opts = ['target_id' => $order->id, 'mobile' => $order->mobile];

        if ($this->isManualRecharge()) {
            $opts['remark'] = "人工充值";
            $diamond = $this->diamond;
            $gold = $this->gold;
        } else {
            $product = $order->product;
            $opts['remark'] = '购买' . $product->full_name;
            $diamond = $product->diamond;
            $gold = $product->gold;
        }

        if ($diamond) {
            AccountHistories::changeBalance($this->user_id, ACCOUNT_TYPE_BUY_DIAMOND, $diamond, $opts);
        }

        if ($gold) {
            GoldHistories::changeBalance($this->user_id, GOLD_TYPE_BUY_GOLD, $gold, $opts);
        }

        return true;
    }

    function getDiamond()
    {
        if ($this->isManualRecharge()) {
            $res = json_decode($this->temp_data, true);
            return fetch($res, 'diamond');
        }

        return 0;
    }

    function getGold()
    {
        if ($this->isManualRecharge()) {
            $res = json_decode($this->temp_data, true);
            return fetch($res, 'gold');
        }

        return 0;
    }

    function getCnyAmount()
    {
        return $this->amount;
    }

    function isApple()
    {
        return 'apple' == $this->payment_type;
    }

    //人工充值
    function isManualRecharge()
    {
        return 'manual_recharge' == $this->payment_type;
    }
}