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
        $conds = array('order' => 'id desc');
        $page = 1;
        $per_page = 30;
        $cond_vars = array();
        $cond_values = array();
        foreach (['order_id', 'id'] as $item) {
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

        $payments = \Payments::findPagination($conds, $page, $per_page);
        $this->view->payments = $payments;
    }

    function detailAction()
    {

    }
}