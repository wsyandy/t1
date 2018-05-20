<?php


class Users extends BaseModel
{
    use UserEnumerations;
    use UserAttrs;
    use UserAbilities;
    use UserWakeup;

    /**
     * @type ProductChannels
     */
    private $_product_channel;
    /**
     * @type Devices
     */
    private $_device;
    /**
     * @type Partners
     */
    private $_partner;
    /**
     * @type Provinces
     */
    private $_province;
    /**
     * @type Cities
     */
    private $_city;
    /**
     * @type Provinces
     */
    private $_geo_province;
    /**
     * @type Cities
     */
    private $_geo_city;
    /**
     * @type Provinces
     */
    private $_ip_province;
    /**
     * @type Cities
     */
    private $_ip_city;
    /**
     * @type Rooms
     */
    private $_current_room;
    /**
     * @type Rooms
     */
    private $_room;
    /**
     * @type RoomSeats
     */
    private $_current_room_seat;
    /**
     * @type Unions
     */
    private $_union;
    /**
     * @type Countries
     */
    private $_country;
    /**
     * @type string
     */
    private $_current_room_channel_name;

    //好友状态 1已添加,2等待验证，3等待接受
    public $friend_status;

    //是否已关注 true:已关注,false:未关注
    public $followed;

    //是否可以发公屏消息 true可以,false不可以
    public $user_chat;

    //申请状态 1已同意,-1拒绝，0等待,
    public $apply_status;

    // 经纬度距离
    public $distance;


    static function getCacheEndpoint($id)
    {
        return self::config('user_db_endpoints');
    }

    static function getUserDb()
    {
        $endpoint = self::config('user_db_endpoints');
        return XRedis::getInstance($endpoint);
    }

    function beforeCreate()
    {
        $this->uid = $this->generateUid();

        $this->user_status = USER_STATUS_ON;
        if (!$this->user_type) {
            $this->user_type = USER_TYPE_ACTIVE;
        }

        if (($this->mobile && $this->isMobileLogin()) || ($this->third_unionid && $this->isThirdLogin()) || ($this->login_name && $this->isEmailLogin())) {
            $this->register_at = time();
            $this->last_at = time();
            info('new_user_register', $this->mobile, $this->third_unionid);
        }
    }

    function afterCreate()
    {
        if ($this->isActive()) {
            if ($this->ip) {
                self::delay(1)->asyncUpdateIpLocation($this->id);
            }
            if ($this->latitude && $this->longitude) {
                self::delay(1)->asyncUpdateGeoLocation($this->id);
            }

            if ($this->register_at) {
                $this->registerStat();
                $this->createEmUser();
            }
        }

        if (!$this->uid) {
            $this->uid = $this->generateUid();
            $this->update();
            info('Exce no_uid', $this->id);
        }
    }

    function beforeUpdate()
    {
        if ($this->hasChanged('mobile') && $this->mobile && $this->isMobileLogin() && $this->register_at < 1) {
            $this->register_at = time();
            $this->last_at = time();
        }

        if ($this->hasChanged('third_unionid') && $this->third_unionid && $this->isThirdLogin() && $this->register_at < 1) {
            $this->register_at = time();
            $this->last_at = time();
        }

        if ($this->hasChanged('login_name') && $this->login_name && $this->isEmailLogin() && $this->register_at < 1) {
            $this->register_at = time();
            $this->last_at = time();
        }
    }

    function afterUpdate()
    {
        if ($this->hasChanged('device_id') && $this->device) {
            $this->calDeviceRegisterNum();
        }

        if ($this->hasChanged('last_at')) {
            $this->updateLastAt();
        }

        if ($this->hasChanged('ip') && $this->ip) {
            self::delay(1)->asyncUpdateIpLocation($this->id);
        }

        if (($this->hasChanged('latitude') && $this->latitude) || ($this->hasChanged('longitude') && $this->longitude)) {
            self::delay(1)->asyncUpdateGeoLocation($this->id);
        }

        if ($this->hasChanged('register_at') && $this->register_at) {
            $this->registerStat();
            $this->createEmUser();

            if ($this->isMobileLogin()) {
                $this->bindMobileSendGold();
            }
        }

        if ($this->hasChanged('user_status') && USER_STATUS_LOGOUT == $this->user_status && $this->current_room_id) {
            $this->current_room->exitRoom($this);
        }

        if ($this->hasChanged('user_role_at') && $this->isActive()) {
            $this->statRoomTime();
        }

        if ($this->hasChanged('union_id') || $this->hasChanged('union_type')) {
            $this->bindRoomUnionId();
        }

        if ($this->hasChanged('uid')) {
            $this->clearUidInGoodNoList();
        }
    }

    function clearUidInGoodNoList()
    {
        $user_db = Users::getUserDb();
        $user_db->zrem('user_not_good_no_uid_list', $this->uid);
        $user_db->zrem('user_good_no_uid_list', $this->uid);
        $user_db->zrem('select_good_no_list', $this->uid);
    }

    function getPushContext()
    {
        return $this->product_channel->getPushContext($this->platform);
    }

    function getPushReceiverContext()
    {
        return ['id' => $this->id, 'platform' => $this->platform, 'push_token' => $this->push_token, 'push_type' => $this->push_type];
    }

    /**
     * 产生 UID
     */
    function generateUid()
    {

        for ($i = 0; $i < 10; $i++) {
            $uid = $this->randUid();
            if (!$uid) {
                continue;
            }
            $lock_key = 'lock_generate_user_uid_' . $uid;
            $hot_cache = self::getHotWriteCache();
            if (!$hot_cache->setnx($lock_key, $uid)) {
                debug('加锁失败', $lock_key);
                continue;
            }
            $hot_cache->expire($lock_key, 3);
            debug('加锁成功', $lock_key);

            return $uid;
        }

        return $this->id;
    }

    function randUid()
    {

        $user_db = Users::getUserDb();
        $not_good_no_uid = 'user_not_good_no_uid_list';
        $offset = mt_rand(0, 100000);
        $uid = $user_db->zrange($not_good_no_uid, $offset, $offset);
        $uid = current($uid);
        if (!$user_db->zrem($not_good_no_uid, $uid)) {
            $user_db->zrem($not_good_no_uid, $uid);
        }

        return $uid;
    }


    /**
     * 产生 SID
     * @return string
     */
    function generateSid($seg)
    {
        $src = $this->id . uniqid(mt_rand()) . microtime();

        $src = $this->id . $seg . md5($src);
        $src .= calculateSum($src);

        return $src;
    }

    // 是否登录
    function isLogin()
    {
        if ($this->isClientPlatform()) {
            return $this->sid && preg_match('/^\d+s/', $this->sid) && $this->user_status == USER_STATUS_ON;
        }

        return !!$this->mobile;
    }

    function bindRoomUnionId()
    {
        $room = $this->room;

        if ($room) {
            $room->union_id = $this->union_id;
            $room->union_type = $this->union_type;
            $room->update();
        }

        if ($this->union_id) {
            UnionHistories::delay()->createRecord($this->id, $this->union_id);
        }
    }

    //统计用户在房间时间
    function statRoomTime()
    {
        $old_user_role_at = $this->was('user_role_at');
        $user_role_at = $this->user_role_at;
        $duration = $user_role_at - $old_user_role_at;
        $old_current_room_seat_id = $this->was('current_room_seat_id');
        $db = Users::getUserDb();
        $action = null;
        $old_user_role = $this->was('user_role');
        $user_role = $this->user_role;


        if ($this->hasChanged('user_role')) {
            switch ($old_user_role) {
                case USER_ROLE_NO:
                    break;
                case USER_ROLE_AUDIENCE:
                    $action = "audience";
                    break;
                case USER_ROLE_BROADCASTER:
                    $action = "broadcaster";
                    break;
                case USER_ROLE_HOST_BROADCASTER:
                    $action = "host_broadcaster";
                    break;
                case USER_ROLE_MANAGER:
                    //退出房间 管理员角色变化
                    if ($this->hasChanged('current_room_seat_id') && $old_current_room_seat_id) {
                        $action = "broadcaster";
                    } else {
                        $action = "audience";
                    }
                    break;
            }

        } elseif (USER_ROLE_MANAGER == $user_role) {

            //上麦下麦时角色发生变化
            if ($this->hasChanged('current_room_seat_id') && $old_current_room_seat_id) {
                $action = "broadcaster";
            } else {
                $action = "audience";
            }
        }

        if ($action) {
            $db->zincrby(Users::generateStatRoomTimeKey($action), $duration, $this->id);
            $db->zincrby(Users::generateStatRoomTimeKey("total"), $duration, $this->id);

            $current_room_id = $this->current_room_id;

            if (!$current_room_id) {
                $current_room_id = $this->was('current_room_id');
            }

            if (!$this->isSilent()) {
                Rooms::delay()->statDayUserTime($action, $current_room_id, $duration);
            }
        }

        info($old_user_role, $user_role, $duration, $action, $old_current_room_seat_id, $this->sid);
    }

    static function generateStatRoomTimeKey($action, $date = null)
    {
        if (is_null($date)) {
            $date = beginOfDay();
        }

        return "user_room_" . $action . "_time_" . $date;
    }

    function calDeviceRegisterNum()
    {
        //更新设备注册数量
        $reg_num = Users::count(['conditions' => 'device_id = :device_id:', 'bind' => ['device_id' => $this->device_id]]);
        $this->device->reg_num = $reg_num;
        $this->device->user_id = $this->id;
        $this->device->update();

        $old_device_id = $this->was('device_id');
        $old_device = Devices::findFirstById($old_device_id);
        if ($old_device) {
            $reg_num = Users::count(['conditions' => 'device_id = :device_id:', 'bind' => ['device_id' => $old_device->id]]);
            $old_device->reg_num = $reg_num;
            $old_device->update();
        }
    }

    function updateLastAt()
    {
        $last_at = $this->was('last_at');
        if (date('YmdH', $last_at) != date('YmdH', $this->last_at)) {
            $attrs = $this->getStatAttrs();
            // 统计活跃
            // 做手机号剔重计算活跃手机号数
            $attrs['mobile'] = $this->mobile;
            $attrs['third_unionid'] = $this->third_unionid;
            \Stats::delay()->record('user', 'active_user', $attrs);
        }

        // 重置任务
        if (date('Ymd', $last_at) != date('Ymd', $this->last_at)) {
            $this->deleteExecutedOfflineTaskIds();
        }
    }

    static function registerForClientByMobile($current_user, $device, $mobile, $context = [])
    {

        if (isBlank($mobile)) {
            return [ERROR_CODE_FAIL, '手机号错误', null];
        }

        info('false_device', $device->id, 'can_register', $device->can_register);
        if (!$device || (!$device->can_register && isProduction())) {
            info('false_device', $current_user->product_channel->code, $mobile, $context);
            return [ERROR_CODE_FAIL, '设备错误!!', null];
        }

        $product_channel = $current_user->product_channel;
        $exist_user = \Users::findFirstByMobile($product_channel, $mobile);
        $sms_distribute_history = \SmsDistributeHistories::findFirstByMobile($product_channel, $mobile);
        if ($exist_user || $sms_distribute_history) {
            return [ERROR_CODE_FAIL, '用户已注册', null];
        }

        $user = $current_user;
        //换个手机号注册，重新生成用户
        if ($current_user->mobile && $current_user->mobile != $mobile || $current_user->login_type && !$current_user->isMobileLogin()) {
            $user = Users::registerForClientByDevice($device, true);
            info('换个手机号注册', $user->id, $user->third_unionid, $context);
        }

        if (!$user) {
            return [ERROR_CODE_FAIL, '注册失败!', null];
        }

        //$user->checkRegisterFr($device, $mobile);

        $fr = $device->fr;
        if (!$fr) {
            $fr = fetch($context, 'fr');
            $user->fr = $fr;
            $device->fr = $fr;
        }

        $partner = \Partners::findFirstByFrHotCache($fr);
        if ($partner) {
            $user->partner_id = $partner->id;
            $device->partner_id = $partner->id;
        }

        $device->user_id = $user->id;
        $device->save();

        $user->manufacturer = $device->manufacturer;
        $user->platform = $device->platform;
        $user->device_id = $device->id;
        $user->device = $device;
        $user->device_no = $device->device_no;
        $user->mobile = $mobile;
        $user->login_type = USER_LOGIN_TYPE_MOBILE;

        $password = fetch($context, 'password');
        if ($password) {
            $user->password = md5($password);
        }
        if (isBlank($user->login_name)) {
            $user->login_name = md5(uuid()) . '@app.com';
        }
        if (isBlank($user->nickname)) {
            $user->nickname = $user->getMaskedMobile();
        }
        $user->user_type = USER_TYPE_ACTIVE;
        $user->save();

        if ($mobile) {
            $user->sid = $user->generateSid('s');
            $user->update();
        }

        info($user->id, $user->mobile, $user->fr, $user->partner_id, date('Ymd H:i:s', $user->created_at), date('Ymd H:i:s', $user->register_at));

        return [ERROR_CODE_SUCCESS, '', $user];
    }

    //根据设备注册
    static function registerForClientByDevice($device, $is_force = false)
    {
        if ($device->isBlocked()) {
            info("block_device_active", $device->id, $device->device_no);
            return null;
        }

        // 重复激活
        // dno重复，存在bug
        $user = $device->user;
        if (!$user || $is_force || $user->isSilent()) {

            $fields = ['product_channel_id', 'platform', 'platform_version', 'version_code', 'version_name',
                'api_version', 'device_no', 'fr', 'partner_id', 'manufacturer', 'ip', 'latitude', 'longitude',
                'lang', 'push_token'];

            $user = new \Users();
            $user->login_name = md5(uuid()) . '@app.com';
            $user->device = $device;
            $user->device_id = $device->id;
            $user->device_no = $device->device_no;
            $user->speaker = true;
            $user->microphone = true;

            foreach ($fields as $field) {
                $user->$field = $device->$field;
            }

            $user->save();
        } else {
            if ($user->mobile) {
                info('用户已注册,有手机号', $device->id, $user->id, $user->mobile);
            } else {
                info('用户已激活', $device->id, $user->id);
            }
        }

        $user->manufacturer = $device->manufacturer;
        $user->platform = $device->platform;
        $user->device = $device;
        $user->device_id = $device->id;
        $user->device_no = $device->device_no;
        $user->sid = $user->generateSid('d.');
        $user->save();

        $device->user_id = $user->id;
        $device->update();

        return $user;
    }

    static function registerForClientByLoginName($current_user, $device, $login_name, $context = [])
    {

        if (isBlank($login_name)) {
            return [ERROR_CODE_FAIL, '请输入注册邮箱', null];
        }

        if (!filter_var($login_name, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)) {
            return [ERROR_CODE_FAIL, '邮箱格式错误', null];
        }

        info('false_device', $device->id, 'can_register', $device->can_register);
        if (!$device || (!$device->can_register && isProduction())) {
            info('false_device', $current_user->product_channel->code, $login_name, $context);
            return [ERROR_CODE_FAIL, '设备错误!!', null];
        }

        $product_channel = $current_user->product_channel;
        $exist_user = \Users::findFirstByLoginName($product_channel, $login_name);

        if ($exist_user) {
            return [ERROR_CODE_FAIL, '用户已注册', null];
        }

        $user = $current_user;
        //换个手机号注册，重新生成用户 其他注册方式校验login_name
        if ($current_user->login_name && $current_user->login_name != $login_name || $current_user->login_type && !$current_user->isEmailLogin()) {
            $user = Users::registerForClientByDevice($device, true);
            info('换个手机号注册', $user->id, $user->mobile, $user->login_name, $user->third_unionid, $context);
        }

        if (!$user) {
            return [ERROR_CODE_FAIL, '注册失败!', null];
        }

        $fr = $device->fr;

        if (!$fr) {
            $fr = fetch($context, 'fr');
            $user->fr = $fr;
            $device->fr = $fr;
        }

        $partner = \Partners::findFirstByFrHotCache($fr);

        if ($partner) {
            $user->partner_id = $partner->id;
            $device->partner_id = $partner->id;
        }

        $device->user_id = $user->id;
        $device->save();

        $user->manufacturer = $device->manufacturer;
        $user->platform = $device->platform;
        $user->device_id = $device->id;
        $user->device = $device;
        $user->device_no = $device->device_no;
        $user->login_name = $login_name;

        $password = fetch($context, 'password');
        if ($password) {
            $user->password = md5($password);
        }

        if (isBlank($user->login_name)) {
            $user->login_name = md5(uuid()) . '@app.com';
        }

        if (isBlank($user->nickname)) {
            $user->nickname = $user->login_name;
        }

        $user->user_type = USER_TYPE_ACTIVE;
        $user->login_type = USER_LOGIN_TYPE_MOBILE;
        $user->save();

        if ($login_name) {
            $user->sid = $user->generateSid('s');
            $user->update();
        }

        info($user->id, $user->login_name, $user->fr, $user->partner_id, date('Ymd H:i:s', $user->created_at), date('Ymd H:i:s', $user->register_at));

        return [ERROR_CODE_SUCCESS, '', $user];
    }

    // 多设备登录
    function clientLogin($context, $device = null)
    {

        if (!$device) {
            $device = \Devices::findFirst([
                'conditions' => 'device_no=:device_no: and product_channel_id=:product_channel_id:',
                'bind' => ['device_no' => $context['device_no'], 'product_channel_id' => $this->product_channel_id],
                'order' => 'id desc']);
        }

        if (!$device) {
            info('Exce false_device', $this->id, $this->mobile, $context);
            return [ERROR_CODE_FAIL, '设备错误!!!'];
        }

        foreach (['ip', 'password', 'platform', 'version_name', 'version_code', 'login_type'] as $key) {

            $val = fetch($context, $key);
            if ($val) {
                if ('password' == $key) {
                    $val = md5($val);
                }
                $this->$key = $val;
            }
        }

        // 设备不一致，发送强行下线推送
        if ($device->id != $this->device_id && !isBlank($this->push_token)) {

            //切换账号登录时如果用户在房间就退出房间
            if ($this->isInAnyRoom()) {
                $current_room = $this->current_room;

                info("change_device_exit_room", $this->current_room->id, $this->id);

                if ($current_room) {
                    $current_room->exitRoom($this);
                }
            }

            $old_device = \Devices::findFirstById($this->device_id);
            if ($old_device) {

                $payload = ['model' => 'user', 'user' => ['action' => 'logout', 'sid' => $this->generateSid('d.')]];
                $client_url = 'app://users/logout';
                $receiver_context = $this->getPushReceiverContext();
                $push_data = ['title' => $this->product_channel->name, 'body' => '您已在其他设备登陆,本次登陆已注销!', 'payload' => $payload,
                    'badge' => null, 'offline' => true, 'client_url' => $client_url, 'icon_url' => $this->product_channel->avatar_url];

                info('多设备登录 push_logout', $this->id, $device->id, $this->device_id, $push_data);

                \Pushers::delay()->push($this->getPushContext(), $receiver_context, $push_data);
            } else {
                warn('Exce', '设备不一致', $this->id, $device->id, $this->device_id);
            }
        }

        $this->user_status = USER_STATUS_ON;
        $this->sid = $this->generateSid('s');
        $this->device_id = $device->id;
        $this->device_no = $device->device_no;
        $this->push_token = $device->push_token;
        $this->update();

        $device->user_id = $this->id;
        $device->save();

        return [ERROR_CODE_SUCCESS, '登陆成功'];
    }

    static function uploadWeixinAvatar($user_id, $headimgurl)
    {
        if (!$headimgurl) {
            return;
        }

        $user = \Users::findFirstById($user_id);

        if ($user) {
            $avatar_file = APP_ROOT . 'temp/' . md5(uniqid(mt_rand())) . '.jpg';

            try {
                httpSave($headimgurl, $avatar_file);
                $user->updateAvatar($avatar_file);
                unlink($avatar_file);
            } catch (\Exception $e) {
                info("Exce", $e->getMessage());
            }
        }
    }

    /**
     * 上传头像
     * @param $filename
     * @return bool
     */
    function updateAvatar($filename)
    {
        $old_avatar = $this->avatar;
        $dest_filename = APP_NAME . '/users/avatar/' . date('YmdH') . uniqid() . '.jpg';
        $res = \StoreFile::upload($filename, $dest_filename);

        if ($res) {
            $this->avatar = $dest_filename;
            $this->avatar_status = AUTH_SUCCESS;
            if ($this->update()) {
                //  删除老头像
                if ($old_avatar) {
                    \StoreFile::delete($old_avatar);
                }
            }
        }
    }

    static function registerUpdateAvatar($id, $avatar_url)
    {
        debug($id, $avatar_url);

        $user = \Users::findFirstById($id);
        $avatar_file = 'temp/' . uniqid() . '.jpg';
        if (httpSave($avatar_url, $avatar_file)) {
            $user->updateAvatar($avatar_file);
            unlink($avatar_file);
        }
    }

    function updatePushToken($device)
    {
        if ($device && $device->push_token) {

            if ($this->device_id != $device->id) {
                $this->device_id = $device->id;
            }
            $this->push_token = $device->push_token;
            $this->update();

            // 更新最后登录的用户
            $device->user_id = $this->id;
            $device->update();

            //在同一台手机上，先后登录A,B用户，防止B用户接收到A用户的消息推送，（pushToList没有指定用户ID，所以不能客户端无法判断是发给哪个用户的）
            $other_users = \Users::find([
                    'conditions' => 'id != :id: and device_id = :device_id: and product_channel_id = :product_channel_id:',
                    'bind' => ['id' => $this->id, 'device_id' => $this->device_id, 'product_channel_id' => $this->product_channel_id]]
            );

            foreach ($other_users as $other_user) {
                info('false_push_token', $this->id, $other_user->id, $this->device_id);
                $other_user->push_token = '';
                $other_user->update();
            }
        }
    }

    //微信
    static function findFirstByOpenid($product_channel, $openid)
    {
        $conds['conditions'] = 'openid=:openid: and product_channel_id=:product_channel_id: and user_status !=:user_status:';
        $conds['bind'] = ['openid' => $openid, 'product_channel_id' => $product_channel->id, 'user_status' => USER_STATUS_OFF];
        $conds['order'] = 'id desc';
        $user = Users::findFirst($conds);
        return $user;
    }

    static function findFirstByMobile($product_channel, $mobile)
    {
        $user = \Users::findFirst([
            'conditions' => 'product_channel_id = :product_channel_id: and mobile=:mobile:',
            'bind' => ['product_channel_id' => $product_channel->id, 'mobile' => $mobile],
            'order' => 'id desc'
        ]);

        return $user;
    }

    static function findFirstByLoginName($product_channel, $login_name)
    {
        $user = \Users::findFirst([
            'conditions' => 'product_channel_id = :product_channel_id: and login_name=:login_name:',
            'bind' => ['product_channel_id' => $product_channel->id, 'login_name' => $login_name],
            'order' => 'id desc'
        ]);

        return $user;
    }

    public static function registerForTouch($product_channel, $opts = [])
    {

        $user = new \Users();
        $user->password = uniqid();
        $user->product_channel_id = $product_channel->id;
        $user->user_type = USER_TYPE_ACTIVE;
        $user->login_name = randStr(20) . '@touch.com';
        $user->platform = 'touch_unknow';
        foreach ($opts as $k => $v) {
            if ($v) {
                $user->$k = $v;
            }
        }

        $fr = fetch($opts, 'fr');
        if (!$fr) {
            $fr = $product_channel->touch_fr;
        }
        if ($fr) {
            $user->fr = $fr;
            $partner = \Partners::findFirstByFrHotCache($fr);
            if ($partner) {
                $user->partner_id = $partner->id;
            }
        }

        $user->save();

        $user->sid = $user->generateSid('d.');
        $user->update();

        info('touch_register:新注册用户=', $user->id, $opts);

        \Stats::delay()->record('user', 'touch_active', $user->getStatAttrs());
        return $user;
    }

    public static function registerForWeb($product_channel, $opts = [])
    {

        $user = new \Users();
        $user->password = uniqid();
        $user->product_channel_id = $product_channel->id;
        $user->user_type = USER_TYPE_ACTIVE;
        $user->login_name = randStr(20) . '@web.com';
        $user->platform = 'web';
        foreach ($opts as $k => $v) {
            if ($v) {
                $user->$k = $v;
            }
        }

        $fr = fetch($opts, 'fr');
        if (!$fr) {
            $fr = $product_channel->web_fr;
        }
        if ($fr) {
            $user->fr = $fr;
            $partner = \Partners::findFirstByFrHotCache($fr);
            if ($partner) {
                $user->partner_id = $partner->id;
            }
        }

        $user->save();

        $user->sid = $user->generateSid('d.');
        $user->update();

        //info('touch_register:新注册用户=', $user->id, $opts);

        \Stats::delay()->record('user', 'web_active', $user->getStatAttrs());
        return $user;
    }

    public static function registerByOpenid($product_channel, $openid, $info = null)
    {
        if (isBlank($openid)) {
            return null;
        }

        $user = new Users();
        $user->id = 0;
        $user->openid = $openid;
        $session = self::di('session');
        $session->set('openid', $openid);

        return $user;

        $user = self::findFirstByOpenid($product_channel, $openid);
        //有用户
        if ($user) {
            return $user;
        }

        //拉取微信用户信息
        $weixin_event = new \WeixinEvents($product_channel);
        if (empty($info) || !isset($info['subscribe'])) {
            $tmp_info = $weixin_event->getUserInfo($openid);
            if (is_array($info) && is_array($tmp_info)) {
                $info = array_merge($info, $tmp_info);
            } else {
                $info = $tmp_info;
            }
        }

        $province_id = 0;
        $province_name = fetch($info, 'province');
        if ($province_name) {
            $province = Provinces::findFirstByName($province_name);
            if ($province) {
                $province_id = $province->id;
            }
        }

        $city_id = 0;
        $city_name = fetch($info, 'city');
        if ($city_name) {
            $city = Cities::findFirstByName($city_name);
            if ($city) {
                $city_id = $city->id;
            }
        }

        $fr = fetch($info, 'fr');
        if (!$fr) {
            $fr = $product_channel->weixin_fr;
        }
        $partner = \Partners::findFirstByFrHotCache($fr);


        $nickname = fetch($info, 'nickname');
        $login_name = $openid . '@weixin.com';

        $user = new \Users();
        $user->sex = fetch($info, 'sex', 1);
        $user->nickname = $nickname;
        $user->password = uniqid();
        $user->product_channel_id = $product_channel->id;
        $user->user_type = USER_TYPE_ACTIVE;
        $user->login_name = $login_name;
        $user->province_id = $province_id;
        $user->city_id = $city_id;
        $user->openid = $openid;
        $user->subscribe = fetch($info, 'subscribe', USER_UNSUBSCRIBE);
        $user->fr = $fr;
        $user->platform = 'weixin_unknow';

        if ($partner) {
            $user->partner_id = $partner->id;
        }

        $user->save();

        $user->sid = $user->generateSid('d.');
        $user->update();

        //头像
        if (isset($info['headimgurl']) && $info['headimgurl']) {
            self::delay()->registerUpdateAvatar($user->id, $info['headimgurl']);
        }

        return $user;
    }

    public function getStatAttrs()
    {

        $stat_keys = ['platform', 'version_code', 'product_channel_id', 'id', 'province_id', 'sex', 'ip', 'partner_id', 'register_at'];
        $hash = [];
        foreach ($stat_keys as $key) {
            $hash[$key] = $this->$key;
        }

        $hash['created_at'] = intval($this->created_at);
        $hash['stat_at'] = time();

        return $hash;
    }

    public function onlineFresh($opts = [])
    {

        $fresh_attrs = [];
        foreach ($opts as $k => $v) {
            if ($this->hasProperty($k)) {
                if ($v && $this->$k !== $v) {
                    $fresh_attrs[$k] = $v;
                }
            }
        }

        // 强制刷新
        $last_at = fetch($opts, 'last_at');
        if ($last_at) {
            $fresh_attrs['last_at'] = time();
        }

        // 定期更新
        if (time() - $this->last_at > 60 || date('Ymd') != date('Ymd', $this->last_at)) {
            $fresh_attrs['last_at'] = time();
            // 记录活跃时间
            $this->addActiveList();

            // 线上提醒
            $this->pushOnlineRemindMessage();

            //次日留存
            $this->appStart();

            $send_gift_data = $this->hasOfflineGift();
            if ($send_gift_data) {
                self::delay()->sendOfflineSendGift($this->id, $send_gift_data);
            }

            $this->updateGeoHashRank();
        }

        debug($this->id, $fresh_attrs);

        if ($fresh_attrs) {
            foreach ($fresh_attrs as $k => $v) {
                $this->$k = $v;
            }
            $this->update();
        }
    }

    function addActiveList()
    {

        $hot_cache = self::getHotWriteCache();
        $group_key = '';
        if ($this->isClientPlatform()) {
            $group_key = 'client_users_active_group_' . $this->product_channel_id;
        }
        if ($this->isWxPlatform()) {
            $group_key = 'weixin_users_active_group_' . $this->product_channel_id;
        }
        if ($this->isTouchPlatform()) {
            $group_key = 'touch_users_active_group_' . $this->product_channel_id;
        }
        if ($this->isWebPlatform()) {
            $group_key = 'web_users_active_group_' . $this->product_channel_id;
        }

        if ($group_key) {
            $hot_cache->zadd($group_key, time(), $this->id);
            $hot_cache->expire($group_key, 60 * 60 * 24 * 30);
        }

        // 实时在线，以十分钟为单位
        $begin_of_hour = beginOfHour();
        $interval = $begin_of_hour + intval(date('i') / 10) * 10 * 60;
        $online_key = 'online_user_list_' . date('YmdHi', $interval);
        $stat_db = Stats::getStatDb();
        $stat_db->zadd($online_key, time(), $this->id);

    }

    //是否需要更新经纬度
    function needUpdateGeo()
    {
        //有经纬度或者用户未注册不更新经纬度
        if ($this->latitude > 1 && $this->longitude > 1 || !$this->mobile) {
            debug("have geo", $this->latitude, $this->longitude);
            return false;
        }

        $hot_cache = self::getHotWriteCache();
        $key = 'update_geo_location_user_id_' . $this->id;
        $location_update = $hot_cache->get($key);
        if (!$location_update) {
            return true;
        }

        debug('do not need update geo', $this->id);
        return false;
    }

    function delGeoHashRank()
    {
        if (!$this->geo_hash || $this->avatar_status != AUTH_SUCCESS) {
            return;
        }

        //{"top":"wtw33","bottom":"wtw2c","right":"wtw34","left":"wtw30","topleft":"wtw32","topright":"wtw36","bottomright":"wtw2f","bottomleft":"wtw2b","0":"wtw31"}
        $geohash = new \geo\GeoHash();
        $prefix = substr($this->geo_hash, 0, 5);
        $neighbors = $geohash->neighbors($prefix);
        $cache_key = 'user_geo_hash_5' . $prefix . '_' . fetch($neighbors, 'top') . '_' . fetch($neighbors, 'bottom')
            . '_' . fetch($neighbors, 'right') . '_' . fetch($neighbors, 'left') . '_' . fetch($neighbors, 'topleft')
            . '_' . fetch($neighbors, 'topright') . '_' . fetch($neighbors, 'bottomright') . '_' . fetch($neighbors, 'bottomleft');

        $user_db = Users::getUserDb();
        $user_db->zrem($cache_key, $this->id);
        info($cache_key, $this->id);

        $prefix = substr($this->geo_hash, 0, 6);
        $neighbors = $geohash->neighbors($prefix);
        $cache_key = 'user_geo_hash_6' . $prefix . '_' . fetch($neighbors, 'top') . '_' . fetch($neighbors, 'bottom')
            . '_' . fetch($neighbors, 'right') . '_' . fetch($neighbors, 'left') . '_' . fetch($neighbors, 'topleft')
            . '_' . fetch($neighbors, 'topright') . '_' . fetch($neighbors, 'bottomright') . '_' . fetch($neighbors, 'bottomleft');

        $user_db->zrem($cache_key, $this->id);
        info($cache_key, $this->id);
    }

    function updateGeoHashRank()
    {
        if (!$this->geo_hash || $this->avatar_status != AUTH_SUCCESS || $this->isCompanyUser() && isProduction()) {
            return;
        }

        $block_near_by_user_ids = Users::getBlockedNearbyUserIds();
        if (count($block_near_by_user_ids) > 0 && in_array($this->id, $block_near_by_user_ids)) {
            return;
        }

        //{"top":"wtw33","bottom":"wtw2c","right":"wtw34","left":"wtw30","topleft":"wtw32","topright":"wtw36","bottomright":"wtw2f","bottomleft":"wtw2b","0":"wtw31"}
        $geohash = new \geo\GeoHash();
        $prefix = substr($this->geo_hash, 0, 5);
        $neighbors = $geohash->neighbors($prefix);
        $cache_key = 'user_geo_hash_5' . $prefix . '_' . fetch($neighbors, 'top') . '_' . fetch($neighbors, 'bottom')
            . '_' . fetch($neighbors, 'right') . '_' . fetch($neighbors, 'left') . '_' . fetch($neighbors, 'topleft')
            . '_' . fetch($neighbors, 'topright') . '_' . fetch($neighbors, 'bottomright') . '_' . fetch($neighbors, 'bottomleft');

        $user_db = Users::getUserDb();
        $user_db->zadd($cache_key, time(), $this->id);
        info($cache_key, $this->id);

        $prefix = substr($this->geo_hash, 0, 6);
        $neighbors = $geohash->neighbors($prefix);
        $cache_key = 'user_geo_hash_6' . $prefix . '_' . fetch($neighbors, 'top') . '_' . fetch($neighbors, 'bottom')
            . '_' . fetch($neighbors, 'right') . '_' . fetch($neighbors, 'left') . '_' . fetch($neighbors, 'topleft')
            . '_' . fetch($neighbors, 'topright') . '_' . fetch($neighbors, 'bottomright') . '_' . fetch($neighbors, 'bottomleft');

        $user_db->zadd($cache_key, time(), $this->id);
        info($cache_key, $this->id);
    }

    static function asyncUpdateGeoLocation($user_id)
    {

        $user = self::findFirstById($user_id);
        $latitude = $user->latitude;
        $longitude = $user->longitude;

        if (!$latitude && !$longitude) {
            debug('false update geo_location', $user->id);
            return;
        }

        $latitude = $latitude / 10000;
        $longitude = $longitude / 10000;
        $result = \Location::gdAddress($latitude, $longitude);
        debug($user->id, $result);

        if ($result && isset($result[0])) {
            $province = \Provinces::findFirstByName($result[0]);
            if ($province) {
                $user->geo_province_id = $province->id;
                $user->geo_city_id = 0;
            } else {
                info('false province', $result);
            }

            if (isset($result[1])) {
                $city = \Cities::findFirstByName($result[1]);
                if ($city) {
                    $user->geo_city_id = $city->id;
                } else {
                    info('false city', $result);
                }
            }

            debug($user->id, 'geo', $user->geo_province_id, $user->geo_city_id);
        }

        // 计算geo hash值
        $geo_hash = new \geo\GeoHash();
        $hash = $geo_hash->encode($latitude, $longitude);
        if ($hash) {
            if ($user->geo_hash && $user->geo_hash != $hash) {
                $user->delGeoHashRank();
            }

            $user->geo_hash = $hash;
        }

        $user->update();
        $user->blockCity();
    }

    function blockCity()
    {
        if (in_array($this->id, [1096845])) {
            return;
        }

        if ($this->register_at <= beginOfDay(strtotime('2018-05-15')) && $this->charm_value >= 100 &&
            $this->wealth_value >= 100) {
            return;
        }

        if ($this->ip_city_id == 33 || $this->geo_city_id == 33) {

            $device = $this->device;

            info("user_id", $this->id, "deveice_id", $device->id, "ip_city", $this->ip_city_id, "geo_city", $this->geo_city_id);

            $device->status = DEVICE_STATUS_BLOCK;
            $device->update();
        }
    }

    static function asyncUpdateIpLocation($user_id)
    {

        $user = self::findFirstById($user_id);
        if ($user && $user->ip) {
            $province = \Provinces::findByIp($user->ip);
            if ($province) {

                if (!$user->province_id) {
                    $user->province_id = $province->id;
                }

                $user->ip_province_id = $province->id;
                $user->ip_city_id = 0;

                $city = \Cities::findByIp($user->ip);

                if ($city) {

                    if (!$user->city_id) {
                        $user->city_id = $city->id;
                    }

                    $user->ip_city_id = $city->id;
                }

                debug($user->id, 'ip', $user->ip, $user->province_id, $user->city_id);
                $user->update();
                $user->blockCity();
            }
        }
    }

    //废弃
    static function checkRegisterMobile($mobile)
    {
        $mobile_operator = mobileOperator($mobile);
        if ($mobile_operator < 1 || $mobile_operator > 3) {
            info('false', $mobile, 'mobile_operator', $mobile_operator);
        }

        $users = Users::find(['conditions' => 'mobile=:mobile:', 'bind' => ['mobile' => $mobile]]);
        $num = count($users);
        foreach ($users as $user) {
            $user->mobile_register_num = $num;
            $user->mobile_operator = $mobile_operator;
            $user->save();
        }
    }

    function createEmUser()
    {
        if ($this->isActive()) {
            \Emchat::delay()->createEmUser($this->id);
            \Chats::delay(5)->sendWelcomeMessage($this->id);
        }
    }

    function registerStat()
    {
        info($this->id, date('YmdHis', $this->register_at), date('YmdHis', $this->last_at));
        \Stats::delay()->record('user', 'register', $this->getStatAttrs());
    }

    static function checkRegisterThirdUnionid($third_unionid, $third_name)
    {
        $users = Users::find([
            'conditions' => 'third_unionid=:third_unionid: and third_name = :third_name:',
            'bind' => ['third_unionid' => $third_unionid, 'third_name' => $third_name]
        ]);

        $num = count($users);

        info($num, $third_unionid, $third_name);

        foreach ($users as $user) {
            $user->third_unionid_register_num = $num;
            $user->save();
        }
    }

    function pushType()
    {
        $push_type = 'transmission';
        if ($this->device) {
            $push_type = $this->device->push_type;
        }

        return $push_type;
    }

    function updateProfile($params = [])
    {
        foreach ($params as $k => $v) {

            debug($this->$k, $k, $v);

            if (!array_key_exists($k, self::$UPDATE_FIELDS)) {
                continue;
            }

            if (!isPresent($v) && 'sex' != $k) {
                continue;
            }

            if ($this->$k == $v) {
                //info('未修改', $this->id, $k, $this->$k, $v);
                continue;
            }

            if ('nickname' == $k) {
                list($res, $v) = BannedWords::checkWord($v);
                if ($res) {
                    Chats::sendTextSystemMessage($this, "您设置的昵称名称违反规则,请及时修改");
                }

                $this->nickname = $v;
                continue;
            }

            if ('monologue' == $k) {
                list($res, $v) = BannedWords::checkWord($v);
                if ($res) {
                    Chats::sendTextSystemMessage($this, "您设置的个性签名违反规则,请及时修改");
                }

                $this->monologue = $v;
                continue;
            }

            if ('province_name' == $k) {
                $province = Provinces::findFirstByName($v);
                if ($province) {
                    $this->province_id = $province->id;
                }

                continue;
            }

            if ('city_name' == $k) {
                $city = Cities::findFirstByName($v);
                if ($city) {
                    $this->province_id = $city->province_id;
                    $this->city_id = $city->id;
                }

                debug($this->id, $k, $v, $this->city_id);
                continue;
            }

            if ('age' == $k) {
                $birthday = date("Y") - intval($v);
                $birthday = strtotime($birthday . '-01-01');
                if ($birthday > time()) {
                    continue;
                }
                $this->birthday = $birthday;
                continue;
            }

            if ('birthday' == $k) {
                $time = strtotime($v);
                $birthday = '';
                if (date("Y") - date("Y", $time) < 18) {
                    $birthday = date("Y") - 18;
                }

                if (date("Y") - date("Y", $time) > 70) {
                    $birthday = date("Y") - 70;
                }

                if ($birthday) {
                    $time = strtotime($birthday . "-" . date("m-d", $time));
                }

                if ($time < time()) {
                    $this->birthday = $time;
                }

                continue;
            }

            $this->$k = $v;
        }

        $this->save();
    }

    //拉黑
    function black($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $black_key = "black_list_user_id" . $this->id;
        $blacked_key = "blacked_list_user_id" . $other_user->id;

        if (!$user_db->zscore($black_key, $other_user->id)) {

            $user_db->zadd($black_key, time(), $other_user->id);
            $user_db->zadd($blacked_key, time(), $this->id);
        }
    }

    //取消拉黑
    function unBlack($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $black_key = "black_list_user_id" . $this->id;
        $blacked_key = "blacked_list_user_id" . $other_user->id;

        if ($user_db->zscore($black_key, $other_user->id)) {
            $user_db->zrem($black_key, $other_user->id);
            $user_db->zrem($blacked_key, $this->id);
        }
    }

    //获取拉黑，关注，好友的列表
    function findByRelations($relations_key, $page, $per_page, $opts = [])
    {
        $user_db = Users::getUserDb();
        $total_entries = $user_db->zcard($relations_key);

        $offset = $per_page * ($page - 1);
        if ($offset >= $total_entries) {
            $users = [];
            $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
            $pagination->clazz = 'Users';
            return $pagination;
        }

        $user_id_scores = $user_db->zrevrange($relations_key, $offset, $offset + $per_page - 1, 'withscores');

        $user_ids = array_keys($user_id_scores);

        // 好友备注
        $friend_note = fetch($opts, 'friend_note');
        $friend_notes = [];
        if ($friend_note) {
            $friend_notes = $this->getFriendNotes($user_ids);
            info('self_introduce', $this->id, $user_ids, $friend_notes);
        }

        // 自我简介
        $self_introduce = fetch($opts, 'self_introduce');
        $self_introduces = [];
        if ($self_introduce) {
            $self_introduces = $this->getSelfIntroducesText($user_ids);
            info('self_introduce', $this->id, $user_ids, $self_introduces);
        }


        $is_friends = [];
        $is_add_friends = [];
        $is_friend = fetch($opts, 'is_friend');
        $friend_new = fetch($opts, 'friend_new');
        if ($is_friend && $friend_new) {
            //是否为好友
            $friend_list_key = 'friend_list_user_id_' . $this->id;
            $is_friends = $user_db->multi_zget($friend_list_key, $user_ids);
            info('is_friend', $this->id, $user_ids, $is_friends);

            //是否为我添加的好友
            $friend_list_key = 'add_friend_list_user_id_' . $this->id;
            $is_add_friends = $user_db->multi_zget($friend_list_key, $user_ids);
            info('is_add_friend', $this->id, $user_ids, $is_add_friends);
        }

        $users = Users::findByIds($user_ids);
        foreach ($users as $user) {
            $user->created_at = fetch($user_id_scores, $user->id);
            if ($friend_notes) {
                $user->friend_note = fetch($friend_notes, $user->id);
            }
            if ($self_introduces) {
                $user->self_introduce = fetch($self_introduces, $user->id);
            }

            if ($is_friend) {
                if ($friend_new) {
                    $user->friend_status = 3;
                    if ($is_friends && fetch($is_friends, $user->id)) {
                        $user->friend_status = 1;
                    }
                    if ($is_add_friends && fetch($is_add_friends, $user->id)) {
                        $user->friend_status = 2;
                    }

                } else {
                    $user->friend_status = 1;
                }
            }
        }

        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }

    //黑名单列表
    function blackList($page, $per_page)
    {
        $black_key = "black_list_user_id" . $this->id;
        $users = $this->findByRelations($black_key, $page, $per_page);
        return $users;
    }

    //是否已关注
    function isFollow($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $follow_key = 'follow_list_user_id' . $this->id;
        return $user_db->zscore($follow_key, $other_user->id) > 0;
    }

    //是否已被关注
    function isFollowed($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $follow_key = 'followed_list_user_id' . $this->id;
        $this->followed = $user_db->zscore($follow_key, $other_user->id) > 0;
        return $this->followed;
    }

    //关注
    function follow($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $follow_key = 'follow_list_user_id' . $this->id;
        $followed_key = 'followed_list_user_id' . $other_user->id;
        if (!$user_db->zscore($follow_key, $other_user->id)) {
            $user_db->zadd($follow_key, time(), $other_user->id);
            $user_db->zadd($followed_key, time(), $this->id);
        }

    }

    //我关注的人数
    function followNum()
    {
        $follow_key = 'follow_list_user_id' . $this->id;
        $user_db = Users::getUserDb();
        return $user_db->zcard($follow_key);
    }

    //取消关注
    function unFollow($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $follow_key = 'follow_list_user_id' . $this->id;
        $followed_key = 'followed_list_user_id' . $other_user->id;
        if ($user_db->zscore($follow_key, $other_user->id)) {
            $user_db->zrem($follow_key, $other_user->id);
            $user_db->zrem($followed_key, $this->id);
        }
    }

    //我关注的列表
    function followList($page, $per_page)
    {
        $follow_key = 'follow_list_user_id' . $this->id;
        $users = $this->findByRelations($follow_key, $page, $per_page, ['friend_note' => 1]);

        return $users;
    }

    //我关注的用户id
    function followUserIds()
    {
        $follow_key = 'follow_list_user_id' . $this->id;
        $user_db = Users::getUserDb();
        return $user_db->zrange($follow_key, 0, -1);
    }


    //关注我的列表
    function followedList($page, $per_page)
    {
        $followed_key = 'followed_list_user_id' . $this->id;
        $users = $this->findByRelations($followed_key, $page, $per_page, ['friend_note' => 1]);
        return $users;
    }

    //关注我的人数
    function followedNum()
    {
        $followed_key = 'followed_list_user_id' . $this->id;
        $user_db = Users::getUserDb();
        return $user_db->zcard($followed_key);
    }

    function getFriendNote($user_id)
    {
        $db = Users::getUserDb();
        $friend_note_key = "friend_note_list_user_id_" . $this->id;
        info($friend_note_key, $user_id);
        $friend_note = $db->hget($friend_note_key, $user_id);
        if (is_null($friend_note)) {
            return '';
        }

        return $friend_note;
    }

    function getFriendNotes($user_ids)
    {
        $db = Users::getUserDb();
        $friend_note_key = "friend_note_list_user_id_" . $this->id;
        $friend_notes = $db->hmget($friend_note_key, $user_ids);
        if (is_null($friend_notes)) {
            return [];
        }

        return $friend_notes;
    }

    function addFriendNote($user_id, $friend_note)
    {
        $db = Users::getUserDb();
        $friend_note_key = "friend_note_list_user_id_" . $this->id;
        if ($friend_note) {
            $db->hset($friend_note_key, $user_id, $friend_note);
        }
    }

    //添加好友
    function addFriend($other_user, $opts = [])
    {
        $add_key = 'add_friend_list_user_id_' . $this->id;
        $added_key = 'added_friend_list_user_id_' . $other_user->id;
        $add_total_key = 'friend_total_list_user_id_' . $this->id;
        $other_total_key = 'friend_total_list_user_id_' . $other_user->id;
        $user_introduce_key = "add_friend_introduce_user_id" . $this->id;
        $other_user_introduce_key = "add_friend_introduce_user_id" . $other_user->id;
        $self_introduce = fetch($opts, 'self_introduce');

        $user_db = Users::getUserDb();
        $added_num_key = 'added_friend_num_user_id_' . $other_user->id;

        if (!$user_db->zscore($added_key, $this->id)) {
            $user_db->incr($added_num_key);
        }

        //在添加我的队列里面清掉对方的id
        if ($user_db->zscore('added_friend_list_user_id_' . $this->id, $other_user->id)) {
            $user_db->zrem('added_friend_list_user_id_' . $this->id, $other_user->id);
        }

        //在对方添加的队列中清掉我的id
        if ($user_db->zscore('add_friend_list_user_id_' . $other_user->id, $this->id)) {
            $user_db->zrem('add_friend_list_user_id_' . $other_user->id, $this->id);
        }

        if ($self_introduce) {
            //存储添加好友的自我介绍
            $user_db->hset($user_introduce_key, $other_user->id, $self_introduce);
            $user_db->hset($other_user_introduce_key, $this->id, $self_introduce);
        }

        //没有在对方总队列里面添加 此时要做通知
//        if (!$user_db->zscore($other_total_key, $this->id)) {
//
//        }

        $time = time();
        $user_db->zadd($add_key, $time, $other_user->id);
        $user_db->zadd($added_key, $time, $this->id);
        $user_db->zadd($add_total_key, $time, $other_user->id);
        $user_db->zadd($other_total_key, $time, $this->id);
    }

    //删除好友
    function deleteFriend($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $friend_list_key = 'friend_list_user_id_' . $this->id;
        $other_friend_list_key = 'friend_list_user_id_' . $other_user->id;
        $add_total_key = 'friend_total_list_user_id_' . $this->id;
        $other_total_key = 'friend_total_list_user_id_' . $other_user->id;
        $user_introduce_key = "add_friend_introduce_user_id" . $this->id;
        $other_user_introduce_key = "add_friend_introduce_user_id" . $other_user->id;
        $friend_note_key = "friend_note_list_user_id_" . $this->id;


        if ($user_db->zscore($friend_list_key, $other_user->id)) {
            $user_db->zrem($friend_list_key, $other_user->id);
        }

        if ($user_db->zscore($other_friend_list_key, $this->id)) {
            $user_db->zrem($other_friend_list_key, $this->id);
        }

        if ($user_db->zscore($add_total_key, $other_user->id)) {
            $user_db->zrem($add_total_key, $other_user->id);
        }

        if ($user_db->zscore($other_total_key, $this->id)) {
            $user_db->zrem($other_total_key, $this->id);
        }

        $user_db->hdel($friend_note_key, $other_user->id);
        $user_db->hdel($user_introduce_key, $other_user->id);
        $user_db->hdel($other_user_introduce_key, $other_user->id);
    }

    //是否为好友
    function isFriend($other_user)
    {
        $user_db = Users::getUserDb();
        $friend_list_key = 'friend_list_user_id_' . $this->id;
        return $user_db->zscore($friend_list_key, $other_user->id) > 0;
    }

    //是否为我添加的好友
    function isAddFriend($other_user)
    {
        $user_db = Users::getUserDb();
        $key = 'add_friend_list_user_id_' . $this->id;
        return $user_db->zscore($key, $other_user->id) > 0;
    }

    //是否为添加我的好友
    function isAddedFriend($other_user)
    {
        $user_db = Users::getUserDb();
        $key = 'added_friend_list_user_id_' . $this->id;
        return $user_db->zscore($key, $other_user->id) > 0;
    }

    function getSelfIntroduceText($other_user)
    {
        $user_db = Users::getUserDb();
        $user_introduce_key = "add_friend_introduce_user_id" . $this->id;
        info($user_introduce_key, $other_user->id);
        $self_introduce = $user_db->hget($user_introduce_key, $other_user->id);
        return $self_introduce;
    }

    function getSelfIntroducesText($other_user_ids)
    {
        $user_db = Users::getUserDb();
        $user_introduce_key = "add_friend_introduce_user_id" . $this->id;
        $self_introduces = $user_db->hmget($user_introduce_key, $other_user_ids);
        return $self_introduces;
    }

    //好友列表
    function friendList($page, $per_page, $new)
    {
        if (1 == $new) {
            //进入列表清空新好友通知个数
            $this->clearNewFriendNum();
            $key = 'friend_total_list_user_id_' . $this->id;
        } else {
            $key = 'friend_list_user_id_' . $this->id;
        }

        $users = $this->findByRelations($key, $page, $per_page, ['friend_note' => 1,
            'self_introduce' => 1, 'friend_new' => $new, 'is_friend' => 1]);

        return $users;
    }

    //同意添加好友
    function agreeAddFriend($other_user)
    {
        $friend_list_key = 'friend_list_user_id_' . $this->id;
        $other_friend_list_key = 'friend_list_user_id_' . $other_user->id;
        $add_key = 'add_friend_list_user_id_' . $other_user->id;
        $added_key = 'added_friend_list_user_id_' . $this->id;
        $user_db = Users::getUserDb();

        $time = time();

        if ($user_db->zscore($add_key, $this->id)) {
            $user_db->zrem($add_key, $this->id);
            $user_db->zadd($other_friend_list_key, $time, $this->id);
        }

        if ($user_db->zscore($added_key, $other_user->id)) {
            $user_db->zrem($added_key, $other_user->id);
            $user_db->zadd($friend_list_key, $time, $other_user->id);
        }

    }

    function refuseAddFriend($other_user)
    {
        $add_key = 'add_friend_list_user_id_' . $other_user->id;
        $added_key = 'added_friend_list_user_id_' . $this->id;

        $add_total_key = 'friend_total_list_user_id_' . $this->id;
        $other_total_key = 'friend_total_list_user_id_' . $other_user->id;

        $user_introduce_key = "add_friend_introduce_user_id" . $this->id;
        $other_user_introduce_key = "add_friend_introduce_user_id" . $other_user->id;

        $user_db = Users::getUserDb();

        if ($user_db->hget($user_introduce_key, $other_user->id)) {
            $user_db->hdel($other_user_introduce_key, $other_user->id);
        }

        if ($user_db->hget($other_user_introduce_key, $this->id)) {
            $user_db->hdel($user_introduce_key, $other_user->id);
        }

        if ($user_db->zscore($add_key, $this->id)) {
            $user_db->zrem($add_key, $this->id);
        }

        if ($user_db->zscore($added_key, $other_user->id)) {
            $user_db->zrem($added_key, $other_user->id);
        }

        if ($user_db->zscore($add_total_key, $other_user->id)) {
            $user_db->zrem($add_total_key, $other_user->id);
        }

        if ($user_db->zscore($other_total_key, $this->id)) {
            $user_db->zrem($other_total_key, $this->id);
        }

    }

    //清空添加好友信息
    function clearAddFriendInfo()
    {
        $user_db = Users::getUserDb();
        $key = 'friend_total_list_user_id_' . $this->id;
        $user_introduce_key = "add_friend_introduce_user_id" . $this->id;
        $user_db->zclear($key);
        $user_db->hclear($user_introduce_key);
    }

    function friendNum()
    {
        $key = 'friend_list_user_id_' . $this->id;
        $user_db = Users::getUserDb();
        return $user_db->zcard($key);
    }

    function newFriendNum()
    {
        $key = 'added_friend_num_user_id_' . $this->id;
        $user_db = Users::getUserDb();
        return intval($user_db->get($key));
    }

    function clearNewFriendNum()
    {
        $key = 'added_friend_num_user_id_' . $this->id;
        $user_db = Users::getUserDb();
        $user_db->del($key);
    }


    //需要更新资料
    function needUpdateInfo()
    {
        //第三方授权登录 不校验
        if ($this->third_name) {
            return false;
        }

        $update_info = ['nickname', 'sex', 'province_id', 'city_id', 'avatar'];
        foreach ($update_info as $k) {
            if (!$this->$k && $k != 'sex' || $k == 'sex' && is_null($this->sex)) {
                return true;
            }
        }
        return false;
    }

    static function search($user, $page, $per_page, $opts = [])
    {
        $user_id = fetch($opts, 'user_id');
        $nickname = fetch($opts, 'nickname');
        $uid = fetch($opts, 'uid');
        $province_id = fetch($opts, 'province_id');
        $city_id = fetch($opts, 'city_id');
        $filter_ids = fetch($opts, 'filter_ids');

        if ($user_id) {
            $cond = ['conditions' => 'id = :user_id:', 'bind' => ['user_id' => $user_id]];
        } else {
            $cond = ['conditions' => 'id <> ' . $user->id];
        }

        if ($uid && $nickname) {
            $cond['conditions'] .= ' and (uid = :uid: or nickname like :nickname:) ';
            $cond['bind']['uid'] = $uid;
            $cond['bind']['nickname'] = "%{$nickname}%";
        } else {

            if ($nickname) {
                $cond['conditions'] .= ' and (nickname like :nickname:) ';
                $cond['bind']['nickname'] = "%{$nickname}%";
            }

            if ($uid) {
                $cond['conditions'] .= ' and (uid = :uid:) ';
                $cond['bind']['uid'] = $uid;
            }
        }


        if ($city_id) {
            $cond['conditions'] .= ' and (city_id=:city_id: or geo_city_id=:geo_city_id: or ip_city_id=:ip_city_id:)';
            $cond['bind']['city_id'] = $city_id;
            $cond['bind']['geo_city_id'] = $city_id;
            $cond['bind']['ip_city_id'] = $city_id;
        }

        if ($province_id) {
            $cond['conditions'] .= ' and (province_id=:province_id: or geo_province_id=:geo_province_id: or ip_province_id=:ip_province_id:)';
            $cond['bind']['province_id'] = $province_id;
            $cond['bind']['geo_province_id'] = $province_id;
            $cond['bind']['ip_province_id'] = $province_id;
        }

        $user_type = fetch($opts, 'user_type', USER_TYPE_ACTIVE);
        if ($user_type) {
            $cond['conditions'] .= " and user_type = " . $user_type;
        }

//        if ($filter_ids) {
//            $filter_ids = implode(',', $filter_ids);
//            $cond['conditions'] .= " and id not in ({$filter_ids})";
//        }

        $cond['conditions'] .= " and id != " . SYSTEM_ID . " and avatar_status = " . AUTH_SUCCESS . ' and (user_status = ' . USER_STATUS_ON .
            ' or user_status = ' . USER_STATUS_LOGOUT . ')';
        $cond['order'] = 'last_at desc,id desc';

        debug($user->id, $cond);

        $users = Users::findPagination($cond, $page, $per_page);

        return $users;
    }

    static function recommend($current_user, $page, $per_page)
    {
        $db = Users::getUserDb();
        $friends_key = 'friend_list_user_id_' . $current_user->id;
        $followed_key = 'followed_list_user_id' . $current_user->id;

        $friend_ids = $db->zrange($friends_key, 0, -1);
        $followed_ids = $db->zrange($followed_key, 0, -1);

        $merge_ids = array_merge($friend_ids, $followed_ids, [SYSTEM_ID]);

        $filter_ids = array_unique($merge_ids);

        $users = $current_user->nearby($page, $per_page, ['filter_ids' => $filter_ids]);

        foreach ($users as $user) {
            $user->recommend_tip = $user->getRecommendTip($user);
        }

        return $users;
    }


    function getRecommendTip($user)
    {
        $register_at = $this->register_at;
        if (time() - $register_at < 86400) {
            $sex_text = '她';
            if ($user->sex == USER_SEX_MALE) {
                $sex_text = '他';
            }

            return $sex_text . "是新人";
        }

        if ($this->monologue) {
            return $this->monologue;
        }

        if ($this->city_id == $user->city_id) {
            return "你们在同一个城市";
        }

        if (isPresent($this->union_id) && isPresent($user->union_id) && $this->union_id == $user->union_id) {
            if ($this->union->type == UNION_TYPE_PRIVATE) {
                return "你们在同一家族";
            }
            if ($this->union->type == UNION_TYPE_PUBLIC) {
                return "你们在同一公会";
            }
        }
    }

    function nearby($page, $per_page, $opts = [])
    {

        if (!$this->geo_hash) {
            $users = \Users::search($this, $page, $per_page, $opts);
            $this->calDistance($users);
            return $users;
        }

        $geohash = new \geo\GeoHash();
        //取前缀，前缀约长范围越小
        $prefix = substr($this->geo_hash, 0, 5);
        //取出相邻八个区域
        $neighbors = $geohash->neighbors($prefix);

        $cache_key = 'user_geo_hash_5' . $prefix . '_' . fetch($neighbors, 'top') . '_' . fetch($neighbors, 'bottom')
            . '_' . fetch($neighbors, 'right') . '_' . fetch($neighbors, 'left') . '_' . fetch($neighbors, 'topleft')
            . '_' . fetch($neighbors, 'topright') . '_' . fetch($neighbors, 'bottomright') . '_' . fetch($neighbors, 'bottomleft');

        $user_db = Users::getUserDb();
        $total_entries = $user_db->zcard($cache_key);

        if ($total_entries >= 3) {
            $offset = $per_page * ($page - 1);
            $user_ids = $user_db->zrevrange($cache_key, $offset, $offset + $per_page - 1);
            $index = array_search($this->id, $user_ids);
            if (false !== $index) {
                unset($user_ids[$index]);
            }

            $cache_users = Users::findByIds($user_ids);
            $users = new PaginationModel($cache_users, $total_entries, $page, $per_page);
            $users->clazz = 'Users';
            info($this->id, $cache_key, $total_entries, $page, $per_page);
        } else {
            $users = \Users::search($this, $page, $per_page, $opts);
        }

        // 计算距离
        $this->calDistance($users);

        return $users;
    }

    // 附近人
    function nearby2($page, $per_page, $opts = [])
    {

        if (!$this->geo_hash) {
            $users = \Users::search($this, $page, $per_page, $opts);
            $this->calDistance($users);
            return $users;
        }

        $geohash = new \geo\GeoHash();
        //$hash = $geohash->encode($latitude, $longitude);
        $hash = $this->geo_hash;
        //取前缀，前缀约长范围越小
        $prefix = substr($this->geo_hash, 0, 5);
        //取出相邻八个区域
        $neighbors = $geohash->neighbors($prefix);
        array_push($neighbors, $prefix);

        $hot_cache = self::getHotWriteCache();
        $cache_key = 'nearby_' . $prefix . '_page' . $page . '_per_page' . $per_page;
        $cache_total_entries_key = 'nearby_' . $prefix . '_total_entries';

        $user_ids = $hot_cache->get($cache_key);
        $total_entries = $hot_cache->get($cache_total_entries_key);

        if ($user_ids && $total_entries) {
            info('cache', $cache_key, $total_entries);
            $user_ids = json_decode($user_ids, true);
            $objects = Users::findByIds($user_ids);
            $users = new PaginationModel($objects, $total_entries, $page, $per_page);
            $users->clazz = 'Users';

            // 计算距离
            $this->calDistance($users);
            return $users;
        }


        $condition = "(";
        $bind = [];
        foreach ($neighbors as $key => $neighbor) {
            $val = $neighbor . '%';
            if ($key) {
                $condition .= 'geo_hash like :val_' . $key . ': or ';
                $bind['val_' . $key] = $val;
            } else {
                $condition .= 'geo_hash like :val_' . $key . ':)';
                $bind['val_' . $key] = $val;
            }
        }

        $condition .= ' and id <> :user_id: and avatar_status = ' . AUTH_SUCCESS;
        $condition .= ' and user_status = ' . USER_STATUS_ON . ' and user_type = ' . USER_TYPE_ACTIVE;
        $condition .= " and organisation = :organisation:";
        $bind['user_id'] = $this->id;
        $bind['organisation'] = USER_ORGANISATION_PERSONAGE;
        $conds['conditions'] = $condition;
        $conds['bind'] = $bind;
        $conds['order'] = 'last_at desc,id desc';

        $users = Users::findPagination($conds, $page, $per_page);
        if ($users->total_entries < 3) {
            $users = \Users::search($this, $page, $per_page, $opts);
        }

        $user_ids = [];

        foreach ($users as $user) {
            $user_ids[] = $user->id;
        }

        $hot_cache->setex($cache_key, 90, json_encode($user_ids, JSON_UNESCAPED_UNICODE));
        $hot_cache->setex($cache_total_entries_key, 90, $users->total_entries);

        // 计算距离
        $this->calDistance($users);

        return $users;
    }

    public function calDistance(&$users)
    {
        if (!$users || count($users) < 1) {
            return;
        }

        // 10km---0.01km
        foreach ($users as $key => $user) {

            if ($this->latitude && $this->longitude && $user->latitude && $user->longitude) {

                $geo_distance = \geo\GeoHash::calDistance($this->latitude / 10000, $this->longitude / 10000,
                    $user->latitude / 10000, $user->longitude / 10000);
                if ($geo_distance < 1000) {
                    $geo_distance = intval($geo_distance);
                    $user->distance = $geo_distance . 'm';
                    if ($geo_distance < 200) {
                        $user->distance = '附近';
                    }
                } else {
                    $geo_distance = sprintf("%0.2f", $geo_distance / 1000);
                    $user->distance = $geo_distance . 'km';
                }

                debug('true', $this->id, $user->id, $user->distance, $this->latitude, $this->longitude, $user->latitude, $user->longitude);
            } else {

                $geo_distance = abs($this->id - $user->id) % 1000;
                $geo_distance = $geo_distance / 100;
                if ($geo_distance < 0.01) {
                    $geo_distance = 0.01;
                }

                $user->distance = $geo_distance . 'km';

                debug('false', $this->id, $user->id, $user->distance, $this->latitude, $this->longitude, $user->latitude, $user->longitude);
            }
        }
    }

    function getSearchCityId()
    {

        if ($this->geo_city_id) {
            return $this->geo_city_id;
        }

        if ($this->ip_city_id) {
            return $this->ip_city_id;
        }

        return $this->city_id;
    }

    function getSearchProvinceId()
    {

        if ($this->geo_province_id) {
            return $this->geo_province_id;
        }

        if ($this->ip_province_id) {
            return $this->ip_province_id;
        }

        return $this->province_id;
    }

    //判断用户是否在指定的房间
    function isInRoom($room = null)
    {
        if ($room) {
            return $this->current_room_id == $room->id;
        }

        return intval($this->current_room_id) > 0;
    }

    function isInAnyRoom()
    {
        return $this->current_room_id > 0;
    }

    //判断用户是否在指定的麦位
    function isInRoomSeat($room_seat)
    {
        return $this->current_room_seat_id == $room_seat->id;
    }

    //1可以聊天 2不可以聊天
    function setChat($room, $chat)
    {
        $cache = Users::getHotWriteCache();

        if ($chat) {
            $cache->del("chat_status_room{$room->id}user{$this->id}");
            return;
        }

        $expire = 3600 * 24;

        if (isDevelopmentEnv()) {
            $expire = 60;
        }

        $cache->setex("chat_status_room{$room->id}user{$this->id}", $expire, 1);
    }

    function canChat($room)
    {
        $db = Users::getHotReadCache();
        $key = "chat_status_room{$room->id}user{$this->id}";
        $chat = $db->get($key);

        if ($chat) {
            return false;
        }

        return true;
    }

    //是否为房间房主
    function isRoomHost($room)
    {
        return $this->id == $room->user_id;
    }

    function setSpeaker($speaker)
    {
        $this->speaker = $speaker;
        $this->update();
    }

    function setMicrophone($microphone)
    {
        $this->microphone = $microphone;
        $this->update();
    }

    function isCalling()
    {
        return VoiceCalls::userIsCalling($this->id);
    }

    //是否为房间的管理员
    function isManager($room)
    {
        $room->freshManagerNum();
        $db = Users::getUserDb();
        $manager_list_key = $room->generateManagerListKey();
        return $db->zscore($manager_list_key, $this->id) > 0;
    }

    //是否为房间永久的管理员
    function isPermanentManager($room)
    {
        $db = Users::getUserDb();
        $manager_list_key = $room->generateManagerListKey();
        return $db->zscore($manager_list_key, $this->id) - time() > 86400 * 300;
    }

    function canManagerRoom($room)
    {
        if ($this->isRoomHost($room) || $this->isManager($room)) {
            return true;
        }

        return false;
    }

    function canKickingUser($room, $other_user)
    {
        if (!$this->canManagerRoom($room)) {
            return false;
        }

        if (USER_ROLE_NO == $other_user->user_role) {
            return true;
        }

        return $this->user_role < $other_user->user_role;
    }

    static function startRoomInteractionTask($user_id, $room_id)
    {

    }

    //上麦
    static function upRoomSeat($user_id, $room_id)
    {
        $room = Rooms::findFirstById($room_id);
        $user = Users::findFirstById($user_id);

        if (!$room || !$user) {
            return;
        }

        if (!$user->isInRoom($room)) {
            return;
        }

        if ($user->current_room_seat_id < 1) {

            $room_seat = \RoomSeats::findFirst(['conditions' => 'room_id = ' . $room->id . " and (user_id = 0 or user_id is null) and status = " . STATUS_ON]);

            if ($room_seat) {
                $room_seat->up($user);
                $room->pushUpMessage($user, $room_seat);
            }
        }
    }

    //送礼物
    static function sendGift($user_id, $room_id)
    {
        $room = Rooms::findFirstById($room_id);
        $user = Users::findFirstById($user_id);

        if (!$room || !$user) {
            return;
        }

        if (!$user->isInRoom($room)) {
            return;
        }

        if ($room->getRealUserNum() > 0) {

            $receiver = $room->findRandomUser([$user_id]);

            if ($receiver) {

                $gift_num = mt_rand(1, 15);

                $gifts = Gifts::find(['conditions' => 'status = :status: and render_type = :render_type: and type = :type:',
                    'bind' => ['status' => STATUS_ON, 'render_type' => 'svga', 'type' => GIFT_TYPE_COMMON], 'columns' => 'id']);

                $gift_ids = [];

                foreach ($gifts as $gift) {
                    $gift_ids[] = $gift->id;
                }

                $index = array_rand($gift_ids);
                $gift_id = $gift_ids[$index];
                $gift = Gifts::findFirstById($gift_id);

                $give_result = true;

                if ($receiver->isActive()) {
                    $give_result = GiftOrders::asyncCreateGiftOrder($user->id, [$receiver->id], $gift->id, ['gift_num' => $gift_num]);
                }

                if ($give_result) {
                    $room->pushGiftMessage($user, $receiver, $gift, $gift_num);
                }
            }
        }
    }

    //公屏消息
    static function sendTopTopicMessage($user_id, $room_id)
    {
        $room = Rooms::findFirstById($room_id);
        $user = Users::findFirstById($user_id);

        if (!$room || !$user) {
            return;
        }

        if (!$user->isInRoom($room)) {
            return;
        }

        $room->pushTopTopicMessage($user);
    }

    //启动房间互动
    function autoActiveRoom($room)
    {

        if (!$room) {
            info("Exce", $this->id, $room->id);
            return;
        }

        if (!$this->isInAnyRoom()) {
            info("user_not_in_room", $this->id, $room->id);
            return;
        }

        if ($this->isRoomHost($room)) {
            info("user_is_room_host", $this->id, $room->id);
            return;
        }

        $rand_num = mt_rand(1, 100);

        info($this->id, $room->id, 'num', $rand_num);

        if (isProduction()) {
            if ($room->isSilent()) {
                if ($rand_num <= 70) {
                    Users::delay(mt_rand(1, 50))->upRoomSeat($this->id, $room->id);
                } elseif (70 < $rand_num && $rand_num <= 80) {
                    $room->exitSilentRoom($this);
                    return;
                }
            } else {
                if ($room->getRealUserNum() < 1) {
                    Rooms::delay(mt_rand(1, 300))->asyncExitSilentRoom($room->id, $this->id);
                    return;
                }
            }
        } else {
//            if ($rand_num <= 50) {
//                if ($room->getRealUserNum() > 0 && $room->chat) {
//                    Users::delay(mt_rand(1, 50))->sendTopTopicMessage($this->id, $room->id);
//                }
//            } elseif (50 < $rand_num && $rand_num <= 52) {
//                if ($room->getRealUserNum() > 0) {
//                    Users::delay(mt_rand(1, 50))->sendGift($this->id, $room->id);
//                }
//            } elseif (53 < $rand_num && $rand_num <= 90) {
//                Users::delay(mt_rand(1, 50))->upRoomSeat($this->id, $room->id);
//            } else {
//                $room->exitSilentRoom($this);
//                return;
//            }

//            if ($room->getRealUserNum() < 1) {
//                Rooms::delay(mt_rand(1, 10))->asyncExitSilentRoom($room->id, $this->id);
//                return;
//            }
            if ($room->getRealUserNum() > 0) {
                if ($rand_num <= 40) {
                    Users::delay(mt_rand(1, 10))->sendGift($this->id, $room->id);
                } elseif ($rand_num > 40 && $rand_num <= 70) {
                    Users::delay(mt_rand(1, 10))->upRoomSeat($this->id, $room->id);
                } elseif ($rand_num > 70 && $rand_num <= 95) {
                    Users::delay(mt_rand(1, 10))->sendTopTopicMessage($this->id, $room->id);
                } else {
                    $room->exitSilentRoom($this);
                    return;
                }
            }
        }
    }

    //沉默用户下麦
    static function asyncDownRoomSeat($user_id, $room_seat_id)
    {
        $user = Users::findFirstById($user_id);
        $room_seat = RoomSeats::findFirstById($room_seat_id);

        if (!$user || !$room_seat) {
            info("Exec", $user_id, $room_seat_id);
            return;
        }

        if ($user->current_room_seat_id == $room_seat_id) {
            $room_seat->down($user);
            $room_seat->room->pushDownMessage($user, $room_seat);
        }
    }

    static function waitAuthKey()
    {
        return "wait_auth_users";
    }

    static function authedKey()
    {
        return "authed_users";
    }

    static function findWaitAuthUsers($page, $per_page = 30)
    {
        $search_db = \Users::getHotReadCache();
        $offset = ($page - 1) * $per_page;

        $user_ids = $search_db->zrange(self::waitAuthKey(), $offset, $offset + 99);
        $total = $search_db->zcard(self::waitAuthKey());

        $users = \Users::findByIds($user_ids);
        return new \PaginationModel($users, $total, $page, $per_page);
    }

    static function findAuthedUsers($page, $per_page = 30)
    {
        $search_db = \Users::getHotReadCache();
        $offset = ($page - 1) * $per_page;

        $user_ids = $search_db->zrange(self::authedKey(), $offset, $offset + 99);
        $total = $search_db->zcard(self::authedKey());

        $users = \Users::findByIds($user_ids);
        return new \PaginationModel($users, $total, $page, $per_page);
    }

    static function exportAuthedUser()
    {
        $search_db = \Users::getHotReadCache();
        $num = $search_db->zcard(\Users::authedKey());
        info("total_num", $num);

        $offset = 0;
        $f = fopen(APP_ROOT . 'log/authed_users.log', 'w');
        while (true) {
            $user_ids = $search_db->zrange(\Users::authedKey(), $offset, $offset + 99);
            if (count($user_ids) <= 0) {
                break;
            }
            $users = \Users::findByIds($user_ids);
            foreach ($users as $user) {
                $data = json_encode($user->toExportJson(), JSON_UNESCAPED_UNICODE);
                fwrite($f, $data . "\r\n");
            }
            $offset += 100;
        }
        fclose($f);

        $search_db->zclear(\Users::authedKey());
    }

    static function importAuthedUser()
    {
        $common_monolog = file_get_contents(APP_ROOT . "doc/user_data/common_monolog.txt");
        $common_monolog = explode(PHP_EOL, $common_monolog);
        $common_monolog_num = count($common_monolog);
        $monologue_index = 0;
        $a_rate_num = 0;
        $b_rate_num = 0;

        if ($common_monolog_num < 1) {
            info("common_monolog_error");
            return;
        }

        $f = fopen(APP_ROOT . 'log/authed_users.log', 'r');
        $hot_db = \Users::getHotWriteCache();
        while ($line = fgets($f)) {
            echo $line . PHP_EOL;
            $data = json_decode($line, true);
            $user = new \Users();
            foreach (['sex', 'platform', 'platform_version',
                         'login_name', 'nickname', 'mobile', 'height'] as $column) {
                $user->$column = $data[$column];
            }

            $old_user = \Users::findFirstByLoginName($user->product_channel, $user->login_name);
            if (isPresent($old_user)) {
                info('old user', $user->login_name);
                continue;
            }

            $monologue = '';
            $random = mt_rand(1, 100);

            if ($random <= 70) {
                $age = mt_rand(16, 21);
                $a_rate_num++;
            } else {
                $b_rate_num++;
                $age = mt_rand(22, 25);

                if (isset($common_monolog[$monologue_index])) {
                    $monologue = $common_monolog[$monologue_index];
                } else {
                    info("monolog_error", $monologue_index);
                }

                $monologue_index++;
                if ($monologue_index > $common_monolog_num - 1) {
                    $monologue_index = 0;
                }
            }

            $birthday = 2018 - $age;
            $month = mt_rand(1, 12);
            $day = mt_rand(1, 28);

            if ($day < 10) {
                $day = "0" . $day;
            }

            if ($month < 10) {
                $month = "0" . $month;
            }

            $new_birthday = $birthday . $month . $day;
            $user->birthday = strtotime($new_birthday);
            $user->user_type = USER_TYPE_SILENT;
            $user->product_channel_id = 1;
            $user->monologue = $monologue;

            if ($user->height > 175 || $user->height < 150) {
                $user->height = 150 + mt_rand(0, 30);
            }
            if (isPresent($data['province_name'])) {
                $province = \Provinces::findFirstByName($data['province_name']);
                if ($province) {
                    $user->province_id = $province->id;
                }
            }
            if (isPresent($data['city_name'])) {
                $city = \Cities::findFirstByName($data['city_name']);
                if ($city) {
                    $user->city_id = $city->id;
                }
            }

            $user->save();


            $source_filename = APP_ROOT . 'temp/avatar_' . md5(uniqid(mt_rand())) . '.jpg';
            if (!httpSave($data['avatar_url'], $source_filename)) {
                info('get avatar error', $data['avatar_url']);
                continue;
            }

            if ($user->updateAvatar($source_filename)) {
                $hot_db->zadd("authed_user_ids", time(), $user->id);
                foreach ($data['albums'] as $album) {
                    $album_url = $album['image_url'];
                    \Albums::createAlbum($album_url, $user->id, AUTH_SUCCESS);
                }
            }

            if (file_exists($source_filename)) {
                unlink($source_filename);
            }
        }

        info($a_rate_num, $b_rate_num, $monologue_index);
    }

    function changeAvatarAuth($avatar_status)
    {
        if (isBlank($avatar_status) ||
            !array_key_exists(intval($avatar_status), \UserEnumerations::$AVATAR_STATUS)
        ) {
            return;
        }
        $this->avatar_status = $avatar_status;
        $this->update();

        if (AUTH_SUCCESS == intval($avatar_status)) {
            $this->addAuthedList();
        }
    }

    function removeFromWaitAuthList()
    {
        $hot_db = Users::getHotWriteCache();
        $hot_db->zrem(Users::waitAuthKey(), $this->id);
    }

    function addAuthedList()
    {
        $hot_db = Users::getHotWriteCache();
        $hot_db->zadd(Users::authedKey(), time(), $this->id);
    }

    //第三方登录
    static function findFirstByThirdUnionid($product_channel, $third_unionid, $third_name)
    {

        $cond['conditions'] = 'third_unionid = :third_unionid: and product_channel_id = :product_channel_id: and user_status != :user_status:' .
            ' and third_name = :third_name:';
        $cond['bind'] = ['third_unionid' => $third_unionid, 'product_channel_id' => $product_channel->id,
            'user_status' => USER_STATUS_OFF, 'third_name' => $third_name];
        $cond['order'] = 'id desc';

        $user = Users::findFirst($cond);
        return $user;
    }

    static function thirdLogin($current_user, $device, $params, $context = [])
    {
        if (!$params) {
            return [ERROR_CODE_FAIL, '参数错误', null];
        }

        if (!$device || (!$device->can_register && isProduction())) {
            info('false_device', $current_user->product_channel->code, $context);
            return [ERROR_CODE_FAIL, '设备错误!!', null];
        }

        $third_id = fetch($params, 'third_id');
        $third_name = fetch($params, 'third_name');
        $third_unionid = fetch($params, 'third_unionid');

        $third_auth = \ThirdAuths::findFirstBy(['product_channel_id' => $current_user->product_channel_id, 'third_id' => $third_id,
            'third_name' => $third_name
        ]);

        if (!$third_auth) {
            $third_auth = new \ThirdAuths();
            $third_auth->third_id = $third_id;
            $third_auth->third_token = $params['third_token'];
            $third_auth->third_name = $third_name;
            $third_auth->product_channel_id = $current_user->product_channel_id;
            $third_auth->third_unionid = $third_unionid;
            $third_auth->save();
        }

        $user = $current_user;

        $third_unionid = $third_unionid ? $third_unionid : $third_id;

        //其他账户的快捷登陆 重新注册新用户
        if ($user && $user->third_unionid && $user->third_unionid != $third_unionid || $user->login_type && !$user->isThirdLogin()) {
            $user = Users::registerForClientByDevice($device, true);
            if (!$user) {
                return [ERROR_CODE_FAIL, '登录失败', $user];
            }
        }

        $third_auth->user_id = $user->id;
        $third_auth->save();

        $fr = $device->fr;

        if (!$fr) {
            $fr = fetch($context, 'fr');
            $user->fr = $fr;
            $device->fr = $fr;
        }

        $partner = \Partners::findFirstByFrHotCache($fr);
        if ($partner) {
            $user->partner_id = $partner->id;
            $device->partner_id = $partner->id;
        }

        $device->user_id = $user->id;
        $device->update();

        $fields = ['platform', 'platform_version', 'version_code', 'version_name', 'api_version', 'device_no', 'manufacturer',
            'ip', 'latitude', 'longitude', 'push_token'];

        foreach ($fields as $field) {

            if ($device->$field) {
                $user->$field = $device->$field;
            }
        }

        $user->third_unionid = $third_unionid;
        $user->login_type = $third_name;
        $user->third_name = $third_name;
        $user->device_id = $device->id;
        $user->login_name = $params['login_name'];
        $user->nickname = $params['nickname'];
        $user->sex = $params['sex'];
        $user->user_type = USER_TYPE_ACTIVE;
        $user->sid = $user->generateSid('s');
        $user->update();

        info('third_login_log,user_id=', $user->id);

        $source_url = fetch($params, 'avatar_url');

        //上传头像
        if ($source_url) {
            \Users::uploadWeixinAvatar($user->id, $source_url);
        }

        return [ERROR_CODE_SUCCESS, '登陆成功', $user];
    }

    function findMusics($page, $per_page)
    {
        $user_db = Users::getUserDb();
        $key = "user_musics_id" . $this->id;
        $total_entries = $user_db->zcard($key);
        $offset = $per_page * ($page - 1);
        $music_ids = $user_db->zrevrange($key, $offset, $offset + $per_page - 1, 'withscores');

        $ids = [];
        $times = [];

        foreach ($music_ids as $music_id => $time) {
            $ids[] = $music_id;
            $times[$music_id] = $time;
        }

        $musics = Musics::findByIds($ids);

        foreach ($musics as $music) {
            $music->down_at = fetch($times, $music->id);
        }

        $pagination = new PaginationModel($musics, $total_entries, $page, $per_page);
        $pagination->clazz = 'Musics';

        return $pagination;
    }

    function calculateLevel()
    {
        $level = $this->level;
        $experience = $this->experience;

        if ($experience < 1) {
            return 0;
        } elseif ($experience >= 386000) {
            return 35;
        }

        $level_ranges = [0, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000,
            10000, 11000, 16000, 21000, 26000, 31000, 36000, 56000, 76000, 96000, 116000, 136000, 186000, 236000, 286000,
            336000, 386000];

        foreach ($level_ranges as $index => $level_range) {

            if (isset($level_ranges[$index + 1]) && $experience >= $level_range &&
                $experience < $level_ranges[$index + 1]
            ) {
                $level = $index;
                break;
            }

        }

        return $level;
    }

    //段位
    function calculateSegment()
    {
        $levels = [1, 6, 11, 16, 21, 26, 31, 36];
        $segment_texts = ['bronze', 'silver', 'gold', 'platinum', 'diamond', 'king', 'starshine'];
        $user_level = $this->level;

        if ($user_level < 1) {
            return '';
        } elseif ($user_level >= 35) {
            return 'starshine5';
        }

        $segment = '';

        foreach ($levels as $index => $level) {

            if (isset($levels[$index + 1]) && $user_level >= $level && $user_level < $levels[$index + 1]) {
                $segment = $segment_texts[$index] . ($user_level - $index * 5);
            }
        }

        return $segment;
    }


    //更新用户等级/经验/财富值
    static function updateExperience($gift_order_id, $opts = [])
    {
        if (is_numeric($gift_order_id)) {
            $gift_order = \GiftOrders::findById($gift_order_id);
        } else {
            $gift_order = $gift_order_id;
        }

        if (isBlank($gift_order) || !$gift_order->isSuccess()) {
            return false;
        }

        $lock_key = "update_user_level_lock_" . $gift_order->sender_id;
        $lock = tryLock($lock_key);

        $sender = $gift_order->sender;
        $amount = $gift_order->amount;
        $sender_experience = 0.02 * $amount;
        $wealth_value = $amount;

        if ($sender) {

            //异步发起时间
            $time = fetch($opts, 'time', time());

            $sender->experience += $sender_experience;
            $sender_level = $sender->calculateLevel();
            $sender->level = $sender_level;
            $sender->segment = $sender->calculateSegment();
            $sender->wealth_value += $wealth_value;

            if (!$sender->isCompanyUser()) {
                Users::updateFiledRankList($sender->id, 'wealth', $wealth_value, ['time' => $time]);
            }


            $union = $sender->union;

            if (isPresent($union) && $union->type == UNION_TYPE_PRIVATE) {
                $sender->union_wealth_value += $wealth_value;
                Unions::updateFameValue($wealth_value, $union->id, ['time' => $time]);
            }

            $sender->update();
        }

        unlock($lock);
    }

    //魅力值
    static function updateCharm($gift_order_id, $opts = [])
    {
        if (is_numeric($gift_order_id)) {
            $gift_order = \GiftOrders::findById($gift_order_id);
        } else {
            $gift_order = $gift_order_id;
        }

        if (isBlank($gift_order) || !$gift_order->isSuccess()) {
            return false;
        }

        $lock_key = "update_user_charm_lock_" . $gift_order->user_id;
        $lock = tryLock($lock_key);

        $user = $gift_order->user;
        $amount = $gift_order->amount;
        $charm_value = $amount;

        if (isPresent($user)) {

            $user->charm_value += $charm_value;

            //异步发起时间
            $time = fetch($opts, 'time', time());

            if (!$user->isCompanyUser()) {
                Users::updateFiledRankList($user->id, 'charm', $charm_value, ['time' => $time]);
            }

            Activities::giftActivityStat($gift_order, $opts);

            $union = $user->union;

            if (isPresent($union) && $union->type == UNION_TYPE_PRIVATE) {

                $user->union_charm_value += $charm_value;

                //不在同一个工会才更新声望值
                if ($gift_order->sender->union_id != $union->id) {
                    Unions::updateFameValue($charm_value, $union->id, ['time' => $time]);
                }
            }

            $user->update();
        }

        unlock($lock);
    }

    function saveFdInfo($fd, $online_token, $ip)
    {
        $hot_cache = self::getHotWriteCache();
        $fd_token_key = "socket_push_online_token_" . $fd;
        $fd_ip_token_key = "socket_push_online_token_" . $fd . '_' . $ip;
        $token_fd_key = "socket_push_fd_" . $online_token;
        $user_id_token_key = "socket_user_online_user_id" . $this->id;
        $token_user_id_key = "socket_fd_user_id" . $online_token;

        $hot_cache->pipeline();
        $hot_cache->setex($fd_token_key, 7 * 24 * 3600, $online_token);
        $hot_cache->setex($fd_ip_token_key, 7 * 24 * 3600, $online_token);
        $hot_cache->setex($token_fd_key, 7 * 24 * 3600, $fd);
        $hot_cache->setex($user_id_token_key, 7 * 24 * 3600, $online_token);
        $hot_cache->setex($token_user_id_key, 7 * 24 * 3600, $this->id);

        if ($ip) {
            $token_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
            $hot_cache->setex($token_intranet_ip_key, 7 * 24 * 3600, $ip);
        }

        $hot_cache->exec();

        info($this->id, 'fd', $fd, $online_token, $ip);
    }

    function deleteFdInfo($fd, $online_token)
    {
        $hot_cache = Users::getHotWriteCache();
        $fd_token_key = "socket_push_online_token_" . $fd;
        $token_fd_key = "socket_push_fd_" . $online_token;
        $token_user_id_key = "socket_fd_user_id" . $online_token;
        $token_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
        $token_room_id_key = "room_token_" . $online_token;
        $token_room_seat_id_key = "room_seat_token_" . $online_token;

        $ip = $hot_cache->get($token_intranet_ip_key);

        info($this->id, 'fd', $fd, $online_token, $ip);

        $hot_cache->pipeline();
        $hot_cache->del($fd_token_key);
        if ($ip) {
            $fd_ip_token_key = "socket_push_online_token_" . $fd . '_' . $ip;
            $hot_cache->del($fd_ip_token_key);
        }
        $hot_cache->del($token_fd_key);
        $hot_cache->del($token_user_id_key);
        $hot_cache->del($token_intranet_ip_key);
        $hot_cache->del($token_room_id_key);
        $hot_cache->del($token_room_seat_id_key);
        $hot_cache->exec();

        if ($this && $this->online_token == $online_token) {
            $user_id_token_key = "socket_user_online_user_id" . $this->id;
            $hot_cache->del($user_id_token_key);
        }
    }

    //是否为公会长
    function isUnionHost($union)
    {
        return $this->id == $union->user_id;
    }

    //更新hi币贡献榜
    function updateHiCoinRankList($sender_id, $hi_coins, $opts = [])
    {
        if ($hi_coins > 0) {
            $db = Users::getUserDb();
            $time = fetch($opts, 'time', time());

            $day_key = "user_hi_coin_rank_list_" . $this->id . "_" . date("Ymd", $time);

            $start = date("Ymd", beginOfWeek($time));
            $end = date("Ymd", endOfWeek($time));
            $weeks_key = "user_hi_coin_rank_list_" . $this->id . "_" . $start . "_" . $end;
            $total_key = "user_hi_coin_rank_list_" . $this->id;

            debug($day_key, $weeks_key, $total_key);
            $hi_coins = intval($hi_coins * 100);
            $db->zincrby($day_key, $hi_coins, $sender_id);
            $db->zincrby($weeks_key, $hi_coins, $sender_id);
            $db->zincrby($total_key, $hi_coins, $sender_id);
        }
    }

    function findHiCoinRankList($list_type, $page, $per_page)
    {
        $db = Users::getUserDb();

        switch ($list_type) {
            case 'day':
                {
                    $key = "user_hi_coin_rank_list_" . $this->id . "_" . date("Ymd");
                    break;
                }
            case 'week':
                {
                    $start = date("Ymd", beginOfWeek());
                    $end = date("Ymd", endOfWeek());
                    $key = "user_hi_coin_rank_list_" . $this->id . "_" . $start . "_" . $end;
                    break;
                }
            case 'total':
                {
                    $key = "user_hi_coin_rank_list_" . $this->id;
                    break;
                }
            default:
                return [];
        }

        $offset = ($page - 1) * $per_page;

        $result = $db->zrevrange($key, $offset, $offset + $per_page - 1, 'withscores');
        $total_entries = $db->zcard($key);

        $ids = [];
        $hi_coins = [];
        foreach ($result as $user_id => $hi_coin) {
            $ids[] = $user_id;
            $hi_coins[$user_id] = $hi_coin;
        }

        $users = Users::findByIds($ids);

        $rank = $offset + 1;
        foreach ($users as $user) {
            $user->contributing_hi_conins = $hi_coins[$user->id] / 100;
            $user->rank = $rank;
            $rank += 1;
        }

        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }

    static function updateFiledRankList($user_id, $field, $value, $opts = [])
    {
        if ($field != 'wealth' && $field != 'charm') {
            return '';
        }

        if ($value > 0) {
            $db = Users::getUserDb();

            $user = Users::findFirstById($user_id);
            if (isBlank($user) || !$user->product_channel_id) {
                info("user_id is invalid", $user);
                return;
            }

            $time = fetch($opts, 'time', time());
            $date = date("Ymd", $time);
            $start = date("Ymd", beginOfWeek($time));
            $end = date("Ymd", endOfWeek($time));

            $day_key = self::generateFieldRankListKey('day', $field, ['date' => $date]);
            $week_key = self::generateFieldRankListKey('week', $field, ['start' => $start, 'end' => $end]);
            $total_key = self::generateFieldRankListKey('total', $field);


            $db->zincrby($day_key . "_" . $user->product_channel_id, $value, $user_id);
            $db->zincrby($day_key, $value, $user_id);

            $db->zincrby($week_key . "_" . $user->product_channel_id, $value, $user_id);
            $db->zincrby($week_key, $value, $user_id);

            $db->zincrby($total_key . "_" . $user->product_channel_id, $value, $user_id);
            $db->zincrby($total_key, $value, $user_id);
        }
    }

    function saveLastFieldRankList($list_type, $field, $rank)
    {
        $db = Users::getUserDb();
        $key = "last_" . $list_type . "_" . $field . "_rank_list";

        $db->zadd($key, $rank, $this->id);
    }

    function myFieldRank($list_type, $field)
    {
        $key = self::generateFieldRankListKey($list_type, $field);

        return $this->getRankByKey($key);
    }


    function myLastFieldRank($list_type, $field)
    {
        $key = "last_" . $list_type . "_" . $field . "_rank_list";
        return $this->getLastRankByKey($key);
    }

    function getLastRankByKey($key)
    {
        $db = Users::getUserDb();
        $rank = $db->zscore($key, $this->id);
        return $rank;
    }

    function myRoomWealthRankByKey($key)
    {
        $db = Users::getUserDb();
        $rank = $db->zrrank($key, $this->id);

        if (is_null($rank)) {
            return 0;
        }

        return $rank + 1;
    }

    function getRankByKey($key)
    {
        $db = Users::getUserDb();
        $rank = $db->zrrank($key, $this->id);

        if (is_null($rank)) {
            $total_entries = $db->zcard($key);
            if ($total_entries) {
                $rank = $total_entries;
            }
        }

        return $rank + 1;
    }

    static function generateFieldRankListKey($list_type, $field, $opts = [])
    {
        switch ($list_type) {
            case 'day':
                {
                    $date = fetch($opts, 'date', date("Ymd"));
                    $key = "day_" . $field . "_rank_list_" . $date;
                    break;
                }
            case 'week':
                {
                    $start = fetch($opts, 'start', date("Ymd", beginOfWeek()));
                    $end = fetch($opts, 'end', date("Ymd", endOfWeek()));
                    $key = "week_" . $field . "_rank_list_" . $start . "_" . $end;
                    break;
                }
            case 'total':
                {
                    $key = "total_" . $field . "_rank_list";
                    break;
                }
            default:
                return '';
        }

        debug($key);

        return $key;
    }


    /**
     * 用户魅力贡献排行榜
     * @param null $day
     * @return string
     */
    static function generateUserRankListKey($day = null)
    {
        if (!$day) $day = date('Ymd');

        $key = 'user_charm_and_wealth_rank_list_day_' . $day;
        return $key;
    }


    /**
     * 更新魅力贡献排行榜
     * @param $receiver
     * @param $sender
     * @param $amount
     * @return bool
     */
    static function updateUserCharmAndWealthRank($receiver_id, $sender_id, $amount)
    {
        $key = self::generateUserRankListKey();
        $user_db = Users::getUserDb();

        // 赠送礼物的增加贡献值，被赠送的增加魅力值
        $user_db->zincrby($key, $amount, $receiver_id);
        $user_db->zincrby($key, $amount, $sender_id);
        return true;
    }


    /**
     * 查询魅力贡献排行榜
     * @param null $time
     * @param int $max_number
     * @return array
     */
    static function findUserCharmAndWealthRank($time = null, $max_number = 100)
    {
        if (!$time) $time = time();
        $day = date('Ymd', $time);

        $key = self::generateUserRankListKey($day);
        $user_db = Users::getUserDb();
        $rank_list = array_keys($user_db->zrevrange($key, 0, $max_number - 1, 'withscores'));

        $yesterday_rank_list = [];
        $number = count($rank_list);
        if (empty($rank_list) || $number < $max_number) {
            $key = self::generateUserRankListKey(date('Ymd', strtotime('-1 day', $time)));
            $yesterday_rank_list = array_keys($user_db->zrevrange($key, 0, $max_number - $number, 'withscores'));
        }
        $rank_list = array_merge($rank_list, $yesterday_rank_list);
        return $rank_list;
    }

    static function findFieldRankList($list_type, $field, $page, $per_page, $opts = [])
    {
        if ($field != 'wealth' && $field != 'charm') {
            return [];
        }

        $key = self::generateFieldRankListKey($list_type, $field, $opts);
        return Users::findFieldRankListByKey($key, $field, $page, $per_page);
    }

    static function findFieldRankListByKey($key, $field, $page, $per_page, $max_entries = 100)
    {
        if (isBlank($key)) {
            return [];
        }

        $offset = ($page - 1) * $per_page;

        $stop = $offset + $per_page - 1;

        if ($offset >= $max_entries) {
            return [];
        }

        if ($stop >= $max_entries) {
            $stop = $max_entries - 1;
        }

        $db = Users::getUserDb();

        $results = $db->zrevrange($key, $offset, $stop, 'withscores');
        $total_entries = $db->zcard($key);

        if ($total_entries > $max_entries) {
            $total_entries = $max_entries;
        }

        $ids = [];
        $fields = [];
        foreach ($results as $user_id => $result) {
            $ids[] = $user_id;
            $fields[$user_id] = $result;
        }

        $users = Users::findByIds($ids);

        $rank = $offset + 1;
        foreach ($users as $user) {
            $user->$field = $fields[$user->id];
            $user->rank = $rank;
            $rank += 1;
        }

        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }


    static function ipLocation($ip)
    {

        $config = self::di('config');
        $endpoint = $config->job_queue->endpoint;
        $x_redis = XRedis::getInstance($endpoint);

        $key = 'ip_location_data_' . $ip;
        $data = $x_redis->get($key);

        if ($data) {
            return json_decode($data, true);
        }

        $data = \IPLocation::find($ip);

        if (is_array($data)) {
            $x_redis->setex($key, 3600 * 24, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        return $data;
    }

    function generateSignInHistoryKey()
    {
        return "sign_in_history_user_" . $this->id;
    }

    function signInGold()
    {
        $golds = [30, 50, 80, 120, 180, 250, 320];

        $db = Users::getUserDb();
        $key = $this->generateSignInHistoryKey();

        $times = $db->get($key);

        $expire = $db->ttl($key);
        $time = 3600 * 24;

        if ($expire > $time) {
            //已签到
            return 0;
        } else if ($expire > 0) {
            //连续签到
            if ($times < count($golds)) {
                return $golds[$times];
            } else {
                return end($golds);
            }
        } else {
            //非连续签到
            return $golds[0];
        }
    }

    function addSignInHistory()
    {
        $golds = [30, 50, 80, 120, 180, 250, 320];

        $res = $this->signInGold();

        $db = Users::getUserDb();
        $key = $this->generateSignInHistoryKey();

        $times = $db->get($key);

        if ($res <= 0) {
            return $res;
        } else if ($res > $golds[0]) {
            $times += 1;
        } else {
            $times = 1;
        }

        $opts = ['remark' => '签到,获得金币' . $res . "个"];
        GoldHistories::changeBalance($this, GOLD_TYPE_SIGN_IN, $res, $opts);

        $time = 3600 * 24;
        $expire = endOfDay() - time() + $time;

        $db->setex($key, $expire, $times);
        return $res;
    }

    function signInStatus()
    {
        if ($this->signInGold()) {
            return USER_SIGN_IN_WAIT;
        }

        return USER_SIGN_IN_SUCCESS;
    }

    function signInMessage()
    {
        $db = Users::getUserDb();
        $key = $this->generateSignInHistoryKey();
        $times = $db->get($key);

        $golds = [30, 50, 80, 120, 180, 250, 320];

        $expire = $db->ttl($key);

        if ($expire > 0) {
            if ($times < count($golds)) {
                $gold = $golds[$times];
            } else {
                $gold = end($golds);
            }
            $time = 3600 * 24;

            if ($expire > $time) {
                $day = "明天";
            } else {
                $day = "今天";
            }
        } else {
            //非连续签到
            $gold = $golds[0];
            $times = 0;
            $day = "今天";
        }

        return "连续签到{$times}天，{$day}签到可获得{$gold}金币";
    }

    function generateShareTask($share_task_type)
    {
        return 'share_task_user_' . $this->id . "share_task_type_" . $share_task_type;
    }

    function shareTaskGold()
    {
        return 50;
    }

    function shareTaskStatus($share_task_type)
    {
        $db = Users::getUserDb();
        $key = $this->generateShareTask($share_task_type);
        if ($db->get($key)) {
            //分享任务已完成
            return STATUS_YES;
        } else {
            //分享任务未完成
            return STATUS_NO;
        }
    }

    function changeShareTaskStatus($share_task_type)
    {
        $db = Users::getUserDb();
        $key = $this->generateShareTask($share_task_type);

        if ($db->get($key)) {
            return false;
        }

        $gold = $this->shareTaskGold();

        $share_des = ShareHistories::$TYPE[$share_task_type];

        $opts = ['remark' => '分享到' . $share_des . '获得金币' . $gold . "个"];

        GoldHistories::changeBalance($this, GOLD_TYPE_SHARE_WORK, $gold, $opts);

        $db->setex($key, endOfDay() - time(), time());
    }

    function isIdCardAuth()
    {
        return AUTH_SUCCESS == $this->id_card_auth;
    }

    function push($opts = [])
    {
        $push_data = [
            'title' => fetch($opts, 'title', $this->product_channel->name),
            'body' => fetch($opts, 'body', ''),
            'badge' => fetch($opts, 'badge', null),
            'offline' => fetch($opts, 'offline', true),
            'client_url' => fetch($opts, 'client_url', 'app://home'),
            'icon_url' => fetch($opts, 'icon_url', $this->product_channel->avatar_url)
        ];

        \Pushers::delay()->push($this->getPushContext(), $this->getPushReceiverContext(), $push_data);
    }

    function unreadMessagesNum()
    {
        $hot_cache = Users::getHotWriteCache();
        return intval($hot_cache->get("unread_messages_num_user_id_" . $this->id));
    }

    function delUnreadMessages()
    {
        $hot_cache = Users::getHotWriteCache();
        $hot_cache->del("unread_messages_num_user_id_" . $this->id);
    }

    function addUnreadMessagesNum()
    {
        $key = "unread_messages_num_user_id_" . $this->id;
        $hot_cache = Users::getHotWriteCache();
        $hot_cache->incr($key);
        $hot_cache->expire($key, 30 * 24 * 3600);
    }

    function isCompanyUser()
    {
        return $this->organisation == USER_ORGANISATION_COMPANY;
    }

    function addCompanyUserSendNumber($send_diamond)
    {
        $cache = \Users::getHotWriteCache();
        $current_day_company_user_send_diamond_to_personage_num = 'current_day_company_user_' . date('Y-m-d', time());
        $cache->zincrby($current_day_company_user_send_diamond_to_personage_num, $send_diamond, $this->id);

        $send_number_over = $cache->zscore($current_day_company_user_send_diamond_to_personage_num, $this->id);
        $total_diamond = $send_diamond;
        if ($send_number_over) {
            $total_diamond = $send_diamond + $send_number_over;
        }

        $cache->zadd($current_day_company_user_send_diamond_to_personage_num, $total_diamond, $this->id);

        $past_at = endOfDay(time()) - time();
        $cache->expire($current_day_company_user_send_diamond_to_personage_num, $past_at);
    }

    function canSendToUser($receiver_ids, $gift_amount)
    {
        if (!$this->isWhiteListUser()) {

            if ($this->isCompanyUser()) {
                $hot_cache = \Users::getHotWriteCache();
                $key = 'current_day_company_user_' . date('Y-m-d', time());
                $send_number = $hot_cache->zscore($key, $this->id);
                $plan_number = $gift_amount + $send_number;

                if ($plan_number > 100) {

                    //内部账号使用

                    $receivers = Users::findByIds($receiver_ids);

                    foreach ($receivers as $receiver) {

                        if (!$receiver->isCompanyUser()) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    function isWhiteListUser()
    {
        $white_list = [100101, 100102, 100103, 1003380, 8888, 1009518, 1106650];

        if (in_array($this->id, $white_list)) {
            return true;
        }
        return false;
    }

    function isSystemUser()
    {
        return SYSTEM_ID == $this->id;
    }

    function bindMobile($mobile)
    {
        if ($this->mobile) {
            return [ERROR_CODE_FAIL, '您已经绑定过手机号码'];
        }

        $this->mobile = $mobile;

        if ($this->update()) {
            $this->bindMobileSendGold();
        }
    }

    function bindMobileSendGold()
    {
        $remark = "绑定手机号码奖励" . BIND_MOBILE_GOLD . "金币";
        GoldHistories::delay()->changeBalance($this->id, GOLD_TYPE_BIND_MOBILE, BIND_MOBILE_GOLD, ['remark' => $remark]);
    }

    //有离线礼物
    function hasOfflineGift()
    {
        $wake_up_user_send_gift_key = "wake_up_user_send_gift_key_user_id_" . $this->id;
        $wake_up_user_send_gift_lock_key = "wake_up_user_send_gift_lock_key_user_id_" . $this->id;

        $hot_cache = self::getHotWriteCache();

        if (!$hot_cache->set($wake_up_user_send_gift_lock_key, 1, ['NX', 'EX' => 2])) {
            info("wake_up_user_send_gift_key_lock", $this->id);
            return null;
        }

        $res = $hot_cache->get($wake_up_user_send_gift_key);
        $hot_cache->del($wake_up_user_send_gift_key);
        return $res;
    }

    static function randomSilentUser()
    {
        $hot_cache = Users::getHotWriteCache();
        $key = "silent_user_key";
        $silent_user_num = $hot_cache->zcard($key);
        $offset = mt_rand(0, $silent_user_num - 1);
        $user_ids = $hot_cache->zrange($key, $offset, $offset);

        if (count($user_ids) < 1) {
            return null;
        }

        $user = Users::findFirstById($user_ids[0]);

        return $user;
    }

    //离线送礼物
    static function sendOfflineSendGift($user_id, $data)
    {

        if (!$data) {
            return;
        }

        if (!is_array($data)) {
            $data = json_decode($data, true);
        }

        $gift_ids = [66, 76];

        if (isDevelopmentEnv()) {
            $gift_ids = [163, 167];
        }

        $gift_id = $gift_ids[array_rand($gift_ids)];
        $sender = Users::randomSilentUser();

        if (!$gift_id || !$sender) {
            info($data, $gift_id);
            return;
        }

        $gift = Gifts::findFirstById($gift_id);

        if (!$gift) {
            info("gift is null", $user_id, $data);
            return;
        }

        GiftOrders::asyncCreateGiftOrder($sender, [$user_id], $gift, 1);

        $content = $sender->nickname . '赠送给你（' . $gift->name . '）礼物，赶紧去看看吧！';
        Chats::sendTextSystemMessage($user_id, $content);
    }

    //获取屏蔽附近的人列表
    static function getBlockedNearbyUserIds()
    {
        $key = 'blocked_nearby_user_list';
        $hot_cache = Users::getHotReadCache();
        return $hot_cache->zrange($key, 0, -1);
    }

    //守护愿望只更新用户自身经验，段位和财富值
    function updateExperienceForWish($amount)
    {
        $lock_key = "update_user_level_lock_" . $this->id;

        $lock = tryLock($lock_key);

        $user_experience = 0.02 * $amount;
        $wealth_value = $amount;
        $this->experience += $user_experience;
        $user_level = $this->calculateLevel();
        $this->level = $user_level;
        $this->segment = $this->calculateSegment();
        $this->wealth_value += $wealth_value;
        $this->update();

        if (!$this->isCompanyUser()) {
            Users::updateFiledRankList($this->id, 'wealth', $wealth_value, ['time' => time()]);
        }

        unlock($lock);
    }

    //获取许愿墙用户分页列表
    static function findByUsersListForWish($relations_key, $page, $per_page)
    {
        $user_db = \Users::getUserDb();

        $offset = $per_page * ($page - 1);
        $res = $user_db->zrevrange($relations_key, $offset, $offset + $per_page - 1, 'withscores');
        $wish_history_ids = [];
        foreach ($res as $wish_history_id => $wish_luck_at) {
            $wish_history_ids[] = $wish_history_id;
        }
        if (!$wish_history_ids) {
            return null;
        }

        $wish_luck_users = self::findByIds($wish_history_ids);

        foreach ($wish_luck_users as $wish_luck_user) {
            $wish_luck_user->winner_at = $res[$wish_luck_user->id];
        }

        $total_entries = $user_db->zcard($relations_key);
        $pagination = new PaginationModel($wish_luck_users, $total_entries, $page, $per_page);
        $pagination->clazz = 'WishLuckUserlist';

        return $pagination;
    }

    //获取系统用户
    static function getSysTemUser()
    {
        $id = SYSTEM_ID;
        $system_user = \Users::findFirstById($id);
        return $system_user;
    }

}