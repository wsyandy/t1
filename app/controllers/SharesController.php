<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/27
 * Time: 上午10:19
 */
class SharesController extends ApplicationController
{
    function indexAction()
    {
        $share_history = \ShareHistories::findFirstById($this->params('share_history_id', 0));
        $code = $this->params('code');

        if (!$share_history) {
            echo "参数错误";
            return false;
        }

        $share_history->increase('view_num');
        $user = $share_history->user;

        $user_agent = $this->request->getUserAgent();
        debug($user_agent);

        $platform = 'android';
        if (preg_match('/ios|iphone|ipad/i', $user_agent)) {
            $platform = 'ios';
        }

        $soft_version = \SoftVersions::findFirst([
            'conditions' => 'product_channel_id=:product_channel_id: and platform=:platform: and channel_package = 0',
            'bind' => ['product_channel_id' => $user->product_channel_id, 'platform' => $platform],
            'order' => 'id desc'
        ]);

        $soft_version_id = 0;

        if ($soft_version) {
            $soft_version_id = $soft_version->id;
        }

        $data = $share_history->data;
        $room_id = '';

        if ($data) {
            $data = json_decode($data, true);
            $room_id = fetch($data, 'room_id');
        }

        $this->view->user = $user;
        $this->view->room_id = $room_id;
        $this->view->soft_version_id = $soft_version_id;

        $file_name = $code . '_share_room';
        $file_path = APP_ROOT . 'app/views/shares/' . $file_name . '.volt';
        if (file_exists($file_path)) {
            $this->pick('shares/' . $file_name);
            return;
        }
    }

    function shareWorkAction()
    {
        $share_history = \ShareHistories::findFirstById($this->params('share_history_id', 0));
        $code = $this->params('code');

        if (!$share_history) {
            echo "参数错误";
            return false;
        }

        $share_history->increase('view_num');
        $user = $share_history->user;

        $user_agent = $this->request->getUserAgent();
        debug($user_agent);

        $platform = 'android';
        if (preg_match('/ios|iphone|ipad/i', $user_agent)) {
            $platform = 'ios';
        }

        $soft_version = \SoftVersions::findFirst([
            'conditions' => 'product_channel_id=:product_channel_id: and platform=:platform: and channel_package = 0 and status = :status:',
            'bind' => ['product_channel_id' => $user->product_channel_id, 'platform' => $platform, 'status' => SOFT_VERSION_STATUS_ON],
            'order' => 'id asc'
        ]);

        $soft_version_id = 0;

        if ($soft_version) {
            $soft_version_id = $soft_version->id;
        }

        $data = $share_history->data;
        $room_id = '';

        if ($data) {
            $data = json_decode($data, true);
            $room_id = fetch($data, 'room_id');
        }

        $this->view->user = $user;
        $this->view->room_id = $room_id;
        $this->view->soft_version_id = $soft_version_id;

        $file_name = $code . '_share_work';
        $file_path = APP_ROOT . 'app/views/shares/' . $file_name . '.volt';
        if (file_exists($file_path)) {
            $this->pick('shares/' . $file_name);
            return;
        }

    }

    // 分销注册页面
    function distributeAction()
    {
        $share_history_id = $this->params('share_history_id');
        $share_history = \ShareHistories::findFirstById($share_history_id);
        $code = $this->params('code');
        if (!$share_history) {
            echo "参数错误";
            return false;
        }

        $share_history->increase('view_num');
        $user = $share_history->user;

    }

    function mobileAuthAction()
    {
        if (!$this->request->isAjax()) {
            return false;
        }

        $image_token = $this->params('image_token');

        if (!$image_token) {
            info("image_token_error", $this->remoteIp(), $this->request->getUserAgent(), $this->headers());
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $user_captcha_code = $this->params('captcha_code', '');
        $hot_cache = Users::getHotReadCache();
        $captcha_code = $hot_cache->get('image_token_' . $image_token);
        if (strtolower($user_captcha_code) != strtolower($captcha_code)) {
            info("user_captcha_code error", $image_token);
            return $this->renderJSON(ERROR_CODE_FAIL, '图片验证码错误');
        }

        $share_history_id = $this->params('share_history_id');
        $share_history = \ShareHistories::findFirstById($share_history_id);
        if (!$share_history) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $product_channel = $share_history->product_channel;
        $mobile = $this->params('mobile');
        if (!isMobile($mobile)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '手机号码不正确');
        }

        $user = \Users::findFirstByMobile($product_channel, $mobile);
        if ($user) {
            info('已注册', $share_history_id, $product_channel->code, $mobile, 'user_fr', $user->fr);
            return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '你已注册，请登录！');
        }

        $auth_code = $this->params('auth_code', '');
        if (!$auth_code) {
            list($error_code, $error_reason, $sms_token) = \SmsHistories::sendAuthCode($product_channel, $mobile,
                'login', ['auth_type' => 'distribute']);
            if ($error_code == ERROR_CODE_SUCCESS) {
                $this->session->set('sms_token', $sms_token);
            }

            return $this->renderJSON($error_code, $error_reason, ['sms_token' => $sms_token]);
        }

        // 验证
        $sms_token = $this->params('sms_token');
        if (!$sms_token) {
            $sms_token = $this->session->get('sms_token');
        }

        list($error_code, $error_reason) = \SmsHistories::checkAuthCode($product_channel, $mobile, $auth_code, $sms_token,
            ['auth_type' => 'distribute']);
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        $user_agent = $this->request->getUserAgent();
        $platform = 'android';
        if (preg_match('/ios|iphone|ipad/i', $user_agent)) {
            $platform = 'ios';
        }

        // 下载哪个软件包 需要重新配置
        $soft_version = \SoftVersions::findFirst([
            'conditions' => 'product_channel_id=:product_channel_id: and platform=:platform: and channel_package = 0 and status = :status:',
            'bind' => ['product_channel_id' => $user->product_channel_id, 'platform' => $platform, 'status' => SOFT_VERSION_STATUS_ON],
            'order' => 'id asc'
        ]);

        $soft_version_id = 0;
        $fr = '';
        if ($soft_version) {
            $soft_version_id = $soft_version->id;
            $fr = $soft_version->built_in_fr;
        }


        $sms_sem_history = new \SmsDistributeHistories();
        $sms_sem_history->share_history_id = $share_history_id;
        $sms_sem_history->share_user_id = $share_history->user_id;
        $sms_sem_history->mobile = $mobile;
        $sms_sem_history->fr = $fr;
        $partner = \Partners::findFirstByFrHotCache($fr);
        if ($partner) {
            $sms_sem_history->partner_id = $partner->id;
        }
        $sms_sem_history->soft_version_id = $soft_version_id;
        $sms_sem_history->product_channel_id = $product_channel->id;
        $sms_sem_history->status = AUTH_WAIT;
        $sms_sem_history->save();

        // 跳转应用宝地址
        
        return $this->renderJSON(ERROR_CODE_SUCCESS, '验证成功', ['weixin_url' => $soft_version->weixin_url]);
    }

}