<?php

class ProductChannels extends BaseModel
{

    static $STATUS = [STATUS_OFF => '关闭', STATUS_ON => '正常'];

    static $files = ['avatar' => APP_NAME . '/product_channels/avatar/%s',
        'weixin_qrcode' => APP_NAME . '/product_channels/weixin_qrcode/%s'];

    static function getCacheEndpoint($id)
    {
        return self::getHotWriteCache()->endpoint;
    }

    function beforeUpdate()
    {
        if ($this->hasChanged('weixin_domain')) {
            $write_cache = self::getHotWriteCache();
            $domain = $this->was('weixin_domain');
            $write_cache->del('product_channel_weixin_domain_' . $domain);
        }

        if ($this->hasChanged('touch_domain')) {
            $write_cache = self::getHotWriteCache();
            $domain = $this->was('touch_domain');
            $write_cache->del('product_channel_touch_domain_' . $domain);
        }

        if ($this->hasChanged('code')) {
            $write_cache = self::getHotWriteCache();
            $code = $this->was('code');
            $write_cache->del('product_channel_code_' . $code);
        }
    }

    static function asyncCopyTo($src_product_channel_id, $dest_product_channel_id)
    {
        $product_channel = ProductChannels::findFirstById($src_product_channel_id);
        $product_channel->copyTo($dest_product_channel_id);
    }

    function copyTo($dest_product_channel_id)
    {
        // 待开发
        return;

        $products = Products::find(['conditions' => 'product_channel_id=:product_channel_id:',
            'bind' => ['product_channel_id' => $this->id]]);
        foreach ($products as $product) {
            if (Products::findFirst(['conditions' => 'product_channel_id=:product_channel_id: and product_id=:product_id:',
                'bind' => ['product_channel_id' => $dest_product_channel_id, 'product_id' => $product->product_id]])
            ) {
                debug($product->id);
                continue;
            }
            $new_product = $product->cloneNewObject();
            $new_product->product_channel_id = $dest_product_channel_id;
            $new_product->save();
        }
    }

    function mergeJson()
    {
        return ['avatar_url' => $this->getAvatarUrl(), 'avatar_small_url' => $this->getAvatarSmallUrl()];
    }

    static function getWeixinThemes()
    {

        $weixin_themes = [];
        $themes = [];
        foreach (glob(APP_ROOT . 'app/views/wx/*') as $filename) {
            $basename = basename($filename);
            $themes[$basename] = fetch($weixin_themes, $basename, $basename);
        }
        return $themes;
    }

    static function getTouchThemes()
    {
        $touch_themes = [];
        $themes = [];
        foreach (glob(APP_ROOT . 'app/views/touch/*') as $filename) {
            $basename = basename($filename);
            $themes[$basename] = fetch($touch_themes, $basename, $basename);
        }
        return $themes;
    }

    static function getWebThemes()
    {

        $web_themes = [];
        $themes = [];
        foreach (glob(APP_ROOT . 'app/views/web/*') as $filename) {
            $basename = basename($filename);
            $themes[$basename] = fetch($web_themes, $basename, $basename);
        }
        return $themes;
    }

    static function findFirstByWeixinDomain($domain)
    {
        $read_cache = self::getHotReadCache();

        $id = $read_cache->get('product_channel_weixin_domain_' . $domain);
        if (!$id) {
            $product_channel = self::findFirstBy(['weixin_domain' => $domain]);
            if ($product_channel) {
                $write_cache = self::getHotWriteCache();
                $write_cache->setex('product_channel_weixin_domain_' . $domain, 60 * 60 - 1, $product_channel->id);
            }
        } else {
            $product_channel = self::findFirstById($id);
        }

        return $product_channel;
    }

    static function findFirstByTouchDomain($domain)
    {
        $read_cache = self::getHotReadCache();
        $id = $read_cache->get('product_channel_touch_domain_' . $domain);
        if (!$id) {
            $product_channel = self::findFirstBy(['touch_domain' => $domain]);
            if ($product_channel) {
                $write_cache = self::getHotWriteCache();
                $write_cache->setex('product_channel_touch_domain_' . $domain, 60 * 60 - 1, $product_channel->id);
            }
        } else {
            $product_channel = self::findFirstById($id);
        }

        return $product_channel;
    }

    static function findFirstByWebDomain($domain)
    {
        $read_cache = self::getHotReadCache();
        $id = $read_cache->get('product_channel_web_domain_' . $domain);
        if (!$id) {
            $product_channel = self::findFirstBy(['web_domain' => $domain]);
            if ($product_channel) {
                $write_cache = self::getHotWriteCache();
                $write_cache->setex('product_channel_web_domain_' . $domain, 60 * 60 - 1, $product_channel->id);
            }
        } else {
            $product_channel = self::findFirstById($id);
        }

        return $product_channel;
    }

    static function findFirstByCodeHotCache($code)
    {
        $write_cache = self::getHotWriteCache();
        $read_cache = self::getHotReadCache();
        $product_channel_id = $read_cache->get('product_channel_code_' . $code);

        $product_channel = null;
        if (!$product_channel_id) {
            $product_channel = self::findFirstByCode($code);
            if ($product_channel) {
                $write_cache->set('product_channel_code_' . $code, $product_channel->id);
            }
        } else {
            $product_channel = self::findFirstById($product_channel_id);
        }

        return $product_channel;
    }


    function isWhiteList($nickname)
    {
        if (!$this->weixin_white_list) {
            return false;
        }

        $weixin_white_list = explode(',', $this->weixin_white_list);
        debug($nickname, $weixin_white_list);
        return in_array($nickname, $weixin_white_list);
    }

    function isBlackList($nickname)
    {
        if (!$this->weixin_black_list) {
            return false;
        }

        $weixin_black_list = explode(',', $this->weixin_black_list);
        foreach ($weixin_black_list as $black) {
            if (mb_stristr($nickname, $black)) {
                info('黑名单', $black, $nickname);
                return true;
            }
        }

        return false;
    }

    function getPushContext($platform)
    {
        $keys = ['app_id', 'app_key', 'app_secret', 'app_master_secret'];
        $prefix = $platform . '_';

        $context = [];
        foreach ($keys as $key) {
            $field = $prefix . $key;
            $context[$key] = $this->$field;
        }

        return $context;
    }

    function getAvatarUrl($size = null)
    {

        if (isBlank($this->avatar)) {
            return null;
        }
        $url = StoreFile::getUrl($this->avatar);
        if ($size) {
            $url .= "@!" . $size;
        }
        return $url;
    }

    function getAvatarSmallUrl()
    {
        return $this->getAvatarUrl('small');
    }

    function getWeixinQrcodeUrl($size = null)
    {
        if (isBlank($this->weixin_qrcode)) {
            return null;
        }
        $url = StoreFile::getUrl($this->weixin_qrcode);

        if ($url) {
            $url .= "@!small";
        }

        return $url;
    }

    function getLimitQrcodeUrl($fr = 'app')
    {
        $hot_cache = self::getHotWriteCache();
        $limit_qrcode_url_key = "limit_qrcode_url_" . $this->code . '_' . $fr;
        $url = $hot_cache->get($limit_qrcode_url_key);
        if (!$url) {
            $url = $this->generateLimitQrcodeByFr($fr);
            $hot_cache->set($limit_qrcode_url_key, $url);
        }

        return $url;
    }

    function generateLimitQrcodeByFr($fr = 'app')
    {
        $weixin_event = new WeixinEvents($this);
        $url = $weixin_event->generateLimitQrcodeByFr($fr);
        $hot_cache = self::getHotWriteCache();
        $limit_qrcode_url_key = "limit_qrcode_url_" . $this->code . '_' . $fr;
        $hot_cache->set($limit_qrcode_url_key, $url);

        return $url;
    }

    function toAboutJson()
    {
        return [
            'name' => $this->name,
            'company_name' => $this->company_name,
            'service_phone' => $this->service_phone,
            'weixin_name' => $this->weixin_name,
            'cooperation_weixin' => $this->cooperation_weixin,
            'cooperation_email' => $this->cooperation_email,
            'cooperation_phone_number' => $this->cooperation_phone_number,
            'official_website' => $this->official_website,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'weixin_limit_qrcode' => $this->weixin_qrcode_url,
            'weixin_no' => $this->weixin_no
        ];
    }

    function getImAppId()
    {
        //return '4b00a7416f75498093bfd7ad09cb31e9';
        $config = self::di('config');
        $agora_app_id = $config->agora_app_id;
        return $agora_app_id;
    }

    // Signaling Key 用于登录信令系统; 有效期1小时
    function getSignalingKey($uid, $valid_timeIn_seconds = 86400)
    {
        //$app_id = '4b00a7416f75498093bfd7ad09cb31e9';
        //$app_certificate = '7b73afdb080244da8d66a41b97e1d5d9';

        $config = self::di('config');
        $app_id = $config->agora_app_id;
        $app_certificate = $config->agora_app_certificate;

        $sdk_version = "1";
        $expired_time = time() + $valid_timeIn_seconds;
        $token_items = [];
        array_push($token_items, $sdk_version);
        array_push($token_items, $app_id);
        array_push($token_items, $expired_time);
        array_push($token_items, md5($uid . $app_id . $app_certificate . $expired_time));

        return join(":", $token_items);
    }

    //Channel Key 用于加入频道; 有效期3小时
    function getChannelKey($channel_name, $uid)
    {
        //$app_id = '4b00a7416f75498093bfd7ad09cb31e9';
        //$app_certificate = '7b73afdb080244da8d66a41b97e1d5d9';

        $config = self::di('config');
        $app_id = $config->agora_app_id;
        $app_certificate = $config->agora_app_certificate;

        //return $this->generateDynamicKey($app_id, $app_certificate, $channel_name, $uid, 1);
        return $this->generateDynamicKey4($app_id, $app_certificate, $channel_name, $uid, 'ACS');
    }

    private function generateDynamicKey($app_id, $app_certificate, $channel_name, $uid, $serviceType, $extra = [])
    {
        $ts = time();
        $random_int = mt_rand();
        //$expired_ts = time() + 3 * 3600; // 3小时服务时间
        $expired_ts = 0; // 不限制服务时间
        $version = '005';

        $signature = $this->generateSignature($serviceType, $app_id, $app_certificate, $channel_name, $uid, $ts, $random_int, $expired_ts, $extra);
        $content = $this->packContent($serviceType, $signature, hex2bin($app_id), $ts, $random_int, $expired_ts, $extra);

        return $version . base64_encode($content);
    }

    private function generateSignature($serviceType, $app_id, $app_certificate, $channel_name, $uid, $ts, $salt, $expired_ts, $extra)
    {
        $raw_app_id = hex2bin($app_id);
        $raw_app_certificate = hex2bin($app_certificate);

        $buffer = pack("S", $serviceType);
        $buffer .= pack("S", strlen($raw_app_id)) . $raw_app_id;
        $buffer .= pack("I", $ts);
        $buffer .= pack("I", $salt);
        $buffer .= pack("S", strlen($channel_name)) . $channel_name;
        $buffer .= pack("I", $uid);
        $buffer .= pack("I", $expired_ts);

        $buffer .= pack("S", count($extra));
        foreach ($extra as $key => $value) {
            $buffer .= pack("S", $key);
            $buffer .= pack("S", strlen($value)) . $value;
        }

        return strtoupper(hash_hmac('sha1', $buffer, $raw_app_certificate));
    }

    private function packContent($serviceType, $signature, $app_id, $ts, $salt, $expired_ts, $extra)
    {
        $buffer = pack("S", $serviceType);
        $buffer .= $this->packString($signature);
        $buffer .= $this->packString($app_id);
        $buffer .= pack("I", $ts);
        $buffer .= pack("I", $salt);
        $buffer .= pack("I", $expired_ts);

        $buffer .= pack("S", count($extra));
        foreach ($extra as $key => $value) {
            $buffer .= pack("S", $key);
            $buffer .= $this->packString($value);
        }

        return $buffer;
    }

    private function packString($value)
    {
        return pack("S", strlen($value)) . $value;
    }

    function generateDynamicKey4($appID, $appCertificate, $channelName, $uid, $serviceType)
    {
        //$uid = 0;
        $ts = time();
        $randomInt = mt_rand();
        //$expired_ts = time() + 3 * 3600; // 3小时服务时间
        $expiredTs = 0; // 不限制服务时间

        $version = "004";
        $randomStr = "00000000" . dechex($randomInt);
        $randomStr = substr($randomStr, -8);

        $uidStr = "0000000000" . $uid;
        $uidStr = substr($uidStr, -10);

        $expiredStr = "0000000000" . $expiredTs;
        $expiredStr = substr($expiredStr, -10);

        $signature = $this->generateSignature4($appID, $appCertificate, $channelName, $ts, $randomStr, $uidStr, $expiredStr, $serviceType);

        return $version . $signature . $appID . $ts . $randomStr . $expiredStr;
    }

    function generateSignature4($appID, $appCertificate, $channelName, $ts, $randomStr, $uidStr, $expiredStr, $serviceType)
    {
        $concat = $serviceType . $appID . $ts . $randomStr . $channelName . $uidStr . $expiredStr;
        return hash_hmac('sha1', $concat, $appCertificate);
    }


    static function validList()
    {
        return \ProductChannels::find(
            [
                "conditions" => "status = :status:",
                "bind" => [
                    "status" => STATUS_ON
                ]
            ]
        );
    }
}