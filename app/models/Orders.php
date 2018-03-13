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
        $lock_key = 'order_create_lock_' . $user->id;
        $hot_cache = self::getHotWriteCache();

        if (!$hot_cache->setnx($lock_key, $user->id)) {
            info('Exce', $user->id, '请求多次，lock', $lock_key);
            return [ERROR_CODE_FAIL, '您发起支付太快啦,请稍后.', null];
        }

        $hot_cache->expire($lock_key, 5);

        $order = new \Orders();
        $order->user_id = $user->id;
        $order->product_id = $product->id;
        $order->status = ORDER_STATUS_WAIT;
        $order->amount = $product->amount;
        $order->product_channel_id = $user->product_channel_id;
        $order->partner_id = $user->partner_id;
        $order->platform = $user->platform;
        $order->province_id = $user->getSearchCityId();
        $order->mobile = $user->mobile;
        $order->union_id = $user->union_id;

        if ($order->create()) {
            \Stats::delay()->record('user', 'create_order', $user->getStatAttrs());
            return [ERROR_CODE_SUCCESS, '', $order];
        }

        return [ERROR_CODE_FAIL, '创建订单失败,请稍后.', null];
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