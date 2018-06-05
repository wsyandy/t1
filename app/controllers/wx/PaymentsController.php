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

        $fee_type = 'diamond';
        $product_group = \ProductGroups::findFirst(['product_channel_id' => $this->currentProductChannel()->id, 'fee_type' => $fee_type, 'status' => STATUS_ON]);
        $products = \Products::find([
            'conditions' => 'product_group_id = :product_group_id: and status = :status: and amount<3000 and (apple_product_no="" or apple_product_no is null)',
            'bind' => ['product_group_id' => $product_group->id, 'status' => STATUS_ON],
            'order' => 'amount asc']);

        $payment_channel_ids = \PaymentChannelProductChannels::findPaymentChannelIdsByProductChannelId($this->currentProductChannel()->id);
        $payment_channels = \PaymentChannels::findByIds($payment_channel_ids);
        $selected_payment_channel = null;
        foreach ($payment_channels as $payment_channel) {
            if (!$payment_channel->isValid()) {
                continue;
            }
            $pay_type = 'weixin_js';

            if (isDevelopmentEnv()) {
                $pay_type = 'weixin_h5';
            }

            if ($payment_channel->payment_type == $pay_type) {
                $selected_payment_channel = $payment_channel;
                break;
            }
        }

        $this->view->pay_user_id = $this->session->get('pay_user_id');
        $this->view->pay_user_name = $this->session->get('pay_user_name');
        $this->view->selected_payment_channel = $selected_payment_channel;
        $this->view->products = $products;
        $this->view->product_channel = $this->currentProductChannel();
        $this->view->title = '充值';
    }

    function createAction()
    {
        $user = null;
        $uid = $this->params('user_id', 0);
        if ($uid) {
            $user = \Users::findFirstByUid($uid);
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

        $result_url = '/wx/payments/result?order_no=' . $order->order_no . '&sid=' . $user->sid . '&code=' . $this->currentProductChannel()->code;
        $cancel_url = $this->headers('Referer');

        $opt = [
            'request_root' => $this->getRoot(),
            'ip' => $this->remoteIp(),
            'show_url' => $cancel_url,
            'cancel_url' => $cancel_url,
            'callback_url' => $this->getRoot() . $result_url,
            'openid' => $this->currentOpenid(), // 代替充值特殊处理
            'product_name' => '订单-' . $order->order_no
        ];

        debug($user->id, 'openid', $user->openid);

        # 返回支付sdk需要的相关信息
        $pay_gateway = $payment_channel->gateway();
        $form = $pay_gateway->buildForm($payment, $opt);

        debug($user->id, 'payment build_form=', $form);

        $result = [
            'form' => $form,
            'payment_type' => $payment_channel->payment_type,
            'order_no' => $order->order_no,
            'paid_status' => $payment->pay_status,
            'result_url' => $result_url,
            'nickname' => $user->nickname
        ];

        $this->renderJSON(ERROR_CODE_SUCCESS, '', $result);
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
        $this->session->set('pay_user_id', $order->user->uid);
        $this->session->set('pay_user_name', $order->user->nickname);

        if (!$payment || !$order->isPaid()) {
            $this->response->redirect('/wx/payments/weixin?ts=' . time());
            return;
        }

        $this->view->title = '支付结果';
        $this->view->payment = $payment;
    }

    function questionsAction()
    {

    }

    function manualRechargeAction()
    {
        $this->view->title = '转帐充值';
    }
}