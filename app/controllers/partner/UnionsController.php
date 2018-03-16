<?php

namespace partner;

class UnionsController extends BaseController
{
    function indexAction()
    {
        $union = $this->currentUser()->union;

        if (!$union) {
            list($error_code, $error_reason, $union) = \Unions::createPublicUnion($this->currentUser());

            if (ERROR_CODE_SUCCESS != $error_code) {
                echo "登录失败";
                return false;
            }
        }

        if ($union->needUpdateProfile() || STATUS_PROGRESS == $union->status) {
            $forward = [
                "namespace" => "partner",
                "controller" => "unions",
                "action" => "update",
                "params" => $this->params()
            ];
            $this->dispatcher->forward($forward);
        }
    }

    function updateAction()
    {
        if ($this->request->isAjax()) {
            $name = $this->params('name');
            $id_name = $this->params('id_name');
            $id_no = $this->params('id_no');
            $alipay_account = $this->params('alipay_account');

            if (isBlank($name) || isBlank($id_name) || isBlank($id_no) || isBlank($alipay_account)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            if (!checkIdCard($id_no)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '身份证号码错误');
            }

            $params = ['name' => $name, 'id_name' => $id_name, 'id_no' => $id_no, 'alipay_account' => $alipay_account];

            $this->currentUser()->union->updateProfile($params);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/partner/unions']);
        }
    }

    public function logoutAction()
    {
        $this->session->set("user_id", null);
        $this->response->redirect("/partner/home");
    }

    function usersAction()
    {
        $union = $this->currentUser()->union;
        $stat_at = $this->params('stat_at', date("Y-m-d"));
        $begin_at = beginOfDay(strtotime($stat_at));
        $end_at = endOfDay(strtotime($stat_at));

        if ($this->request->isAjax()) {

            $page = $this->params('page');
            $per_page = 6;

            $cond = [
                'conditions' => '(sender_union_id = :sender_union_id: or receiver_union_id = :receiver_union_id:)' .
                    ' and created_at >= :begin_at: and created_at <= :end_at:',
                'bind' => ['sender_union_id' => $union->id, 'receiver_union_id' => $union->id, 'begin_at' => $begin_at, 'end_at' => $end_at],
                'columns' => 'distinct user_id'
            ];

            $user_ids = [];
            $gift_orders = \GiftOrders::find($cond);

            foreach ($gift_orders as $gift_order) {
                $user_ids[] = $gift_order->user_id;
            }

            if (count($user_ids) < 1) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['users' => []]);
            }

            $cond = [
                'conditions' => 'id <> :union_user_id: and id in (' . implode(',', $user_ids) . ')',
                'bind' => ['union_user_id' => $union->user_id]
            ];

            $users = \Users::findPagination($cond, $page, $per_page);

            foreach ($users as $user) {
                $user->income = $user->getDaysIncome($begin_at, $end_at);
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toUnionStatJson'));
        }

        $this->currentUser()->income = $this->currentUser()->getDaysIncome($begin_at, $end_at);
        $this->view->stat_at = $stat_at;
    }

    function roomsAction()
    {
        $union = $this->currentUser()->union;
        $stat_at = $this->params('stat_at', date("Y-m-d"));
        $begin_at = beginOfDay(strtotime($stat_at));
        $end_at = endOfDay(strtotime($stat_at));

        $cond = [
            'conditions' => 'room_union_id = :room_union_id: and created_at >= :begin_at: and created_at <= :end_at:',
            'bind' => ['room_union_id' => $union->id, 'begin_at' => $begin_at, 'end_at' => $end_at],
            'columns' => 'distinct room_id'
        ];

        $room_ids = [];
        $gift_orders = \GiftOrders::find($cond);

        foreach ($gift_orders as $gift_order) {
            $room_ids[] = $gift_order->room_id;
        }

        $total_amount = 0;

        if (count($room_ids) < 1) {
            $rooms = [];
        } else {

            $cond = [
                'conditions' => 'id in (' . implode(',', $room_ids) . ')',
            ];

            $rooms = \Rooms::find($cond);

            foreach ($rooms as $room) {
                $room->amount = $room->getDayAmount($begin_at, $end_at);
                $total_amount += $room->amount;
            }

        }

        $this->view->rooms = $rooms;
        $this->view->total_amount = $total_amount;
        $this->view->stat_at = $stat_at;
    }

    function incomeDetailsAction()
    {

    }

    function withdrawHistoriesAction()
    {
        $union = $this->currentUser()->union;

        if ($this->request->isAjax()) {
            $page = $this->params('page');
            $per_page = 15;

            $cond = ['conditions' => 'union_id = :union_id:', 'bind' => ['union_id' => $union->id], 'order' => 'id desc'];

            $withdraw_histories = \WithdrawHistories::findPagination($cond, $page, $per_page);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $withdraw_histories->toJson('withdraw_histories', 'toSimpleJson'));
        }
    }

    function authWaitAction()
    {

    }
}