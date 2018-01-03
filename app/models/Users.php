<?php


class Users extends BaseModel
{
    use UserEnumerations;
    use UserAttrs;

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



    //好友状态 1已添加,2等待验证，3等待接受
    public $friend_status;

    //是否已关注 true:已关注,false:未关注
    public $followed;

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

        if ($this->hasChanged('status')) {
            if (!$this->isNormal() && $this->isClientPlatform() && $this->device) {

                $payload = array('model' => 'user', 'user' => ['action' => 'logout', 'sid' => $this->device->sid]);
                $client_url = 'app://users/logout';
                $receiver_context = $this->getPushReceiverContext();
                $push_data = ['title' => '', 'body' => '请重新登录!', 'payload' => $payload,
                    'badge' => null, 'offline' => true, 'client_url' => $client_url, 'icon_url' => $this->product_channel->avatar_url];

                info('用户状态异常 push_logout', $this->id, $push_data);

                \Pushers::delay()->push($this->getPushContext(), $receiver_context, $push_data);
            }
        }

        if ($this->hasChanged('last_at')) {
            $last_at = $this->was('last_at');
            if (date('YmdH', $last_at) != date('YmdH', $this->last_at)) {
                $attrs = $this->getStatAttrs();
                // 统计活跃
                \Stats::delay()->record('user', 'active_user', $attrs);

                if ($this->mobile) {
                    \Stats::delay()->record('user', 'active_register_user', $attrs);

                    $attrs['id'] = $this->mobile;
                    \Stats::delay()->record('user', 'active_mobile', $attrs);
                }
            }
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

    static function registerByClientMobile($product_channel, $device, $mobile, $context = [], $params = [])
    {
        if (isBlank($mobile)) {
            return [ERROR_CODE_FAIL, '手机号错误', null];
        }
        if (!$device || !$device->can_register && isProduction()) {
            info('false_device', $product_channel->code, $mobile, $context, $params);
            return [ERROR_CODE_FAIL, '设备错误!!', null];
        }

        $user = \Users::findFirstByMobile($product_channel, $mobile);
        $id_name = fetch($params, 'id_name');
        if (!$user) {

            debug("client no user");
            $user = new \Users();

            $user->id_name = $id_name;
            $id_no = fetch($params, 'id_no');
            if ($id_no) {
                $user->id_no = $id_no;
                $sex = substr($id_no, -2, -1);
                if ($sex % 2 == 1) {
                    $sex = USER_SEX_MALE;
                } else {
                    $sex = USER_SEX_FEMALE;
                }
                $user->sex = $sex;
            }

            $province_name = fetch($params, 'province_name');
            $city_name = fetch($params, 'city_name');
            $province = Provinces::findFirstByName($province_name);
            $city = Cities::findFirstByName($city_name);

            if ($province && $city) {
                $user->province_id = $province->id;
                $user->city_id = $city->id;
            }

            $user->platform = fetch($context, 'platform');
            $user->ip = fetch($context, 'ip');

            $fr = $device->fr;
            if (!$fr) {
                $fr = fetch($context, 'fr');
            }

            $user->fr = $fr;
            $partner = \Partners::findFirstByFrHotCache($fr);
            if ($partner) {
                $user->partner_id = $partner->id;
                // 特殊情况
                $device->fr = $fr;
                $device->partner_id = $partner->id;
            }

            $user->device_id = $device->id;
            $device->reg_num += 1;
            $device->update();
        } else {
            return [ERROR_CODE_FAIL, '已注册', null];
        }

        $user->mobile = $mobile;
        $password = fetch($params, 'password');

        if ($password) {
            $user->password = md5($password);

        }

        if (isBlank($user->login_name)) {
            $user->login_name = md5(uuid()) . '@app.com';
        }
        if (isBlank($user->nickname)) {
            $user->nickname = $user->getMaskedMobile() ?? '昵称';
        }

        $user->product_channel_id = $product_channel->id;
        $user->user_type = USER_TYPE_ACTIVE;
        $user->login_type = USER_LOGIN_TYPE_MOBILE;

        if ($device) {
            $user->device_id = $device->id;
        }

        $user->save();

        $user->sid = $user->generateSid();
        $user->update();

        \Stats::delay()->record('user', 'register', $user->getStatAttrs());

        $other_user = Users::findFirst(['conditions' => 'mobile=:mobile: and id!=:id:',
            'bind' => ['mobile' => $user->mobile, 'id' => $user->id], 'order' => 'id asc'
        ]);
        if (!$other_user) {
            debug('register_mobile', $user->mobile);
            \Stats::delay()->record('user', 'register_mobile', $user->getStatAttrs());
        }

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
            $old_device = \Devices::findFirstById($this->device_id);
            if ($old_device) {

                $payload = array('model' => 'user', 'user' => array('action' => 'logout', 'sid' => $old_device->sid));
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


        $this->sid = $this->generateSid();
        $this->device_id = $device->id;
        $this->user_status = USER_STATUS_ON;

        $this->update();
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
    function generateSid()
    {
        $src = $this->id . uniqid(mt_rand()) . microtime();

        $src = $this->id . "s" . md5($src);
        $src .= calculateSum($src);

        return $src;
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
            $this->update();
            //  删除老头像
            if ($old_avatar) {
                \StoreFile::delete($old_avatar);
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

            // 更新最后登录的用户
            $device->user_id = $this->id;
            $device->update();

            //在同一台手机上，先后登录A,B用户，防止B用户接收到A用户的消息推送，（pushToList没有指定用户ID，所以不能客户端无法判断是发给哪个用户的）
            $other_users = \Users::find([
                    'conditions' => 'id != :id: and device_id = :device_id: and product_channel_id = :product_channel_id:',
                    'bind' => array('id' => $this->id, 'device_id' => $this->device_id, 'product_channel_id' => $this->product_channel_id)]
            );

            foreach ($other_users as $other_user) {
                info('false_push_token', $this->id, $other_user->id, $this->device_id);
                $other_user->push_token = '';
                $other_user->update();
            }

            $this->update();
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

        $user->sid = $user->generateSid();
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

        $user->sid = $user->generateSid();
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

        $user->sid = $user->generateSid();
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
        debug("fresh params", $this->id);

        $fresh_attrs = [];
        $platform = fetch($opts, 'platform');
        if ($platform && $this->platform !== $platform) {
            $fresh_attrs['platform'] = $platform;
        }

        $version_name = fetch($opts, 'version_name');
        if ($version_name && $this->version_name !== $version_name) {
            $fresh_attrs['version_name'] = $version_name;
        }

        $version_code = fetch($opts, 'version_code');
        if ($version_code && $this->version_code !== $version_code) {
            $fresh_attrs['version_code'] = $version_code;
        }

        $api_version = fetch($opts, 'api_version');
        if ($api_version && $this->api_version !== $api_version) {
            $fresh_attrs['api_version'] = $api_version;
        }

        $province_id = fetch($opts, 'province_id');
        if ($province_id && $this->province_id != $province_id) {
            $fresh_attrs['province_id'] = $province_id;
        }

        $city_id = fetch($opts, 'city_id');
        if ($city_id && $this->city_id != $city_id) {
            $fresh_attrs['city_id'] = $city_id;
        }

        $ip = fetch($opts, 'ip');
        if ($ip && $this->ip != $ip) {
            $fresh_attrs['ip'] = $ip;
        }

        $latitude = fetch($opts, 'latitude');
        $longitude = fetch($opts, 'longitude');
        if ($latitude && $longitude && ($this->latitude != $latitude || $this->longitude != $longitude)) {
            $fresh_attrs['latitude'] = $latitude;
            $fresh_attrs['longitude'] = $longitude;
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

    static function asyncUpdateGeoLocation($user_id, $latitude = null, $longitude = null)
    {
        $user = self::findFirstById($user_id);
        if (!$latitude) {
            $latitude = $user->latitude;
            $longitude = $user->longitude;
        }

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
            $user->update();
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

            if (!isPresent($v) && $k != 'sex') {
                continue;
            }

            if ($k == 'province_name') {
                $province = Provinces::findFirstByName($v);
                if ($province) {
                    $this->province_id = $province->id;
                }

                continue;
            }

            if ($k == 'city_name') {
                $city = Cities::findFirstByName($v);
                if ($city) {
                    $this->province_id = $city->province_id;
                    $this->city_id = $city->id;
                }

                continue;
            }

            if ($k == 'city_id') {
                $city = \Cities::findFirstById($k);
                $this->province_id = $city->province_id;
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
            $user->created_at = fetch($times, $user_id);
        }

        return $users;
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
            $user_db->zrem($follow_key, time(), $other_user->id);
            $user_db->zrem($followed_key, time(), $this->id);
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

        $user_db = Users::getUserDb();

        //在添加我的队列里面清掉对方的id
        if ($user_db->zscore('added_friend_list_user_id_' . $this->id, $other_user->id)) {
            $user_db->zrem('added_friend_list_user_id_' . $this->id, $other_user->id);
        }

        //在对方添加的队列中清掉我的id
        if ($user_db->zscore('add_friend_list_user_id_' . $other_user->id, $this->id)) {
            $user_db->zrem('add_friend_list_user_id_' . $other_user->id, $this->id);
        }

        //没有在对方总队列里面添加 此时要做通知
        if (!$user_db->zscore($other_total_key, $this->id)) {
        }

        $user_db->zadd($add_key, time(), $other_user->id);
        $user_db->zadd($added_key, time(), $this->id);
        $user_db->zadd($add_total_key, time(), $other_user->id);
        $user_db->zadd($other_total_key, time(), $this->id);
    }

    //删除好友
    function deleteFriend($other_user, $opts = [])
    {
        $user_db = Users::getUserDb();
        $friend_list_key = 'friend_list_user_id_' . $this->id;
        $other_friend_list_key = 'friend_list_user_id_' . $other_user->id;

        if ($user_db->zscore($friend_list_key, $other_user->id)) {
            $user_db->zrem($friend_list_key, $other_user->id);
        }

        if ($user_db->zscore($other_friend_list_key, $this->id)) {
            $user_db->zrem($other_friend_list_key, $this->id);
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

    //好友列表
    function friendList($page, $per_page, $new)
    {
        if (1 == $new) {
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

            $user->friend_status = $friend_status;
        }

        return $users;
    }

    //同意添加好友
    function agreeAddFriend($other_user)
    {
        $friend_list_key = 'friend_list_user_id_' . $this->id;
        $other_friend_list_key = 'friend_list_user_id_' . $other_user->id;
        $add_key = 'add_friend_list_user_id_' . $this->id;
        $added_key = 'added_friend_list_user_id_' . $other_user->id;

        $user_db = Users::getUserDb();

        if ($user_db->zscore($add_key, $other_user->id)) {
            $user_db->zrem($add_key, $other_user->id);
            $user_db->zadd($friend_list_key, time(), $other_user->id);
        }

        if ($user_db->zscore($added_key, $this->id)) {
            $user_db->zrem($added_key, $this->id);
            $user_db->zadd($other_friend_list_key, time(), $this->id);
        }
    }

    //清空添加好友信息
    function clearAddFriendInfo()
    {
        $user_db = Users::getUserDb();
        $key = 'friend_total_list_user_id_' . $this->id;
        $user_db->zclear($key);
    }

    function friendNum()
    {
        $key = 'friend_list_user_id_' . $this->id;
        $user_db = Users::getUserDb();
        return $user_db->zcard($key);
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
}