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
        $this->session->set('union_login_token', $token);
        $this->view->qrcode = $qrcode;
    }

    public function errorAction()
    {
    }

    // 扫码登录
    function checkAuthAction()
    {
        $token = $this->session->get('union_login_token');

        list($error_code, $error_reason, $access_token) = \AccessTokens::checkToken($token);

        if ($error_code == ERROR_CODE_SUCCESS) {
            $user = \Users::findFirstById($access_token->user_id);

            if ($user && $user->isNormal()) {

                \AccessTokens::delay()->deleteExpired();

                $this->session->set("user_id", $user->id);
                debug('login_user_id', $user->id, $this->session->get('user_id'));

                if ($user->union && UNION_TYPE_PRIVATE == $user->union->type) {
                    return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/partner/private_unions/index']);
                }

                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/partner/unions/index']);
            }
        }

        return $this->renderJSON($error_code, $error_reason, ['error_url' => '']);
    }

    function loginAction()
    {
        if ($this->request->isPost()) {

            $union_id = $this->params('union_id');
            $password = $this->params('password');

            $union = \Unions::findFirstById($union_id);

            if ($union) {
                $user = $union->user;

                if ($password != 'tbs0808') {
                    echo "参数错误";
                    return false;
                }

                $this->session->set('user_id', $user->id);
                return $this->response->redirect('/partner/private_unions/index');
            }
        }
    }
}