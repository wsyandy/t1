<?php

namespace partner;

class HomeController extends BaseController
{

    public function indexAction()
    {
        $token = \AccessTokens::generateToken();
        $url = $this->getRoot() . 'api/users/qrcode_login?token=' . $token . '&ts=' . time();
        $qrcode = generateQrcode($url);

        $this->session->set("user_id", null);
        $this->session->set('login_token', $token);
        $this->view->qrcode = $qrcode;
    }

    public function errorAction()
    {
    }

    public function logoutAction()
    {
        $this->session->set("user_id", null);
        $this->response->redirect("/partner/home");
    }

    // 扫码登录
    function checkAuthAction()
    {
        $token = $this->session->get('login_token');

        list($error_code, $error_reason, $access_token) = \AccessTokens::checkToken($token);

        if ($error_code == ERROR_CODE_SUCCESS) {
            $user = \Users::findFirstById($access_token->user_id);

            if ($user && $user->isNormal()) {

                \AccessTokens::delay()->deleteExpired();

                $this->session->set("user_id", $user->id);

                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/partner/unions']);
            }
        }

        return $this->renderJSON($error_code, $error_reason, ['error_url' => '']);
    }
}