<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/5
 * Time: 上午10:51
 */

namespace admin;

class OrdersController extends BaseController
{
    function indexAction()
    {

        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $status = $this->params('order[status_eq]');

        $cond = $this->getConditions('order');
        $cond['order'] = 'id desc';


        $order_id = $this->params('id');

        if ($order_id) {
            $cond = ['conditions' => "id = " . $order_id];
        }

        $uid = $this->params('uid');

        if ($uid) {
            $user = \Users::findFirst(array(
                "uid = " . $uid
            ));
            $cond['conditions'] .= " user_id = :user_id:";
            $cond['bind']['user_id'] = $user->id;
        }


        $orders = \Orders::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->orders = $orders;
        $this->view->status = $status != "" ? intval($status) : "";
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function detailAction()
    {
        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $cond = $this->getConditions('order');
        $cond['order'] = 'id desc';

        $order_id = $this->params('id');

        if ($order_id) {
            $cond = ['conditions' => "id = " . $order_id];
        }

        $orders = \Orders::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->orders = $orders;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function manualRechargeAction()
    {
        $user_id = intval($this->params('user_id'));
        if ($this->request->isPost()) {

            if (!$this->currentOperator()->isSuperOperator()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '你无此权限');
            }

            $amount = $this->params('amount');
            $paid_amount = $this->params('paid_amount');
            $diamond = $this->params('diamond');
            $gold = $this->params('gold');

            $user = \Users::findFirstById($user_id);
            if (isBlank($user)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
            }

            list($error_code, $error_reason, $order) = \Orders::createOrder($user, null, ['amount' => $amount, 'operator_id' => $this->currentOperator()->id]);

            if ($error_code != ERROR_CODE_SUCCESS) {
                return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
            }

            $payment_channel = \PaymentChannels::getManualRechargeChannel();

            if (isBlank($payment_channel)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '无可用的人工支付渠道');
            }

            $payment = \Payments::createPayment($user, $order, $payment_channel, ['diamond' => $diamond,
                'gold' => $gold, 'paid_amount' => $paid_amount]);

            if (isBlank($payment)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '创建支付失败');
            }

            //触发afterUpdate
            $payment->pay_status = PAYMENT_PAY_STATUS_SUCCESS;
            $payment->update();

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/account_histories?user_id=' . $user_id]);
        }
        $this->view->user_id = $user_id;
    }
}