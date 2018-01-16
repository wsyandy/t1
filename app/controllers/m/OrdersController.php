<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 16:09
 */

namespace m;

class OrdersController extends BaseController
{
    function createAction()
    {
        if (isBlank($this->params('product_id'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
        if (isBlank($this->params('payment_channel_id'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
        $product = \Products::findById($this->params('product_id'));
        $order = \Orders::createOrder($this->currentUser(), $product);
        if (!$order) {
            return $this->renderJSON(ERROR_CODE_FAIL, '订单创建失败');
        }
        $payment_channel = \PaymentChannels::findById($this->params('payment_channel_id'));
        $payment = \Payments::createPayment($this->currentUser(), $order, $payment_channel);
        if (!$payment) {
            return $this->renderJSON(ERROR_CODE_FAIL, '支付失败');
        }
        $opts = [
            'ip' => $this->remoteIp(),
            'product_name' => $product->name,
            'request_root' => $this->getRoot(),
            'mobile' => $this->currentUser()->mobile
        ];
        $result = $payment_channel->gateway()->buildForm($payment, $opts);
        if (is_array($result) && isset($result['url'])) {
            return $this->response->redirect($result['url']);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', array_merge([
            'name' => $order->name,
            'amount' => $order->amount,
            'payment_id' => $payment->id,
            'payment_no' => $payment->payment_no
        ], $result));
    }
}