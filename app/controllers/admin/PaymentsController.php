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
            return $this->renderJSON(ERROR_CODE_FAIL, '线上不支持修改');
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
}