<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/27
 * Time: 下午9:21
 */
class ProvinceStats extends BaseModel
{
    /**
     * @type Provinces
     */
    private $_province;

    static $TIME_TYPE = [STAT_DAY => '天'];

    static $PLATFORMS = ['-1' => '全部', USER_PLATFORM_IOS => '苹果客户端', USER_PLATFORM_ANDROID => '安卓客户端',
        USER_PLATFORM_WEIXIN_IOS => '微信苹果端', USER_PLATFORM_WEIXIN_ANDROID => '微信安卓端'];

    static $STAT_FIELDS = [
        'active_num' => '激活数(关注数)',
        'register_num' => "当天注册数",
        'register_rate' => "注册率%",
        'order_num' => '订单次数',
        'order_user' => '订单人数',
        'order_amount' => '订单金额'
    ];

    static function dayStat($product_channel_id, $province_id, $partner_id, $platform, $stat_at)
    {

        $start_at = beginOfDay($stat_at);
        $end_at = endOfDay($stat_at);

        $cond = [
            'conditions' => 'stat_at=:stat_at: and time_type=:time_type: and province_id=:province_id: and product_channel_id=:product_channel_id: and partner_id=:partner_id: and platform=:platform:',
            'bind' => ['stat_at' => $start_at, 'time_type' => STAT_DAY, 'province_id' => $province_id, 'product_channel_id' => $product_channel_id, 'partner_id' => $partner_id, 'platform' => $platform]
        ];

        $province_stat = ProvinceStats::findFirst($cond);

        if (!$province_stat) {
            $province_stat = new ProvinceStats();
        }

        $data_hash = [];
        if ($province_stat->data) {
            $data_hash = json_decode($province_stat->data, true);
        }

        // 激活数
        $data_hash['active_num'] = ProvinceStats::activeNum($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at);
        if ($data_hash['active_num'] < 1) {
            // 没有激活，不保存
            return;
        }
        // 注册数
        $register_num = ProvinceStats::registerNum($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at);
        $data_hash['register_num'] = $register_num;

        // 订单次数
        $order_num = ProvinceStats::orderNum($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at);
        $data_hash['order_num'] = $order_num;
        // 订单人数
        $order_user = 0;
        if ($order_num) {
            $order_user = ProvinceStats::orderUser($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at);
        }
        $data_hash['order_user'] = $order_user;

        //订单金额
        $order_amount = 0;
        if ($order_num) {
            $order_amount = self::orderAmount($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at);
        }
        $data_hash['order_amount'] = $order_amount;

        $total_val = 0;
        foreach ($data_hash as $k => $v) {
            $total_val += $v;
        }

        if ($total_val < 1) {
            // 没有数据，不保存
            return;
        }


        $province_stat->product_channel_id = $product_channel_id;
        $province_stat->stat_at = $start_at;
        $province_stat->time_type = STAT_DAY;
        $province_stat->platform = $platform;
        $province_stat->province_id = $province_id;
        $province_name = '无省份';
        if ($province_id) {
            $province = Provinces::findFirstById($province_id);
            $province_name = $province->name;
        }
        $province_stat->partner_id = $partner_id;
        $province_stat->province_name = $province_name;
        $province_stat->data = json_encode($data_hash, JSON_UNESCAPED_UNICODE);
        $province_stat->calculate();
        $province_stat->save();

        info($province_stat->id, date('Ymd', $start_at), $data_hash);
    }

    static function activeNum($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at)
    {

        $find_cond['conditions'] = 'created_at>=:start_at: and created_at<=:end_at: ';
        $find_cond['bind'] = ['start_at' => $start_at, 'end_at' => $end_at];
        if ($province_id) {
            $find_cond['conditions'] .= ' and province_id=:province_id:';
            $find_cond['bind']['province_id'] = $province_id;
        }

        if ($product_channel_id > 0) {
            $find_cond['conditions'] .= ' and product_channel_id=:product_channel_id:';
            $find_cond['bind']['product_channel_id'] = $product_channel_id;
        }

        if ($partner_id) {
            $find_cond['conditions'] .= ' and partner_id=:partner_id:';
            $find_cond['bind']['partner_id'] = $partner_id;
        }

        if ($platform > 0) {
            $find_cond['conditions'] .= ' and platform=:platform:';
            $find_cond['bind']['platform'] = $platform;
        }

        $total = Users::count($find_cond);

        return $total;
    }

    static function registerNum($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at)
    {
        $find_cond['conditions'] = 'register_at>=:start_at: and register_at<=:end_at: and platform = :platform:';
        $find_cond['bind'] = ['start_at' => $start_at, 'end_at' => $end_at, 'platform' => $platform];
        if ($province_id) {
            $find_cond['conditions'] .= ' and province_id=:province_id:';
            $find_cond['bind']['province_id'] = $province_id;
        } else {
            $find_cond['conditions'] .= ' and (province_id is null or province_id=0)';
        }
        if ($product_channel_id > 0) {
            $find_cond['conditions'] .= ' and product_channel_id=:product_channel_id:';
            $find_cond['bind']['product_channel_id'] = $product_channel_id;
        }

        if ($partner_id) {
            $find_cond['conditions'] .= ' and partner_id=:partner_id:';
            $find_cond['bind']['partner_id'] = $partner_id;
        } else {
            $find_cond['conditions'] .= ' and (partner_id is null or partner_id=0)';
        }

        $find_cond['conditions'] .= ' and mobile!=:mobile:';
        $find_cond['bind']['mobile'] = '';

        $total = Users::count($find_cond);

        return $total;
    }


    static function getOrderCond($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at)
    {
        $find_cond['conditions'] = 'created_at>=:start_at: and created_at<=:end_at: and status =:status:';
        $find_cond['bind'] = ['start_at' => $start_at, 'end_at' => $end_at, 'status' => ORDER_STATUS_SUCCESS];
        if ($province_id) {
            $find_cond['conditions'] .= ' and province_id=:province_id:';
            $find_cond['bind']['province_id'] = $province_id;
        }

        if ($product_channel_id > 0) {
            $find_cond['conditions'] .= ' and product_channel_id=:product_channel_id:';
            $find_cond['bind']['product_channel_id'] = $product_channel_id;
        }

        if ($partner_id) {
            $find_cond['conditions'] .= ' and partner_id=:partner_id:';
            $find_cond['bind']['partner_id'] = $partner_id;
        }

        if ($platform > 0) {
            $find_cond['conditions'] .= ' and platform=:platform:';
            $find_cond['bind']['platform'] = $platform;
        }

        return $find_cond;
    }

    static function orderNum($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at)
    {
        $find_cond = ProvinceStats::getOrderCond($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at);

        $total = Orders::count($find_cond);

        return $total;
    }

    static function orderUser($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at)
    {
        $find_cond = ProvinceStats::getOrderCond($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at);

        $find_cond['column'] = 'distinct user_id';
        $total = Orders::count($find_cond);

        return $total;
    }

    static function orderAmount($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at)
    {
        $find_cond = ProvinceStats::getOrderCond($province_id, $product_channel_id, $partner_id, $platform, $start_at, $end_at);

        $find_cond['column'] = 'distinct amount';
        $total_amount = Orders::sum($find_cond);

        return $total_amount;
    }

    function calculate()
    {
        if (!$this->data) {
            return;
        }

        $data_hash = json_decode($this->data, true);

        // 注册率
        $register_num = fetch($data_hash, 'register_num');
        $register_num = intval($register_num);
        $active_num = intval(fetch($data_hash, 'active_num'));
        $register_rate = 0;
        if ($active_num > 0) {
            $register_rate = sprintf("%0.2f", $register_num * 100 / $active_num);
        }
        $data_hash['register_rate'] = $register_rate;

        $this->data = json_encode($data_hash, JSON_UNESCAPED_UNICODE);
    }
}