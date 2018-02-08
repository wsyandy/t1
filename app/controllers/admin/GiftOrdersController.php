<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 20:27
 */

namespace admin;

class GiftOrdersController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('gift_order');
        $conds['order'] = 'id desc';
        $page = 1;
        $per_page = 50;
        $total_entries = $page * $per_page;
        $gift_orders = \GiftOrders::findPagination($conds, $page, $total_entries);
        $this->view->gift_orders = $gift_orders;
    }

    function showAction()
    {
        $gift_orders = \GiftOrders::findByIds($this->params('id'));
        $this->view->gift_orders = $gift_orders;
    }
}