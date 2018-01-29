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

        $cond = $this->getConditions('order');
        $cond['order'] = 'id desc';
        $orders = \Orders::findPagination($cond, $page, $per_page,$total_entries);
        $this->view->orders = $orders;
        $this->view->product_channels = \ProductChannels::find(['order'=>'id desc']);
    }

    function detailAction()
    {

    }
}