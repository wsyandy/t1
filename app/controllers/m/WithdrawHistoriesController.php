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
        $rate = \HiCoinHistories::rateOfHiCoinToCny();
        $code = $this->currentProductChannel()->code;

        $is_height_version = false;
        if ($user->isIos()) {
            $is_height_version = $user->version_code > $user->product_channel->apple_stable_version;
        }

        $show_withdraw = true;

        $this->view->is_height_version = $is_height_version;
        $this->view->user = $user;
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        $this->view->title = '我的收益';
        $this->view->show_exchange = true;
        $this->view->show_withdraw = $show_withdraw;
        $this->view->rate = $rate;
        $this->view->hi_coins = $user->getHiCoinText();
        $this->view->amount = $user->getWithdrawAmount();

        $file_name = $code . '_index';
        $file_path = APP_ROOT . 'app/views/m/withdraw_histories/' . $file_name . '.volt';


        if (file_exists($file_path)) {
            $this->pick('m/withdraw_histories/' . $file_name);
            return;
        }
    }

    function createAction()
    {
        if ($this->request->isAjax()) {

//            if (isProduction()) {
//                return $this->renderJSON(ERROR_CODE_FAIL, '系统维护中');
//            }

            if (UNION_TYPE_PUBLIC == $this->currentUser()->union_type) {
                return $this->renderJSON(ERROR_CODE_FAIL, '公会成员禁止提现,请联系您的公会长');
            }

            $amount = $this->params('amount');
            $withdraw_account_id = $this->params('withdraw_account_id');

            if (isBlank($amount) || !preg_match('/^\d+\d$/', $amount) || $amount < 50) {
                return $this->renderJSON(ERROR_CODE_FAIL, '请输入正确的提现金额');
            }

            $white_user_ids = [153717];

            if (in_array($this->currentUser()->uid, $white_user_ids)) {
                if ($amount > 40000) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '超出单次体现金额');
                }
            } elseif ($amount > 20000) {
                return $this->renderJSON(ERROR_CODE_FAIL, '单次限额20000元');
            }

            if (isBlank($withdraw_account_id)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '账户不能为空');
            }

            $amount = intval($amount);

            $opts = ['amount' => $amount, 'withdraw_account_id' => $withdraw_account_id];
            list($error_code, $error_reason) = \WithdrawHistories::createWithdrawHistory($this->currentUser(), $opts);

            return $this->renderJSON($error_code, $error_reason);
        }

    }

    //已废弃
    function getMoneyAction()
    {
        return;
        $user = $this->currentUser();
        if ($this->request->isPost()) {
            $wait_withdraw_history = \WithdrawHistories::waitWithdrawHistory($user);

            if ($wait_withdraw_history) {

                if (WITHDRAW_STATUS_WAIT == $wait_withdraw_history->status) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '您有一笔正在提现的订单,请勿重复提现');
                }

                return $this->renderJSON(ERROR_CODE_FAIL, '一周只能提现一次哦');
            } else {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            }
        }

        $last_withdraw_history = \WithdrawHistories::findLastWithdrawHistory($user->id);

        $this->view->amount = $user->getWithdrawAmount();
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        $this->view->title = '我要提现';
        $this->view->user_name = $last_withdraw_history ? $last_withdraw_history->user_name : '';
        $this->view->alipay_account = $last_withdraw_history ? $last_withdraw_history->alipay_account : '';
    }

    function withdrawAction()
    {
        $user = $this->currentUser();
        $amount = $user->getWithdrawAmount();
        $code = $this->params('code');
        if ($this->request->isPost()) {
            if ($amount <= 0) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您还没有可提现的额度哟');
            }
            $wait_withdraw_history = \WithdrawHistories::waitWithdrawHistory($user);

            if ($wait_withdraw_history) {

                if (WITHDRAW_STATUS_WAIT == $wait_withdraw_history->status) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '您有一笔正在提现的订单,请勿重复提现');
                }
                return $this->renderJSON(ERROR_CODE_FAIL, '一周只能提现一次哦');
            } else {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            }
        }

        $coin_type_text = 'Hi币';
        if ($code == 'ruanyuyin') {
            $coin_type_text = 'R币';
        }

        $withdraw_account = \WithdrawAccounts::getDefaultWithdrawAccount($user);

        $this->view->amount = $amount;
        $this->view->code = $code;
        $this->view->sid = $this->params('sid');
        $this->view->title = '我要提现';
        $this->view->withdraw_account = $withdraw_account;
        $this->view->coin_type_text = $coin_type_text;
    }

    function recordsAction()
    {
        $code = $this->params('code');
        $this->view->code = $code;
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

        $file_name = $code . '_records';
        $file_path = APP_ROOT . 'app/views/m/withdraw_histories/' . $file_name . '.volt';
        if (file_exists($file_path)) {
            $this->pick('m/withdraw_histories/' . $file_name);
            return;
        }
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