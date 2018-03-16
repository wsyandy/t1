<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/3/15
 * Time: 下午9:19
 */

namespace wx;


class PaymentsController extends BaseController
{

    function weixinAction()
    {

        $products = \Products::findDiamondListByUser($this->currentUser());
        $payment_channels = \PaymentChannels::selectByUser($this->currentUser());
        $selected_payment_channel = $payment_channels[0];
        $this->view->selected_payment_channel = $selected_payment_channel;
        $this->view->products = $products;
        $this->view->current_user = $this->currentUser();
        $this->view->product_channel = $this->currentProductChannel();
        $this->view->title = '充值';
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

        $product = \Products::findById($this->params('product_id'));

        list($error_code, $error_reason, $order) = \Orders::createOrder($this->currentUser(), $product);

        if (ERROR_CODE_FAIL == $error_code) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        if (!$order) {
            return $this->renderJSON(ERROR_CODE_FAIL, '订单创建失败');
        }

        $payment_channel = \PaymentChannels::findFirstById($this->params('payment_channel_id'));
        $payment = \Payments::createPayment($this->currentUser(), $order, $payment_channel);
        if (!$payment) {
            return $this->renderJSON(ERROR_CODE_FAIL, '支付失败');
        }

        $result_url = '/wx/payments/result?order_no=' . $order->order_no . '&sid=' . $user->sid . '&code=' . $this->currentProductChannel()->code;
        $cancel_url = $this->headers('Referer');

        $opt = [
            'request_root' => $this->getRoot(),
            'ip' => $this->remoteIp(),
            'show_url' => $cancel_url,
            'cancel_url' => $cancel_url,
            'callback_url' => $this->getRoot() . $result_url,
            'openid' => $this->currentUser()->openid,
            'product_name' => '订单-' . $order->order_no
        ];
        debug('openid', $this->currentUser()->openid, 'userinfo', $this->currentUser());

        # 返回支付sdk需要的相关信息
        $pay_gateway = $payment_channel->gateway();
        $form = $pay_gateway->buildForm($payment, $opt);
        debug('payment build_form=', $form);
        $result = [
            'form' => $form,
            'payment_type' => $payment_channel->payment_type,
            'order_no' => $order->order_no,
            'paid_status' => $payment->pay_status,
            'result_url' => $result_url
        ];

        $this->renderJSON(ERROR_CODE_SUCCESS, '', $result);
    }

    function resultAction()
    {

        $order_no = $this->params('order_no');
        $order = \Orders::findFirstByOrderNo($order_no);

        if (!$order || $order->user_id != $this->currentUser()->id) {
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