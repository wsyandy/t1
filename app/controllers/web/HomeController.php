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

        $ios_apk = \SoftVersions::findFirstById(2);

        $android_apk = \SoftVersions::findFirst(1);
        $this->view->ios_url = $ios_apk->ios_down_url;
        $this->view->android_url = $android_apk->weixin_url;
        $this->view->product_channel = $product_channel;
    }

    public function errorAction()
    {
    }

    public function logoutAction()
    {
        $this->session->set("user_id", null);
        $this->session->set("user_login_time", null);
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
                $user_login_time = md5(date("Ymd"));
                if (isDevelopmentEnv()) {
                    $user_login_time = md5(date("Ymdh"));
                }
                $this->session->set("user_login_time", $user_login_time);

                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/web/users']);
            }
        }
        return $this->renderJSON($error_code, $error_reason, ['error_url' => '']);
    }

    function simulatorApkAction()
    {

        if ($this->request->isAjax()) {

            $soft = \SoftVersions::findFirstById(9);
            $file_url = '';

            if ($soft) {
                $file_url = $soft->file_url;
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => $file_url]);
        }
    }
}