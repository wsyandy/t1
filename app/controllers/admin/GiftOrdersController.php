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
        $cond = $this->getConditions('gift_order');
        $cond['order'] = 'id desc';

        $start_at = $this->params('start_at', date('Y-m-d H:i:s', beginOfDay()));
        $end_at = $this->params('end_at', date('Y-m-d H:i:s', endOfDay()));
        if ($start_at) {
            $start_at = strtotime($start_at);
            if (isset($cond['conditions'])) {
                $cond['conditions'] .= ' and created_at >=:start_at:';
            } else {
                $cond['conditions'] = ' created_at >=:start_at:';
            }
            $cond['bind']['start_at'] = $start_at;
        }
        if ($end_at) {
            $end_at = strtotime($end_at);
            if (isset($cond['conditions'])) {
                $cond['conditions'] .= ' and created_at <=:end_at:';
            } else {
                $cond['conditions'] = ' created_at <=:end_at:';
            }
            $cond['bind']['end_at'] = $end_at;
        }

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);
        $gift_orders = \GiftOrders::findPagination($cond, $page, $per_page);
        $this->view->gift_orders = $gift_orders;

        $cond['column'] = 'amount';
        $total_amount = \GiftOrders::sum($cond);

        $this->view->start_at = date("Y-m-d H:i:s", $start_at);
        $this->view->end_at = date("Y-m-d H:i:s", $end_at);
        $this->view->total_amount = $total_amount;
        $room_id = $this->params('gift_order[room_id_eq]');
        $this->view->room_id = $room_id ? intval($room_id) : '';
    }

    function detailAction()
    {
        $user_id = $this->params('user_id');

        if ($user_id) {
            $cond['conditions'] = 'user_id = ' . $user_id;
        }

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);

        $gift_orders = \GiftOrders::findPagination($cond, $page, $per_page);

        $this->view->gift_orders = $gift_orders;
    }

    function showAction()
    {
        $gift_orders = \GiftOrders::findByIds($this->params('id'));
        $this->view->gift_orders = $gift_orders;
    }
}