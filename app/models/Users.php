<?php


class Users extends BaseModel
{
    use UserEnumerations;
    use UserAttrs;

    // 用户状态
    static $USER_STATUS = [USER_STATUS_OFF => '注销', USER_STATUS_ON => '正常', USER_STATUS_BLOCKED_ACCOUNT => '封账号',
        USER_STATUS_BLOCKED_DEVICE => '封设备', USER_STATUS_LOGOUT => '已退出'];
    // 用户类型
    static $USER_TYPE = [USER_TYPE_ACTIVE => '活跃', USER_TYPE_SILENT => '沉默', USER_TYPE_TEST => '测试'];

    static $SEX = [USER_SEX_MALE => '男', USER_SEX_FEMALE => '女'];

    static $PLATFORM = [USER_PLATFORM_IOS => '苹果客户端', USER_PLATFORM_ANDROID => '安卓客户端',
        USER_PLATFORM_WEIXIN_IOS => '微信苹果端', USER_PLATFORM_WEIXIN_ANDROID => '微信安卓端'];

    static $LOGIN_TYPE = [USER_LOGIN_TYPE_MOBILE => '手机', USER_LOGIN_TYPE_WEIXIN => '微信', USER_LOGIN_TYPE_QQ => 'QQ',
        USER_LOGIN_TYPE_OTHER => '其他'];

    static $PROVINCE = array(1 => "北京", 2 => "上海", 3 => "天津", 4 => "重庆",
        5 => "河北", 6 => "山西", 7 => "河南", 8 => "辽宁",
        9 => "吉林", 10 => "黑龙江", 11 => "内蒙古", 12 => "江苏",
        13 => "山东", 14 => "安徽", 15 => "浙江", 16 => "福建",
        17 => "湖北", 18 => "湖南", 19 => "广东", 20 => "广西",
        21 => "江西", 22 => "四川", 23 => "海南", 24 => "贵州",
        25 => "云南", 26 => "西藏", 27 => "陕西", 28 => "甘肃",
        29 => "青海", 30 => "宁夏", 31 => "新疆", 32 => "台湾",
        33 => "香港", 34 => "澳门");

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

            $user->city_id = fetch($params, 'city_id');
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
                $user->province_id = $province->id;
                $user->ip_province_id = $province->id;
                $city = \Cities::findByIp($user->ip);
                if ($city) {
                    $user->city_id = $city->id;
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

            if (!isPresent($v)) {
                continue;
            }

            if (!array_key_exists($k, self::$UPDATE_FIELDS)) {
                continue;
            }

            if ($k == 'province_name') {
                $province = Provinces::findFirstByName($k);

                if ($province) {
                    $this->province_id = $province->id;
                }

                continue;
            }

            if ($k == 'city_name') {
                $city = Cities::findFirstByName($k);

                if ($city) {
                    $this->province_id = $city->province_id;
                    $this->city_id = $city->city_id;
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

    //关注
    function follow($other_user, $opts = [])
    {

    }

    //取消关注
    function unFollow($other_user, $opts = [])
    {

    }

    //我关注的列表
    function followList()
    {

    }

    //关注我的列表
    function followedList()
    {

    }


    //添加好友
    function addFriend($other_user, $opts = [])
    {

    }

    //删除好友
    function deleteFriend($other_user, $opts = [])
    {

    }

    //是否为好友
    function isFriend($other_user, $opts = [])
    {

    }

    //好友列表
    function friendList($type = null)
    {

    }

    //同意添加好友
    function agreeAddFriend()
    {

    }

}