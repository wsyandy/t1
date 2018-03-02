<?php

namespace web;

class BaseController extends \ApplicationController
{

    /**
     * @var \Users
     */
    private $_current_user;

    static $SKIP_ACTIONS = [
        'home' => ['index', 'login', 'logout', 'check_auth']
    ];

    /**
     * @return \Users
     */
    function currentUser()
    {
        if (!isset($this->_current_user)) {
            $user_id = $this->currentUserId();
            $this->_current_user = \Users::findFirstById($user_id);
//            if (!$this->_current_user && isDevelopmentEnv()) {
//                $this->_current_user = \Users::findLast();
//            }
        }

        return $this->_current_user;
    }

    function currentUserId()
    {
//        if (isDevelopmentEnv()) {
//            $user = \Users::findLast();
//            return $user->id;
//        }
        return $this->session->get('user_id');
    }

    function beforeAction($dispatcher)
    {
        $this->view->title = "";

        $login_time = $this->session->get("login_time");
        debug($login_time);
        $time = 86400;
        if (isDevelopmentEnv()) {
            $time = 60 * 1;
        }
        if ($login_time && time() - $login_time > $time) {
            $this->session->set('user_id', null);
            $this->session->set('login_time', null);
        }

        $current_user = $this->currentUser();

        $controller_name = \Phalcon\Text::uncamelize($dispatcher->getControllerName());
        $action_name = \Phalcon\Text::uncamelize($dispatcher->getActionName());
        $controller_name = strtolower($controller_name);
        $action_name = strtolower($action_name);

        $show_logout = false;
        if (isPresent($current_user)) {
            $show_logout = true;
        }

        $this->view->show_logout = $show_logout;

        // 不验证用户登录
        if ($this->skipAuth($controller_name, $action_name)) {
            return;
        }

        if (isBlank($current_user)) {
            $this->response->redirect('/web/home/login');
            return;
        }
    }


    function skipAuth($controller_name, $action_name)
    {
        if (isset(self::$SKIP_ACTIONS[$controller_name])) {
            $values = self::$SKIP_ACTIONS[$controller_name];
            if ($values == '*') {
                return true;
            }

            if (is_array($values) && in_array($action_name, $values)) {
                return true;
            }
        }
        return false;
    }
}