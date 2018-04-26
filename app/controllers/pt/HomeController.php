<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2016/12/10
 * Time: 下午4:53
 */

namespace pt;


class HomeController extends \ApplicationController
{

    function indexAction()
    {

        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);

        $html = <<<OEF
<!DOCTYPE html>
            <html lang="zh-CN">
            <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="refresh" content="300">
            <title>管理后台</title>
OEF;

        $html .= js('jquery', 'jquery.form', 'bootstrap', 'admin');
        $html .= css('bootstrap');


        $html .= <<<OEF
            </head>
            <body>
<div style="width: 250px; margin: 100px auto;">
OEF;

        $html .= <<<OEF
                    
                            <form action="/pt/home/login" id="login_form" class="ajax_form" method="post">
                                <div class="form-group">
                                    <input name="username" type="text" class="form-control" placeholder="用户"/>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" placeholder="密码"/>
                                </div>
                                <div class="error_reason" style="color: red"></div>
                                <input type="submit" class="btn btn-primary" value="登录"/>
                            </form>
                    </div>
   
OEF;


        $html .= <<<OEF
            </body>
</html>
OEF;
        echo $html;
    }

    public function loginAction()
    {
        $username = $this->params('username');
        $password = $this->params('password');

        $partner_account = \PartnerAccounts::findFirstBy(['username' => $username]);
        info('合作方账户',$partner_account);

        if (!$partner_account || md5($password) != $partner_account->password) {
            return $this->renderJSON(ERROR_CODE_FORM, '用户不存在或密码不正确');
        }
        if ($partner_account->isBlocked()) {
            return $this->renderJSON(ERROR_CODE_FORM, '帐号被禁用');
        }

        $partner_account->active_at = time();
        $partner_account->update();

        $this->session->set('partner_account_id', $partner_account->id);
        $this->session->set('partner_account_md5', $partner_account->md5);

        $this->renderJSON(ERROR_CODE_SUCCESS, '登录成功', ['redirect_url' => '/pt/partner_datas','error_url' => '/pt/partner_datas']);
    }

    function logoutAction()
    {
        $this->session->destroy();
        $this->response->redirect('/pt');
    }

}



