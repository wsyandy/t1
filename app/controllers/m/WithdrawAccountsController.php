<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/11
 * Time: 下午4:15
 */

namespace m;

class WithdrawAccountsController extends BaseController
{
    function indexAction()
    {
        $current_user = $this->currentUser();

        $withdraw_accounts = \WithdrawAccounts::find([
            'conditions' => "status = " . STATUS_ON . " and user_id = " . $current_user->id,
            'order' => 'id desc'
        ]);

        $withdraw_accounts_json = [];
        foreach ($withdraw_accounts as $withdraw_account) {
            $withdraw_accounts_json[] = $withdraw_account->toJson();
        }

        $this->view->withdraw_accounts = json_encode($withdraw_accounts_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        $this->view->title = "银行卡管理";
    }

    function addMobileAction()
    {
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        $this->view->title = "添加银行卡";
    }

    function sendAuthAction()
    {
        $mobile = $this->params('mobile');
        $context = $this->context();
        $context['user_id'] = $this->currentUser()->id;

        list($error_code, $error_reason, $sms_token) = \SmsHistories::sendAuthCode($this->currentProductChannel(),
            $mobile, 'login', $context);

        return $this->renderJSON($error_code, $error_reason, ['sms_token' => $sms_token]);
    }

    function createAction()
    {
        $auth_code = $this->params('auth_code');
        $sms_token = $this->params('sms_token');
        $mobile = $this->params('mobile');

        $sid = $this->params('sid');
        $code = $this->params('code');

        // 测试白名单
        $is_white_mobile = false;
        if ($mobile && in_array($mobile, ['13912345678'])
        ) {
            $is_white_mobile = true;
        }
        $context = $this->context();

        if ($auth_code) {
            $context['is_white_mobile'] = $is_white_mobile;

            list($error_code, $error_reason) = \SmsHistories::checkAuthCode($this->currentProductChannel(), $mobile,
                $auth_code, $sms_token, $context);

        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '验证码错误');
        }

        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }


        $withdraw_account = \WithdrawAccounts::createWithdrawAccount($this->currentUser(), $mobile);

        $error_url = "/m/withdraw_accounts/add_bank_card?sid=" . $sid . "&code=" . $code . "&id=" . $withdraw_account;

        return $this->renderJSON($error_code, $error_reason, ['error_url' => $error_url]);
    }

    function addBankCardAction()
    {
        $banks = \AccountBanks::find(['conditions' => "status = " . STATUS_ON, 'order' => 'rank desc']);
        $banks_json = [];

        foreach ($banks as $bank) {
            $banks_json[] = ['text' => $bank->name, 'value' => $bank->id];
        }

        $provinces = \Provinces::find();
        foreach ($provinces as $province) {
            $provinces_json[] = ['text' => $province->name, 'value' => $province->id];
        }

        $cities = \Cities::find();
        foreach ($cities as $city) {
            $cities_json[] = ['text' => $city->name, 'value' => $city->id];
        }


        $this->view->banks = json_encode($banks_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $this->view->provinces = json_encode($provinces_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $this->view->cities = json_encode($cities_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        $this->view->id = $this->params('id');
        $this->view->title = "添加银行卡";
    }

    function updateAction()
    {
        $id = $this->params('id');
        $withdraw_account = \WithdrawAccounts::findFirstById($id);
        if (isBlank($withdraw_account)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $account = $this->params('account');
        $account_withdraw_account_id = intval($this->params('account_withdraw_account_id'));
        $type = intval($this->params('type', 2));

        //校验银行卡

        if (!preg_match('/^\d+\d$/', $account) && $type == 2) {
            return $this->renderJSON(ERROR_CODE_FAIL, '银行卡号必须是数字');
        }

        $opts = ['account' => $account, 'account_withdraw_account_id' => $account_withdraw_account_id, 'type' => $type];

        $withdraw_account->updateProfile($opts);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function unbindAction()
    {
        $id = $this->params('id');
        $withdraw_account = \WithdrawAccounts::findFirstById($id);
        if (isBlank($withdraw_account)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        if ($withdraw_account->unbind($this->currentUser())) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '解绑失败');
        }
    }

    function getCitiesAction()
    {
        $province_id = $this->params('province_id');
        $cond = ['conditions' => 'province_id=:province_id:',
            'bind' => ['province_id' => $province_id], 'order' => 'id asc'];
        $cities = \Cities::find($cond);
        $cities_json = [];
        info($cities);
        foreach ($cities as $city) {
            $cities_json[] = ['text' => $city->name, 'value' => $city->id];
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['cities' => $cities_json]);

    }
}