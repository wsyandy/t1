<?php

namespace web;

class HomeController extends BaseController
{
    public function indexAction()
    {
        $user = $this->currentUser();
        if (!$user) {
            $product_channel = \ProductChannels::findLast();
        } else {
            $product_channel = $user->product_channel;
        }

        $this->view->product_channel = $product_channel;
    }

    public function errorAction()
    {
    }

    public function logoutAction()
    {
        $this->session->set("user_id", null);
        $this->response->redirect("/web/home/login");
    }

    // 扫码登录
    public function loginAction()
    {
        $token = \AccessTokens::generateToken();
        $url = $this->getRoot() . 'api/users/qrcode_login?token=' . $token . '&ts=' . time();
        $qrcode = generateQrcode($url);

        $this->session->set("user_id", null);
        $this->session->set('token', $token);
        $this->view->qrcode = $qrcode;
    }

    // 扫码登录
    function checkAuthAction()
    {
        $token = $this->session->get('token');
        list($error_code, $error_reason, $access_token) = \AccessTokens::checkToken($token);
        debug($token);
        if ($error_code == ERROR_CODE_SUCCESS) {
            $user = \Users::findFirstById($access_token->user_id);
            if ($user && $user->isNormal()) {

                \AccessTokens::delay()->deleteExpired();

                $this->session->set("user_id", $user->id);
                $user_login_at = md5(date("Ymd"));
                if (isDevelopmentEnv()) {
                    $user_login_at = md5(date("Ymdh"));
                }
                $this->session->set("user_login_at", $user_login_at);

                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/web/users']);
            }
        }
        return $this->renderJSON($error_code, $error_reason, ['error_url' => '']);
    }

}