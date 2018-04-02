<?php


class Partners extends BaseModel
{
    static $STATUS = [PARTNER_STATUS_NORMAL => '正常', PARTNER_STATUS_BLOCK => '无效'];

    static $NOTIFY_CALLBACK = ['' => '不支持', 'notify_gdt' => '广点通回调', 'notify_active' => '头条回调',
        'notify_momo' => '陌陌回调', 'notify_uc' => 'UC回调', 'notify_baidu' => '百度回调'];

    static $GROUP_TYPE = [PARTNER_GROUP_TYPE_NO => '默认'];

    static function getFastCacheEndpoint($id)
    {
        return self::getHotWriteCache()->endpoint;
    }

    static function findFirstByFrHotCache($fr = '')
    {

        $partner = null;
        $read_cache = \Partners::getHotReadCache();
        $key = 'partner_id_for_fr_' . $fr;
        $id = $read_cache->get($key);
        if ($id) {
            $partner = \Partners::findFirstById($id);
            if ($partner && $partner->fr != $fr) {
                $partner = null;
            }
        }

        if (!$partner) {
            $partner = \Partners::findFirstByFr($fr);
            if ($partner) {
                $write_cache = \Partners::getHotWriteCache();
                $write_cache->set($key, $partner->id);
                debug('set cache', $key, ',id=', $partner->id);
            }
        }

        return $partner;
    }

    static public function exportPartners()
    {
        $partners = Partners::find(['order' => 'id desc', 'columns' => 'id,name,fr']);
        $temp_file = APP_ROOT . 'temp/export_all_fr_' . date('YmdHis') . '.txt';
        foreach ($partners as $partner) {
            file_put_contents($temp_file, $partner->name . ',' . $partner->fr . PHP_EOL, FILE_APPEND);
        }

        self::delay(60)->deleteExportFile($temp_file);

        return $temp_file;
    }

    static function deleteExportFile($filename)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    // 推广的fr
    static function getPromoteFr($attributes)
    {

        $fr = null;
        $hot_cache = self::getHotWriteCache();
        $muid = self::generateMuid($attributes);

        info($attributes['code'], $attributes['platform'], $attributes['fr'], $muid);

        $click_key = 'new_click_ad_event_' . $attributes['code'] . '_muid_' . $muid;
        $data = $hot_cache->get($click_key);
        if ($data) {
            $data = json_decode($data, true);
            $fr = fetch($data, 'fr');
            $data['act_time'] = time();

            info($click_key, $attributes['fr'], $attributes['manufacturer'], $data);

            if (!$fr) {
                $fr = fetch($attributes, 'fr');
            }

            // 激活回调
            self::delay(1)->notifyCallback($fr, $data);

            // 清除cache
            $hot_cache->del($click_key);
        } else {
            $active_fr = fetch($attributes, 'fr');
            if ($active_fr) {
                self::delay(600)->asyncCheckCallback($active_fr, $click_key);
            }
        }

        return $fr;
    }

    static function asyncCheckCallback($active_fr, $click_key)
    {

        $hot_cache = self::getHotWriteCache();
        $data = $hot_cache->get($click_key);
        debug($click_key, $active_fr, $data);
        if ($data) {
            $data = json_decode($data, true);
            $fr = fetch($data, 'fr');
            $data['act_time'] = time();
            if (!$fr) {
                $fr = $active_fr;
            }

            info($click_key, $data);
            // 激活回调
            self::delay(1)->notifyCallback($fr, $data);
            // 清除cache
            $hot_cache->del($click_key);
        }
    }

    static function notifyCallback($fr, $data)
    {
        if (!$fr) {
            info('callback false no_fr', $data);
            return;
        }

        // gdt
        $source = fetch($data, 'source');
//        if (preg_match('/gdt/', $source) && preg_match('/(market_vivo_01|market_oppo_01)/', $fr)) {
//            info('gdt_market', $fr, $data);
//            Partners::notifyGdt($data);
//            return;
//        }

        $partner = Partners::findFirstByFrHotCache($fr);
        if (!$partner || !$partner->notify_callback) {
            $click_time = fetch($data, 'click_time');
            $diff = time() - $click_time;
            info('callback false', $fr, $data, 'interval', $diff);
            return;
        }

        $notify_callback = $partner->notify_callback;
        $method_name = \Phalcon\Text::camelize($notify_callback);
        $method_name = lcfirst($method_name);

        call_user_func(get_called_class() . '::' . $method_name, $data);
    }

    static function generateMuid($attributes)
    {
        $muid = null;
        if (isset($attributes['idfa']) && $attributes['idfa']) {
            $muid = md5(strtoupper($attributes['idfa']));
        } elseif (isset($attributes['imei']) && $attributes['imei']) {
            $muid = md5(strtolower($attributes['imei']));
        }

        if ($muid) {
            $muid = strtolower($muid);
        }

        return $muid;
    }

    // 通知百度
    static function notifyBaidu($data)
    {

        $source = fetch($data, 'source');
        $callback = fetch($data, 'callback_url');
        if ($callback && $source == 'baidu') {
            $call_url = preg_replace('/{{ATYPE}}/', 'activate', $callback);
            $call_url = preg_replace('/{{AVALUE}}/', 0, $call_url);
            $call_url = preg_replace('/&sign={{SIGN}}/', '', $call_url);

            // 重新优化配置
            $akey_xianjinzhijia = 'MjM5NDUxMjE=';
            $akey_money = 'MjM4MzQ5OTg=';
            $code = fetch($data, 'code');
            if ($code == 'money') {
                $akey = $akey_money;
            } else {
                $akey = $akey_xianjinzhijia;
            }

            $sign = md5($call_url . $akey);
            $call_url = $call_url . '&sign=' . $sign;
            $res = httpGet($call_url);
            info($call_url, $res->body, $data);
        }
    }

    // 通知陌陌
    static function notifyUc($data)
    {
        $source = fetch($data, 'source');
        $callback = fetch($data, 'callback');
        if ($callback && $source == 'uc') {
            $res = httpGet($callback);
            info($callback, $res->headers, $res->body);
        }
    }

    // 通知陌陌
    static function notifyMomo($data)
    {
        $source = fetch($data, 'source');
        $callback = fetch($data, 'callback');
        if ($callback && $source == 'momo') {
            $res = httpGet($callback);
            info($callback, $res->body);
        }
    }

    // 通知头条
    static function notifyActive($data)
    {
        $source = fetch($data, 'source');
        $callback = fetch($data, 'callback');
        if ($callback && $source == 'toutiao') {
            $res = httpGet($callback);
            info($callback, $res->body);
        }
    }

    // 通知广点通
    static function notifyGdt($data)
    {

        $notify_url = self::generateNotifyUrl($data);
        if ($notify_url) {
            $resp = httpGet($notify_url);
            info("IOS|NOTIFY|{$notify_url}|{$resp->raw_body}", $data);
            $result = json_decode($resp->raw_body, true);
            return $result;
        } else {
            info("false Exce gdt no sign key", $data);
        }

        return null;
    }


    // 生成通知url
    static function generateNotifyUrl($data)
    {
        $advertiser_id = fetch($data, 'advertiser_id');
        if (!$advertiser_id) {
            info('false no advertiser_id', $data);
            return '';
        }

        $gdt_config = GdtConfigs::findFirstByAdvertiserId($advertiser_id);
        if ($gdt_config) {
            $sign_key = $gdt_config->sign_key;
            $encrypt_key = $gdt_config->encrypt_key;

            info($advertiser_id, $sign_key, $encrypt_key);
        } else {

            info('Exce false_no_gdt_config', $advertiser_id, $data);
            return '';
        }

        $query_string = "muid={$data['muid']}&conv_time={$data['act_time']}&click_id={$data['click_id']}";
        $page = "http://t.gdt.qq.com/conv/app/{$data['appid']}/conv?{$query_string}";
        $encode_page = urlencode($page);
        $property = "{$sign_key}&GET&{$encode_page}";
        $signature = md5($property);
        $sign = urlencode($signature);
        $base_data = "{$query_string}&sign={$sign}";
        $app_type = strtoupper($data['app_type']);
        $new_data = self::simpleXor($base_data, $encrypt_key);
        $new_data = urlencode($new_data);
        $attachment = "conv_type=MOBILEAPP_ACTIVITE&app_type={$app_type}&advertiser_id={$data['advertiser_id']}";
        $url = "http://t.gdt.qq.com/conv/app/{$data['appid']}/conv?v={$new_data}&{$attachment}";

        return $url;
    }

    static function simpleXor($base_data, $encrypt_key)
    {
        $result = "";
        $len = strlen($encrypt_key);
        $source_len = strlen($base_data);

        for ($index = 0; $index < $source_len; $index++) {

            if (!isset($encrypt_key[$index]) || '' === $encrypt_key[$index]) {
                $v = $encrypt_key[$index % $len];
            } else {
                $v = $encrypt_key[$index];
            }

            $b = ord($base_data[$index]);
            $v = ord($v);

            $result .= chr($b ^ $v);
        }

        $result = base64_encode($result);

        return trim(str_replace('\n', '', $result));
    }

    static function testGdt($attributes)
    {

        $fr = null;
        $hot_cache = self::getHotWriteCache();
        $muid = self::generateMuid($attributes);

        $click_key = 'new_click_ad_event_' . $attributes['code'] . '_muid_' . $muid;
        $data = $hot_cache->get($click_key);
        if (!$data) {
            info('false', $attributes);
            return '失败，未收到gdt点击通知';
        }

        $data = json_decode($data, true);
        $fr = fetch($data, 'fr');
        $data['act_time'] = time();
        if (!$fr) {
            $fr = fetch($attributes, 'fr');
        }

        $partner = Partners::findFirstByFrHotCache($fr);
        if (!$partner || $partner->notify_callback != 'notify_gdt') {
            info('false', $attributes, $fr);
            return '失败，渠道fr配置错误';
        }

        // 激活回调
        $result = self::notifyGdt($data);
        $ret = fetch($result, 'ret');
        if ($ret == 0 || $ret == -17) {
            info('联调成功', $attributes, $fr);
            return '联调成功';
        }

        info('false', $attributes, $fr, $data);
        return '失败，联系开发人员';
    }

}