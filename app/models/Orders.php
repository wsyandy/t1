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

    static $status = array(
        ORDER_STATUS_WAIT => '等待支付',
        ORDER_STATUS_SUCCESS => '支付成功',
        ORDER_STATUS_FAIL => '支付失败'
    );

    static function createOrder($user, $product)
    {
        $order = new \Orders();
        $order->user_id = $user->id;
        $order->product_id = $product->id;
        $order->status = ORDER_STATUS_WAIT;
        $order->amount = $product->amount;
        return $order->create();
    }

    function getStatusText()
    {
        return fetch(\Orders::$status, $this->status);
    }

    function toJson()
    {
        return array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'status_text' => $this->status_text,
            'amount' => $this->amount,
            'user_avatar_url' => $this->user->avatar_small_url,
            'product_name' => $this->product->name
        );
    }
}