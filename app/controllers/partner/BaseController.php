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

    /**
     * @var \Users
     */
    private $_current_user;


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

    static $SKIP_ACTIONS = [
        'home' => ['index', 'check_auth', 'login'],
        'unions' => ['register', 'send_auth', 'login']
    ];

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
        $union_login_token = $this->session->get("union_login_token");

        $access_token = \AccessTokens::findFirstByToken($union_login_token);

        $expire_at = 3600;

        if (isDevelopmentEnv()) {
            $expire_at = 300;
        }

        if ($access_token && time() - $access_token->login_at > $expire_at || !$access_token) {
//            $this->session->set('user_id', null);
        }
    }

    function beforeAction($dispatcher)
    {
        $this->checkLoginTime();

        $this->view->title = "";
        $current_user = $this->currentUser();

        $controller_name = \Phalcon\Text::uncamelize($dispatcher->getControllerName());
        $action_name = \Phalcon\Text::uncamelize($dispatcher->getActionName());
        $controller_name = strtolower($controller_name);
        $action_name = strtolower($action_name);

        // 不验证用户登录
        if ($this->skipAuth($controller_name, $action_name)) {
            return;
        }

        if (isBlank($current_user)) {
            $this->response->redirect("/partner/home");
            return false;
        }

        $union = $this->currentUser()->union;

//        if ($union && $union->type == UNION_TYPE_PRIVATE) {
//            $this->clearLoginInfo();
//            echo "您已经加入其它家族, 不能进入工会";
//            return false;
//        }

        if ($union && (!$this->currentUser()->isUnionHost($union) || !$union->canLoginBackstage())) {
            $this->clearLoginInfo();
            echo "您无权限登录";
            return false;
        }

//        if (!$union) {
//
//            list($error_code, $error_reason, $union) = \Unions::createPublicUnion($this->currentUser());
//
//            if (ERROR_CODE_SUCCESS != $error_code) {
//                $this->clearLoginInfo();
//                echo "登录失败";
//                return false;
//            }
//        }

        if (!$union || $union->status == STATUS_BLOCKED || STATUS_OFF == $union->status) {
            $this->clearLoginInfo();
            echo "账号异常,请联系官方员";
            return false;
        }

        $this->view->current_user = $this->currentUser();
        $this->view->union = $this->currentUser()->union;
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

    function clearLoginInfo()
    {
        $this->session->set("user_id", null);
        $this->session->set('union_login_token', null);
    }
}