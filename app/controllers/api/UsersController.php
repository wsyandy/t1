<?php

namespace api;

class UsersController extends BaseController
{
    function registerAction()
    {
        $mobile = $this->params('mobile');
        $auth_code = $this->params('auth_code');
        $sms_token = $this->params('sms_token');
        $password = $this->params('password');

        if (!isMobile($mobile)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '手机号码不正确');
        }

        if (isBlank($password)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请设置密码');
        }

        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请设置6~16位的密码');
        }

        $opts = $this->context();

        list($error_code, $error_reason) = \SmsHistories::checkAuthCode($this->currentProductChannel(), $mobile, $auth_code, $sms_token, $opts);
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        $device = $this->currentDevice();
        list($error_code, $error_reason, $user) = \Users::registerByClientMobile($this->currentProductChannel(),
            $device, $mobile, $this->context(), $this->params());

        if ($error_code !== ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        $user->updatePushToken($device);

        return $this->renderJSON($error_code, $error_reason, ['sid' => $user->sid]);
    }


    function sendAuthAction()
    {
        $mobile = $this->params('mobile');

        $context = $this->context();
        $context['device_id'] = $this->currentDeviceId();

        list($error_code, $error_reason, $sms_token) = \SmsHistories::sendAuthCode($this->currentProductChannel(),
            $mobile, 'login', $context);

        return $this->renderJSON($error_code, $error_reason, ['sms_token' => $sms_token]);
    }


    //设备号：不唯一
    //设备号：注册和登录接口已sid或device_no为准获取device
    //设备号：其他接口，使用device都以user->device为准
    function loginAction()
    {
        if ($this->request->isPost()) {

            $mobile = $this->params('mobile');
            $auth_code = $this->params('auth_code');
            $sms_token = $this->params('sms_token');
            $password = $this->params('password');

            $device = $this->currentDevice();

            if (!isMobile($mobile)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '手机号码不正确');
            }


            if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
                return $this->renderJSON(ERROR_CODE_FAIL, '请输入6~16位的密码');
            }

            if (!$device) {
                return $this->renderJSON(ERROR_CODE_FAIL, '设备数据错误,请重试');
            }

            $user = \Users::findFirstByMobile($this->currentProductChannel(), $mobile);

            if (!$user) {
                return $this->renderJSON(ERROR_CODE_FAIL, '手机号码未注册');
            }

            if ($auth_code) {

                //开发环境单独验证
                if (!(isDevelopmentEnv() && '1234' == $auth_code)) {
                    $context = $this->context();
                    list($error_code, $error_reason) = \SmsHistories::checkAuthCode($this->currentProductChannel(),
                        $mobile, $auth_code, $sms_token, $context);

                    if ($error_code != ERROR_CODE_SUCCESS) {
                        return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
                    }
                }
            } else {
                if (!$user || $user->password != md5($password)) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '手机号码或密码不正确');
                }
            }

            list($error_code, $error_reason) = $user->clientLogin($this->params(), $device);

            if ($error_code != ERROR_CODE_SUCCESS) {
                return $this->renderJSON($error_code, $error_reason);
            }

            $user->updatePushToken($device);

            $error_url = '';

            if ($this->currentUser()->needUpdateInfo()) {
                $error_url = 'app://users/update_info';
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '登陆成功', ['sid' => $user->sid, 'error_url' => $error_url]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '非法访问!');
        }
    }


    function logoutAction()
    {

        if (!$this->currentUser()) {
            $this->renderJSON(ERROR_CODE_FAIL, '用户未登陆!');
            return;
        }

        $user = $this->currentUser();
        $device = $user->device;

        if (!$device) {
            $device = \Devices::findFirst([
                'conditions' => 'device_no=:device_no: and product_channel_id=:product_channel_id:',
                'bind' => ['device_no' => $this->context('device_no'), 'product_channel_id' => $this->currentProductChannelId()],
                'order' => 'id desc']);
        }

        $user->user_status = USER_STATUS_LOGOUT;
        $user->update();

        $this->renderJSON(ERROR_CODE_SUCCESS, '已退出', ['sid' => $device->sid]);
    }

    function updateAction()
    {
        $user = $this->currentUser();

        $avatar_file = $this->file('avatar_file');
        debug('update_info', $avatar_file, $this->params());

        if ($avatar_file) {
            $user->updateAvatar($avatar_file);
        }

        $user->updateProfile($this->params());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }

    function updateAvatarAction()
    {
        $user = $this->currentUser();

        // 更新头像
        $avatar_file = $this->file('avatar_file');
        if ($avatar_file) {
            $user->updateAvatar($avatar_file);
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');

    }

    function pushTokenAction()
    {
        $push_token = $this->params('push_token');
        $push_from = $this->params('push_from', 'getui');

        if (!$push_token) {
            info('Exce push_token', $this->context(), $this->params());
            return $this->renderJSON(ERROR_CODE_SUCCESS, '数据错误');
        }

        $device = null;

        if ($this->currentUser()) {
            $device = $this->currentUser()->device;
        }

        if (!$device) {
            $device = $this->currentDevice();
            if (!$device) {
                info('Exce false_device', $this->context(), $this->params());
                return $this->renderJSON(ERROR_CODE_FAIL, '设备错误!');
            }
        }

        $device->platform_version = $this->context('platform_version');
        $device->push_token = $push_from . '_' . $push_token;
        $device->update();

        $user = $this->currentUser();

        if ($user) {
            $user->updatePushToken($device);
        }

        $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function clientStatusAction()
    {
        $status = $this->params('client_status');

        if ($this->currentUser()) {
            $device = $this->currentUser()->device;
        } else {
            $device = $this->currentDevice();
        }

        if ($device) {
            $device->client_status = $status;
            $device->update();
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        $this->renderJSON(ERROR_CODE_FAIL, '设备不存在');
    }

    function detailAction()
    {
        $detail_json = [];

        if ($this->otherUser()) {
            $detail_json = $this->otherUser()->toDetailJson();
        } elseif ($this->currentUser()) {
            $detail_json = $this->currentUser()->toDetailJson();
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $detail_json);
    }
}