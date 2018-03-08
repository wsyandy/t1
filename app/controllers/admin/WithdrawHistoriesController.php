<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/5
 * Time: 上午10:51
 */

namespace admin;

class WithdrawHistoriesController extends BaseController
{
    function indexAction()
    {

        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;
        $cond = $this->getConditions('withdraw_history');
        $cond['withdraw_history'] = 'id desc';

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

        $cond['order'] = 'id desc';

        $withdraw_histories = \WithdrawHistories::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->withdraw_histories = $withdraw_histories;
        $this->view->product_channels = \ProductChannels::find(['withdraw_historie' => 'id desc']);
        $this->view->start_at = $this->params('start_at', null) ?? date('Y-m-d');
        $this->view->end_at = $this->params('end_at', null) ?? date('Y-m-d');
    }

    function editAction()
    {
        $withdraw_historie_id = $this->params('id');
        $withdraw_historie = \WithdrawHistories::findFirstById($withdraw_historie_id);

        $this->view->withdraw_historie = $withdraw_historie;
    }

    function updateAction()
    {
        $withdraw_history_id = $this->params('id');
        $withdraw_history = \WithdrawHistories::findFirstById($withdraw_history_id);

        if (!$withdraw_history) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        if (WITHDRAW_STATUS_WAIT != $withdraw_history->status) {
            return $this->renderJSON(ERROR_CODE_FAIL, '只允许修改提现中状态的订单');
        }

        $this->assign($withdraw_history, 'withdraw_history');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $withdraw_history);

        if ($withdraw_history->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['withdraw_history' => $withdraw_history->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

}