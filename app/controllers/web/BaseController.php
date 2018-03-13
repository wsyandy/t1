<?php

namespace web;

class BaseController extends \ApplicationController
{

    /**
     * @var \Users
     */
    private $_current_user;

    /**
     * @var \ProductChannels
     */
    private $_current_product_channel;

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
        }

        return $this->_current_user;
    }

    function currentUserId()
    {
        return $this->session->get('user_id');
    }

    /**
     * @return \ProductChannels
     */
    function currentProductChannel()
    {

        if (isset($this->_current_product_channel)) {
            return $this->_current_product_channel;
        }

        $domain = $this->getHost();
        $this->_current_product_channel = \ProductChannels::findFirstByWebDomain($domain);
        return $this->_current_product_channel;

    }

    function currentProductChannelId()
    {
        if ($this->currentProductChannel()) {
            return $this->_current_product_channel->id;
        }

        return 0;
    }

    function checkLoginTime()
    {
        $user_login_at = $this->session->get("user_login_at");
        if ($user_login_at) {
            $time = md5(date("Ymd"));
            if (isDevelopmentEnv()) {
                $time = md5(date("Ymdh"));
            }
            if ($user_login_at != $time) {
                $this->session->set('user_id', null);
                $this->session->set('user_login_time', null);
            }
        }
    }

    function beforeAction($dispatcher)
    {
        $this->view->title = "";

        $this->checkLoginTime();

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