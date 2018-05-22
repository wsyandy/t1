<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午2:34
 */

namespace api;

use ApplicationController;


class BaseController extends ApplicationController
{
    private $_other_user;
    private $_current_user;
    private $_current_device;
    private $_current_product_channel;
    public $remote_ip;


    static $SKIP_ACTIONS = [
        'banners' => ['index'],
        'devices' => '*',
        'users' => ['send_auth', 'logout', 'login', 'new', 'register', 'push_token', 'client_status', 'third_login'],
        'soft_versions' => '*',
        'product_channels' => ['boot_config'],
        'product_menus' => ['index']
    ];

    static $CHECK_LOGIN_STATUS_ACTIONS = [
        'users' => ['register', 'login', 'client_status', 'third_login', 'update', 'send_auth'],
        'rooms' => '*',
        'room_seats' => ['up', 'down'],
        'gifts' => ['create']
    ];

    static $CHECK_OTHER_USER_ACTIONS = [
        'blacks' => ['create', 'destroy'],
        'followers' => ['create', 'destroy'],
        'friends' => ['create', 'destroy', 'agree'],
        'users' => ['other_detail', 'add_friend_note'],
        'rooms' => ['open_user_chat', 'close_user_chat', 'kicking', 'add_manager', 'delete_manager', 'update_manager'],
    ];

    static $SKIP_USER_INFO_ACTIONS = [
        'users' => ['update', 'emchat']
    ];

    function isDebug()
    {
        return '1' == $this->params('debug') && isDevelopmentEnv();
    }

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

        //$force 强制重新查用户
        if (!isset($this->_current_user) && $user_id || $force) {
            $user = \Users::findFirstById($user_id);
            $this->_current_user = $user;
        }

        if ($this->_current_user && $this->_current_product_channel) {
            $this->_current_user->product_channel = $this->_current_product_channel;
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

        if (!isset($this->_other_user) && $other_user_id || $force) {
            $other_user = \Users::findFirstById($other_user_id);
            $this->_other_user = $other_user;
        }

        if ($this->_other_user && $this->_current_product_channel) {
            $this->_other_user->product_channel = $this->_current_product_channel;
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

        return $this->_current_product_channel;
    }

    function currentProductChannelId()
    {
        if ($this->currentProductChannel()) {
            return $this->_current_product_channel->id;
        }
        return 0;
    }

    function getPushContext()
    {
        if ($this->context('platform') == '') {
            return [];
        }
        return $this->currentProductChannel()->getPushContext($this->context('platform'));
    }

    function isLogin()
    {
        return $this->currentUser() && $this->params('sid') === $this->currentUser()->sid && $this->currentUser()->isLogin();
    }

    function authorize()
    {
        return $this->currentUser() && $this->isLogin();
    }


    function beforeAction($dispatcher)
    {

        if (!$this->isHttps()) {
            info('no_https', $this->getFullUrl());
        }

        // 为啥要限制
        if (in_array($this->remoteIp(), ['112.1.160.168', '61.158.148.7', '61.158.149.145'])) {
            info("ip_illegal", $this->context(), $this->params());
            return $this->renderJSON(ERROR_CODE_FAIL, '请求非法', ['sid' => $this->currentUser()->generateSid('d.')]);
        }

        debug($this->params(), $this->headers(), $this->request->getRawBody());

        $code = $this->params('code');
        if (!$code || !$this->currentProductChannel()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '产品渠道不能为空');
        }

        if (isProduction()) {

            //1a8e9fc5bc1a3ba200feacc0de7676e6
            if ($this->context('platform') == 'android') {

                $ckey = $this->context('ckey');

                if (!$ckey) {
                    info("ckey_is_null", $this->params());
                }

                if ($ckey && '1a8e9fc5bc1a3ba200feacc0de7676e6' != $ckey) {
                    info("ckey_is_error", $this->params());
                }

                if ($this->currentProductChannel()->ckey &&
                    $this->currentProductChannel()->ckey != $ckey
                ) {
                    info("Exce 客户端异常", $this->context());
                    return $this->renderJSON(ERROR_CODE_FAIL, 'illegal invoke 客户端异常');
                }
            }
        }

        // 表单数据安全性验证
        list($result, $error_reason) = $this->validSign();
        if (false === $result) {
            info("Exce 表单数据安全性验证", $error_reason, $this->context(), $this->params());
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        // 修复老用户
        $fix_user = $this->fixOldUser();
        if ($fix_user) {
            info('fix_user', $fix_user->id, $this->params());
            return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '请登录', ['sid' => $fix_user->generateSid('d.')]);
        }


        $this->remote_ip = $this->remoteIp();
        $controller_name = \Phalcon\Text::uncamelize($dispatcher->getControllerName());
        $action_name = \Phalcon\Text::uncamelize($dispatcher->getActionName());
        $controller_name = strtolower($controller_name);
        $action_name = strtolower($action_name);

        //对方用户不存在
        if (!$this->skipCheckOtherUser($controller_name, $action_name) && !$this->otherUser()
            || $this->otherUserId() && !$this->otherUser()
        ) {
            return $this->renderJSON(ERROR_CODE_FAIL, '对方用户不存在');
        }

        if ($this->currentUser() && $this->currentUser()->isBlocked()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '账号被封!');
        }

        // 更新设备或用户状态
        if (!$this->skipCheckLoginStatus($controller_name, $action_name)) {
            $this->checkLoginStatus();
        }

        // 不验证用户登录
        if ($this->skipAuth($controller_name, $action_name)) {
            return;
        }

        if (!$this->authorize()) {
            info('请登录 authorize', $this->params());

            if (!$this->currentUser()) {
                info('非法请求 authorize', $this->params());
                return $this->renderJSON(ERROR_CODE_FAIL, '非法请求');
            }

            return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '请登录', ['sid' => $this->currentUser()->generateSid('d.')]);
        }

        if (!$this->skipCheckUserInfo($controller_name, $action_name) && $this->currentUser()->needUpdateInfo()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '需要更新资料', ['error_url' => 'app://users/update_info']);
        }
    }

    function fixOldUser()
    {
        $sid = $this->context('sid');

        if (isBlank($sid) || preg_match('/^\d+s/', $sid) || preg_match('/^\d+d\./', $sid)) {
            if (!$this->currentUserId() || $this->currentUserId() && $this->currentUser()) {
                return null;
            }
        }

        $device = $this->currentDevice();
        if ($device) {
            return \Users::registerForClientByDevice($device);
        }

        return null;
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

    function skipCheckUserInfo($controller_name, $action_name)
    {
        if (isset(self::$SKIP_USER_INFO_ACTIONS[$controller_name])) {
            $values = self::$SKIP_USER_INFO_ACTIONS[$controller_name];
            if ($values == '*') {
                return true;
            }

            if (is_array($values) && in_array($action_name, $values)) {
                return true;
            }
        }
        return false;

    }

    function skipCheckLoginStatus($controller_name, $action_name)
    {
        if (isset(self::$CHECK_LOGIN_STATUS_ACTIONS[$controller_name])) {
            $actions = self::$CHECK_LOGIN_STATUS_ACTIONS[$controller_name];

            if ("*" == $actions) {
                return false;
            }

            if (is_array($actions) && in_array($action_name, $actions)) {
                return false;
            }
        }

        return true;
    }

    function skipCheckOtherUser($controller_name, $action_name)
    {
        if (isset(self::$CHECK_OTHER_USER_ACTIONS[$controller_name])) {
            $actions = self::$CHECK_OTHER_USER_ACTIONS[$controller_name];

            if ("*" == $actions) {
                return false;
            }

            if (is_array($actions) && in_array($action_name, $actions)) {
                return false;
            }
        }

        return true;
    }

    public function checkLoginStatus()
    {

        if (!$this->currentUser()) {
            return;
        }

        $fresh_attrs = [
            'platform_version' => $this->context('platform_version'),
            'platform' => $this->context('platform'),
            'version_name' => $this->context('version_name'),
            'version_code' => $this->context('version_code'),
            'ip' => $this->remote_ip,
            'latitude' => $this->context('latitude'),
            'longitude' => $this->context('longitude'),
            'api_version' => $this->context('an'),
            'manufacturer' => $this->context('manufacturer'),
        ];

        $this->currentUser()->onlineFresh($fresh_attrs);
        $this->currentUser()->startOfflineTask();
    }

    function validSign()
    {
//        if(isDevelopmentEnv()) {
//            return [true, t('base_valid_sign_signature_success')];
//        }

        // 如果debug 并且在开发模式下，不验证签名
        if ($this->isDebug()) {
            return [true, ""];
        }

        $dno = $this->params('dno');
        if (isBlank($dno) || !checkSum($dno)) {
            debug('dno error');
            return [false, t('base_valid_sign_param_error')];
        }

        if ($_REQUEST) {
            $data = [];
            foreach ($_REQUEST as $key => $val) {
                if ($key == 'h' || $key == '_url' || $key == 'file') {
                    continue;
                }
                $data[] = $key . '=' . $val;
            }

            sort($data);
            $sign_str = implode('&', $data);
            $ckey = $this->params('ckey');
            $sign = md5(md5($sign_str) . $ckey);
            if ($this->params('h') == $sign) {
                return [true, t('base_valid_sign_signature_success')];
            }
        }

        if (isDevelopmentEnv()) {
            debug('base_valid_sign_signature_error', $sign_str, md5($sign_str), $sign);
            return [false, "Sign error! md5(md5($sign_str) + $ckey) sign=$sign client_sign=" . $this->params('h')];
        }

        info('base_valid_sign_signature_error', $this->params(), $sign_str, md5($sign_str), $sign);

        return [false, t('base_valid_sign_signature_error')];
    }

    // 签名验证字段， 生成规则： md5(md5(提交表单数据，键值对字典排序 &链接) 小写) + ckey)
    function signDate($data = [])
    {
        /*
        # 客户端指纹
        $ckey = md5(uniqid());
        $data = array("username"=>"test_username", 'password'=>'test_pwd',
            'sex'=>"1", 'nickname'=>'test_nickname', 'ckey'=>$ckey);
        */

        ksort($data);
        $signStr = "";
        foreach ($data as $key => $val) $signStr .= '&' . $key . $val;

        $signStr = ltrim($signStr, "&");
        $sign = md5(md5(strtolower($signStr)) . $data['ckey']);

        return $sign;
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

        if ($this->isAndroid()) {
            if ($this->context('version_code') > $this->currentProductChannel()->android_stable_version) {
                return true;
            }
            return false;
        }
        return false;
    }
}
