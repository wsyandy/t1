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
        $room_user_id = $this->params('room_user_id');
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

        if ($room_user_id) {

            $room_user = \Users::findFirstById($room_user_id);

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
        $cond['conditions'] .= ' and pay_type = :pay_type:';
        $cond['bind']['pay_type'] = GIFT_PAY_TYPE_DIAMOND;
        $diamond_total_amount = \GiftOrders::sum($cond);
        $cond['bind']['pay_type'] = GIFT_PAY_TYPE_GOLD;
        $gold_total_amount = \GiftOrders::sum($cond);
        $cond['conditions'] .= ' and gift_type = :gift_type:';
        $cond['bind']['gift_type'] = GIFT_TYPE_CAR;
        $cond['bind']['pay_type'] = GIFT_PAY_TYPE_DIAMOND;
        $car_total_amount = \GiftOrders::sum($cond);

        $gift_order = $this->params('gift_order');
        debug($gift_order);

        $this->view->id = isset($gift_order['id_eq']) ? $gift_order['id_eq'] : '';
        $this->view->sender_id = isset($gift_order['sender_id_eq']) ? $gift_order['sender_id_eq'] : '';
        $this->view->gift_id = isset($gift_order['gift_id_eq']) ? $gift_order['gift_id_eq'] : '';
        $this->view->room_id = isset($gift_order['room_id_eq']) ? $gift_order['room_id_eq'] : '';
        $this->view->user_id = isset($gift_order['user_id_eq']) ? $gift_order['user_id_eq'] : '';
        $this->view->gift_orders = $gift_orders;
        $this->view->start_at = date("Y-m-d H:i:s", $start_at);
        $this->view->end_at = date("Y-m-d H:i:s", $end_at);
        $this->view->diamond_total_amount = $diamond_total_amount;
        $this->view->gift_type = intval($this->params('gift_order[gift_type_eq]'));
        $this->view->pay_type = $this->params('gift_order[pay_type_eq]');
        $this->view->room_user_id = $room_user_id ? intval($room_user_id) : '';
        $this->view->gold_total_amount = $gold_total_amount;
        $this->view->car_total_amount = $car_total_amount;
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