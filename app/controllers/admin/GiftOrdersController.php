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
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $gift_orders = \GiftOrders::findOrderListByUser($this->params('user_id'), $page, $per_page);
        $this->view->gift_orders = $gift_orders;
    }

    function showAction()
    {
        $gift_orders = \GiftOrders::findByIds($this->params('id'));
        $this->view->gift_orders = $gift_orders;
    }
}