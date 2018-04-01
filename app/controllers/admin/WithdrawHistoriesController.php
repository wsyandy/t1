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

        $start_at = $this->params('start_at');
        $end_at = $this->params('end_at');
        $user_id = $this->params('user_id');

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

        if ($user_id) {

            if (isset($cond['conditions'])) {
                $cond['conditions'] .= ' and user_id =:user_id:';
            } else {
                $cond['conditions'] = ' user_id =:user_id:';
            }

            $cond['bind']['user_id'] = $user_id;
        }


        debug($cond);

        $cond['order'] = 'id desc';

        $status = $this->params('withdraw_history[status_eq]');

        if ('' !== $status) {
            $status = intval($status);
        }

        $withdraw_histories = \WithdrawHistories::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->withdraw_histories = $withdraw_histories;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->start_at = $start_at ? date("Y-m-d", $start_at) : '';
        $this->view->end_at = $end_at ? date("Y-m-d", $end_at) : '';
        $this->view->user_name = $this->params('withdraw_history[user_name_eq]');
        $this->view->id = $this->params('withdraw_history[id_eq]');
        $this->view->status = $status;
        $this->view->user_id = $user_id;
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

        if ($withdraw_history->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['withdraw_history' => $withdraw_history->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, $withdraw_history->error_reason);
        }
    }

    function exportAction()
    {

        $start_at = $this->params('start_at', date('Y-m-d'));
        $end_at = $this->params('end_at', date('Y-m-d'));
        $status = $this->params('status', 0);

        $start_at_time = beginOfDay(strtotime($start_at));
        $end_at_time = endOfDay(strtotime($end_at));

        if ($end_at_time - $start_at_time > 60 * 60 * 24 * 32) {
            $this->renderJSON(ERROR_CODE_FAIL, '时间跨度最大一个月');
            return;
        }

        $cond = ['conditions' => ' created_at >= :start_at: and created_at <= :end_at: ',
            'bind' => ['start_at' => $start_at_time, 'end_at' => $end_at_time],
            'order' => 'id desc'
        ];

        $cond['conditions'] .= " and status = :status:";
        $cond['bind']['status'] = $status;

        $export_history = new \ExportHistories();
        $export_history->operator_id = $this->currentOperator()->id;
        $export_history->name = '提现统计';
        $export_history->table_name = 'WithdrawHistories';
        $export_history->conditions = json_encode($cond['bind'], JSON_UNESCAPED_UNICODE);
        $export_history->download_num = 1;
        $export_history->save();

        \WithdrawHistories::delay()->exportData($export_history->id, $cond);

        $this->response->redirect('/admin/export_histories/download?id=' . $export_history->id);

        $this->view->disable;
    }


    function basicAction()
    {
        $user_id = $this->params('user_id');
        $page = 1;
        $per_page = 100;
        $cond = ['conditions' => 'user_id = ' . $user_id];
        $withdraw_histories = \WithdrawHistories::findPagination($cond, $page, $per_page);
        $this->view->withdraw_histories = $withdraw_histories;
    }


}