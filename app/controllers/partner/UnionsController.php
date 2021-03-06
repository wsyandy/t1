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

        if ($union->needUpdateProfile()) {
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

            $union = $this->currentUser()->union;
            $union->updateProfile($params);

            $union->status = STATUS_ON;
            $union->auth_status = AUTH_WAIT;
            $union->update();
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/partner/unions/index']);
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

        $this->currentUser()->audience_time = $this->currentUser()->getAudienceTimeByDate($begin_at);
        $this->currentUser()->broadcaster_time = $this->currentUser()->getBroadcasterTimeByDate($begin_at);
        $this->currentUser()->host_broadcaster_time = $this->currentUser()->getHostBroadcasterTimeByDate($begin_at);

        if ($this->request->isAjax()) {

            $page = $this->params('page');
            $per_page = 6;

            $cond = [
                'conditions' => 'sender_union_id = :sender_union_id: and created_at >= :begin_at: and created_at <= :end_at:',
                'bind' => ['sender_union_id' => $union->id, 'begin_at' => $begin_at, 'end_at' => $end_at],
                'columns' => 'distinct sender_id'
            ];

            $cond2 = [
                'conditions' => 'receiver_union_id = :receiver_union_id:and created_at >= :begin_at: and created_at <= :end_at:',
                'bind' => ['receiver_union_id' => $union->id, 'begin_at' => $begin_at, 'end_at' => $end_at],
                'columns' => 'distinct user_id'
            ];

            $user_ids = [];
            $sender_gift_orders = \GiftOrders::find($cond);
            $user_gift_orders = \GiftOrders::find($cond2);

            foreach ($user_gift_orders as $gift_order) {
                $user_ids[] = $gift_order->user_id;
            }

            foreach ($sender_gift_orders as $gift_order) {
                $user_ids[] = $gift_order->sender_id;
            }

            $user_ids = array_unique($user_ids);

            if (count($user_ids) < 1) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['users' => []]);
            }

            $cond = [
                'conditions' => 'id <> :union_user_id: and id in (' . implode(',', $user_ids) . ')',
                'bind' => ['union_user_id' => $union->user_id]
            ];

            $users = \Users::findPagination($cond, $page, $per_page);

            foreach ($users as $user) {

                $union_history = \UnionHistories::findFirstBy([
                    'user_id' => $user->id, 'union_id' => $union->id
                ], 'id desc');

                $user->audience_time = $user->getAudienceTimeByDate($begin_at);
                $user->broadcaster_time = $user->getBroadcasterTimeByDate($begin_at);
                $user->host_broadcaster_time = $user->getHostBroadcasterTimeByDate($begin_at);

                if ($union_history->join_at && $union_history->join_at > $begin_at) {
                    $begin_at = $union_history->join_at;
                }

                if ($union_history->exit_at && $union_history->exit_at < $end_at) {
                    $end_at = $union_history->exit_at;
                }

                $user->income = $user->getDaysIncome($begin_at, $end_at);
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toUnionStatJson'));
        }

        $union_history = \UnionHistories::findFirstBy([
            'user_id' => $this->currentUser()->id, 'union_id' => $union->id
        ], 'id desc');

        if ($union_history->join_at && $union_history->join_at > $begin_at) {
            $begin_at = $union_history->join_at;
        }

        if ($union_history->exit_at && $union_history->exit_at < $end_at) {
            $end_at = $union_history->exit_at;
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

                $union_history = \UnionHistories::findFirstBy([
                    'user_id' => $room->user->id, 'union_id' => $union->id
                ], 'id desc');

                if ($union_history->join_at && $union_history->join_at > $begin_at) {
                    $begin_at = $union_history->join_at;
                }

                if ($union_history->exit_at && $union_history->exit_at < $end_at) {
                    $end_at = $union_history->exit_at;
                }

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

    function withdrawAction()
    {

        $amount = $this->params('amount');
        $alipay_account = $this->params('alipay_account');

        if (isBlank($amount) || !preg_match('/^\d+\d$/', $amount) || $amount < 1000) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请输入正确的提现金额');
        }

        $amount = intval($amount);

        if (!$alipay_account) {
            return $this->renderJSON(ERROR_CODE_FAIL, '支付宝账户不能为空');
        }

        $opts = ['amount' => $amount, 'alipay_account' => $alipay_account];
        $union = $this->currentUser()->union;

        if (!$union) {
            return $this->renderJSON(ERROR_CODE_FAIL, '提现失败,请联系官方人员');
        }

        list($error_code, $error_reason) = \WithdrawHistories::createUnionWithdrawHistories($union, $opts);

        return $this->renderJSON($error_code, $error_reason);
    }
}