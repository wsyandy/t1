<?php

namespace partner;

class UnionsController extends BaseController
{
    function registerAction()
    {
        if ($this->request->isAjax()) {
            $mobile = $this->params('mobile');
            $password = $this->params('password');
            $auth_code = $this->params('auth_code');

            $union = \Unions::findFirstBy(['mobile' => $mobile, 'status' => STATUS_ON, 'type' => UNION_TYPE_PUBLIC]);

            if ($union) {
                return $this->renderJSON(ERROR_CODE_FAIL, '改手机号码已注册工会');
            }

            if (!isMobile($mobile)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '手机号码不正确');
            }

            if (isBlank($password)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '请设置密码');
            }

            if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
                return $this->renderJSON(ERROR_CODE_FAIL, '请设置6~16位的密码');
            }

            $sms_token = $this->session->get('sms_token');

            list($error_code, $error_reason) = \SmsHistories::checkAuthCode($this->currentProductChannel(), $mobile, $auth_code, $sms_token);

            if ($error_code != ERROR_CODE_SUCCESS) {
                return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
            }

            $opts = ['mobile' => $mobile, 'password' => $password];

            list($error_code, $error_reason) = \Unions::createPublicUnion($opts);

            return $this->renderJSON($error_code, $error_reason);
        }
    }

    function loginAction()
    {
        $mobile = $this->params('mobile');
        $password = $this->params('password');

        if (!$mobile || !$password) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $union = \Unions::findFirstBy(['mobile' => $mobile, 'status' => STATUS_ON, 'type' => UNION_TYPE_PUBLIC]);

        if (!$union) {
            return $this->renderJSON(ERROR_CODE_FAIL, '账户不存在');
        }

        if (md5($password) != $union->password) {
            return $this->renderJSON(ERROR_CODE_FAIL, '密码不正确');
        }

        $this->session->set('union_id', $union->id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function sendAuthAction()
    {
        if ($this->request->isAjax()) {

            $mobile = $this->params('mobile');
            $union = \Unions::findFirstBy(['mobile' => $mobile, 'status' => STATUS_ON, 'type' => UNION_TYPE_PUBLIC]);

            if ($union) {
                return $this->renderJSON(ERROR_CODE_FAIL, '改手机号码已注册工会');
            }

            list($error_code, $error_reason, $sms_token) = \SmsHistories::sendAuthCode($this->currentProductChannel(),
                $mobile, 'register');

            $this->session->set('sms_token', $sms_token);

            return $this->renderJSON($error_code, $error_reason);
        }
    }

    function updateAction()
    {

    }
}