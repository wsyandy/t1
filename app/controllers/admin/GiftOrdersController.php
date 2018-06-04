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
        $room_user_uid = $this->params('room_user_uid');
        $sender_uid = $this->params('sender_uid');
        $user_union_id = $this->params('user_union_id');
        $sender_union_id = $this->params('sender_union_id');
        $room_union_id = $this->params('room_union_id');
        $user_uid = $this->params('user_uid');
        $cond = $this->getConditions('gift_order');
        $cond['order'] = 'id desc';

        $start_at = $this->params('start_at', date('Y-m-d H:i:s', beginOfDay()));
        $end_at = $this->params('end_at', date('Y-m-d H:i:s', endOfDay()));
        $gift_order = $this->params('gift_order');
        $id = fetch($gift_order, 'id_eq');

        if (!isset($cond['conditions'])) {
            $cond['conditions'] = 'id > 0';
        }

        if (!$id) {

            if ($start_at) {
                $start_at = strtotime($start_at);
                $cond['conditions'] .= ' and created_at >=:start_at:';
                $cond['bind']['start_at'] = $start_at;
            }

            if ($end_at) {
                $end_at = strtotime($end_at);
                $cond['conditions'] .= ' and created_at <=:end_at:';
                $cond['bind']['end_at'] = $end_at;
            }

            if ($end_at - $start_at > 86400 * 31) {
                return $this->renderJSON(ERROR_CODE_FAIL, '时间跨度最大7天');
            }
        }

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);

        if ($room_user_uid) {

            $room_user = \Users::findFirstByUid($room_user_uid);

            if ($room_user->room_id) {
                $cond['conditions'] .= ' and room_id =:room_id:';
                $cond['bind']['room_id'] = $room_user->room_id;
            }

        }

        if ($sender_uid) {

            $sender = \Users::findFirstByUid($sender_uid);

            if ($sender) {
                $cond['conditions'] .= ' and sender_id =:sender_id:';
                $cond['bind']['sender_id'] = $sender->id;
            }

        }

        if ($user_uid) {

            $user = \Users::findFirstByUid($user_uid);

            if ($user) {
                $cond['conditions'] .= ' and user_id =:user_id:';
                $cond['bind']['user_id'] = $user->id;
            }

        }

        if ($sender_union_id) {
            $cond['conditions'] .= ' and sender_union_id =:sender_union_id:';
            $cond['bind']['sender_union_id'] = $sender_union_id;
        }

        if ($user_union_id) {
            $cond['conditions'] .= ' and receiver_union_id =:receiver_union_id:';
            $cond['bind']['receiver_union_id'] = $user_union_id;
        }

        if ($room_union_id) {
            $cond['conditions'] .= ' and room_union_id =:room_union_id:';
            $cond['bind']['room_union_id'] = $room_union_id;
        }

        $gift_orders = \GiftOrders::findPagination($cond, $page, $per_page);

        $diamond_total_amount = 0;
        $gold_total_amount = 0;
        $car_total_amount = 0;

        if ($user_uid || $sender_uid || $room_user_uid || $sender_union_id || $user_union_id || $room_union_id) {
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
        }

        $this->view->id = $id ? $id : '';
        $this->view->sender_uid = $sender_uid ? $sender_uid : '';
        $this->view->gift_id = isset($gift_order['gift_id_eq']) ? $gift_order['gift_id_eq'] : '';
        $this->view->room_id = isset($gift_order['room_id_eq']) ? $gift_order['room_id_eq'] : '';
        $this->view->user_uid = $user_uid ? $user_uid : '';
        $this->view->gift_orders = $gift_orders;
        $this->view->start_at = date("Y-m-d H:i:s", $start_at);
        $this->view->end_at = date("Y-m-d H:i:s", $end_at);
        $this->view->diamond_total_amount = $diamond_total_amount;
        $this->view->gift_type = intval($this->params('gift_order[gift_type_eq]'));
        $this->view->pay_type = $this->params('gift_order[pay_type_eq]');
        $this->view->room_user_uid = $room_user_uid ? intval($room_user_uid) : '';
        $this->view->gold_total_amount = $gold_total_amount;
        $this->view->car_total_amount = $car_total_amount;
        $this->view->user_union_id = $user_union_id;
        $this->view->sender_union_id = $sender_union_id;
        $this->view->room_union_id = $room_union_id;
    }

    function detailAction()
    {
        $user_id = $this->params('user_id');

        if ($user_id) {
            $cond['conditions'] = 'user_id = ' . $user_id;
        }

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);
        $cond['order'] = 'id desc';

        $gift_orders = \GiftOrders::findPagination($cond, $page, $per_page);

        $this->view->gift_orders = $gift_orders;
        $this->view->user_id = $user_id;
    }

    function showAction()
    {
        $gift_orders = \GiftOrders::findByIds($this->params('id'));
        $this->view->gift_orders = $gift_orders;
    }

    function giveCarAction()
    {
        $user = \Users::findFirstById($this->params('user_id'));
        $gifts = \Gifts::findValidList($user, ['gift_type' => GIFT_TYPE_CAR]);
        if ($this->request->isPost()) {
            $gift = \Gifts::findFirstById($this->params('gift_id'));

            if (isBlank($gift)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '礼物错误');
            }

            $content = $this->params('content');
            $expire_day = $this->params('expire_day');
            $operator = $this->currentOperator();
            $opts = ['content' => $content, 'operator_id' => $operator->id, 'expire_day' => $expire_day];
            \GiftOrders::giveCarBySystem($user, $gift, $opts);
            return $this->renderJSON(ERROR_CODE_SUCCESS, "赠送成功", ['error_url' => '/admin/gift_orders?user_id=' . $user->id]);
        }

        $this->view->user_id = $user->id;
        $this->view->gifts = $gifts;
    }
}