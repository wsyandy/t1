<?php

# 统计分析类

class Stats extends BaseModel
{

    static function getStatDb()
    {
        $endpoint = self::config('stat_db');

        if (isProduction()) {
            $endpoint = 'ssdb://172.16.253.46:8880';
        }

        return XRedis::getInstance($endpoint);
    }

    static $PLATFORM = [-1 => '全部', 'android' => '安卓', 'ios' => 'ios', 'weixin_android' => '微信安卓',
        'weixin_ios' => '微信ios', 'touch_android' => 'H5安卓', 'touch_ios' => 'H5 ios'];

    static $MONTH = ['1' => '1月', '2' => '2月', '3' => '3月', '4' => '4月', '5' => '5月', '6' => '6月', '7' => '7月',
        '8' => '8月', '9' => '9月', '10' => '10月', '11' => '11月', '12' => '12月'];

    static $TIME_TYPE = [STAT_HOUR => '小时', STAT_DAY => '天', STAT_MONTH => '月'];

    static $FOLLOW_STAT_ITEMS = ["weixin_all_new_num" => '新增用户数', "weixin_all_cancel_num" => '取消用户数',
        'weixin_all_add_num' => '净增用户数', "weixin_total_cumulate_num" => '总用户数'];

    static $ACTION_FIELDS = [
        'device_active', 'subscribe', 'touch_active', 'web_active', // 激活
        'register', 'unsubscribe', // 注册
        'active_user', // 活跃用户
        'create_order', 'create_payment', 'payment_success',
        'diamond_recharge', 'diamond_recharge_give', 'diamond_cost', 'hi_coin_cost', //钻石购买和消耗
        'withdraw' //提现
    ];

    // 单课：要同时记录浏览课程和浏览章节

    static $STAT_FIELDS = [
        'device_active_num' => '设备激活数',
        //'subscribe_num' => '微信关注数',
        //'touch_active_num' => 'H5激活数',
        //'web_active_num' => 'web站点激活数',
        //'sem_sms_active_num' => '落地页激活手机次数',
        //'sem_sms_active_mobile_num' => '落地页激活手机个数',
        'total_active_num' => '总激活数',
        'register_num' => "注册数",
        'register_rate' => '注册率%',
        //'first_register_mobile_num' => '注册新手机号数',
        //'register_repeat_rate' => '注册重复率%',
        'register_ip_num' => "注册IP数",
        //'unsubscribe_num' => '微信取消关注数',
        'active_user_num' => '活跃用户数',
        'active_register_user_num' => '活跃注册用户数',

        'create_order_num' => '下单次数',
        'create_order_user' => '下单人数',
        'create_order_average' => '人均下单次数',
        'create_payment_num' => '支付次数',
        'create_payment_user' => '支付人数',
        'create_payment_average' => '人均支付次数',
        'payment_success_num' => '支付成功次数',
        'payment_success_user' => '支付成功人数',
        'payment_success_average' => '人均支付成功次数',

        'withdraw_total' => '申请提现金额',

        //用户签到  分享任务 购买金币  Hi币兑钻石获金币 系统赠送
//        'gold_obtain_total' => '获得金币总额',
//        'gold_obtain_num' => '获得金币次数',
//        'gold_obtain_user' => '获得金币人数',
//        'gold_obtain_num_average' => '人均获得金币次数',
//        'gold_obtain_user_average' => '人均获得金币数额',
//
//        'gold_cost_total' => '消耗金币总额',
//        'gold_cost_num' => '消耗金币次数',
//        'gold_cost_user' => '消耗金币人数',
//        'gold_cost_num_average' => '人均消耗金币次数',
//        'gold_cost_user_average' => '人均消耗金币数额',
//        'gold_cost_balance' => '消耗金币余额',
//
//        'gold_give_total' => '系统赠送金币总额',
//        'gold_give_num' => '系统赠送金币次数',
//        'gold_give_user' => '系统赠送金币人数',
//        'gold_give_num_average' => '人均系统赠送金币次数',
//        'gold_give_user_average' => '人均系统赠送金币数额',

        'hi_coin_cost_total' => 'Hi币兑换钻石金额',
        'hi_coin_cost_user' => 'Hi币兑换钻石人数',
        'hi_coin_cost_user_average' => '人均Hi币兑换钻石金额',

        'diamond_recharge_total' => '新增钻石总额',
        'diamond_recharge_give_total' => '赠送钻石总额',
        'diamond_recharge_user' => '新增钻石人数',
        'diamond_recharge_give_user' => '赠送钻石人数',
        'diamond_recharge_user_average' => '人均新增钻石数额',
        'diamond_recharge_give_user_average' => '人均赠送钻石数额',

        'diamond_cost_total' => '消耗钻石总额',
        'diamond_cost_num' => '消耗钻石次数',
        'diamond_cost_user' => '消耗钻石人数',
        'diamond_cost_num_average' => '人均消耗钻石次数',
        'diamond_cost_user_average' => '人均消耗钻石数额',
        'diamond_recharge_balance' => '钻石余额**',

        'new_create_order_num' => '新用户下单次数',
        'new_create_order_user' => '新用户下单人数',
        'new_create_order_average' => '新用户人均下单次数',
        'new_create_payment_num' => '新用户支付次数',
        'new_create_payment_user' => '新用户支付人数',
        'new_create_payment_average' => '新用户人均支付次数',
        'new_payment_success_num' => '新用户支付成功次数',
        'new_payment_success_user' => '新用户支付成功人数',
        'new_payment_success_average' => '新用户人均支付成功次数',


        'order_payment_rate' => '订单转化率%',
        'payment_success_rate' => '支付成功率%',
        'payment_success_total' => '支付总额',
        'paid_arpu' => '人均客单价',
        'arpu' => '人均arpu',

        'new_order_payment_rate' => '新用户订单转化率%',
        'new_payment_success_rate' => '新用户支付成功率%',
        'new_payment_success_total' => '新用户支付总额',
        'new_paid_arpu' => '新用户人均客单价',
        'new_arpu' => '新用户人均arpu'
    ];

    // 渠道统计
    static $STAT_PARTNER_FIELDS = [
        'device_active_num' => '设备激活数',
        'register_num' => "注册数",
        'register_rate' => '注册率%',

        'active_user_num' => '活跃用户数',
        'active_register_user_num' => '活跃注册用户数',

        'order_payment_rate' => '订单转化率%',
        'payment_success_rate' => '支付成功率%',
        'payment_success_total' => '支付总额',
        'paid_arpu' => '人均客单价',
        'arpu' => '人均arpu',

        'new_order_payment_rate' => '新用户订单转化率%',
        'new_payment_success_rate' => '新用户支付成功率%',
        'new_payment_success_total' => '新用户支付总额',
        'new_paid_arpu' => '新用户人均客单价',
        'new_arpu' => '新用户人均arpu'
    ];

    static function record($field, $action, $attrs)
    {

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
        $mobile = fetch($attrs, 'mobile', ''); // 剔重手机号
        $third_unionid = fetch($attrs, 'third_unionid', ''); //第三方登录标识
        $add_value = fetch($attrs, 'add_value', 0);

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

            // 活跃统计
            if (in_array($action, ['active_user']) && ($mobile || $third_unionid)) {
                $stat_db->zadd($date_key . "_register_user", $stat_at, $id); // 注册用户
                $stat_db->zadd($hour_key . "_register_user", $stat_at, $id); // 注册用户
            }

            if ($add_value) {
                $stat_db->incrby($date_key . "_total", $add_value);
                $stat_db->incrby($hour_key . "_total", $add_value);
            }

            // 新用户
            if (in_array($action, ['create_order', 'create_payment', 'payment_success'])) {

                // 天新用户
                if ($created_at > strtotime(date('Ymd 00:00:00', $stat_at))) {

                    $stat_db->zadd($date_key . "_new", $stat_at, $id);
                    $stat_db->incr($date_key . "_new_num");

                    if ($add_value) {
                        $stat_db->incrby($date_key . "_new_total", $add_value);
                    }
                }

                // 小时新用户
                if ($created_at > strtotime(date('Ymd H:00:00', $stat_at))) {

                    $stat_db->zadd($hour_key . "_new", $stat_at, $id);
                    $stat_db->incr($hour_key . "_new_num");

                    if ($add_value) {
                        $stat_db->incrby($hour_key . "_new_total", $add_value);
                    }
                }
            }
        }

    }

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
        foreach (array('platform', 'version_code', 'product_channel_id', 'partner_id', 'province_id', 'sex') as $key) {
            $val = $this->$key;
            $cache_key .= "_{$key}{$val}";
        }

        $cache_key .= "_" . $action;

        //debug($cache_key);

        return $cache_key;
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

    function webActiveNum()
    {
        $key = $this->statCacheKey("user", "web_active");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['web_active_num'] = intval($num);
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

    function totalActiveNum()
    {
        $active_num = fetch($this->data_hash, 'device_active_num', 0);
        $active_num += fetch($this->data_hash, 'subscribe_num', 0);
        $active_num += fetch($this->data_hash, 'touch_active_num', 0);
        $active_num += fetch($this->data_hash, 'web_active_num', 0);

        $this->data_hash['total_active_num'] = $active_num;
    }

    function registerNum()
    {
        $key = $this->statCacheKey("user", "register");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['register_num'] = intval($num);
    }

    function firstRegisterMobileNum()
    {
        $key = $this->statCacheKey("user", "first_register_mobile");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['first_register_mobile_num'] = intval($num);
    }

    function registerIpNum()
    {
        $key = $this->statCacheKey("user", "register");
        $key .= "_ip";
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['register_ip_num'] = intval($num);
    }

    function registerRate()
    {
        $active_num = $this->data_hash['total_active_num'];
        $register_num = $this->data_hash['register_num'];
        $register_rate = 0;
        if ($active_num > 0) {
            $register_rate = sprintf("%0.2f", $register_num * 100 / $active_num);
        }

        $this->data_hash['register_rate'] = $register_rate;
    }

    function registerRepeatRate()
    {
        $first_register_mobile_num = $this->data_hash['first_register_mobile_num'];
        $register_num = $this->data_hash['register_num'];
        $num = $register_num - $first_register_mobile_num;
        $register_rate = 0;
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
        $key = $this->statCacheKey("user", "active_user");
        $key .= '_register_user';
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $register_num = fetch($this->data_hash, 'register_num', 0);
        $num = intval($num) + $register_num;

        $this->data_hash['active_register_user_num'] = $num;
    }

    //课程报名次数
    function applyCourseNum()
    {
        $key = $this->statCacheKey("user", "apply_course");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['apply_course_num'] = intval($num);
    }

    //课程报名人数
    function applyCourseUser()
    {
        $key = $this->statCacheKey("user", "apply_course");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['apply_course_user'] = intval($num);
    }

    function applyCourseAverage()
    {
        $view_course_num = $this->data_hash['apply_course_num'];
        $view_course_user = $this->data_hash['apply_course_user'];
        $avg = 0;
        if ($view_course_user > 0) {
            $avg = intval($view_course_num * 100 / $view_course_user) / 100;
        }
        $this->data_hash['apply_course_average'] = $avg;
    }

    function viewCourseNum()
    {
        $key = $this->statCacheKey("user", "view_course");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['view_course_num'] = intval($num);
    }

    function viewCourseUser()
    {
        $key = $this->statCacheKey("user", "view_course");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['view_course_user'] = intval($num);
    }

    function viewCourseAverage()
    {
        $view_course_num = $this->data_hash['view_course_num'];
        $view_course_user = $this->data_hash['view_course_user'];
        $avg = 0;
        if ($view_course_user > 0) {
            $avg = intval($view_course_num * 100 / $view_course_user) / 100;
        }
        $this->data_hash['view_course_average'] = $avg;
    }

    function viewCourseCourseNum()
    {
        $key = $this->statCacheKey("user", "view_course");
        $stat_db = Stats::getStatDb();
        $key .= '_target';
        $num = $stat_db->zcard($key);
        $this->data_hash['view_course_course_num'] = intval($num);
    }

    function viewCourseCourseAverage()
    {
        $view_course_course_num = $this->data_hash['view_course_course_num'];
        $view_course_user = $this->data_hash['view_course_user'];
        $avg = 0;
        if ($view_course_user > 0) {
            $avg = intval($view_course_course_num * 100 / $view_course_user) / 100;
        }

        $this->data_hash['view_course_course_average'] = $avg;
    }


    function viewChapterNum()
    {
        $key = $this->statCacheKey("user", "view_chapter");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['view_chapter_num'] = intval($num);
    }

    function viewChapterUser()
    {
        $key = $this->statCacheKey("user", "view_chapter");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['view_chapter_user'] = intval($num);
    }

    function viewChapterAverage()
    {
        $view_chapter_num = $this->data_hash['view_chapter_num'];
        $view_chapter_user = $this->data_hash['view_chapter_user'];
        $avg = 0;
        if ($view_chapter_user > 0) {
            $avg = intval($view_chapter_num * 100 / $view_chapter_user) / 100;
        }
        $this->data_hash['view_chapter_average'] = $avg;
    }

    function viewChapterChapterNum()
    {
        $key = $this->statCacheKey("user", "view_chapter");
        $stat_db = Stats::getStatDb();
        $key .= '_target';
        $num = $stat_db->zcard($key);
        $this->data_hash['view_chapter_chapter_num'] = intval($num);
    }

    function viewChapterChapterAverage()
    {
        $view_chapter_chapter_num = $this->data_hash['view_chapter_chapter_num'];
        $view_chapter_user = $this->data_hash['view_chapter_user'];
        $avg = 0;
        if ($view_chapter_user > 0) {
            $avg = intval($view_chapter_chapter_num * 100 / $view_chapter_user) / 100;
        }

        $this->data_hash['view_chapter_chapter_average'] = $avg;
    }


    function playChapterNum()
    {
        $key = $this->statCacheKey("user", "play_chapter");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['play_chapter_num'] = intval($num);
    }

    function playChapterUser()
    {
        $key = $this->statCacheKey("user", "play_chapter");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['play_chapter_user'] = intval($num);
    }

    function playChapterAverage()
    {
        $play_chapter_num = $this->data_hash['play_chapter_num'];
        $play_chapter_user = $this->data_hash['play_chapter_user'];
        $avg = 0;
        if ($play_chapter_user > 0) {
            $avg = intval($play_chapter_num * 100 / $play_chapter_user) / 100;
        }
        $this->data_hash['play_chapter_average'] = $avg;
    }

    // 剔重，重复点击一个章节
    function playChapterChapterNum()
    {
        $key = $this->statCacheKey("user", "play_chapter");
        $stat_db = Stats::getStatDb();
        $key .= '_target';
        $num = $stat_db->zcard($key);
        $this->data_hash['play_chapter_chapter_num'] = intval($num);
    }

    function playChapterChapterAverage()
    {
        $play_chapter_chapter_num = $this->data_hash['play_chapter_chapter_num'];
        $play_chapter_user = $this->data_hash['play_chapter_user'];
        $avg = 0;
        if ($play_chapter_user > 0) {
            $avg = intval($play_chapter_chapter_num * 100 / $play_chapter_user) / 100;
        }
        $this->data_hash['play_chapter_chapter_average'] = $avg;
    }

    // 下单
    function createOrderNum()
    {
        $key = $this->statCacheKey("user", "create_order");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['create_order_num'] = intval($num);
    }

    function createOrderUser()
    {
        $key = $this->statCacheKey("user", "create_order");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['create_order_user'] = intval($num);
    }

    function createOrderAverage()
    {

        $num = $this->data_hash['create_order_num'];
        $user_num = $this->data_hash['create_order_user'];
        $avg = 0;
        if ($user_num > 0) {
            $avg = intval($num * 100 / $user_num) / 100;
        }

        $this->data_hash['create_order_average'] = $avg;
    }

    function newCreateOrderNum()
    {
        $key = $this->statCacheKey("user", "create_order");
        $stat_db = Stats::getStatDb();
        $key .= '_new_num';
        $num = $stat_db->get($key);
        $this->data_hash['new_create_order_num'] = intval($num);
    }

    function newCreateOrderUser()
    {
        $key = $this->statCacheKey("user", "create_order");
        $stat_db = Stats::getStatDb();
        $key .= '_new';
        $num = $stat_db->zcard($key);
        $this->data_hash['new_create_order_user'] = intval($num);
    }

    function newCreateOrderAverage()
    {

        $num = $this->data_hash['new_create_order_num'];
        $user_num = $this->data_hash['new_create_order_user'];
        $avg = 0;
        if ($user_num > 0) {
            $avg = intval($num * 100 / $user_num) / 100;
        }

        $this->data_hash['new_create_order_average'] = $avg;
    }


    function createPaymentNum()
    {
        $key = $this->statCacheKey("user", "create_payment");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['create_payment_num'] = intval($num);
    }

    function createPaymentUser()
    {
        $key = $this->statCacheKey("user", "create_payment");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['create_payment_user'] = intval($num);
    }

    function createPaymentAverage()
    {
        $create_payment_num = $this->data_hash['create_payment_num'];
        $create_payment_user = $this->data_hash['create_payment_user'];
        $avg = 0;
        if ($create_payment_user > 0) {
            $avg = intval($create_payment_num * 100 / $create_payment_user) / 100;
        }
        $this->data_hash['create_payment_average'] = $avg;
    }

    function newCreatePaymentNum()
    {
        $key = $this->statCacheKey("user", "create_payment");
        $stat_db = Stats::getStatDb();
        $key .= '_new_num';
        $num = $stat_db->get($key);
        $this->data_hash['new_create_payment_num'] = intval($num);
    }

    function newCreatePaymentUser()
    {
        $key = $this->statCacheKey("user", "create_payment");
        $stat_db = Stats::getStatDb();
        $key .= '_new';
        $num = $stat_db->zcard($key);
        $this->data_hash['new_create_payment_user'] = intval($num);
    }

    function newCreatePaymentAverage()
    {
        $new_create_payment_num = $this->data_hash['new_create_payment_num'];
        $new_create_payment_user = $this->data_hash['new_create_payment_user'];
        $avg = 0;
        if ($new_create_payment_user > 0) {
            $avg = intval($new_create_payment_num * 100 / $new_create_payment_user) / 100;
        }
        $this->data_hash['new_create_payment_average'] = $avg;
    }

    function withdrawTotal()
    {
        $key = $this->statCacheKey("user", "withdraw");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['withdraw_total'] = intval($num);
    }

    function paymentSuccessNum()
    {
        $key = $this->statCacheKey("user", "payment_success");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['payment_success_num'] = intval($num);
    }

    function paymentSuccessUser()
    {
        $key = $this->statCacheKey("user", "payment_success");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['payment_success_user'] = intval($num);
    }

    function paymentSuccessAverage()
    {
        $payment_success_num = $this->data_hash['payment_success_num'];
        $payment_success_user = $this->data_hash['payment_success_user'];
        $avg = 0;
        if ($payment_success_user > 0) {
            $avg = intval($payment_success_num * 100 / $payment_success_user) / 100;
        }
        $this->data_hash['payment_success_average'] = $avg;
    }

    function newPaymentSuccessNum()
    {
        $key = $this->statCacheKey("user", "payment_success");
        $stat_db = Stats::getStatDb();
        $key .= '_new_num';
        $num = $stat_db->get($key);
        $this->data_hash['new_payment_success_num'] = intval($num);
    }

    function newPaymentSuccessUser()
    {
        $key = $this->statCacheKey("user", "payment_success");
        $stat_db = Stats::getStatDb();
        $key .= '_new';
        $num = $stat_db->zcard($key);
        $this->data_hash['new_payment_success_user'] = intval($num);
    }

    function newPaymentSuccessAverage()
    {
        $new_payment_success_num = $this->data_hash['new_payment_success_num'];
        $new_payment_success_user = $this->data_hash['new_payment_success_user'];
        $avg = 0;
        if ($new_payment_success_user > 0) {
            $avg = intval($new_payment_success_num * 100 / $new_payment_success_user) / 100;
        }
        $this->data_hash['new_payment_success_average'] = $avg;
    }

    function paymentSuccessTotal()
    {
        $key = $this->statCacheKey("user", "payment_success");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['payment_success_total'] = intval($num);
    }

    //人均贡献值
    function arpu()
    {
        $payment_success_total = $this->data_hash['payment_success_total'];
        $active_register_user_num = $this->data_hash['active_register_user_num'];
        $avg = 0;
        if ($active_register_user_num > 0) {
            $avg = intval($payment_success_total * 100 / $active_register_user_num) / 100;
        }
        $this->data_hash['arpu'] = $avg;
    }

    //客单价
    function paidArpu()
    {
        $payment_success_total = $this->data_hash['payment_success_total'];
        $payment_success_user = $this->data_hash['payment_success_user'];

        $avg = 0;

        if ($payment_success_user > 0) {
            $avg = intval($payment_success_total * 100 / $payment_success_user) / 100;
        }
        $this->data_hash['paid_arpu'] = $avg;
    }

    function newPaymentSuccessTotal()
    {
        $key = $this->statCacheKey("user", "payment_success");
        $stat_db = Stats::getStatDb();
        $key .= '_new_total';
        $num = $stat_db->get($key);
        $this->data_hash['new_payment_success_total'] = intval($num);
    }

    function newArpu()
    {
        $payment_success_total = $this->data_hash['new_payment_success_total'];
        $register_num = $this->data_hash['register_num'];
        $avg = 0;
        if ($register_num > 0) {
            $avg = intval($payment_success_total * 100 / $register_num) / 100;
        }
        $this->data_hash['new_arpu'] = $avg;
    }

    function newPaidArpu()
    {
        $payment_success_total = $this->data_hash['new_payment_success_total'];
        $payment_success_user = $this->data_hash['new_payment_success_user'];
        $avg = 0;

        if ($payment_success_user > 0) {
            $avg = intval($payment_success_total * 100 / $payment_success_user) / 100;
        }

        $this->data_hash['new_paid_arpu'] = $avg;
    }

    /**
     * 活跃注册用户浏览率
     */
    function activeRegisterUserViewCourseRate()
    {
        $user_num = $this->data_hash['view_course_user'];
        $active_register_user_num = $this->data_hash['active_register_user_num'];

        $rate = 0;
        if ($active_register_user_num) {
            $rate = sprintf("%0.2f", $user_num * 100 / $active_register_user_num);
        }

        $this->data_hash['active_register_user_view_course_rate'] = $rate;
    }

    /**
     * 订单转化率
     */
    function orderPaymentRate()
    {
        $order_user = $this->data_hash['create_order_user'];
        $create_payment_user = $this->data_hash['create_payment_user'];

        $rate = 0;
        if ($order_user) {
            $rate = sprintf("%0.2f", $create_payment_user * 100 / $order_user);
        }
        $this->data_hash['order_payment_rate'] = $rate;
    }

    /**
     * 新用户订单转化率
     */
    function newOrderPaymentRate()
    {
        $order_user = $this->data_hash['new_create_order_user'];
        $create_payment_user = $this->data_hash['new_create_payment_user'];

        $rate = 0;
        if ($order_user) {
            $rate = sprintf("%0.2f", $create_payment_user * 100 / $order_user);

        }
        $this->data_hash['new_order_payment_rate'] = $rate;

    }

    /**
     * 支付成功率
     */
    function paymentSuccessRate()
    {
        $create_payment_user = $this->data_hash['create_payment_user'];
        $payment_success_user = $this->data_hash['payment_success_user'];

        $rate = 0;
        if ($create_payment_user) {
            $rate = sprintf("%0.2f", $payment_success_user * 100 / $create_payment_user);
        }
        $this->data_hash['payment_success_rate'] = $rate;
    }

    /**
     * 新用户支付成功率
     */
    function newPaymentSuccessRate()
    {
        $new_create_payment_user = $this->data_hash['new_create_payment_user'];
        $new_payment_success_user = $this->data_hash['new_payment_success_user'];

        $rate = 0;
        if ($new_create_payment_user) {
            $rate = sprintf("%0.2f", $new_payment_success_user * 100 / $new_create_payment_user);
        }
        $this->data_hash['new_payment_success_rate'] = $rate;
    }


    /**
     * 金币赠送总额
     */
    function goldObtainTotal()
    {
        $key = $this->statCacheKey("user", "gold_obtain");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['gold_obtain_total'] = intval($num);
    }

    /**
     * 金币赠送次数
     */
    function goldObtainNum()
    {
        $key = $this->statCacheKey("user", "gold_obtain");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['gold_obtain_num'] = intval($num);
    }

    /**
     * 金币赠送人数
     */
    function goldObtainUser()
    {
        $key = $this->statCacheKey("user", "gold_obtain");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['gold_obtain_user'] = intval($num);
    }

    /**
     * 人均获得金币次数
     */
    function goldObtainNumAverage()
    {
        $gold_obtain_num = $this->data_hash['gold_obtain_num'];
        $gold_obtain_user = $this->data_hash['gold_obtain_user'];
        $avg = 0;

        if ($gold_obtain_user > 0) {
            $avg = intval($gold_obtain_num * 100 / $gold_obtain_user) / 100;
        }

        $this->data_hash['gold_obtain_num_average'] = $avg;
    }

    /**
     * 人均获得金币数额
     */
    function goldObtainUserAverage()
    {
        $gold_obtain_total = $this->data_hash['gold_obtain_total'];
        $gold_obtain_user = $this->data_hash['gold_obtain_user'];

        $avg = 0;

        if ($gold_obtain_user > 0) {
            $avg = intval($gold_obtain_total * 100 / $gold_obtain_user) / 100;
        }

        $this->data_hash['gold_obtain_user_average'] = $avg;
    }

    /**
     * 消耗金币总额
     */
    function goldCostTotal()
    {
        $key = $this->statCacheKey("user", "gold_cost");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['gold_cost_total'] = intval($num);
    }

    /**
     * 金币赠送次数
     */
    function goldCostNum()
    {
        $key = $this->statCacheKey("user", "gold_cost");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['gold_cost_num'] = intval($num);
    }

    /**
     * 金币赠送人数
     */
    function goldCostUser()
    {
        $key = $this->statCacheKey("user", "gold_cost");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['gold_cost_user'] = intval($num);
    }

    /**
     * 人均获得金币次数
     */
    function goldCostNumAverage()
    {
        $gold_cost_num = $this->data_hash['gold_cost_num'];
        $ggold_cost_user = $this->data_hash['gold_cost_user'];
        $avg = 0;

        if ($ggold_cost_user > 0) {
            $avg = intval($gold_cost_num * 100 / $ggold_cost_user) / 100;
        }

        $this->data_hash['gold_cost_num_average'] = $avg;
    }

    /**
     * 人均获得金币数额
     */
    function goldCostUserAverage()
    {
        $gold_cost_total = $this->data_hash['gold_cost_total'];
        $gold_cost_user = $this->data_hash['gold_cost_user'];

        $avg = 0;

        if ($gold_cost_user > 0) {
            $avg = intval($gold_cost_total * 100 / $gold_cost_user) / 100;
        }

        $this->data_hash['gold_cost_user_average'] = $avg;
    }

    //消耗金币余额
    function goldCostBalance()
    {
        $gold_obtain_total = $this->data_hash['gold_obtain_total'];
        $gold_cost_total = $this->data_hash['gold_cost_total'];

        return intval($gold_obtain_total - $gold_cost_total);
    }

    /**
     * 金币赠送总额
     */
    function goldGiveTotal()
    {
        $key = $this->statCacheKey("user", "gold_give");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['gold_give_total'] = intval($num);
    }

    /**
     * 金币赠送次数
     */
    function goldGiveNum()
    {
        $key = $this->statCacheKey("user", "gold_give");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['gold_give_num'] = intval($num);
    }

    /**
     * 金币赠送人数
     */
    function goldGiveUser()
    {
        $key = $this->statCacheKey("user", "gold_give");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['gold_give_user'] = intval($num);
    }

    /**
     * 人均获得金币次数
     */
    function goldGiveNumAverage()
    {
        $gold_give_num = $this->data_hash['gold_give_num'];
        $gold_give_user = $this->data_hash['gold_give_user'];
        $avg = 0;

        if ($gold_give_user > 0) {
            $avg = intval($gold_give_num * 100 / $gold_give_user) / 100;
        }

        $this->data_hash['gold_give_num_average'] = $avg;
    }

    /**
     * 人均获得金币数额
     */
    function goldGiveUserAverage()
    {
        $gold_give_total = $this->data_hash['gold_give_total'];
        $gold_give_user = $this->data_hash['gold_give_user'];

        $avg = 0;

        if ($gold_give_user > 0) {
            $avg = intval($gold_give_total * 100 / $gold_give_user) / 100;
        }

        $this->data_hash['gold_give_user_average'] = $avg;
    }

    /**
     * 购买钻石总额
     */
    function diamondRechargeTotal()
    {
        $key = $this->statCacheKey("user", "diamond_recharge");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['diamond_recharge_total'] = intval($num);
    }

    /**
     * 购买钻石次数
     */
    function diamondRechargeNum()
    {
        $key = $this->statCacheKey("user", "diamond_recharge");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['diamond_recharge_num'] = intval($num);
    }

    /**
     * 购买钻石人数
     */
    function diamondRechargeUser()
    {
        $key = $this->statCacheKey("user", "diamond_recharge");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['diamond_recharge_user'] = intval($num);
    }

    /**
     * 人均购买钻石次数
     */
    function diamondRechargeNumAverage()
    {
        $diamond_recharge_num = $this->data_hash['diamond_recharge_num'];
        $diamond_recharge_user = $this->data_hash['diamond_recharge_user'];
        $avg = 0;

        if ($diamond_recharge_user > 0) {
            $avg = intval($diamond_recharge_num * 100 / $diamond_recharge_user) / 100;
        }

        $this->data_hash['diamond_recharge_num_average'] = $avg;
    }

    /**
     * 人均购买钻石数额
     */
    function diamondRechargeUserAverage()
    {
        $diamond_recharge_total = $this->data_hash['diamond_recharge_total'];
        $diamond_recharge_user = $this->data_hash['diamond_recharge_user'];

        $avg = 0;

        if ($diamond_recharge_user > 0) {
            $avg = intval($diamond_recharge_total * 100 / $diamond_recharge_user) / 100;
        }

        $this->data_hash['diamond_recharge_user_average'] = $avg;
    }

    /**
     * Hi兑换总额
     */
    function hiCoinCostTotal()
    {
        $key = $this->statCacheKey("user", "hi_coin_cost");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['hi_coin_cost_total'] = intval($num);
    }

    /**
     * Hi兑换人数
     */
    function hiCoinCostUser()
    {
        $key = $this->statCacheKey("user", "hi_coin_cost");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['hi_coin_cost_user'] = intval($num);
    }

    /**
     * 人均Hi币兑换数额
     */
    function hiCoinCostUserAverage()
    {
        $hi_coin_cost_total = $this->data_hash['hi_coin_cost_total'];
        $hi_coin_cost_user = $this->data_hash['hi_coin_cost_user'];

        $avg = 0;

        if ($hi_coin_cost_user > 0) {
            $avg = intval($hi_coin_cost_total * 100 / $hi_coin_cost_user) / 100;
        }

        $this->data_hash['hi_coin_cost_user_average'] = $avg;
    }


    /**
     * 赠送钻石总额
     */
    function diamondRechargeGiveTotal()
    {
        $key = $this->statCacheKey("user", "diamond_recharge_give");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['diamond_recharge_give_total'] = intval($num);
    }

    /**
     * 赠送钻石人数
     */
    function diamondRechargeGiveUser()
    {
        $key = $this->statCacheKey("user", "diamond_recharge_give");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['diamond_recharge_give_user'] = intval($num);
    }

    /**
     * 人均赠送钻石数额
     */
    function diamondRechargeGiveUserAverage()
    {
        $diamond_recharge_total = $this->data_hash['diamond_recharge_give_total'];
        $diamond_recharge_user = $this->data_hash['diamond_recharge_give_user'];

        $avg = 0;

        if ($diamond_recharge_user > 0) {
            $avg = intval($diamond_recharge_total * 100 / $diamond_recharge_user) / 100;
        }

        $this->data_hash['diamond_recharge_give_user_average'] = $avg;
    }

    /**
     * 消耗钻石总额
     */
    function diamondCostTotal()
    {
        $key = $this->statCacheKey("user", "diamond_cost");
        $stat_db = Stats::getStatDb();
        $key .= '_total';
        $num = $stat_db->get($key);
        $this->data_hash['diamond_cost_total'] = intval($num);
    }

    /**
     * 消耗钻石次数
     */
    function diamondCostNum()
    {
        $key = $this->statCacheKey("user", "diamond_cost");
        $stat_db = Stats::getStatDb();
        $key .= '_num';
        $num = $stat_db->get($key);
        $this->data_hash['diamond_cost_num'] = intval($num);
    }

    /**
     * 消耗钻石人数
     */
    function diamondCostUser()
    {
        $key = $this->statCacheKey("user", "diamond_cost");
        $stat_db = Stats::getStatDb();
        $num = $stat_db->zcard($key);
        $this->data_hash['diamond_cost_user'] = intval($num);
    }

    /**
     * 人均消耗钻石次数
     */
    function diamondCostNumAverage()
    {
        $diamond_cost_num = $this->data_hash['diamond_cost_num'];
        $diamond_cost_user = $this->data_hash['diamond_cost_user'];
        $avg = 0;

        if ($diamond_cost_user > 0) {
            $avg = intval($diamond_cost_num * 100 / $diamond_cost_user) / 100;
        }

        $this->data_hash['diamond_cost_num_average'] = $avg;
    }

    /**
     * 人均消耗钻石数额
     */
    function diamondCostUserAverage()
    {
        $diamond_cost_total = $this->data_hash['diamond_cost_total'];
        $diamond_cost_user = $this->data_hash['diamond_cost_user'];

        $avg = 0;

        if ($diamond_cost_user > 0) {
            $avg = intval($diamond_cost_total * 100 / $diamond_cost_user) / 100;
        }

        $this->data_hash['diamond_cost_user_average'] = $avg;
    }

    /**
     * 购买钻石余额
     */
    function diamondRechargeBalance()
    {
        $diamond_cost_total = $this->data_hash['diamond_cost_total'];
        $diamond_recharge_total = $this->data_hash['diamond_recharge_total']; //购买钻石数额
        $diamond_recharge_give_total = $this->data_hash['diamond_recharge_give_total']; // 赠送钻石数额
        $avg = $diamond_recharge_total + $diamond_recharge_give_total - $diamond_cost_total;

        $this->data_hash['diamond_recharge_balance'] = $avg;
    }

    /**
     * 微信用户统计
     * @param $product_channel_id
     * @param $stat_date
     * @return mixed
     */
    static function followStat($product_channel_id, $stat_date)
    {
        $stat_ssdb = self::getStatDb();
        $weixin_user_stat_key = 'weixin_user_stat_' . $product_channel_id . "_" . $stat_date;

        $json_data = $stat_ssdb->get($weixin_user_stat_key);
        if (isBlank($json_data)) {
            $product_channel = ProductChannels::findFirstById($product_channel_id);
            $weixin_event = new \WeixinEvents($product_channel);
            $date = str_replace('_', '-', $stat_date);
            $result = $weixin_event->getStats($date);

            $weixin_all_new_num = 0;
            $weixin_all_cancel_num = 0;

            $sources = [0 => 'total', 1 => 'search', 17 => 'card', 30 => 'scan', 43 => 'right', 51 => 'paid', 57 => 'page', 75 => 'article', 78 => 'quan'];

            foreach ($sources as $source) {
                $new_num_key = 'weixin_' . $source . '_new_num';
                $cancle_num_key = 'weixin_' . $source . '_cancel_num';

                if (isset($result[$new_num_key])) {
                    $weixin_all_new_num += $result[$new_num_key];
                }

                if (isset($result[$cancle_num_key])) {
                    $weixin_all_cancel_num += $result[$cancle_num_key];
                }
            }

            #总的净增人数
            $result['weixin_all_add_num'] = $weixin_all_new_num - $weixin_all_cancel_num;
            #总的新增人数
            $result['weixin_all_new_num'] = $weixin_all_new_num;
            #总的取消人数
            $result['weixin_all_cancel_num'] = $weixin_all_cancel_num;

            $json_data = json_encode($result, JSON_UNESCAPED_UNICODE);
            $stat_ssdb->set($weixin_user_stat_key, $json_data);
        }

        $stat_data = json_decode($json_data, true);

        return $stat_data;
    }

    //'operat_manager' => '推广运营经理', 'operator' => '推广运营专员'
    static function statFields($operator)
    {
        $fields = ['device_active_num' => '设备激活数', 'total_active_num' => '总激活数', 'register_num' => '注册数',
            'register_rate' => '注册率%', 'new_payment_success_total' => '新用户支付总额',
            'new_paid_arpu' => '新用户人均客单价', 'new_arpu' => '新用户人均arpu'];

        if (in_array($operator->role, ['operat_manager', 'operator'])) {
            return $fields;
        }

        return self::$STAT_FIELDS;
    }

    static function statPartnerFields($operator)
    {
        $fields = ['device_active_num' => '设备激活数', 'total_active_num' => '总激活数', 'register_num' => '注册数',
            'register_rate' => '注册率%', 'new_payment_success_total' => '新用户支付总额',
            'new_paid_arpu' => '新用户人均客单价', 'new_arpu' => '新用户人均arpu'];


        if (in_array($operator->role, ['operat_manager', 'operator'])) {
            return $fields;
        }

        return self::$STAT_PARTNER_FIELDS;
    }
}