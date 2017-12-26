<?php

namespace admin;

class BaseController extends \ApplicationController
{
    /**
     * @var \Operators
     */
    private $_current_operator;

    function currentOperator()
    {
        $operator_id = $this->session->get('operator_id');
        $operator_md5 = $this->session->get('operator_md5');
        if (!$operator_id || !$operator_md5) {
            return null;
        }

        if ($this->_current_operator) {
            return $this->_current_operator;
        }

        $this->_current_operator = \Operators::auth($operator_id, $operator_md5);
        return $this->_current_operator;
    }

    function skipAuth($controller_name, $action_name)
    {
        if ($controller_name == 'monitor') {
            return true;
        }

        return false;
    }

    function authIp()
    {
        $ip = $this->remoteIp();
        $operator_login_ip = $this->session->get('operator_login_ip');
        if (!$operator_login_ip || $operator_login_ip != $ip) {
            info($operator_login_ip, $ip);
            return false;
        }

        return true;
    }

    function beforeAction($dispatcher)
    {

        $this->view->is_development = isDevelopmentEnv();

        $controller_name = \Phalcon\Text::uncamelize($dispatcher->getControllerName());
        $action_name = \Phalcon\Text::uncamelize($dispatcher->getActionName());

//        if (isProduction() && $this->request->isGet() && !$this->request->isAjax() && !$this->isHttps() && $controller_name != 'monitor') {
//
//            $url = $this->getFullUrl();
//            $url = preg_replace('/^http:/', 'https:', $url);
//
//            $this->response->redirect($url);
//            return false;
//        }

        if ($this->skipAuth($controller_name, $action_name)) {
            return;
        }

        if (!$this->currentOperator() || !$this->authIp()) {
            if ($this->request->isAjax()) {
                $this->renderJSON(ERROR_CODE_FAIL, '请重新登录', array('redirect_url' => '/admin'));
            } else {
                $this->response->redirect('/admin');
            }
            $this->view->disable();
            return false;
        }

        if (!$this->currentOperator()->checkStatus()) {
            $this->response->redirect('/admin');
            $this->view->disable();
            return false;
        }

        $this->request->current_role = $this->currentOperator()->role;
        $this->request->current_operator = $this->currentOperator();
        $this->request->current_username = $this->currentOperator()->username;

        $is_allowed = isAllowed($controller_name, $action_name);
        debug($controller_name, $action_name, 'allow', $is_allowed);

        if (!$is_allowed) {
            $this->renderJSON(ERROR_CODE_FAIL, '没有操作权限');
            $this->view->disable();
            return false;
        }

        if (time() - $this->currentOperator()->active_at > 60) {
            $this->currentOperator()->active_at = time();
            $this->currentOperator()->update();
        }

        $this->view->current_operator = $this->currentOperator();
    }
}