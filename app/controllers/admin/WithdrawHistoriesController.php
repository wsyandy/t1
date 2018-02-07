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
        $cond['order'] = 'id desc';
        $withdraw_histories = \WithdrawHistories::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->withdraw_histories = $withdraw_histories;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function editAction()
    {
        $withdraw_historie_id = $this->params('id');
        $withdraw_historie = \WithdrawHistories::findFirstById($withdraw_historie_id);

        $this->view->withdraw_historie = $withdraw_historie;
    }

    function updateAction()
    {
        $withdraw_historie_id = $this->params('id');
        $withdraw_historie = \WithdrawHistories::findFirstById($withdraw_historie_id);
        if (WITHDRAW_STATUS_WAIT != $withdraw_historie->status) {
            return $this->renderJSON(ERROR_CODE_FAIL, '只允许修改提现中状态的订单');
        }
        $this->assign($withdraw_historie, 'withdraw_history');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $withdraw_historie);
        if ($withdraw_historie->save()) {
            if (WITHDRAW_STATUS_SUCCESS == $withdraw_historie->status) {
                $user = \Users::findFirstById($withdraw_historie->user_id);
                $user->hi_coins = $user->hi_coins - $withdraw_historie->amount * 10;
                $user->save();
            }
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('withdraw_history' => $withdraw_historie->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

}