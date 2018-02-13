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

    //好友状态 1已添加,2等待验证，3等待接受
    public $friend_status;

    //是否已关注 true:已关注,false:未关注
    public $followed;

    //是否可以发公屏消息 true可以,false不可以
    public $user_chat;

    function beforeCreate()
    {
        $this->user_status = USER_STATUS_ON;
        if (!$this->user_type) {
            $this->user_type = USER_TYPE_ACTIVE;
        }

        if ($this->mobile) {
            $this->register_at = time();
        }
    }

    function afterCreate()
    {
        if ($this->isActive()) {
            $this->addActiveList();
            if ($this->ip) {
                self::delay(1)->asyncUpdateIpLocation($this->id);
            }
            if ($this->latitude && $this->longitude) {
                self::delay(1)->asyncUpdateGeoLocation($this->id);
            }
            if ($this->mobile) {
                $this->bindMobile();
            }
            \Emchat::delay()->createEmUser($this->id);
            \Chats::delay(5)->sendWelcomeMessage($this->id);
        }
    }

    function beforeUpdate()
    {
        if ($this->hasChanged('mobile') && $this->mobile && $this->register_at < 1) {
            $this->register_at = time();
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

        if ($this->hasChanged('mobile') && $this->mobile) {
            $this->bindMobile();
        }

        if ($this->hasChanged('user_status') && USER_STATUS_LOGOUT == $this->user_status && $this->current_room_id) {
            $this->current_room->exitRoom($this);
        }

        if ($this->hasChanged('user_role_at')) {
            $this->statRoomTime();
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

            if ($action) {
                $db->zincrby(Users::generateStatRoomTimeKey($action), $duration, $this->id);
                $db->zincrby(Users::generateStatRoomTimeKey("total"), $duration, $this->id);
            }
            info($old_user_role, $user_role, $duration, $action, $old_current_room_seat_id, $this->sid);
            return;
        }

        //上麦下面为角色发生变化
        if (USER_ROLE_MANAGER == $user_role) {

            if ($this->hasChanged('current_room_seat_id') && $old_current_room_seat_id) {
                $action = "broadcaster";
            } else {
                $action = "audience";
            }

            if ($action) {
                $db->zincrby(Users::generateStatRoomTimeKey($action), $duration, $this->id);
                $db->zincrby(Users::generateStatRoomTimeKey("total"), $duration, $this->id);
            }
            info($old_user_role, $user_role, $duration, $action, $old_current_room_seat_id, $this->sid);
        }
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
            \Stats::delay()->record('user', 'active_user', $attrs);
        }
        // 重置任务
        if (date('Ymd', $last_at) != date('Ymd', $this->last_at)) {
            $this->deleteExecutedOfflineTaskIds();
        }
    }

    function isSilent()
    {
        return USER_TYPE_SILENT == $this->user_type;
    }

    function isActive()
    {
        return USER_TYPE_ACTIVE == $this->user_type;
    }

    function isBlocked()
    {
        return USER_STATUS_BLOCKED_ACCOUNT == $this->user_status;
    }

    function isNormal()
    {
        if ($this->isWxPlatform() || $this->isTouchPlatform()) {
            return USER_STATUS_ON === $this->user_status || USER_STATUS_LOGOUT == $this->user_status;
        }

        return USER_STATUS_ON === $this->user_status;
    }

    static function getUserDb()
    {
        $endpoint = self::config('user_db_endpoints');
        return XRedis::getInstance($endpoint);
    }

    function getPushContext()
    {
        return $this->product_channel->getPushContext($this->platform);
    }

    function getPushReceiverContext()
    {
        return ['id' => $this->id, 'platform' => $this->platform, 'push_token' => $this->push_token, 'push_type' => $this->push_type];
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
        if ($exist_user) {
            return [ERROR_CODE_FAIL, '用户已注册', null];
        }

        $user = $current_user;
        //换个手机号注册，重新生成用户
        if ($current_user->mobile && $current_user->mobile != $mobile) {
            $user = Users::registerForClientByDevice($device, true);
            info('换个手机号注册', $user->id, $context);
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
        $user->login_type = USER_LOGIN_TYPE_MOBILE;
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
        // 重复激活
        // dno重复，存在bug
        $user = $device->user;
        if (!$user || $is_force) {

            $fields = ['product_channel_id', 'platform', 'platform_version', 'version_code', 'version_name',
                'api_version', 'device_no', 'fr', 'partner_id', 'manufacturer', 'ip', 'latitude', 'longitude',
                'push_token'];

            $user = new \Users();
            $user->login_name = md5(uuid()) . '@app.com';
            $user->device = $device;
            $user->device_id = $device->id;
            $user->device_no = $device->device_no;

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

        foreach (['ip', 'password', 'platform', 'version_name', 'version_code'] as $key) {

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


        $this->sid = $this->generateSid('s');
        $this->device_id = $device->id;
        $this->user_status = USER_STATUS_ON;
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
            httpSave($headimgurl, $avatar_file);
            $user->updateAvatar($avatar_file);
            unlink($avatar_file);
        }
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
            return $this->mobile && preg_match('/^\d+s/', $this->sid) && $this->user_status == USER_STATUS_ON;
        }

        return !!$this->mobile;
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

    public static function registerForTouch($product_channel, $opts = [])
    {

        $user = new \Users();
        $user->password = uniqid();
        $user->product_channel_id = $product_channel->id;
        $user->user_type = USER_TYPE_ACTIVE;
        $user->user_status = USER_STATUS_ON;
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
        $user->user_status = USER_STATUS_ON;
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
        $user->user_status = USER_STATUS_ON;
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

        $stat_keys = ['platform', 'version_code', 'product_channel_id', 'id', 'province_id', 'sex', 'ip', 'partner_id'];
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
        info($user->id, $latitude, $longitude, $hash);
        if ($hash) {
            $user->geo_hash = $hash;
        }

        $user->update();
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
                $city = \Cities::findByIp($user->ip);
                if ($city) {

                    if (!$user->city_id) {
                        $user->city_id = $city->id;
                    }

                    $user->ip_city_id = $city->id;
                }

                debug($user->id, 'ip', $user->ip, $user->province_id, $user->city_id);
                $user->update();
            }
        }
    }

    function bindMobile()
    {
        self::delay(2)->checkRegisterMobile($this->mobile);
        self::delay(2)->registerStat($this->id);
    }

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

    static function registerStat($user_id)
    {
        $user = Users::findFirstById($user_id);
        \Stats::delay()->record('user', 'register', $user->getStatAttrs());

        $other_user = Users::findFirst(['conditions' => 'mobile=:mobile: and id!=:id:',
            'bind' => ['mobile' => $user->mobile, 'id' => $user->id], 'order' => 'id asc'
        ]);

        // 手机第一次注册
        if (!$other_user && time() - $user->register_at < 60) {
            debug('first_register_mobile', $user->mobile);
            \Stats::delay()->record('user', 'first_register_mobile', $user->getStatAttrs());
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

            if ('province_name' == $k) {
                $province = Provinces::findFirstByName($v);
                if ($province) {
                    $this->province_id = $province->id;
                }

                continue;
            }

            if ('city_name' == $k) {
                $city = Cities::findFirstByName($v);

                debug($v);

                if ($city) {

                    debug($city->id);

                    $this->province_id = $city->province_id;
                    $this->city_id = $city->id;
                }

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

            info("black success", $black_key, $blacked_key);

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

            info("unblack success", $black_key, $blacked_key);

            $user_db->zrem($black_key, $other_user->id);
            $user_db->zrem($blacked_key, $this->id);
        }
    }

    //获取拉黑，关注，好友的列表
    static function findByRelations($relations_key, $page, $per_page)
    {
        $user_db = Users::getUserDb();

        $offset = $per_page * ($page - 1);
        $res = $user_db->zrevrange($relations_key, $offset, $offset + $per_page - 1, 'withscores');
        $user_ids = [];
        $times = [];

        foreach ($res as $user_id => $time) {
            $user_ids[] = $user_id;
            $times[$user_id] = $time;
        }

        $users = Users::findByIds($user_ids);

        foreach ($users as $user) {
            $user->created_at = fetch($times, $user->id);
        }

        $total_entries = $user_db->zcard($relations_key);
        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }

    //黑名单列表
    function blackList($page, $per_page)
    {
        $black_key = "black_list_user_id" . $this->id;
        $users = self::findByRelations($black_key, $page, $per_page);
        return $users;
    }

    //是否已关注
    function isFollow($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $follow_key = 'follow_list_user_id' . $this->id;
        return $user_db->zscore($follow_key, $other_user->id) > 0;
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
        $follow_list = self::findByRelations($follow_key, $page, $per_page);
        return $follow_list;
    }

    //关注我的列表
    function followedList($page, $per_page)
    {
        $followed_key = 'followed_list_user_id' . $this->id;
        $followed_list = self::findByRelations($followed_key, $page, $per_page);
        return $followed_list;
    }

    //关注我的人数
    function followedNum()
    {
        $followed_key = 'followed_list_user_id' . $this->id;
        $user_db = Users::getUserDb();
        return $user_db->zcard($followed_key);
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
        if (!$user_db->zscore($other_total_key, $this->id)) {
        }

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
        $self_introduce = $user_db->hget($user_introduce_key, $other_user->id);
        return $self_introduce;
    }

    //好友列表
    function friendList($page, $per_page, $new)
    {
        if (1 == $new) {
            //进入列表清空消息
            $this->clearNewFriendNum();
            $key = 'friend_total_list_user_id_' . $this->id;
        } else {
            $key = 'friend_list_user_id_' . $this->id;
        }

        $users = self::findByRelations($key, $page, $per_page);
        foreach ($users as $user) {

            //3接受状态 2等待状态 1已添加
            if ($new == 1) {
                $friend_status = 3;
                if ($this->isFriend($user)) {
                    $friend_status = 1;
                } elseif ($this->isAddFriend($user)) {
                    $friend_status = 2;
                }
            } else {
                $friend_status = 1;
            }

            $user->self_introduce = $this->getSelfIntroduceText($user);
            $user->friend_status = $friend_status;
        }

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
        $province_id = fetch($opts, 'province_id');
        $city_id = fetch($opts, 'city_id');

        if ($user_id) {
            $cond = ['conditions' => 'id = :user_id:', 'bind' => ['user_id' => $user_id]];
        } else {
            $cond = ['conditions' => 'id <> ' . $user->id];
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

        $user_type = fetch($opts, 'user_type');
        if ($user_type) {
            $cond['conditions'] .= " and user_type = " . $user_type;
        }

        $cond['conditions'] .= " and id != " . SYSTEM_ID . " and avatar_status = " . AUTH_SUCCESS . ' and user_status = ' . USER_STATUS_ON;
        $cond['order'] = 'last_at desc,id desc';

        info($user->id, $cond);

        $users = Users::findPagination($cond, $page, $per_page);

        return $users;
    }

    // 附近人
    function nearby($page, $per_page, $opts = [])
    {

        $latitude = $this->latitude / 10000;
        $longitude = $this->longitude / 10000;

        if (!$latitude || !$longitude) {
            $users = \Users::search($this, $page, $per_page);
            return $users;
        }

        $geohash = new \geo\GeoHash();
        $hash = $geohash->encode($latitude, $longitude);
        //取前缀，前缀约长范围越小
        $prefix = substr($hash, 0, 6);
        //取出相邻八个区域
        $neighbors = $geohash->neighbors($prefix);
        array_push($neighbors, $prefix);

        debug($this->id, $neighbors);

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

        $condition .= ' and id <> :user_id: and id != ' . SYSTEM_ID . ' and avatar_status = ' . AUTH_SUCCESS;
        $condition .= ' and user_status = ' . USER_STATUS_ON . ' and user_type = ' . USER_TYPE_ACTIVE;
        $bind['user_id'] = $this->id;

        $conds['conditions'] = $condition;
        $conds['bind'] = $bind;
        $conds['order'] = 'last_at desc,id desc';

        info($this->id, $hash, $conds);

        $users = Users::findPagination($conds, $page, $per_page);

        if ($users->count() < 3) {
//            $opts['city_id'] = $this->getSearchCityId();
//            if (!$opts['city_id']) {
//                $opts['province_id'] = $this->getSearchProvinceId();
//            }
            $users = \Users::search($this, $page, $per_page, $opts);
        }

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
                $geo_distance = sprintf("%0.2f", $geo_distance / 1000);
                if ($geo_distance < 0.01) {
                    $geo_distance = 0.01;
                }
                $user->distance = $geo_distance . 'km';

                debug($this->id, $user->id, $geo_distance, $user->distance);
            } else {
                $geo_distance = abs($this->id - $user->id) % 1000;
                $geo_distance = $geo_distance / 100;
                if ($geo_distance < 0.01) {
                    $geo_distance = 0.01;
                }
                $user->distance = $geo_distance . 'km';
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
        $db = Rooms::getRoomDb();
        $manager_list_key = $room->generateManagerListKey();
        return $db->zscore($manager_list_key, $this->id) > 0;
    }

    //是否为房间永久的管理员
    function isPermanentManager($room)
    {
        $db = Rooms::getRoomDb();
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

    static function pushTopTopicMessage($user_id, $room_id)
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

    static function pushGiftMessage($user_id, $room_id)
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
                $gifts = Gifts::findBy(['status' => STATUS_ON]);
                $gift_ids = [];

                foreach ($gifts as $gift) {
                    $gift_ids[] = $gift->id;
                }

                $index = array_rand($gift_ids);
                $gift_id = $gift_ids[$index];
                $gift = Gifts::findFirstById($gift_id);

                if ($receiver->isActive()) {
                    $give_result = GiftOrders::giveTo($user->id, $receiver->id, $gift, $gift_num);
                    if ($give_result) {
                        $room->pushGiftMessage($user, $receiver, $gift, $gift_num);
                    }
                } else {
                    $room->pushGiftMessage($user, $receiver, $gift, $gift_num);
                }
            }
        }
    }

    static function pushUpMessage($user_id, $room_id)
    {
        $room = Rooms::findFirstById($room_id);
        $user = Users::findFirstById($user_id);

        if (!$room || !$user) {
            return;
        }

        if (!$user->isInRoom($room)) {
            return;
        }

        if ($user->current_room_seat_id < 1 && $room->getRealUserNum() > 0) {

            $room_seat = \RoomSeats::findFirst(['conditions' => 'room_id = ' . $room->id . " and (user_id = 0 or user_id is null) and status = " . STATUS_ON]);

            if ($room_seat) {
                $room_seat->up($user);
                $room->pushUpMessage($user, $room_seat);
            }
        }
    }

    //启动房间互动
    function activeRoom($room)
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

        info($rand_num, $this->id, $room->id);

        if ($rand_num <= 50) {
            Users::delay(mt_rand(1, 50))->pushTopTopicMessage($this->id, $room->id);
        } elseif (50 < $rand_num && $rand_num <= 60) {
            Users::delay(mt_rand(1, 50))->pushGiftMessage($this->id, $room->id);
        } elseif (60 < $rand_num && $rand_num <= 90) {
            Users::delay(mt_rand(1, 50))->pushUpMessage($this->id, $room->id);
        } else {
            $room->exitSilentRoom($this);
            return;
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

            $old_user = \Users::findFirstByLoginName($user->login_name);
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
            $user->user_status = USER_STATUS_ON;
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

    function changeAvatarAuth($avatar_auth)
    {
        if (isBlank($avatar_auth) ||
            !array_key_exists(intval($avatar_auth), \UserEnumerations::$AVATAR_STATUS)) {
            return;
        }
        $this->avatar_auth = $avatar_auth;
        $this->update();

        if (AUTH_SUCCESS == intval($avatar_auth)) {
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
}