<?php

namespace partner;

class BaseController extends \ApplicationController
{

    /**
     * @var \Unions
     */
    private $_current_union;

    /**
     * @var \ProductChannels
     */
    private $_current_product_channel;

    static $SKIP_ACTIONS = [
        'home' => ['index'],
        'unions' => ['register']
    ];

    /**
     * @return \Unions
     */
    function currentUnion()
    {
        if (!isset($this->_current_union)) {
            $union_id = $this->currentUnionId();
            $this->_current_union = \Unions::findFirstById($union_id);
        }

        return $this->_current_union;
    }

    function currentUnionId()
    {
        return $this->session->get('union_id');
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

        $current_union = $this->currentUnion();

        $controller_name = \Phalcon\Text::uncamelize($dispatcher->getControllerName());
        $action_name = \Phalcon\Text::uncamelize($dispatcher->getActionName());
        $controller_name = strtolower($controller_name);
        $action_name = strtolower($action_name);

        // 不验证用户登录
        if ($this->skipAuth($controller_name, $action_name)) {
            return;
        }

        if (isBlank($current_union)) {
            //$this->response->redirect('/unions/home');
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