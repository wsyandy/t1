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

        // 测试白名单
        $is_white_mobile = false;
        if ($mobile && in_array($mobile, ['13912345678'])
        ) {
            $is_white_mobile = true;
        }

        $context = $this->context();

        $context['is_white_mobile'] = $is_white_mobile;
        list($error_code, $error_reason) = \SmsHistories::checkAuthCode($this->currentProductChannel(), $mobile, $auth_code, $sms_token, $context);

        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        // 存在更换设备登录
        $device = $this->currentDevice();
        $product_channel = $this->currentProductChannel();

        if (!$device) {
            $device = $this->currentUser()->device;
        }

        $current_user = $this->currentUser();
        $current_user->product_channel = $product_channel;
        list($error_code, $error_reason, $user) = \Users::registerForClientByMobile($current_user, $device, $mobile, $context);

        $db = \Users::getUserDb();
        $good_num_list_key = 'good_num_list';

        if ($db->zscore($good_num_list_key, $user->id)) {
            info("good_num", $user->id);
            $user->user_type = USER_TYPE_SILENT;
            $user->user_status = USER_STATUS_OFF;
            $user->mobile = '';
            $user->device_id = 0;
            $user->password = '';
            $user->update();

            $device->user_id = 0;
            $device->update();

            list($error_code, $error_reason, $user) = \Users::registerForClientByMobile($current_user, $device, $mobile, $context);
        }

        if ($error_code !== ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        list($error_code, $error_reason) = $user->clientLogin($context, $device);
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        $user->updatePushToken($device);

        $key = $this->currentProductChannel()->getSignalingKey($user->id);
        $app_id = $this->currentProductChannel()->getImAppId();

        $opts = ['sid' => $user->sid, 'im_password' => $user->im_password, 'id' => $user->id, 'app_id' => $app_id, 'signaling_key' => $key];

        return $this->renderJSON($error_code, $error_reason, $opts);
    }


    function sendAuthAction()
    {
        $mobile = $this->params('mobile');
        $sms_type = $this->params('sms_type');
        $context = $this->context();
        $context['user_id'] = $this->currentUser()->id;
        $user = \Users::findFirstByMobile($this->currentProductChannel(), $mobile);

        if (!$sms_type) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        if ('login' == $sms_type && !$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        if ('register' == $sms_type && $user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户已注册');
        }

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

            if ($user->isBlocked()) {
                info("block_user_login", $user->sid);
                return $this->renderJSON(ERROR_CODE_FAIL, '账户异常');
            }

            // 测试白名单
            $is_white_mobile = false;
            if ($mobile && in_array($mobile, ['13912345678'])
            ) {
                $is_white_mobile = true;
            }

            $context = $this->context();

            if ($auth_code) {

                $context['is_white_mobile'] = $is_white_mobile;
                list($error_code, $error_reason) = \SmsHistories::checkAuthCode($this->currentProductChannel(),
                    $mobile, $auth_code, $sms_token, $context);

                if ($error_code != ERROR_CODE_SUCCESS) {
                    return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
                }

            } else {
                if (!$user || $user->password != md5($password)) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '手机号码或密码不正确');
                }
            }

            $context['login_type'] = USER_LOGIN_TYPE_MOBILE;

            list($error_code, $error_reason) = $user->clientLogin($context, $device);

            if ($error_code != ERROR_CODE_SUCCESS) {
                return $this->renderJSON($error_code, $error_reason);
            }

            $user->updatePushToken($device);

            $error_url = '';

            if ($user->needUpdateInfo()) {
                $error_url = 'app://users/update_info';
            }

            $key = $this->currentProductChannel()->getSignalingKey($user->id);
            $app_id = $this->currentProductChannel()->getImAppId();

            $opts = ['sid' => $user->sid, 'app_id' => $app_id, 'signaling_key' => $key, 'error_url' => $error_url];
            $opts = array_merge($opts, $user->toBasicJson());

            return $this->renderJSON(ERROR_CODE_SUCCESS, '登陆成功', $opts);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '非法访问!');
        }
    }

    //第三方登陆 qq weixin sinaweibo
    //access_token openid app_id(微信不需要此参数)
    function thirdLoginAction()
    {
        if (!$this->request->isPost()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '非法访问!');
        }

        $device = $this->currentDevice();
        if (!$device) {
            $device = $this->currentUser()->device;
        }

        if (!$device) {
            return $this->renderJSON(ERROR_CODE_FAIL, '设备数据错误,请重试');
        }

        $third_name = $this->params('third_name'); // qq, weixin
        $third_gateway = \thirdgateway\Base::gateway($third_name);
        if (!$third_gateway) {
            return $this->renderJSON(ERROR_CODE_FAIL, '不支持该登陆方式!');
        }

        $context = $this->context();
        if ($third_name == 'qq') {
            $context['has_unionid'] = 1;
        }

        //登陆认证
        $form = $third_gateway->auth($context);
        if (!$form) {
            return $this->renderJSON(ERROR_CODE_FAIL, '登陆信息错误');
        }

        info($third_name, 'third_login_info=', $form);

        if ($form['error_code'] != ERROR_CODE_SUCCESS) {
            return $this->renderJSON($form['error_code'], $form['error_reason']);
        }

        $third_unionid = isset($form['third_unionid']) ? $form['third_unionid'] : $form['third_id'];

        $user = \Users::findFirstByThirdUnionid($this->currentProductChannel(), $third_unionid, $third_name);
        $error_url = '';

        if (!$user) {
            list($error_code, $error_reason, $user) = \Users::thirdLogin($this->currentUser(), $device, $form, $context);
            if ($error_code != ERROR_CODE_SUCCESS) {
                return $this->renderJSON($error_code, $error_reason);
            }

            if (isDevelopmentEnv()) {
                //第一次注册 跳转更新资料
                $error_url = 'app://users/update_info';
            }
        }

        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '登陆失败!');
        }

        if ($user->isBlocked()) {
            info("block_user_login", $user->sid);
            return $this->renderJSON(ERROR_CODE_FAIL, '账户异常');
        }

        $context['login_type'] = $third_name;

        list($error_code, $error_reason) = $user->clientLogin($context, $device);

        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        $user->updatePushToken($device);

        $key = $this->currentProductChannel()->getSignalingKey($user->id);
        $app_id = $this->currentProductChannel()->getImAppId();

        $user_simple_json = ['sid' => $user->sid, 'app_id' => $app_id, 'signaling_key' => $key, 'error_url' => $error_url];
        $user_simple_json = array_merge($user_simple_json, $user->toBasicJson());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '登陆成功', $user_simple_json);
    }

    function logoutAction()
    {

        if (!$this->currentUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户未登陆!');
        }

        $user = $this->currentUser();
        $user->sid = $user->generateSid('d.');
        $user->user_status = USER_STATUS_LOGOUT;
        $user->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '已退出', ['sid' => $user->sid]);
    }

    function updateAction()
    {
        $user = $this->currentUser();

        $avatar_file = $this->file('avatar_file');

        if ($avatar_file) {
            $user->updateAvatar($avatar_file);
        }

        $params = $this->params();
        $monologue = fetch($params, 'monologue');
        $birthday = fetch($params, 'birthday');

        if ($monologue && mb_strlen($monologue) > 250) {
            return $this->renderJSON(ERROR_CODE_FAIL, '个性签名字数过长');
        }

        if ($birthday && (strtotime($birthday) > time() || strtotime($birthday) < strtotime('1935-01-01'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请输入正确的生日');
        }

        $user->updateProfile($params);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功', $user->toDetailJson());
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

        $device = $this->currentDevice();
        if (!$device) {
            $device = $this->currentUser()->device;
        }

        if (!$device) {
            info('Exce false_device', $this->context(), $this->params());
            return $this->renderJSON(ERROR_CODE_FAIL, '设备错误!');
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
            $this->currentUser()->client_status = $status;
            $this->currentUser()->update();
        }

        $device = $this->currentDevice();
        if ($device) {
            $device->client_status = $status;
            $device->update();
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '设备不存在');
    }

    function detailAction()
    {
        $detail_json = $this->currentUser()->toDetailJson();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $detail_json);
    }

    function otherDetailAction()
    {
        //房间是否加锁
        $other_current_room = $this->otherUser()->current_room;
        $current_room_lock = false;

        if ($other_current_room) {
            $current_room_lock = $other_current_room->lock;
        }

        $detail_json = $this->otherUser()->toDetailJson();
        $detail_json['is_friend'] = $this->currentUser()->isFriend($this->otherUser());
        $detail_json['is_follow'] = $this->currentUser()->isFollow($this->otherUser());
        $detail_json['current_room_lock'] = $current_room_lock;

        if (!$this->otherUser()->isActive()) {
            $detail_json['province_name'] = $this->currentUser()->province_name;
            $detail_json['city_name'] = $this->currentUser()->city_name;
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $detail_json);
    }

    function setSpeakerAction()
    {
        $speaker = $this->params('speaker', true);
        $this->currentUser()->setSpeaker($speaker);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function setMicrophoneAction()
    {
        $microphone = $this->params('microphone', true);
        $this->currentUser()->setMicrophone($microphone);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function basicInfoAction()
    {
        $basic_json = $this->currentUser()->toBasicJson();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $basic_json);
    }

    function emchatAction()
    {
        if (\Emchat::createEmUser($this->currentUser())) {
            return $this->renderJSON(
                ERROR_CODE_SUCCESS, '创建成功',
                [
                    'id' => $this->currentUser()->id,
                    'im_password' => $this->currentUser()->im_password
                ]
            );
        }
        return $this->renderJSON(ERROR_CODE_FAIL, '创建失败,请稍后再试');
    }

    function searchAction()
    {

        $cond = [];
        $user_id = intval($this->params('user_id'));
        if ($user_id) {
            $cond = ['user_id' => intval($user_id)];
        }

        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $users = \Users::search($this->currentUser(), $page, $per_page, $cond);
        if (count($users)) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toSimpleJson'));
        }

        info($this->params());
        return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
    }

    // 附近的人
    function nearbyAction()
    {

        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $users = $this->currentUser()->nearby($page, $per_page);
        if (count($users)) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toSimpleJson'));
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
    }

    /**
     * 用户账户
     * IOS审核版本使用这个接口,不用h5页面
     */
    function accountAction()
    {
        $products = \Products::findDiamondListByUser($this->currentUser(), 'toApiJson');

        $resp = array('diamond' => $this->currentUser()->diamond);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', array_merge($resp, array(
            'products' => $products
        )));
    }

    function qrcodeLoginAction()
    {
        $token = $this->params('token');
        debug($token, $this->params());
        $access_token = \AccessTokens::validToken($token);

        if (!$access_token) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误, token错误或者过期');
        }

        $user = $this->currentUser();

        if (!$user || $user->isBlocked()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '没有对应的用户');
        }

        $confirm = $this->params('confirm');

        if ($confirm) {

            $access_token->status = AUTH_SUCCESS;
            $access_token->user_id = $user->id;
            $access_token->login_at = time();
            $access_token->save();

            return $this->renderJSON(ERROR_CODE_SUCCESS, '确认成功');
        }

        $auth_url = $this->getRoot() . 'api/users/qrcode_login?token=' . $token . '&confirm=1';

        return $this->renderJSON(ERROR_CODE_SUCCESS, '确认登录', ['auth_url' => $auth_url]);
    }

    //用户音乐列表
    function musicsAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $musics = $this->currentUser()->findMusics($page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $musics->toJson('musics', 'toSimpleJson'));
    }

    function isSignInAction()
    {
        $user = $this->currentUser();
        $gold = $user->signInGold();
        $tip = "恭喜您获得" . $gold . "金币";
        $message = "七天以上连续签到可每天获得320金币";
        if ($gold) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['sign_in_status' => USER_SIGN_IN_WAIT, 'tip' => $tip, 'message' => $message]);
        } else {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['sign_in_status' => USER_SIGN_IN_SUCCESS, 'tip' => '', 'message' => '']);
        }
    }

    function signInAction()
    {
        $user = $this->currentUser();
        $gold = $user->addSignInHistory();
        if ($gold) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '已签到');
        }
    }

    function hiCoinRankListAction()
    {
        $list_type = $this->params('list_type');
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

        if ($list_type != 'day' && $list_type != 'week' && $list_type != 'total') {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $user = $this->currentUser();
        $users = $user->findHiCoinRankList($list_type, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toRankListJson'));
    }

    function charmRankListAction()
    {
        $list_type = $this->params('list_type');
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

        if ($list_type != 'day' && $list_type != 'week' && $list_type != 'total') {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $users = \Users::findFieldRankList($list_type, 'charm', $page, $per_page);

        $res = $users->toJson('users', 'toRankListJson');

        $user = $this->currentUser();

        $res['current_rank'] = $user->myFieldRank($list_type, 'charm');
        $res['changed_rank'] = $user->myLastFieldRank($list_type, 'charm') - $res['current_rank'];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    function wealthRankListAction()
    {
        $list_type = $this->params('list_type');
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

        if ($list_type != 'day' && $list_type != 'week' && $list_type != 'total') {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $users = \Users::findFieldRankList($list_type, 'wealth', $page, $per_page);

        $res = $users->toJson('users', 'toRankListJson');

        $user = $this->currentUser();

        $res['current_rank'] = $user->myFieldRank($list_type, 'wealth');
        $res['changed_rank'] = $user->myLastFieldRank($list_type, 'wealth') - $res['current_rank'];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }
}