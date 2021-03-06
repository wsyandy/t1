<?php

namespace wx;

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

    public $remote_ip;

    function isDebug()
    {
        return '1' == $this->params('debug') && isDevelopmentEnv();
    }

    /**
     * @return \Users
     */
    function currentUser()
    {
        if (!$this->_current_user) {
            $user_id = $this->session->get('user_id');
            if (!$user_id) {
                return null;
            }

            debug('session_user_id', $user_id);

            $this->_current_user = \Users::findFirstById($user_id);
        }

        return $this->_current_user;
    }

    function currentUserId()
    {
        if ($this->currentUser()) {
            return $this->currentUser()->id;
        }
        return null;

    }

    function currentProductChannel()
    {

        if (isset($this->_current_product_channel)) {
            return $this->_current_product_channel;
        }
        $domain = $this->getHost();
        $this->_current_product_channel = \ProductChannels::findFirstByWeixinDomain($domain);
        return $this->_current_product_channel;

    }

    function currentProductChannelId()
    {
        if ($this->currentProductChannel()) {
            return $this->_current_product_channel->id;
        }
        return 0;
    }

    function currentOpenid()
    {
        $openid = $this->session->get('openid');
        return $openid;
    }

    function beforeAction($dispatcher)
    {

        if (isProduction() && $this->request->isGet() && !$this->request->isAjax() && !$this->isHttps()) {

            $url = $this->getFullUrl();
            $url = preg_replace('/^http:/', 'https:', $url);

            info('isHttps', $url);

            $this->response->redirect($url);
            return false;
        }

        if (!$this->isWeixinClient()) {
            echo "请使用微信访问";
            return false;
        }

        if (!$this->currentProductChannel()) {
            echo '非法访问';
            return false;
        }

        $controller_name = $dispatcher->getControllerName();
        $action_name = $dispatcher->getActionName();
        $controller_name = \Phalcon\Text::uncamelize($controller_name);
        $action_name = \Phalcon\Text::uncamelize($action_name);
        $controller_name = strtolower($controller_name);
        $action_name = strtolower($action_name);


        $this->remote_ip = $this->remoteIp();
        $this->view->title = $this->currentProductChannel()->weixin_name;
        $this->view->current_theme = $this->currentProductChannel()->weixin_theme;
        $this->view->current_namespace = 'wx';

        if (isDevelopmentEnv()) {
            $new_register = $this->params('new_register');
            if ($new_register) {
                $this->session->destroy();
                $sex = $this->params('sex', 1);
                $info = ['sex' => $sex, 'nickname' => 'XX', 'province' => '上海', 'city' => '上海', 'subscribe' => 1];
                $this->_current_user = \Users::registerByOpenid($this->currentProductChannel(), randStr(20), $info);
                $this->session->set('user_id', $this->_current_user->id);
            }
        }

        if (!$this->currentOpenid()) {

            if ($this->request->isAjax()) {
                $this->renderJSON(ERROR_CODE_FAIL, '需要登录授权', ['error_url' => '/wx/home/error']);
            } else {

                info('需要授权', $this->remoteIp(), 'wx_return_url:', $this->getUri(), $this->request->getUserAgent());
                $this->session->destroy();

                $wx_return_url = $this->request->getURI();
                $session_data = ['wx_return_url' => $wx_return_url];
                if ($this->params('fr')) {
                    $session_data['weixin_fr'] = $this->params('fr');
                }

                $this->session->set($session_data);

                debug("wx_return_url", $session_data, $this->session->get('wx_return_url'));

                $return_url = $this->getRoot() . 'weixin/auth_callback';
                $weixin_event = new \WeixinEvents($this->currentProductChannel());
                $weixin_event->auth($return_url);
            }

            $this->view->disable();
            return false;
        }


//        if (!$this->request->isAjax()) {
//            if (!$this->checkLoginStatus()) {
//                return false;
//            }
//        }

        return true;

    }

    public function checkLoginStatus()
    {
        $fresh_attrs = [
            'platform' => $this->getWeixinPlatform(),
            'version_name' => $this->getWeixinVersion(),
            'ip' => $this->remote_ip
        ];

        $this->currentUser()->onlineFresh($fresh_attrs);

        // 启动离线任务
        // $this->currentUser()->startOfflineTask();

        return true;
    }

    // 客户端微信版本
    function getWeixinVersion()
    {
        $ua = $this->request->getUserAgent();
        $version = '1.0.0';

        debug($ua);
        if ($this->isWeixinClient()) {
            if (preg_match('/MicroMessenger\/([^ ]+)/i', $ua, $result)) {
                $version = explode('_', $result[1]);
                $version = $version[0];
                $tmp = explode('.', $version);
                if (count($tmp) > 3) {
                    $tmp = array_slice($tmp, 0, 3);
                }

                $version = implode('.', $tmp);
            }
        }

        return $version;
    }

    function getSignPackage()
    {
        $product_channel = $this->currentProductChannel();
        $url = $this->getFullUrl();
        $weixin_event = new \WeixinEvents($product_channel);
        $sign_package = $weixin_event->getSignPackage($url);
        return $sign_package;
    }
}