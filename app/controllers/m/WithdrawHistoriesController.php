<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/6
 * Time: 上午11:56
 */
namespace m;

class WithdrawHistoriesController extends BaseController
{
    function indexAction()
    {
        $user = $this->currentUser();
        $hi_coins = $user->hi_coins;
        $this->view->hi_coins = $hi_coins;
        $this->view->amount = $hi_coins / 10;
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
    }

    function createAction()
    {
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        if ($this->request->isAjax()) {
            $money = floatval($this->params('money'));
            $name = $this->params('name', null);
            $account = $this->params('account', null);
            debug($money);
            if (!$money || $money < 0) {
                return $this->renderJSON(ERROR_CODE_FAIL, '请输入正确的提现金额');
            }

            if (!$name) {
                return $this->renderJSON(ERROR_CODE_FAIL, '姓名不能为空');
            }

            if (!$account) {
                return $this->renderJSON(ERROR_CODE_FAIL, '账户不能为空');
            }


            $opts = ['money' => $money, 'name' => $name, 'account' => $account];
            list($error_code, $error_reason) = \WithdrawHistories::createWithdrawHistories($this->currentUser(), $opts);

            $this->renderJSON($error_code, $error_reason);
        }

    }

    function getMoneyAction()
    {
        $user = $this->currentUser();
        $hi_coins = $user->hi_coins;
        $this->view->amount = $hi_coins / 10;
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
    }

    function recordsAction()
    {
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');

        $user = $this->currentUser();

        $withdraw_histories = \WithdrawHistories::find(
            [
                'conditions' => ' id != :id: and product_channel_id = :product_channel_id: and status = :status:',
                'bind' => ['product_channel_id' => $user->product_channel_id, 'id' => $user->id, 'status' => WITHDRAW_STATUS_SUCCESS],
                'order' => 'id desc',
                'column' => 'amount'
            ]
        );

        $total_money = 0;
        foreach ($withdraw_histories as $history) {
            $total_money = $total_money + $history->amount;
        }

        $flag = 0;

        if (count($withdraw_histories)) {
            $flag = 1;
        }

        $this->view->flag = $flag;
        $this->view->total_money = $total_money;
    }

    function listAction()
    {
        if ($this->request->isAjax()) {
            $page = $this->params('page', 1);
            $per_page = $this->params('per_page', 10);

            $withdraw_histories = \WithdrawHistories::search($this->currentUser(), $page, $per_page);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',
                $withdraw_histories->toJson('withdraw_histories', 'toSimpleJson'));
        }
    }
}