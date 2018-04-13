<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/19
 * Time: 下午8:33
 */

namespace m;

class IdCardAuthsController extends BaseController
{
    function indexAction()
    {
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
        $this->view->current_user = $this->currentUser();
        $this->view->title = "主持认证";
        $id_auth_auth = \IdCardAuths::findFirstByUserId($this->currentUser()->id);
        $this->view->id_auth_auth = $id_auth_auth;

        $banks = \AccountBanks::find(['conditions' => "status = " . STATUS_ON, 'order' => 'rank desc']);
        $banks_json = [];

        foreach ($banks as $bank) {
//            $banks_json[] = $bank;
            $banks_json[] = ['text' => $bank->name, 'value' => $bank->id];
        }

        $this->view->banks = json_encode($banks_json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $file_name = $this->params('code') . '_compere_auth';
        $file_path = APP_ROOT . 'app/views/m/id_card_auths/' . $file_name . '.volt';
        if (file_exists($file_path)) {
            $this->pick('m/id_card_auths/' . $file_name);
            return;
        }
    }

    function createAction()
    {
        $id_name = $this->params('id_name');
        $id_no = $this->params('id_no');
        $mobile = $this->params('mobile');
        $bank_account = $this->params('bank_account');

        if (!$id_no || !$id_name || !$mobile) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请填写正确的信息');
        }

        if (!checkIdCard($id_no)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '身份证号码错误');
        }

        if (!isMobile($mobile)) {
            return $this->renderJSON(ERROR_CODE_FAIL, "手机号错误");
        }

        if (!\IdCardAuths::checkBankAccount($bank_account)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '银行卡错误');
        }

        $opts = ['id_name' => $id_name, 'id_no' => $id_no, 'mobile' => $mobile, 'bank_account' => $bank_account];
        list($error_code, $error_reason) = \IdCardAuths::createIdCardAuth($this->currentUser(), $opts);

        return $this->renderJSON($error_code, $error_reason);
    }

    function agreementAction()
    {
        $product_channel = $this->currentProductChannel();
        $this->view->product_channel = $product_channel;
    }
}