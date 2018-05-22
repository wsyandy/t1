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

        if ($error_code !== ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        if ($user->isBlocked()) {
            info("block_user_login", $user->sid);
            return $this->renderJSON(ERROR_CODE_FAIL, '账户异常');
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

        if ('bind_mobile' == $sms_type) {

            if ($this->currentUser()->mobile) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您已经绑定了手机号码');
            }

            if ($user) {
                return $this->renderJSON(ERROR_CODE_FAIL, '手机号码已绑定其他用户');
            }
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
                $current_user = $this->currentUser();
                info('当前用户ID', $current_user->id, '手机号', $current_user->mobile, '登录方式', $current_user->login_type);
                if (isPresent($current_user->mobile) || $current_user->isThirdLogin()) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '注册无效');
                }
                $opts = [
                    'mobile' => $mobile,
                    'product_channel_id' => $this->currentProductChannelId(),
                    'type' => 'register',
                    'current_user' => $current_user
                ];
                $is_have_sms_distribute_history = \SmsDistributeHistories::isUserForShare($opts);
                if (!$is_have_sms_distribute_history) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '手机号码未注册');
                }
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

    function fixLogin($third_name, $form)
    {

        if ($third_name != 'qq') {
            return null;
        }

        $third_unionid = fetch($form, 'third_unionid');
        if (!$third_unionid) {
            return null;
        }

        $third_id = $form['third_id'];
        $user = \Users::findFirstByThirdUnionid($this->currentProductChannel(), $third_id, $third_name);
        if ($user) {
            info($user->id, $form);
            $user->third_unionid = $third_unionid;
            $user->save();
            return $user;
        }

        return null;
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

        $third_name = $this->params('third_name'); // qq, weixin sinaweibo
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

        $third_unionid = isset($form['third_unionid']) && $form['third_unionid'] ? $form['third_unionid'] : $form['third_id'];
        $user = \Users::findFirstByThirdUnionid($this->currentProductChannel(), $third_unionid, $third_name);
        if (!$user) {
            $user = $this->fixLogin($third_name, $form);
        }

        $error_url = '';
        if (!$user) {

            list($error_code, $error_reason, $user) = \Users::thirdLogin($this->currentUser(), $device, $form, $context);
            if ($error_code != ERROR_CODE_SUCCESS) {
                return $this->renderJSON($error_code, $error_reason);
            }

            //第一次注册 跳转更新资料
            $error_url = 'app://users/update_info';
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

        //用户对象属性不改变
        $user = \Users::findFirstById($user->id);

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
        if (!$user->isBlocked()) {
            $user->user_status = USER_STATUS_LOGOUT;
        }
        $user->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '已退出', ['sid' => $user->sid]);
    }

    function updateAction()
    {
        $user = $this->currentUser();

        $hot_cache = \Users::getHotWriteCache();
        $cache_key = 'avatar_upload_cache_' . $this->currentUser()->id;

        $avatar_file = $this->file('avatar_file');
        if ($avatar_file) {
            $md5_val = md5_file($avatar_file);
            if (!$hot_cache->get($cache_key . '_' . $md5_val)) {
                $hot_cache->setex($cache_key . '_' . $md5_val, 600, time());
                $user->updateAvatar($avatar_file);
            } else {
                info('重复上传', $avatar_file);
            }
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

        $hot_cache = \Users::getHotWriteCache();
        $cache_key = 'avatar_upload_cache_' . $this->currentUser()->id;

        // 更新头像
        $avatar_file = $this->file('avatar_file');
        if ($avatar_file) {
            $md5_val = md5_file($avatar_file);
            if (!$hot_cache->get($cache_key . '_' . $md5_val)) {
                $hot_cache->setex($cache_key . '_' . $md5_val, 600, time());
                $user->updateAvatar($avatar_file);
            } else {
                info('重复上传', $avatar_file);
            }
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

        if ($this->currentUser()->isIdCardAuth()) {
            $detail_json['broadcaster_image_url'] = $this->getRoot() . "images/broadcaster.png";
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $detail_json);
    }

    function otherDetailAction()
    {
        $other_user = $this->otherUser();
        if ($other_user->id == SYSTEM_ID) {
            return $this->renderJSON(ERROR_CODE_FAIL, '系统小助手');
        }

        $current_user = $this->currentUser();
        $current_room_lock = false;
        //房间是否加锁
        $other_current_room = $other_user->current_room;
        if ($other_current_room) {
            $current_room_lock = $other_current_room->lock;
        }

        $detail_json = $other_user->toDetailJson();
        $detail_json['is_friend'] = $current_user->isFriend($other_user);
        $detail_json['is_follow'] = $current_user->isFollow($other_user);
        $detail_json['friend_note'] = $current_user->getFriendNote($other_user->id);
        $detail_json['current_room_lock'] = $current_room_lock;

        if (!$this->otherUser()->isActive()) {
            $detail_json['province_name'] = $current_user->province_name;
            $detail_json['city_name'] = $current_user->city_name;
        }

        if ($this->otherUser()->isIdCardAuth()) {
            $detail_json['broadcaster_image_url'] = $this->getRoot() . "images/broadcaster.png";
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
        $app_id = $this->currentProductChannel()->getImAppId();
        $signaling_key = $this->currentProductChannel()->getSignalingKey($this->currentUser()->id);

        $basic_json['signaling_key'] = $signaling_key;
        $basic_json['app_id'] = $app_id;
        $basic_json['im_password'] = $this->currentUser()->im_password;

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

        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $cond = [];
        if ($this->currentUser()->product_channel_id == 1) {
            $user_id = intval($this->params('user_id'));
            if ($user_id) {
                $cond = ['user_id' => intval($user_id)];
            }
        }

        $uid = intval($this->params('uid'));
        $nickname = null;

        $keyword = $this->params('keyword', null);

        if (!is_null($keyword)) {
            if (preg_match('/^[0-9]*$/', $keyword)) {
                $uid = intval($keyword);
                $nickname = $keyword;
            } else {
                $nickname = $keyword;
            }

            $cond['nickname'] = $nickname;

        }

        if ($uid && $uid != SYSTEM_ID) {
            $cond['uid'] = intval($uid);
        }

        $users = \Users::search($this->currentUser(), $page, $per_page, $cond);
        if (count($users)) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toSimpleJson'));
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
    }

    function searchByUidAction()
    {
        $uid = intval($this->params('uid'));
        $user = \Users::findFirstByUid($uid);
        if (!$user || $user->id == SYSTEM_ID || $user->isBlocked()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $user->toSimpleJson());
    }

    // 附近的人
    function nearbyAction()
    {

        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        if ($per_page > 15) {
            $per_page = 15;
        }

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
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['gold' => $gold]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '已签到', ['gold' => $gold]);
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

        if ($page == 1) {

            $user = $this->currentUser();
            $current_rank = $user->myFieldRank($list_type, 'charm');
            $last_rank = $user->myLastFieldRank($list_type, 'charm');
            $changed_rank = 0;

            if ($last_rank) {
                $changed_rank = $last_rank - $current_rank;
            }

            $res['current_rank'] = $current_rank <= 100 ? $current_rank : $current_rank + 1000; //大于100加1000
            $res['current_rank_text'] = $current_rank <= 100 ? $current_rank : '100+';
            $res['changed_rank'] = $changed_rank;

            debug($current_rank, $last_rank);

            $user->saveLastFieldRankList($list_type, 'charm', $current_rank);
        }

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

        $product_channel_id = $this->currentProductChannelId();

        $users = \Users::findFieldRankList($list_type, 'wealth', $page, $per_page);

        $res = $users->toJson('users', 'toRankListJson');

        if ($page == 1) {

            $user = $this->currentUser();
            $current_rank = $user->myFieldRank($list_type, 'wealth');
            $last_rank = $user->myLastFieldRank($list_type, 'wealth');
            $changed_rank = 0;

            if ($last_rank) {
                $changed_rank = $last_rank - $current_rank;
            }

            $res['current_rank'] = $current_rank <= 100 ? $current_rank : $current_rank + 1000; //大于100加1000
            $res['changed_rank'] = $changed_rank;
            $res['current_rank_text'] = $current_rank <= 100 ? $current_rank : '100+';

            debug($current_rank, $last_rank);

            $user->saveLastFieldRankList($list_type, 'wealth', $current_rank);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    //添加好友备注
    function addFriendNoteAction()
    {
        $friend_note = $this->params('friend_note');

        if (isBlank($friend_note)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        if ($friend_note && mb_strlen($friend_note) > 10) {
            return $this->renderJSON(ERROR_CODE_FAIL, '备注字数过长');
        }

        $user = $this->currentUser();

        $other_user = $this->otherUser();

        if (!$user->isFriend($other_user)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '只能给好友加备注');
        }

        $user->addFriendNote($other_user->id, $friend_note);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function recommendAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 12);

        if ($per_page > 12) {
            $per_page = 12;
        }

        if (isDevelopmentEnv()) {

            $user_ids = \Users::findUserCharmAndWealthRank($page, $per_page);
            if (empty($user_ids)) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            }

            $users = \Users::findByIds($user_ids);

        } else {
            $users = $this->currentUser()->nearby($page, $per_page);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toSimpleJson'));

    }

    function bindMobileAction()
    {
        if ($this->request->isPost()) {

            $mobile = $this->params('mobile');
            $auth_code = $this->params('auth_code');
            $sms_token = $this->params('sms_token');

            if (!$auth_code || !$sms_token) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            if (!isMobile($mobile)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '手机号码不正确');
            }

            $user = \Users::findFirstByMobile($this->currentProductChannel(), $mobile);

            if ($user) {
                return $this->renderJSON(ERROR_CODE_FAIL, '手机号码已绑定其他用户');
            }

            $context = $this->context();

            list($error_code, $error_reason) = \SmsHistories::checkAuthCode($this->currentProductChannel(),
                $mobile, $auth_code, $sms_token, $context);

            if ($error_code != ERROR_CODE_SUCCESS) {
                return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
            }

            list($error_code, $error_reason) = $this->currentUser()->bindMobile($mobile);

            if ($error_code != ERROR_CODE_SUCCESS) {
                return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '绑定成功');
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '非法访问!');
        }
    }
}