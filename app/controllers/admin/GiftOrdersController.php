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
        $user_id = $this->params('user_id');
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

        if ($end_at - $start_at > 86400 * 7) {
            return $this->renderJSON(ERROR_CODE_FAIL, '时间跨度最大7天');
        }

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);

        if ($user_id) {

            $room_user = \Users::findFirstById($user_id);

            if ($room_user->room_id) {

                if (isset($cond['conditions'])) {
                    $cond['conditions'] .= ' and room_id =:room_id:';
                } else {
                    $cond['conditions'] = ' room_id =:room_id:';
                }

                $cond['bind']['room_id'] = $room_user->room_id;
            }

        }


        $gift_orders = \GiftOrders::findPagination($cond, $page, $per_page);
        $cond['column'] = 'amount';
        $cond['conditions'] .= ' and pay_type = :pay_type';
        $cond['bind']['bind'] = GIFT_PAY_TYPE_DIAMOND;
        $total_amount = \GiftOrders::sum($cond);

        $gift_order = $this->params('gift_order');
        debug($gift_order);

        $this->view->id = $gift_order['id_eq'];
        $this->view->sender_id = $gift_order['sender_id_eq'];
        $this->view->gift_id = $gift_order['gift_id_eq'];
        $this->view->room_id = $gift_order['room_id_eq'];

        $this->view->gift_orders = $gift_orders;
        $this->view->start_at = date("Y-m-d H:i:s", $start_at);
        $this->view->end_at = date("Y-m-d H:i:s", $end_at);
        $this->view->total_amount = $total_amount;
        $this->view->gift_type = intval($this->params('gift_order[gift_type_eq]'));
        $this->view->pay_type = $this->params('gift_order[pay_type_eq]');
        $this->view->total_amount = $total_amount;
        $this->view->user_id = $user_id ? intval($user_id) : '';
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