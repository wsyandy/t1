<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 16:09
 */

namespace m;

class PaymentsController extends BaseController
{
    function indexAction()
    {

    }

    function createAction()
    {
        $user = $this->currentUser();

        if (isBlank($this->params('product_id'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
        if (isBlank($this->params('payment_channel_id'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }

        $payment_channel = \PaymentChannels::findById($this->params('payment_channel_id'));

        if ($payment_channel->isApple()) {

            $apple_pay_total_amount = $this->currentDevice()->getTodayApplePayAmount();

            if ($apple_pay_total_amount >= 500) {
                info($this->currentUser()->sid, $this->currentDevice()->id, $apple_pay_total_amount);
                return $this->renderJSON(ERROR_CODE_FAIL, '苹果支付异常');
            }

            if ($apple_pay_total_amount >= 300) {
                info("apple_pay_total_amount_300", $this->currentUser()->sid, $this->currentDevice()->id, $apple_pay_total_amount);
            }
        }

        $product = \Products::findById($this->params('product_id'));

        list($error_code, $error_reason, $order) = \Orders::createOrder($this->currentUser(), $product);

        if (ERROR_CODE_FAIL == $error_code) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        if (!$order) {
            return $this->renderJSON(ERROR_CODE_FAIL, '订单创建失败');
        }

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

        $result_url = '/m/payments/result?order_no=' . $order->order_no . '&sid=' . $user->sid . '&code=' . $this->currentProductChannel()->code;

        info($result);

        if (is_array($result) && isset($result['url'])) {
            return $this->response->redirect($result['url']);
        }

        if ($payment_channel->payment_type == "alipay_sdk") {
            $result['rsa2'] = true;
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', array_merge([
            'name' => $order->name,
            'amount' => $order->amount,
            'payment_id' => $payment->id,
            'payment_no' => $payment->payment_no,
            'result_url' => $result_url
        ], $result));
    }

    function resultAction()
    {

        $order_no = $this->params('order_no');
        $order = \Orders::findFirstByOrderNo($order_no);

        if (!$order || $order->user_id != $this->currentUser()->id) {
            info($this->currentUser()->sid, $order_no);
            if ($this->request->isAjax()) {
                $this->renderJSON(ERROR_CODE_FAIL, '订单不存在!');
            }
            return;
        }

        $this->view->order = $order;
        $this->view->user = $this->currentUser();
        $this->view->product_channel = $this->currentUser()->product_channel;

        $payment = \Payments::findFirstByOrderId($order->id);

        if (!$payment) {
            info($this->currentUser()->sid, $order_no, $order->id);
            if ($this->request->isAjax()) {
                $this->renderJSON(ERROR_CODE_FAIL, '支付失败');
            }
            return;
        }

        if ($this->request->isAjax()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['pay_status' => $payment->pay_status]);
            return;
        }

        $this->view->title = '支付结果';
        $this->view->payment = $payment;
    }

}