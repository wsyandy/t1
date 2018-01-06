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
        if (isPresent($this->params('user_id'))) {
            $cond = array(
                "conditions" => "user_id = :user_id:",
                "bind" => array(
                    "user_id" => $this->params('user_id')
                ),
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