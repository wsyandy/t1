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
        $rate = $user->rateOfHiCoinToMoney();
        $hi_coins = $user->hi_coins;
        $this->view->rate = $rate;
        $this->view->hi_coins = $user->getHiCoinText();
        $this->view->amount = $user->getWithdrawAmount();

        $is_height_version = false;
        if ($user->isIos()){
            $is_height_version = $user->version_code > $user->product_channel->apple_stable_version;
        }
        info($is_height_version);
        $this->view->is_height_version = $is_height_version;

        $this->view->user = $user;
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        $this->view->title = '我的收益';
    }

    function createAction()
    {
        if ($this->request->isAjax()) {

            return $this->renderJSON(ERROR_CODE_FAIL, '系统维护中');
            if (UNION_TYPE_PUBLIC == $this->currentUser()->union_type) {
                return $this->renderJSON(ERROR_CODE_FAIL, '公会成员禁止提现,请联系您的公会长');
            }

            $money = $this->params('money');
            $name = $this->params('name', null);
            $account = $this->params('account', null);

            if (isBlank($money) || !preg_match('/^\d+\d$/', $money) || $money < 50) {
                return $this->renderJSON(ERROR_CODE_FAIL, '请输入正确的提现金额');
            }

            $money = intval($money);

            if (!$name) {
                return $this->renderJSON(ERROR_CODE_FAIL, '姓名不能为空');
            }

            if (!$account) {
                return $this->renderJSON(ERROR_CODE_FAIL, '账户不能为空');
            }


            $opts = ['money' => $money, 'name' => $name, 'account' => $account];
            list($error_code, $error_reason) = \WithdrawHistories::createWithdrawHistories($this->currentUser(), $opts);

            return $this->renderJSON($error_code, $error_reason);
        }

    }

    function getMoneyAction()
    {
        $user = $this->currentUser();
        if ($this->request->isPost()) {
            if (\WithdrawHistories::hasWaitedHistoryByUser($user)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '一周只能提现一次哦');
            } else {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            }
        }
        $this->view->amount = $user->withdraw_amount;
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        $this->view->title = '我要提现';
    }

    function recordsAction()
    {
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');

        $user = $this->currentUser();

        $total_money = \WithdrawHistories::sum(
            [
                'conditions' => ' user_id = :user_id: and product_channel_id = :product_channel_id: and status = :status:',
                'bind' => ['product_channel_id' => $user->product_channel_id, 'user_id' => $user->id, 'status' => WITHDRAW_STATUS_SUCCESS],
                'order' => 'id desc',
                'column' => 'amount'
            ]
        );

        $this->view->total_money = $total_money;
        $this->view->title = '领取记录';
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