<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/5
 * Time: 上午10:52
 */

namespace admin;

class PaymentsController extends BaseController
{
    function indexAction()
    {

        $page = 1;
        $per_page = 60;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $conds = array('order' => 'id desc');
        $cond_vars = array();
        $cond_values = array();
        foreach (['order_id', 'id', 'user_id'] as $item) {
            if (isPresent($this->params($item))) {
                $cond_vars[] = $item . ' = ' . ':' . $item . ':';
                $cond_values[$item] = $this->params($item);
            }
        }
        if (isPresent($cond_vars)) {
            $conditions = implode('and', $cond_vars);
            $conds = array(
                'conditions' => $conditions,
                'bind' => $cond_values,
                'order' => 'id desc'
            );
        }

        $payments = \Payments::findPagination($conds, $page, $per_page, $total_entries);
        $this->view->payments = $payments;
    }

    function payStatusAction()
    {
        $payment = \Payments::findById($this->params('id'));
        $this->view->payment = $payment;
    }

    function updateAction()
    {
        if (isProduction()) {
            if (!$this->currentOperator()->isSuperOperator()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '线上不支持修改');
            }
        }

        $payment = \Payments::findById($this->params('id'));
        if ($payment) {
            $this->assign($payment, 'payment');
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $payment);
            if ($payment->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('payment' => $payment->toJson()));
            }
        }
        return $this->response->redirect('/admin/payemnts');
    }

    function detailAction()
    {

    }

    function dayStatAction()
    {

        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $stat_at = strtotime($stat_at);
        $start_at = beginOfDay($stat_at);
        $end_at = endOfDay($stat_at);

        $stats = [];
        $total_amount = 0;
        $payment_types = \PaymentChannels::$PAYMENT_TYPE;
        foreach ($payment_types as $payment_type) {
            $amount = \Payments::sum([
                'conditions' => 'pay_status = :pay_status: and payment_type = :payment_type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['pay_status' => PAYMENT_PAY_STATUS_SUCCESS, 'payment_type' => $payment_type, 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'amount'
            ]);

            if ($amount > 0) {
                $stats[$payment_type] = $amount;
                $total_amount += $amount;
            }
        }

        $stats['total'] = $total_amount;

        $this->view->stats = $stats;
        $this->view->stat_at = date('Y-m-d', $stat_at);
        $this->view->payment_types = $payment_types;
    }

}