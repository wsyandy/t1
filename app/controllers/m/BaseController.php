<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午5:18
 */

namespace m;

class BaseController extends \ApplicationController
{
    private $_current_user;
    private $_current_device;
    private $_current_product_channel;
    public $remote_ip;

    static $SKIP_ACTIONS = [
        'activities' => '*',
        'product_channels' => ['user_agreement', 'privacy_agreement', 'strategies'],
        'payments' => ['index']
    ];

    function currentUserId()
    {
        $sid = $this->context('sid');
        if (isBlank($sid)) {
            return null;
        }

        // 登录
        if (preg_match('/^\d+s/', $sid)) {
            $user_id = intval(explode('s', $sid, 2)[0]);
            return $user_id;
        }

        // 未登录
        if (preg_match('/^\d+d\./', $sid)) {
            $user_id = intval(explode('d.', $sid, 2)[0]);
            return $user_id;
        }

        return null;
    }

    /**
     * @return \Users
     */
    function currentUser($force = false)
    {
        $user_id = $this->currentUserId();

        if (isBlank($user_id)) {
            return null;
        }

        //强制重新查用户
        if ($force) {
            $user = \Users::findFirstById($user_id);
            return $user;
        }

        if (!isset($this->_current_user) && $user_id) {
            $user = \Users::findFirstById($user_id);
            $this->_current_user = $user;
        }

        return $this->_current_user;
    }

    function otherUserId()
    {
        $user_id = $this->context('user_id');

        if (isBlank($user_id)) {
            return null;
        }

        $user_id = intval($user_id);
        debug('user_id', $user_id);

        return $user_id;
    }

    /**
     * @return \Users
     */
    function otherUser($force = false)
    {
        $other_user_id = $this->otherUserId();

        if (isBlank($other_user_id)) {
            return null;
        }

        //强制重新查用户
        if ($force) {
            $user = \Users::findFirstById($other_user_id);
            return $user;
        }

        if (!isset($this->_other_user) && $other_user_id) {
            $other_user = \Users::findFirstById($other_user_id);
            if ($other_user) {
                $this->_other_user = $other_user;
            }
        }

        return $this->_other_user;
    }

    function currentDeviceId()
    {
        $user = $this->currentUser();
        if ($user && $user->device_id) {
            return $user->device_id;
        }

        // 兼容以前
        $sid = $this->context('sid');
        if ($sid && !preg_match('/^\d+d\./', $sid) && preg_match('/^\d+d/', $sid)) {
            $device_id = intval(explode('d', $sid, 2)[0]);
            return $device_id;
        }

        return null;
    }

    /**
     * @return \Devices
     */
    function currentDevice()
    {

        if (!isset($this->_current_device)) {

            $device_no = $this->context('device_no');
            $this->_current_device = \Devices::findFirst([
                'conditions' => 'device_no=:device_no: and product_channel_id=:product_channel_id:',
                'bind' => ['device_no' => $device_no, 'product_channel_id' => $this->currentProductChannelId()],
                'order' => 'id desc']);

            info('device_no', $device_no, $this->currentProductChannelId(), 'context', $this->context());
        }

        return $this->_current_device;
    }


    /**
     * @return \ProductChannels
     */
    function currentProductChannel()
    {
        $code = $this->context('code');

        if (!isset($this->_current_product_channel) && $code) {
            $this->_current_product_channel = \ProductChannels::findFirstByCodeHotCache($code);
        }

        if (!isset($this->_current_product_channel) && isPresent($this->currentUser())) {
            $this->_current_product_channel = $this->currentUser()->product_channel;
        }

        return $this->_current_product_channel;
    }

    function currentProductChannelId()
    {
        if ($this->currentProductChannel()) {
            return $this->_current_product_channel->id;
        }
        return 0;
    }

    function beforeAction($dispatcher)
    {

        if (!$this->currentProductChannel()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '产品渠道非法');
        }

        $controller_name = $dispatcher->getControllerName();
        $action_name = $dispatcher->getActionName();
        $controller_name = \Phalcon\Text::uncamelize($controller_name);
        $action_name = \Phalcon\Text::uncamelize($action_name);
        $controller_name = strtolower($controller_name);
        $action_name = strtolower($action_name);

        $is_foreign_ip = true;
        $ip = $this->remoteIp();
        $data = \Users::ipLocation($ip);

        debug("is_foreign_ip", $data);

        if (is_array($data) && preg_match('/中国/', $data[0])) {
            $is_foreign_ip = false;
        }

        $this->view->is_ios = $this->isIos();
        $this->view->title = $this->currentProductChannel()->name;
        $this->view->code = $this->currentProductChannel()->code;
        $this->view->is_foreign_ip = $is_foreign_ip;

        if ($this->currentUser() && $this->currentUser()->isBlocked()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '账户被封');
        }

        // 不验证用户登录
        if ($this->skipAuth($controller_name, $action_name)) {
            return;
        }

        if (!$this->authorize()) {
            return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '请登录');
        }

        $this->view->sid = $this->currentUser()->sid;
        $this->view->current_user = $this->currentUser();
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

    function isLogin()
    {
        return $this->currentUser() && $this->params('sid') === $this->currentUser()->sid && $this->currentUser()->isLogin();
    }

    function authorize()
    {
        return $this->currentUser() && $this->isLogin();
    }

    function isIos()
    {
        return USER_PLATFORM_IOS == $this->context('platform');
    }

    function isAndroid()
    {
        return USER_PLATFORM_ANDROID == $this->context('platform');
    }

    function isHightVersion()
    {
        if ($this->isIos()) {
            if ($this->context('version_code') > $this->currentProductChannel()->apple_stable_version) {
                return true;
            }
            return false;
        }

//        if ($this->isAndroid()) {
//            if ($this->context('version_code') > $this->currentProductChannel()->android_stable_version) {
//                return true;
//            }
//            return false;
//        }
        return false;
    }
}
