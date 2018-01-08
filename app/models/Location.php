<?php

class Location
{
    static $EARTH_RADIUS = 6378.137;
    static $AK = "ylCrHWBX7Xupt5a3k96rOTdU";
    static $BAIDU_MAP_URL = "http://api.map.baidu.com/geocoder/v2/";

    // 经纬度定位地区, 百度
    static function address($lat, $lng, $has_pois = 0)
    {

        $hot_cache = Provinces::getHotWriteCache();
        $exce_key = 'location_address_exception';
        if ($hot_cache->get($exce_key)) {
            info('Exception 百度经纬度 暂停服务');
            return null;
        }

        $param = ['ak' => self::$AK, 'output' => 'json', 'pois' => $has_pois, 'location' => "{$lat},{$lng}"];
        $to_param = '';
        foreach ($param as $key => $value) {
            $to_param .= "{$key}={$value}&";
        }

        try {
            $resp = httpGet(self::$BAIDU_MAP_URL . "?{$to_param}");
        } catch (\Exception $e) {
            $exce_num_key = 'location_address_exception_num';
            $num = $hot_cache->incr($exce_num_key);
            $hot_cache->expire($exce_num_key, 120);
            if ($num >= 5) {
                $hot_cache->setex($exce_key, 180, 1);
            }

            info('Exception', $num, $e->getMessage());
            return null;
        }

        // 返回内容示例:
        // {"status":0,"result":{"location":{"lng":116.32298703399,"lat":39.983424051248},
        // "formatted_address":"北京市海淀区中关村大街27号1101-08室","business":"中关村,人民大学,苏州街",
        // "addressComponent":{"city":"北京市","district":"海淀区",
        // "province":"北京市","street":"中关村大街",
        // "street_number":"27号1101-08室"},"cityCode":131}}

        if (200 == $resp->code) {
            $body = json_decode($resp->raw_body, JSON_UNESCAPED_UNICODE);

            if (0 == $body['status']) {
                $province_name = "";
                $city_name = "";
                if (isset($body['result']['addressComponent'])) {
                    $address_component = $body['result']['addressComponent'];
                    if (isset($address_component['province'])) {
                        $province_name = $address_component['province'];
                        //$province_name = preg_replace('/市|省/', '', $province_name);

                        // 省份名称处理
                        foreach (Users::$PROVINCE as $province_id => $name) {
                            if ($province_name && $name && preg_match('/' . $name . '/', $province_name)) {
                                $province_name = $name;
                                break;
                            }
                        }

                    }
                    if (isset($address_component['city'])) {
                        $city_name = $address_component['city'];
                    }
                }

                // 标志性建筑
                $pois = [];

                if ($has_pois && isset($body['result']['pois'])) {
                    $pois = $body['result']['pois'];
                }

                if ($city_name) {
                    $encode = mb_detect_encoding($city_name, ["ASCII", "UTF-8", "GB2312", "GBK", "BIG5"]);

                    if ('UTF-8' != $encode) {
                        $iconv_value = iconv($encode, "UTF-8", $city_name);
                        info($encode, $city_name, $iconv_value);
                    }
                }

                return [$province_name, $city_name, 'pois' => $pois];
            }
        }

        return null;
    }

    static function gdAddress($lat, $lng)
    {
        $hot_cache = Users::getHotWriteCache();

        $exce_key = 'gaode_location_address_exception';

        if ($hot_cache->get($exce_key)) {
            info('Exception 高德经纬度 暂停服务');
            return null;
        }

        $app_key = 'e9d535bc5bb4d8245963092851618610';
        $location = "{$lng},{$lat}";
        $url = "http://restapi.amap.com/v3/geocode/regeo?key={$app_key}&location={$location}&poitype=&radius=1000&extensions=all&batch=false&roadlevel=1";

        try {
            $resp = httpGet($url);
        } catch (\Exception $e) {
            $exce_num_key = 'gaode_location_address_exception_num';
            $num = $hot_cache->incr($exce_num_key);
            $hot_cache->expire($exce_num_key, 120);
            if ($num >= 5) {
                $hot_cache->setex($exce_key, 180, 1);
            }

            info('Exception', $num, $e->getMessage());
            return null;
        }

        $hot_cache = Users::getHotWriteCache();
        $call_num_key = "gaode_call_num_" . date('Ymd');
        $call_num = $hot_cache->incr($call_num_key);
        $hot_cache->expire($call_num_key, 60 * 60 * 24);

        info($call_num_key, $call_num);

        $body = json_decode($resp->raw_body, JSON_UNESCAPED_UNICODE);

        if (isset($body['status']) && '1' == $body['status']) {
            if (isset($body['regeocode']['addressComponent'])) {
                $address_component = $body['regeocode']['addressComponent'];

                $province_name = "";
                if ($address_component['province']) {
                    $province_name = $address_component['province'];
                }

                $city_name = "";
                if ($address_component['city']) {
                    $city_name = $address_component['city'];
                }

                // 省份名称处理
                foreach (Users::$PROVINCE as $province_id => $name) {
                    if ($province_name && $name && preg_match('/' . $name . '/', $province_name)) {
                        $province_name = $name;

                        //直辖市
                        if ($province_id <= 4) {
                            $city_name = $province_name;
                        }

                        break;
                    }
                }

                $business_area = "";

                if (isset($address_component['businessAreas'][0])) {
                    $business_areas = $address_component['businessAreas'][0];
                    if (isset($business_areas['name']) && $business_areas['name']) {
                        $business_area = $business_areas['name'];
                    }
                }

                $res = [$province_name, $city_name, 'pois' => $business_area];

                debug($res);

                return $res;
            }
        }

        return null;
    }
}
