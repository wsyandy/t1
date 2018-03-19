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

        $id_auth_auth = \IdCardAuths::findFirstByUserId($this->currentUser()->id);
        $this->view->id_auth_auth = $id_auth_auth;
    }

    function createAction()
    {
        $id_name = $this->params('id_name');
        $id_no = $this->params('id_no');
        $mobile = $this->params('mobile');
        $bank_account = $this->params('bank_account');

        if (!$id_no || !$id_name || !$mobile || !$bank_account) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请填写正确的信息');
        }

        if (!checkIdCard($id_no)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '身份证号码错误');
        }

        $opts = ['id_name' => $id_name, 'id_no' => $id_no, 'mobile' => $mobile, 'bank_account' => $bank_account];
        list($error_code, $error_reason) = \IdCardAuths::createIdCardAuth($this->currentUser(), $opts);

        return $this->renderJSON($error_code, $error_reason);
    }
}