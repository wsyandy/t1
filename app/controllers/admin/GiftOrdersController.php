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

        $start_at = $this->params('start_at', date('Y-m-d'));
        $end_at = $this->params('end_at', date('Y-m-d'));
        if ($start_at) {
            $start_at = beginOfDay(strtotime($start_at));
            if (isset($cond['conditions'])) {
                $cond['conditions'] .= ' and created_at >=:start_at:';
            } else {
                $cond['conditions'] = ' created_at >=:start_at:';
            }
            $cond['bind']['start_at'] = $start_at;
        }
        if ($end_at) {
            $end_at = endOfDay(strtotime($end_at));
            if (isset($cond['conditions'])) {
                $cond['conditions'] .= ' and created_at <=:end_at:';
            } else {
                $cond['conditions'] = ' created_at <=:end_at:';
            }
            $cond['bind']['end_at'] = $end_at;
        }

        $page = 1;
        $per_page = 50;
        $total_entries = $page * $per_page;
        $gift_orders = \GiftOrders::findPagination($conds, $page,$per_page, $total_entries);
        $this->view->gift_orders = $gift_orders;

        $this->view->start_at = $this->params('start_at', null) ?? date('Y-m-d');
        $this->view->end_at = $this->params('end_at', null) ?? date('Y-m-d');
    }

    function showAction()
    {
        $gift_orders = \GiftOrders::findByIds($this->params('id'));
        $this->view->gift_orders = $gift_orders;
    }
}