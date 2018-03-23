<?php

class PayController extends ApplicationController
{

    function indexAction()
    {

        $code = $this->params('code', 'yuewan');
        $product_channel = \ProductChannels::findFirstByCode($code);

        $fee_type = 'diamond';
        $product_group = \ProductGroups::findFirst(['product_channel_id' => $product_channel->id, 'fee_type' => $fee_type, 'status' => STATUS_ON]);
        $products = \Products::find([
            'conditions' => 'product_group_id = :product_group_id: and status = :status: and (apple_product_no="" or apple_product_no is null)',
            'bind' => ['product_group_id' => $product_group->id, 'status' => STATUS_ON],
            'order' => 'amount asc']);

        $payment_channel_ids = \PaymentChannelProductChannels::findPaymentChannelIdsByProductChannelId($product_channel->id);
        $payment_channels = \PaymentChannels::findByIds($payment_channel_ids);
        $selected_payment_channels = [];
        foreach ($payment_channels as $payment_channel) {
            if (!$payment_channel->isValid()) {
                continue;
            }

            if (in_array($payment_channel->payment_type, ['weixin_h5', 'alipay_h5'])) {
                $selected_payment_channels[] = $payment_channel;
            }
        }

        $this->view->title = '大额充值';
        $this->view->pay_user_id = $this->session->get('pay_user_id');
        $this->view->pay_user_name = $this->session->get('pay_user_name');
        $this->view->products = $products;
        $this->view->product_channel = $product_channel;
        $this->view->payment_channels = $selected_payment_channels;

    }

    function createAction()
    {

        $user_id = $this->params('user_id');
        $user = null;
        if ($user_id) {
            $user = \Users::findFirstById($user_id);
        }

        if (!$user || $user->isSilent()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请填写正确的HI ID');
        }

        if (isBlank($this->params('product_id'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        if (isBlank($this->params('payment_channel_id'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $product = \Products::findFirstById($this->params('product_id'));

        list($error_code, $error_reason, $order) = \Orders::createOrder($user, $product);
        if (ERROR_CODE_FAIL == $error_code) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        if (!$order) {
            return $this->renderJSON(ERROR_CODE_FAIL, '订单创建失败');
        }

        $payment_channel = \PaymentChannels::findFirstById($this->params('payment_channel_id'));
        $payment = \Payments::createPayment($user, $order, $payment_channel);
        if (!$payment) {
            return $this->renderJSON(ERROR_CODE_FAIL, '支付失败');
        }

        $result_url = '/pay/result?order_no=' . $order->order_no;
        $cancel_url = $this->headers('Referer');

        $opt = [
            'request_root' => $this->getRoot(),
            'ip' => $this->remoteIp(),
            'show_url' => $cancel_url,
            'cancel_url' => $cancel_url,
            'callback_url' => $this->getRoot() . $result_url,
            'product_name' => '订单-' . $order->order_no
        ];

        # 返回支付sdk需要的相关信息
        $pay_gateway = $payment_channel->gateway();
        $form = $pay_gateway->buildForm($payment, $opt);

        info($user->id, 'payment build_form=', $form);

        $this->renderJSON(ERROR_CODE_SUCCESS, '', $form);
    }

    function resultAction()
    {

        $order_no = $this->params('order_no');
        $order = \Orders::findFirstByOrderNo($order_no);
        if (!$order) {
            if ($this->request->isAjax()) {
                $this->renderJSON(ERROR_CODE_FAIL, '订单不存在!');
            }
            return;
        }

        $this->view->order = $order;
        $payment = \Payments::findFirstByOrderId($order->id);
        $this->session->set('pay_user_id', $order->user_id);
        $this->session->set('pay_user_name', $order->user->nickname);

        if (!$payment || !$order->isPaid()) {
            $this->response->redirect('/pay/index?ts=' . time());
            return;
        }

        $this->view->title = '支付结果';
        $this->view->payment = $payment;
    }

    function checkUserAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findFirstById($user_id);
        $nickname = '此ID不存在';
        if ($user) {
            $nickname = $user->nickname;
        }
        $result = ['nickname' => $nickname];
        $this->renderJSON(ERROR_CODE_SUCCESS, '', $result);

    }
}