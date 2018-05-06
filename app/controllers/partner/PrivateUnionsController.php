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
        $start_at_time = $this->params('start_at_time', date("Y-m-d", beginOfMonth(beginOfMonth() - 3600)));
        $end_at_time = $this->params('end_at_time', date("Y-m-d", endOfMonth(beginOfMonth() - 3600)));
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

            $users = \Users::findFieldRankListByKey($charm_key, 'charm', $page, $per_page, $user_db->zcard($charm_key));

            info("union_stat", $key, $charm_key, $hi_coin_key);

            foreach ($users as $user) {
                $user->wealth = $user_db->zscore($key, $user->id);
                $hi_coins = $user_db->zscore($hi_coin_key, $user->id);
                $hi_coins = sprintf("%0.2f", $hi_coins / 1000);
                $user->hi_coins = $hi_coins;
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toUnionJson'));
        }

        $cond = [
            'conditions' => 'union_id = :union_id: and fee_type = :fee_type:',
            'bind' => ['union_id' => $union->id, 'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT],
            'column' => 'hi_coins'
        ];

        if ($start_at_time) {
            $cond['conditions'] .= " and created_at >= :start:";
            $cond['bind']['start'] = beginOfDay(strtotime($start_at_time));
        }

        if ($end_at_time) {
            $cond['conditions'] .= " and created_at <= :end:";
            $cond['bind']['end'] = endOfDay(strtotime($end_at_time));
        }

        $total_hi_coins = \HiCoinHistories::sum($cond);
        $this->view->start_at_time = $start_at_time;
        $this->view->end_at_time = $end_at_time;
        $this->view->total_hi_coins = sprintf("%0.2f", $total_hi_coins);
    }

    function roomsAction()
    {
        $union = $this->currentUser()->union;

        $start_at_time = $this->params('start_at_time', date("Y-m-d", beginOfMonth(beginOfMonth() - 3600)));
        $end_at_time = $this->params('end_at_time', date("Y-m-d", endOfMonth(beginOfMonth() - 3600)));

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
        $data = [];

        if ($room_ids) {

            $cond = [
                'conditions' => 'id in (' . implode(',', $room_ids) . ')',
            ];

            $rooms = \Rooms::find($cond);
        }

        $total_amount = 0;

        if ($union->room_ids) {

            $room_ids = explode(',', $union->room_ids);

            foreach ($rooms as $room) {

                if (!in_array($room->id, $room_ids)) {
                    continue;
                }

                $room->amount = $user_db->zscore($key, $room->id);
                $data[] = $room;
                $total_amount += $room->amount;
            }
        }


        usort($data, function ($a, $b) {

            if ($a->amount == $b->amount) {
                return 0;
            }

            return $a->amount > $b->amount ? -1 : 1;
        });

        $this->view->rooms = $data;
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