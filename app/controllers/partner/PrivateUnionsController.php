<?php

namespace partner;

class PrivateUnionsController extends BaseController
{
    function indexAction()
    {
        $union = $this->currentUser()->union;
    }

    public function logoutAction()
    {
        $this->session->set("user_id", null);
        $this->response->redirect("/partner/home");
    }

    function usersAction()
    {
        $union = $this->currentUser()->union;
        $start_at_time = $this->params('start_at_time', '');
        $end_at_time = $this->params('end_at_time', '');
        $start_at = date("Ymd", beginOfDay(strtotime($start_at_time)));
        $end_at = date("Ymd", beginOfDay(strtotime($end_at_time)));

        if ($this->request->isAjax()) {

            $page = $this->params('page');
            $per_page = 10;
            $user_db = \Users::getUserDb();

            if (!$start_at_time && !$end_at_time) {
                $key = 'union_user_total_wealth_rank_list_union_id_' . $union->id;
                $charm_key = 'union_user_total_charm_rank_list_union_id_' . $union->id;
                $hi_coin_key = 'union_user_total_hi_coins_rank_list_union_id_' . $union->id;
            } elseif ($start_at == $end_at) {
                $key = 'union_user_day_wealth_rank_list_' . $start_at . '_union_id_' . $union->id;
                $charm_key = 'union_user_day_charm_rank_list_' . $start_at . '_union_id_' . $union->id;
                $hi_coin_key = 'union_user_day_hi_coins_rank_list_' . $start_at . '_union_id_' . $union->id;
            } else {
                $month_start = date('Ymd', beginOfMonth(strtotime($start_at_time)));
                $month_end = date('Ymd', endOfMonth(strtotime($start_at_time)));
                $key = 'union_user_month_wealth_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
                $charm_key = 'union_user_month_charm_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
                $hi_coin_key = 'union_user_month_hi_coins_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
            }

            $users = \Users::findFieldRankListByKey($key, 'wealth', $page, $per_page);

            info("union_stat", $key, $charm_key, $hi_coin_key);

            foreach ($users as $user) {
                $user->charm = $user_db->zscore($charm_key, $user->id);
                $hi_coins = $user_db->zscore($hi_coin_key, $user->id);
                $hi_coins = sprintf("%0.2f", $hi_coins / 1000);
                $user->hi_coins = $hi_coins;
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toUnionJson'));
        }

        $this->view->start_at_time = $start_at_time;
        $this->view->end_at_time = $end_at_time;
    }

    function roomsAction()
    {
        $union = $this->currentUser()->union;

        $start_at_time = $this->params('start_at_time', '');
        $end_at_time = $this->params('end_at_time', '');

        $start_at = date("Ymd", beginOfDay(strtotime($start_at_time)));
        $end_at = date("Ymd", beginOfDay(strtotime($end_at_time)));

        $user_db = \Users::getUserDb();

        if (!$start_at_time && !$end_at_time) {
            $key = 'union_room_total_amount_union_id_' . $union->id;
        } elseif ($start_at == $end_at) {
            $key = 'union_room_day_amount_' . $start_at . '_union_id_' . $union->id;
        } else {
            $month_start = date('Ymd', beginOfMonth(strtotime($start_at_time)));
            $month_end = date('Ymd', endOfMonth(strtotime($start_at_time)));
            $key = 'union_room_month_amount_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
        }

        $room_ids = $user_db->zrange($key, 0, -1);
        $rooms = [];

        if ($room_ids) {

            $cond = [
                'conditions' => 'id in (' . implode(',', $room_ids) . ')',
            ];

            $rooms = \Rooms::find($cond);
        }

        $total_amount = 0;

        foreach ($rooms as $room) {
            $room->amount = $user_db->zscore($key, $room->id);
            $total_amount += $room->amount;
        }

        $this->view->rooms = $rooms;
        $this->view->start_at_time = $start_at_time;
        $this->view->end_at_time = $end_at_time;
        $this->view->total_amount = $total_amount;
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