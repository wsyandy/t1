<?php

# 统计分析类

class Stats extends BaseModel
{

    static $PLATFORM = [-1 => '全部', 'android' => '安卓', 'ios' => 'ios', 'weixin_android' => '微信安卓',
        'weixin_ios' => '微信ios', 'weixin_unknow' => '微信关注平台', 'touch_android' => 'H5安卓', 'touch_ios' => 'H5 ios'];

    static $MONTH = ['1' => '1月', '2' => '2月', '3' => '3月', '4' => '4月', '5' => '5月', '6' => '6月', '7' => '7月',
        '8' => '8月', '9' => '9月', '10' => '10月', '11' => '11月', '12' => '12月'];

    static $ACTION_FIELDS = ['device_active', 'subscribe', 'touch_active', 'register', 'unsubscribe', 'active_user',
        'create_order', 'sem_sms_active', 'sem_sms_active_mobile', 'register_mobile', 'active_mobile',
        'active_register_user', 'view_product', 'first_register_create_order'
    ];

    static $TIME_TYPE = [STAT_HOUR => '小时', STAT_DAY => '天', STAT_MONTH => '月'];

    static $FOLLOW_STAT_ITEMS = ["weixin_all_new_num" => '新增用户数', "weixin_all_cancel_num" => '取消用户数',
        'weixin_all_add_num' => '净增用户数', "weixin_total_cumulate_num" => '总用户数'];


    static function getStatDb()
    {
        $endpoint = self::config('stat_db');
        return XRedis::getInstance($endpoint);
    }

    static function record($field, $action, $attrs)
    {

        return;

        if (is_string($attrs)) {
            $attrs = json_decode($attrs, true);
            if (!is_array($attrs)) {
                return;
            }
        }

        debug($field, $action, $attrs);

        $id = fetch($attrs, 'id', null);
        $sex = fetch($attrs, 'sex', -1);
        $sex = -1;
        $partner_id = fetch($attrs, 'partner_id', -1);
        if ($partner_id < 1) {
            $partner_id = -1;
        }
        $province_id = fetch($attrs, 'province_id', -1);
        if ($province_id < 1) {
            $province_id = -1;
        }
        $province_id = -1;

        $product_channel_id = fetch($attrs, 'product_channel_id', -1);
        $platform = fetch($attrs, 'platform', -1);
        if (!$platform) {
            $platform = -1;
        }
        $created_at = fetch($attrs, 'created_at', time());
        $ip = fetch($attrs, 'ip', '');
        $stat_at = fetch($attrs, 'stat_at', time());

        $target_id = fetch($attrs, 'target_id', -1);

        // platform-1后面的-1表示所有
        $stats_keys = [];
        $stats_keys[] = "platform-1_version_code-1_product_channel_id-1_partner_id-1_province_id-1_sex-1";

        $stats_keys[] = "platform{$platform}_version_code-1_product_channel_id-1_partner_id-1_province_id-1_sex-1";
        $stats_keys[] = "platform-1_version_code-1_product_channel_id{$product_channel_id}_partner_id-1_province_id-1_sex-1";
        $stats_keys[] = "platform-1_version_code-1_product_channel_id-1_partner_id{$partner_id}_province_id-1_sex-1";

        $stats_keys[] = "platform{$platform}_version_code-1_product_channel_id{$product_channel_id}_partner_id-1_province_id-1_sex-1";
        $stats_keys[] = "platform{$platform}_version_code-1_product_channel_id-1_partner_id{$partner_id}_province_id-1_sex-1";
        $stats_keys[] = "platform-1_version_code-1_product_channel_id{$product_channel_id}_partner_id{$partner_id}_province_id-1_sex-1";

        $stats_keys[] = "platform{$platform}_version_code-1_product_channel_id{$product_channel_id}_partner_id{$partner_id}_province_id-1_sex-1";

        //统计屏蔽用户只要区分产品，渠道，性别，所以key值可能会有重复的
        $stats_keys = array_unique($stats_keys);

        $day = "stats_" . date('Ymd', $stat_at);
        $hour = "stats_" . date('YmdH', $stat_at);

        $all_stat_key = 'stats_keys_' . date('Ymd', $stat_at);

        $stat_db = Stats::getStatDb();
        foreach ($stats_keys as $key) {

            if (!preg_match('/province_id-1/', $key) || !preg_match('/version_code-1/', $key)) {
                continue;
            }

            $stat_db->zincrby($all_stat_key, 1, $key);
            // key 对应的action
            //$stat_db->zincrby($all_stat_key.'_'.$key, 1, $action);

            $date_key = $day . "_" . $field . "_" . $key . "_" . $action;
            $hour_key = $hour . "_" . $field . "_" . $key . "_" . $action;

            // 用户数
            $stat_db->zadd($date_key, $stat_at, $id);
            $stat_db->zadd($hour_key, $stat_at, $id);

            // 次数
            $stat_db->incr("{$date_key}_num");
            $stat_db->incr("{$hour_key}_num");

            if (in_array($action, ['register', 'device_active']) && $ip) {
                $stat_db->zadd($date_key . "_ip", $stat_at, $ip);
                $stat_db->zadd($hour_key . "_ip", $stat_at, $ip);
            }
        }

    }

    static $STAT_FIELDS = [
        'device_active_num' => '设备激活数',
        'subscribe_num' => '微信关注数',
        'touch_active_num' => 'H5激活数',
        'sem_sms_active_num' => '落地页激活手机次数',
        'sem_sms_active_mobile_num' => '落地页激活手机个数',
        'register_num' => "注册数",
        'register_mobile_num' => '注册新手机号数',
        'register_ip_num' => "注册IP数",
        'register_rate' => '注册率%',
        'register_repeat_rate' => '注册重复率%',
        'unsubscribe_num' => '微信取消关注数',
        'register_fail_num' => "未绑定手机用户",
        'active_user_num' => '活跃用户数',
        'active_register_user_num' => '活跃注册用户数',
        'active_mobile_num' => '活跃手机号数'
    ];

    public $data_hash = [];

    function needSave()
    {
        $v_total = 0;
        foreach ($this->data_hash as $k => $v) {
            $v_total += intval($v);
        }

        if ($v_total < 1) {
            return false;
        }

        return true;
    }

    // 生成统计的key
    function statCacheKey($field, $action, $conds = [])
    {
        $cache_key = "";

        switch ($this->time_type) {
            case STAT_HOUR:
                $cache_key = "stats_" . date('YmdH', $this->stat_at);
                break;
            case STAT_DAY:
                $cache_key = "stats_" . date('Ymd', $this->stat_at);
                break;
        }

        $this->sex = fetch($conds, 'sex', -1);

        $cache_key .= "_{$field}";
        foreach (['platform', 'version_code', 'product_channel_id', 'partner_id', 'province_id', 'sex'] as $key) {
            $val = $this->$key;
            $cache_key .= "_{$key}{$val}";
        }

        $cache_key .= "_" . $action;

        return $cache_key;
    }

    function deviceActiveNum()
    {
        $key = $this->statCacheKey("user", "device_active");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['device_active_num'] = intval($num);
    }

    function touchActiveNum()
    {
        $key = $this->statCacheKey("user", "touch_active");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['touch_active_num'] = intval($num);
    }

    function semSmsActiveNum()
    {
        $key = $this->statCacheKey("user", "sem_sms_active");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['sem_sms_active_num'] = intval($num);
    }

    function semSmsActiveMobileNum()
    {
        $key = $this->statCacheKey("user", "sem_sms_active_mobile");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['sem_sms_active_mobile_num'] = intval($num);
    }

    function subscribeNum()
    {
        $key = $this->statCacheKey("user", "subscribe");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['subscribe_num'] = intval($num);
    }

    function unsubscribeNum()
    {
        $key = $this->statCacheKey("user", "unsubscribe");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['unsubscribe_num'] = intval($num);
    }

    function registerNum()
    {
        $key = $this->statCacheKey("user", "register");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['register_num'] = intval($num);
    }

    function registerMobileNum()
    {
        $key = $this->statCacheKey("user", "register_mobile");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['register_mobile_num'] = intval($num);
    }

    function registerIpNum()
    {
        $key = $this->statCacheKey("user", "register");
        $key .= "_ip";
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['register_ip_num'] = intval($num);
    }

    // 关注数减去注册数
    function registerFailNum()
    {
        $this->data_hash['register_fail_num'] = 0;
        $active_num = $this->data_hash['device_active_num'] + $this->data_hash['subscribe_num'] + $this->data_hash['touch_active_num'];
        $register_fail_num = $active_num - $this->data_hash['register_num'];
        if ($register_fail_num > 0) {
            $this->data_hash['register_fail_num'] = $register_fail_num;
        }
    }

    function registerRate()
    {
        $active_num = $this->data_hash['device_active_num'] + $this->data_hash['subscribe_num'] + $this->data_hash['touch_active_num'];
        $register_num = $this->data_hash['register_num'];
        $register_rate = 0;
        if ($active_num > 0) {
            $register_rate = sprintf("%0.2f", $register_num * 100 / $active_num);
        }

        $this->data_hash['register_rate'] = $register_rate;
    }

    function registerRepeatRate()
    {
        $register_mobile_num = $this->data_hash['register_mobile_num'];
        $register_num = $this->data_hash['register_num'];
        $register_rate = 0;
        $num = $register_num - $register_mobile_num;
        if ($num > 0) {
            $register_rate = sprintf("%0.2f", $num * 100 / $register_num);
        }

        $this->data_hash['register_repeat_rate'] = $register_rate;
    }

    // 每日活跃用户数
    function activeUserNum()
    {
        $key = $this->statCacheKey("user", "active_user");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['active_user_num'] = intval($num);
    }

    function activeRegisterUserNum()
    {
        $key = $this->statCacheKey("user", "active_register_user");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['active_register_user_num'] = intval($num);
    }

    function activeMobileNum()
    {
        $key = $this->statCacheKey("user", "active_mobile");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['active_mobile_num'] = intval($num);
    }

}