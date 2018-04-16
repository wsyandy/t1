<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 23/01/2018
 * Time: 16:33
 */

namespace iapi;

class PaymentsController extends BaseController
{
    function appleResultAction()
    {
        if (isBlank($this->params('product_id')) || isBlank($this->params('data'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, t('参数错误',$this->currentUser()->lang));
        }
        $product = \Products::findById($this->params('product_id'));

        if (isBlank($product)) {
            return $this->renderJSON(ERROR_CODE_FAIL, t('参数错误',$this->currentUser()->lang));
        }

        $payments = \Payments::findByConditions(
            array(
                'user_id' => $this->currentUserId(),
                'result_data' => $this->params('data')
            )
        );
        if ($payments && count($payments) > 0) {
            return $this->renderJSON(ERROR_CODE_FAIL, t('重复支付',$this->currentUser()->lang));
        }

        list($error_code, $error_reason, $order) = \Orders::createOrder($this->currentUser(), $product);

        if (ERROR_CODE_FAIL == $error_code) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        if (!$order) {
            return $this->renderJSON(ERROR_CODE_FAIL, t('支付失败',$this->currentUser()->lang));
        }

        $payment_channels = \PaymentChannels::selectByUser($this->currentUser());
        $payment_channel = fetch($payment_channels, 0);
        if (isBlank($payment_channel)) {
            return $this->renderJSON(ERROR_CODE_FAIL, t('不支持苹果支付',$this->currentUser()->lang));
        }
        $payment = \Payments::createPayment($this->currentUser(), $order, $payment_channel);
        if ($payment) {
            $result = $payment->validResult(array('data' => $this->params('data')), '');
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $result);
        }
        return $this->renderJSON(ERROR_CODE_FAIL, t('支付失败',$this->currentUser()->lang));
    }
}