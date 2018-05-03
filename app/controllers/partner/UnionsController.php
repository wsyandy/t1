<?php

namespace partner;

class UnionsController extends BaseController
{
    function indexAction()
    {
        $union = $this->currentUser()->union;
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

        if ($this->request->isAjax()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['users' => '']);
        }
    }

    function roomsAction()
    {
        $union = $this->currentUser()->union;
        $stat_at = $this->params('stat_at', date("Y-m-d"));
        $begin_at = beginOfDay(strtotime($stat_at));
        $end_at = endOfDay(strtotime($stat_at));
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