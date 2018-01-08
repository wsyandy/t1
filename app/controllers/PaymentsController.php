<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 08/01/2018
 * Time: 10:30
 */

class PaymentsController extends ApplicationController
{
    function notifyAction()
    {
        $payment_id = $this->params('id');
        if (isBlank($payment_id)) {
            $payment_id = $this->params('order_id');
        }

        $payment = \Payments::findById($payment_id);
        if (!$payment) {
            echo 'error';
            return;
        }

        $body = $this->request->getRawBody();
        $opts = $this->params();

        $result = $payment->validResult($opts, $body);
        $user_id = $payment->user_id;

        if (is_array($result) && count($result) > 0) {
            debug("USER_ID" . $user_id);
            $this->response->setContentType('application/json', 'utf-8');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            return;
        } else {
            debug("USER_ID" . $user_id);
            echo $result;
            return;
        }
    }
}