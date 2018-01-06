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
        $cond = array('order' => 'id desc');
        $page = 1;
        $per_page = 30;
        $cond_vars = array();
        $cond_values = array();
        foreach (['user_id', 'id'] as $item) {
            if (isPresent($this->params($item))) {
                $cond_vars[] = $item . ' = ' . ':' . $item . ':';
                $cond_values[$item] = $this->params($item);
            }
        }
        if (isPresent($cond_vars)) {
            $conditions = implode('and', $cond_vars);
            $cond = array(
                'conditions' => $conditions,
                'bind' => $cond_values,
                'order' => 'id desc'
            );
        }
        debug($cond);
        $orders = \Orders::findPagination($cond, $page, $per_page);
        $this->view->orders = $orders;
    }

    function detailAction()
    {

    }
}