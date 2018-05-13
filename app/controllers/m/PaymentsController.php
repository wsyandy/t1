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

            $device = $this->currentUser()->device;

            $apple_pay_today_amount = $device->getTodayApplePayAmount();
            $apple_pay_total_amount = $device->getTotalApplePayAmount();

            if ($apple_pay_today_amount >= 30 || $apple_pay_total_amount >= 100) {
                info("apple_pay_total_amount_30", $this->currentUser()->id, $this->currentUser()->device_id, $apple_pay_today_amount, $apple_pay_total_amount);
                return $this->renderJSON(ERROR_CODE_FAIL, '苹果支付异常');
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

        info($this->currentUser()->id, $result);

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
            info('订单不存在', $this->currentUser()->id, $order_no);
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
            info('支付失败', $this->currentUser()->id, $order_no, $order->id);
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