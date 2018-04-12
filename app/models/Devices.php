<?php

class Devices extends BaseModel
{
    /**
     * @type ProductChannels
     */
    private $_product_channel;
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
     * @type Users
     */
    private $_user;

    static $STATUS = [DEVICE_STATUS_NORMAL => '正常', DEVICE_STATUS_BLOCK => '封设备', DEVICE_STATUS_WHITE => '白名单'];

    static $CLIENT_STATUS = [STATUS_ON => '前台', STATUS_OFF => '后台'];


    function afterCreate()
    {
        $this->addActiveList();

        if ($this->ip) {
            self::delay(1)->asyncUpdateIpLocation($this->id);
        }

        if ($this->latitude && $this->longitude) {
            self::delay(1)->asyncUpdateGeoLocation($this->id);
        }
    }

    function beforeUpdate()
    {

        if ($this->hasChanged('user_id')) {
            $reg_num = Users::count(['conditions' => 'device_id = :device_id:', 'bind' => ['device_id' => $this->id]]);
            $this->reg_num = $reg_num;
        }
    }

    function afterUpdate()
    {
        if ($this->hasChanged('status')) {
            if ($this->isBlocked()) {
                $users = Users::findForeach(['conditions' => 'device_id=:device_id:',
                    'bind' => ['device_id' => $this->id]]);
                foreach ($users as $user) {
                    $user->user_status = USER_STATUS_BLOCKED_DEVICE;
                    $user->update();
                }
            }
        }

        if ($this->hasChanged('ip') && $this->ip) {
            self::delay(1)->asyncUpdateIpLocation($this->id);
        }

        if (($this->hasChanged('latitude') && $this->latitude) || ($this->hasChanged('longitude') && $this->longitude)) {
            self::delay(1)->asyncUpdateGeoLocation($this->id);
        }

    }


    static function active($product_channel, $attributes)
    {
        $device = self::findFirst([
            'conditions' => 'device_no = :device_no: and product_channel_id=:product_channel_id:',
            'bind' => ['device_no' => $attributes['device_no'], 'product_channel_id' => $product_channel->id],
            'order' => 'id desc'
        ]);

        debug($product_channel->id, $attributes);

        foreach ($attributes as $k => $v) {
            debug($k, $v);
        }


        if ($device) {

            //临时修复,老版安卓
            foreach (['imei', 'imsi', 'idfa'] as $k) {
                if (isset($attributes[$k]) && $attributes[$k]) {
                    $device->$k = $attributes[$k];
                }
            }

            // 测试渠道包fr
            if ($device->inWhiteList()) {

                $promote_fr = Partners::getPromoteFr($attributes);

                if ($promote_fr) {
                    $device->fr = $promote_fr;
                } else {
                    $device->fr = fetch($attributes, 'fr');
                }

                if (isPresent($device->fr)) {
                    $partner = \Partners::findFirstByFrHotCache($device->fr);
                    if ($partner) {
                        $device->partner_id = $partner->id;
                    }
                }

                info('测试渠道包fr', $device->device_no, $promote_fr, $device->fr, $attributes);
            }

            $device->update();
            info('false repeat_device', $device->id, $product_channel->code, 'imei', $device->imei, 'idfa', $device->idfa, $attributes);
            return $device;
        }

        $device = new Devices();
        foreach (['platform', 'platform_version', 'device_no', 'fr', 'ua', 'imei', 'imsi',
                     'manufacturer', 'model', 'version_code', 'version_name', 'ip', 'idfa', 'phone_number',
                     'lang', 'net', 'local_mac', 'gateway_mac'] as $k) {

            if (isset($attributes[$k])) {
                $device->$k = $attributes[$k];
            }
        }

        $device->api_version = fetch($attributes, 'an');
        $device->reg_num = 0;
        $device->status = DEVICE_STATUS_NORMAL;

        $promote_fr = Partners::getPromoteFr($attributes);
        if ($promote_fr) {
            $device->fr = $promote_fr;
        }

        if (isPresent($device->fr)) {
            $partner = \Partners::findFirstByFrHotCache($device->fr);
            if ($partner) {
                $device->partner_id = $partner->id;
            }
        }

        if ($product_channel) {
            $device->product_channel_id = $product_channel->id;
        }

        $device->save();

        $device->sid = $device->generateSid();
        $device->update();

        $attrs = $device->getStatAttrs();
        \Stats::delay()->record('user', 'device_active', $attrs);

        return $device;
    }

    static function setMarketingStartAppMuid($muid)
    {
        $user_db = Devices::getHotWriteCache();
        $marketing_start_app_key = 'marketing_api_start_app_muid_' . $muid;
        $user_db->setex($marketing_start_app_key, 2 * 24 * 60 * 60, $muid);
    }

    static function getMarketingStartAppMuid($muid)
    {
        $user_db = Devices::getHotWriteCache();
        $marketing_start_app_key = 'marketing_api_start_app_muid_' . $muid;
        return $user_db->get($marketing_start_app_key);
    }

    private function generateSid()
    {
        $src = $this->id . uniqid(mt_rand()) . microtime();
        $src = $this->id . "d" . md5($src);
        $src .= calculateSum($src);

        return $src;
    }

    function canRegister()
    {
        if ($this->isBlocked()) {
            return false;
        }

        if ($this->status == DEVICE_STATUS_WHITE) {
            return true;
        }

        return $this->reg_num <= 10;
    }

    public function getStatAttrs()
    {

        $stat_keys = ['platform', 'version_code', 'product_channel_id', 'id', 'ip', 'partner_id'];
        $hash = [];
        foreach ($stat_keys as $key) {
            $hash[$key] = $this->$key;
        }

        $hash['created_at'] = intval($this->created_at);
        $hash['stat_at'] = time();

        return $hash;
    }

    function isBlocked()
    {
        return DEVICE_STATUS_BLOCK === $this->status;
    }

    function isNormal()
    {
        return DEVICE_STATUS_NORMAL === $this->status || DEVICE_STATUS_WHITE === $this->status;
    }

    function getIpText()
    {
        return $this->ip . ' ' . Provinces::findIpPosition($this->ip);
    }

    function mergeJson()
    {
        return ['product_channel_name' => $this->product_channel_name, 'partner_name' => $this->partner_name,
            'geo_province_name' => $this->geo_province_name, 'geo_city_name' => $this->geo_city_name,
            'province_name' => $this->province_name, 'city_name' => $this->city_name];
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

    static function asyncUpdateGeoLocation($device_id)
    {
        $device = self::findFirstById($device_id);
        $latitude = $device->latitude;
        $longitude = $device->longitude;

        if (!$latitude && !$longitude) {
            info('false update geo_location', $device->id);
            return;
        }

        $latitude = $latitude / 10000;
        $longitude = $longitude / 10000;
        $result = \Location::gdAddress($latitude, $longitude);
        debug($device->id, $result);

        if ($result && isset($result[0])) {
            $province = \Provinces::findFirstByName($result[0]);
            if ($province) {
                $device->geo_province_id = $province->id;
            }

            if (isset($result[1])) {
                $city = \Cities::findFirstByName($result[1]);
                if ($city) {
                    $device->geo_city_id = $city->id;
                }
            }

            debug($device->id, 'geo', $device->geo_province_id, $device->geo_city_id);

            $device->update();
        }
    }

    static function asyncUpdateIpLocation($device_id)
    {

        $device = self::findFirstById($device_id);
        if ($device && $device->ip) {
            $province = \Provinces::findByIp($device->ip);
            if ($province) {

                if (!$device->province_id) {
                    $device->province_id = $province->id;
                }

                $device->ip_province_id = $province->id;
                $city = \Cities::findByIp($device->ip);
                if ($city) {

                    if (!$device->city_id) {
                        $device->city_id = $city->id;
                    }

                    $device->ip_city_id = $city->id;
                }

                debug($device->id, 'ip', $device->ip, $device->province_id, $device->city_id);
                $device->update();
            }
        }
    }

    function addActiveList()
    {

        $hot_cache = self::getHotWriteCache();

        $group_key = 'devices_active_group_' . $this->product_channel_id;
        $hot_cache->zadd($group_key, time(), $this->id);
        $hot_cache->expire($group_key, 60 * 60 * 24 * 30);
    }

    static public function exportColumn($column, $start_at, $end_at, $temp_file)
    {

        $is_export_md5 = false;
        if (preg_match('/_md5/', $column)) {
            $column = preg_replace('/_md5/', '', $column);
            $is_export_md5 = true;
        }

        $conds = ['conditions' => 'created_at>=:start_at: and created_at<=:end_at:',
            'bind' => ['start_at' => $start_at, 'end_at' => $end_at],
            'columns' => 'distinct ' . $column
        ];

        $devices = Devices::find($conds);

        foreach ($devices as $device) {
            $val = $device->$column;
            if ($val) {
                if ($column == 'imei' && (strlen($val) >= 20 && strlen($val) <= 22) && strlen(base64_decode($val)) == 15) {
                    $val = base64_decode($val);
                }

                if ($is_export_md5) {
                    $val = Partners::generateMuid([$column => $val]);
                }

                file_put_contents($temp_file, $val . PHP_EOL, FILE_APPEND);
            }
        }

        //self::delay(120)->deleteExportFile($temp_file);

        return $temp_file;
    }

    static function deleteExportFile($filename)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    function pushType()
    {
        $push_type = 'transmission';
        if ($this->manufacturer && preg_match('/(Xiaomi|HUAWEI|Meizu)/i', $this->manufacturer)) {
            $push_type = 'notification';
        }

        return $push_type;
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'last_at_text' => $this->last_at_text,
            'model' => $this->model,
        ];
    }

    public function lastLoginAt()
    {
        if (!$this->last_at) {
            return $this->created_at;
        }

        return $this->last_at;
    }

    public function isTouchPlatform()
    {
        if (preg_match('/^(touch_unknow|touch_ios|touch_android)$/i', $this->platform)) {
            return true;
        }

        return false;
    }

    public function isWxPlatform()
    {
        if (preg_match('/^(weixin_unknow|weixin_ios|weixin_android)$/i', $this->platform)) {
            return true;
        }

        return false;
    }

    public function isClientPlatform()
    {
        if (preg_match('/^(ios|android)$/i', $this->platform)) {
            return true;
        }

        return false;
    }

    function getPushContext()
    {
        return $this->product_channel->getPushContext($this->platform);
    }

    function getPushReceiverContext()
    {
        return ['id' => $this->id, 'platform' => $this->platform, 'push_token' => $this->push_token, 'push_type' => $this->push_type];
    }

    function pushMessage($push_message)
    {

        $push_url = $push_message->getPushUrl($this);
        if (!$push_url || !$this->push_token) {
            info('false push', $this->id, $push_url, $this->push_token, $push_message->id);
            return false;
        }

        $payload = ['model' => 'user', 'created_at' => time()];
        $receiver_context = $this->getPushReceiverContext();
        $push_data = ['title' => $push_message->title, 'body' => $push_message->description,
            'payload' => $payload, 'badge' => 1, 'offline' => true, 'client_url' => $push_url,
            'icon_url' => $push_message->image_url];

        debug($this->id, $this->getPushContext(), $receiver_context, $push_data);

        if (Pushers::push($this->getPushContext(), $receiver_context, $push_data)) {
            $push_message->sendStat($this);
            return true;
        }

        return false;
    }

    function canPush()
    {

        if (!$this->push_token) {
            info('false no push_token', $this->id);
            return false;
        }

        return true;
    }

    function inWhiteList()
    {
        $hot_cache = \Devices::getHotWriteCache();
        $key = "white_device_no_list";

        return $hot_cache->zscore($key, $this->device_no) > 0;
    }

    function getTodayApplePayAmount()
    {
        $hot_cache = Payments::getHotWriteCache();
        $key = "stat_apple_day_total_pay_amount_list_" . date("Ymd");
        return intval($hot_cache->zscore($key, $this->id));
    }
}