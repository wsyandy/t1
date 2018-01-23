<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 23/01/2018
 * Time: 16:33
 */
namespace api;

class PaymentsController extends BaseController
{
    function appleResultAction()
    {
        if (isBlank($this->params('product_id')) || isBlank($this->params('data'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }
        $product = \Products::findById($this->params('product_id'));
        $payments = \Payments::findByConditions(
            array(
                'user_id' => $this->currentUserId(),
                'result_data' => $this->params('data')
            )
        );
        if ($payments && count($payments) > 0) {
            return $this->renderJSON(ERROR_CODE_FAIL, '重复支付');
        }
        $order = \Orders::createOrder($this->currentUser(), $product);
        if (!$order) {
            return $this->renderJSON(ERROR_CODE_FAIL, '支付失败');
        }
        $payment_channels = \PaymentChannels::selectByUser($this->currentUser());
        $payment_channel = fetch($payment_channels, 0);
        if (isBlank($payment_channel)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '不支持苹果支付');
        }
        $payment = \Payments::createPayment($this->currentUser(), $order, $payment_channel);
        if ($payment) {
            $result = $payment->validResult(array('data' => $this->params('data')), '');
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $result);
        }
        return $this->renderJSON(ERROR_CODE_FAIL, '支付失败');
    }
}