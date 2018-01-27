<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 上午10:47
 */

class Orders extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Products
     */
    private $_product;

    /**
     * @type ProductChannels
     */
    private $_product_channel;

    /**
     * @type Partners
     */
    private $_partner;

    static $status = [
        ORDER_STATUS_WAIT => '等待支付',
        ORDER_STATUS_SUCCESS => '支付成功',
        ORDER_STATUS_FAIL => '支付失败'
    ];

    static function createOrder($user, $product)
    {
        $order = new \Orders();
        $order->user_id = $user->id;
        $order->product_id = $product->id;
        $order->status = ORDER_STATUS_WAIT;
        $order->amount = $product->amount;
        $order->product_channel_id = $user->product_channel_id;
        $order->partner_id = $user->partner_id;
        $order->platform = $user->platform;
        $order->province_id = $user->getSearchCityId();
        if ($order->create()) {
            \Stats::delay()->record('user', 'create_order', $user->getStatAttrs());
            return $order;
        }
        return false;
    }

    function getStatusText()
    {
        return fetch(\Orders::$status, $this->status);
    }

    function toJson()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'status_text' => $this->status_text,
            'amount' => $this->amount,
            'user_avatar_url' => $this->user->avatar_small_url,
            'product_name' => $this->product->name
        ];
    }

    function getOrderNo()
    {
        return $this->id . 'd' . substr(md5($this->id . '$' . $this->user_id), 0, 5);
    }

    static function findFirstByOrderNo($order_no)
    {
        $order = self::findFirstById(intval($order_no));
        if ($order && $order_no == $order->order_no) {
            return $order;
        }
        return null;
    }

    function isPaid()
    {
        return ORDER_STATUS_SUCCESS == $this->status;
    }
}