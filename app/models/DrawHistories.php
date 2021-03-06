<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/28
 * Time: 下午4:29
 */
class DrawHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type ProductChannels
     */
    private $_product_channel;

    /**
     * @type Gifts
     */
    private $_gift;

    static $TYPE = ['gold' => '金币', 'diamond' => '钻石', 'gift' => '礼物'];

    static $PAY_TYPE = ['gold' => '金币', 'diamond' => '钻石'];

    function beforeCreate()
    {
        return $this->checkBalance();
    }

    function afterCreate()
    {

        // 汇总
        $user_db = Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_' . $this->pay_type;
        $user_db->incrby($cache_key, intval($this->pay_amount));

        // 系统支出: 金币，钻石，礼物
        $cache_decr_key = 'draw_history_total_amount_decr_' . $this->type;
        $user_db->incrby($cache_decr_key, intval($this->number));

        $new_cache_key = 'new_v2_draw_history_total_amount_incr_' . $this->pay_type;
        $new_cache_decr_key = 'new_v2_draw_history_total_amount_decr_' . $this->type;
        $new_total_incr_diamond = $user_db->incrby($new_cache_key, intval($this->pay_amount));
        $new_total_decr_diamond = $user_db->incrby($new_cache_decr_key, intval($this->number));

        info($new_cache_key, $new_total_incr_diamond, $new_cache_decr_key, $new_total_decr_diamond);

        // 全服通知：个推，系统消息，公屏消息
        if ($this->type == 'diamond' && $this->number == 100000
            || $this->type == 'gift' && $this->gift_id == 73
        ) {
            $cache_hit_10w_key = 'draw_history_hit_all_notice';
            $hot_cache = Users::getHotWriteCache();
            $hot_cache->setex($cache_hit_10w_key, 3600 * 25, $this->id);
        }

        // 全服通知：公屏消息
        if ($this->number >= 10000 || 'gift' == $this->type || isDevelopmentEnv()) {

            $content = '';
            if ('diamond' == $this->type) {
                $content = '哇哦！' . $this->user->nickname . '刚刚砸出' . $this->number . '钻大奖！还不快来砸金蛋，试试手气~';
            }
            if ('gift' == $this->type) {
                $content = '哇哦！' . $this->user->nickname . '刚刚砸出' . $this->gift->name . '大奖！还不快来砸金蛋，试试手气~';
            }

            if ($content) {
                info('全服公屏消息', $this->id, $this->type, $this->user_id, $this->number);
                Rooms::delay()->asyncAllNoticePush($content, ['type' => 'top_topic_message']);
            }
        }

    }

    function checkBalance()
    {
        $history = self::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $this->user_id],
            'order' => 'id desc']);

        $old_total_number = 0;
        $old_total_pay_amount = 0;
        $old_total_gold = 0;
        $old_total_diamond = 0;
        $old_total_gift_diamond = 0;
        $old_total_gift_num = 0;
        if ($history) {
            $old_total_number = $history->total_number;
            $old_total_pay_amount = $history->total_pay_amount;
            $old_total_gold = $history->total_gold;
            $old_total_diamond = $history->total_diamond;
            $old_total_gift_diamond = $history->total_gift_diamond;
            $old_total_gift_num = $history->total_gift_num;
        }

        $this->total_number = $old_total_number + $this->number;
        $this->total_pay_amount = $old_total_pay_amount + $this->pay_amount;
        if ($this->type == 'gold') {
            $this->total_gold = $old_total_gold + $this->number;
        } else {
            $this->total_gold = $old_total_gold;
        }
        if ($this->type == 'diamond') {
            $this->total_diamond = $old_total_diamond + $this->number;
        } else {
            $this->total_diamond = $old_total_diamond;
        }
        if ($this->type == 'gift') {
            $this->total_gift_diamond = $old_total_gift_diamond + $this->number;
            $this->total_gift_num = $old_total_gift_num + $this->gift_num;
        } else {
            $this->total_gift_diamond = $old_total_gift_diamond;
            $this->total_gift_num = $old_total_gift_num;
        }

        return false;
    }

    static function getData()
    {
        $data = [];
        if (date("Ymd") == '20180515') {
            $data[0] = ['id' => 1, 'type' => 'diamond', 'name' => '100000钻石', 'number' => 100000, 'rate' => 0.1, 'day_limit_num' => 2];
        } else {
            $data[0] = ['id' => 1, 'type' => 'diamond', 'name' => '100000钻石', 'number' => 100000, 'rate' => 0.1, 'day_limit_num' => 1];
        }
        if (isProduction()) {
            $data[1] = ['id' => 2, 'type' => 'gift', 'name' => '梦境奇迹座驾', 'number' => 35000, 'rate' => 0.3, 'gift_id' => 73, 'gift_num' => 1, 'day_limit_num' => 1];
        } else {
            $data[1] = ['id' => 2, 'type' => 'gift', 'name' => '梦境奇迹座驾', 'number' => 35000, 'rate' => 0.3, 'gift_id' => 142, 'gift_num' => 1, 'day_limit_num' => 1];
        }
        if (isProduction()) {
            $data[2] = ['id' => 3, 'type' => 'gift', 'name' => 'UFO座驾', 'number' => 12000, 'rate' => 0.6, 'gift_id' => 33, 'gift_num' => 1, 'day_limit_num' => 1];
        } else {
            $data[2] = ['id' => 3, 'type' => 'gift', 'name' => 'UFO座驾', 'number' => 12000, 'rate' => 0.6, 'gift_id' => 61, 'gift_num' => 1, 'day_limit_num' => 1];
        }
        $data[3] = ['id' => 4, 'type' => 'diamond', 'name' => '10000钻石', 'number' => 10000, 'rate' => 1.1, 'day_limit_num' => 0];
        if (isProduction()) {
            $data[4] = ['id' => 5, 'type' => 'gift', 'name' => '光电游侠座驾', 'number' => 5000, 'rate' => 1.7, 'gift_id' => 57, 'gift_num' => 1, 'day_limit_num' => 3];
        } else {
            $data[4] = ['id' => 5, 'type' => 'gift', 'name' => '光电游侠座驾', 'number' => 5000, 'rate' => 1.7, 'gift_id' => 63, 'gift_num' => 1, 'day_limit_num' => 3];
        }
        $data[5] = ['id' => 6, 'type' => 'diamond', 'name' => '1000钻石', 'number' => 1000, 'rate' => 2.7, 'day_limit_num' => 0];
        $data[6] = ['id' => 7, 'type' => 'diamond', 'name' => '500钻石', 'number' => 500, 'rate' => 4.7, 'day_limit_num' => 0];
        $data[7] = ['id' => 8, 'type' => 'diamond', 'name' => '100钻石', 'number' => 100, 'rate' => 7.7, 'day_limit_num' => 0];
        $data[8] = ['id' => 9, 'type' => 'diamond', 'name' => '30钻石', 'number' => 30, 'rate' => 15.7, 'day_limit_num' => 0];
        $data[9] = ['id' => 10, 'type' => 'diamond', 'name' => '10钻石', 'number' => 10, 'rate' => 27.7, 'day_limit_num' => 0];
        $data[10] = ['id' => 11, 'type' => 'gold', 'name' => '100金币', 'number' => 100, 'rate' => 57.7, 'day_limit_num' => 0];
        $data[11] = ['id' => 12, 'type' => 'gold', 'name' => '50金币', 'number' => 50, 'rate' => 100, 'day_limit_num' => 0];

        return $data;
    }

    static function isDayLimit($data)
    {
        $id = fetch($data, 'id');
        $day_limit_num = fetch($data, 'day_limit_num', 0);
        if ($day_limit_num < 1) {
            return false;
        }

        $user_db = Users::getUserDb();
        $cache_key = 'draw_history_hit_num_' . date('Ymd') . '_' . $id;
        $num = $user_db->get($cache_key);
        if ($day_limit_num && $num >= $day_limit_num) {
            //info('limit', $cache_key, $num, $day_limit_num);
            return true;
        }

        return false;
    }

    static function calUserRateMulti($user, $last_history)
    {

        // 倍率
        $user_rate_multi = 1;
        $total_pay_amount = 0;
        $total_get_amount = 0;

        if ($last_history) {
            $total_pay_amount = intval($last_history->total_pay_amount);
            $total_get_amount = $last_history->total_diamond + $last_history->total_gift_diamond;

            // 第一次抽奖5倍概率，开始抽奖的前5次，如果不中奖，每次增加10倍概率；
            if ($total_pay_amount < 50 && $total_get_amount < 10) {
                $user_rate_multi = $total_pay_amount;
            }

        } else {
            // 第一次抽奖5倍概率，开始抽奖的前5次，如果不中奖，每次增加10倍概率；
            $user_rate_multi = 5;
        }

        // 老用户
        if ($user_rate_multi <= 1) {

            // 根据损失钻石计算倍率
            if ($total_pay_amount > $total_get_amount) {
                $decr_rate = ($total_pay_amount - $total_get_amount) / $total_pay_amount;
                if ($decr_rate * 100 > mt_rand(20, 40) && mt_rand(1, 100) < 75) {
                    $user_rate_multi = ceil(($total_pay_amount - $total_get_amount) / mt_rand(60, 360));
                    if ($user_rate_multi > 100) {
                        $user_rate_multi = 100;
                    }
                }
            }

            if ($user_rate_multi <= 1) {

                $user_hit_num = \DrawHistories::count([
                    'conditions' => 'user_id = :user_id: and created_at>=:start_at:',
                    'bind' => ['user_id' => $user->id, 'start_at' => time() - 300]
                ]);

                if ($user_hit_num > mt_rand(40, 75)) {

                    $user_hit_diamond = \DrawHistories::sum([
                        'conditions' => 'user_id = :user_id: and type = :type: and created_at>=:start_at:',
                        'bind' => ['user_id' => $user->id, 'type' => 'diamond', 'start_at' => time() - 300],
                        'column' => 'number'
                    ]);

                    if (($user_hit_num * 10 - $user_hit_diamond) / $user_hit_num * 10 > 0.5 && mt_rand(1, 100) < 55) {
                        $user_rate_multi = mt_rand(2, 5) * intval($user_hit_num / 40);
                        if ($user_rate_multi > 10) {
                            $user_rate_multi = 10;
                        }
                    }

                    if ($user_rate_multi > 4 && $total_get_amount > $total_pay_amount + 3000) {
                        $user_rate_multi = mt_rand(1, 4);
                    }

                    info('五分钟内倍率', $user->id, 'user_hit_num', $user_hit_num, 'user_hit_diamond', $user_hit_diamond, '倍率', $user_rate_multi);
                }
            }
        }

        //info($user->id, '用户消耗', $total_pay_amount, '用户获得', $total_get_amount, '倍率', $user_rate_multi);

        return [$user_rate_multi, $total_pay_amount];
    }

    static function isWhiteList($user)
    {
        return in_array($user->id, [1152242]);
    }

    static function calPayAmountRate($user, $datum, $opts)
    {

        $user_total_get_amount = fetch($opts, 'user_total_get_amount');
        $user_rate_multi = fetch($opts, 'user_rate_multi');
        $total_pay_amount = fetch($opts, 'total_pay_amount');
        $total_incr_diamond = fetch($opts, 'total_incr_diamond');
        $total_decr_diamond = fetch($opts, 'total_decr_diamond');
        $is_block_user = fetch($opts, 'is_block_user', false);

        $type = fetch($datum, 'type');
        $number = fetch($datum, 'number');

        $pool_rate = mt_rand(700, 945) / 1000;

        $hour = intval(date("H"));

        if ($number > 10000 && $is_block_user) {
            return 0;
        }

        if ($type == 'diamond') {

            // 第一次抽奖限制100
            if ($total_pay_amount < 1 && $number > 100) {
                info('continue 第一次抽奖最多100钻', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                return 0;
            }

            // 奖金池控制
            if ($number != 100000 && $total_pay_amount > 50 && $total_decr_diamond + $number > $total_incr_diamond * ($pool_rate + 0.011)) {
                info('continue 奖金池控制', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                return 0;
            }

            $hit_num = self::count([
                'conditions' => 'user_id = :user_id: and (type=:type: or type=:type2:) and created_at>=:start_at:',
                'bind' => ['user_id' => $user->id, 'type' => 'diamond', 'type2' => 'gift', 'start_at' => time() - 1],
                'order' => 'id desc']);

            if ($hit_num >= 3) {
                info('continue hit_num', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                return 0;
            }

            // 15分钟 倍率小于15倍，只能中一次1万钻
            // && $user_rate_multi < 30
            if ($number >= 10000) {

                if ($total_pay_amount && (($user_total_get_amount + $number) / $total_pay_amount > 2)) {
                    info('continue hit1w超出支出2倍', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                    return 0;
                }

                if ($user_total_get_amount + mt_rand(7000, 10000) > $total_pay_amount && $is_block_user) {
                    info('continue hit1w超出支出, 屏蔽', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                    return 0;
                }

                $hit_1w_history = self::findFirst([
                    'conditions' => 'type=:type: and number>=:number: and created_at>=:start_at:',
                    'bind' => ['type' => 'diamond', 'number' => 10000, 'start_at' => time() - 300],
                    'order' => 'id desc']);

                if ($hit_1w_history) {
                    info('continue hit1w_sys', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                    return 0;
                }

                $user_hit_1w_history = self::findFirst([
                    'conditions' => 'user_id = :user_id: and (type=:type: or type=:type2:) and number>=:number: and created_at>=:start_at:',
                    'bind' => ['user_id' => $user->id, 'type' => 'diamond', 'type2' => 'gift', 'number' => 10000, 'start_at' => time() - 1200],
                    'order' => 'id desc']);
                if ($user_hit_1w_history) {
                    info('continue hit1w', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                    return 0;
                }

                // 爆10w钻
                if ($number == 100000) {

                    if ($hour < 20) {
                        return 0;
                    }

                    if ($user_total_get_amount + 5000 > $total_pay_amount) {
                        info('continue hit10w超出支出', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                        return 0;
                    }

                    if ($total_pay_amount < 15000 && !$user->union_id || $total_pay_amount < 50000 || !$user->segment || mt_rand(1, 100) < 80) {
                        info('continue hit10w没资格', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                        return 0;
                    }

                    $user_hit_10w_history = self::findFirst([
                        'conditions' => 'user_id = :user_id: and type=:type: and number=:number:',
                        'bind' => ['user_id' => $user->id, 'type' => 'diamond', 'number' => 100000]]);
                    if ($user_hit_10w_history && (time() - $user_hit_10w_history->created_at < 7 * 3600 * 24
                            || $user_total_get_amount + mt_rand(80000, 100000) > $total_pay_amount)) {
                        info('continue hit10w已命中', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                        return 0;
                    }

                    if ($total_decr_diamond + $number > $total_incr_diamond) {
                        info('continue hit10w超出奖金池', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                        return 0;
                    }

                    $user_hit_10w_histories = self::find([
                        'conditions' => 'type=:type: and number=:number:',
                        'bind' => ['type' => 'diamond', 'number' => 100000],
                        'columns' => 'id,user_id,created_at'
                    ]);

                    foreach ($user_hit_10w_histories as $history) {

                        $hit_user = Users::findFirstById($history->user_id);
                        if ($hit_user && $hit_user->id != $user->id && ($hit_user->device_id == $user->device_id || $hit_user->ip == $user->ip)
                            && $user_total_get_amount + mt_rand(80000, 100000) > $total_pay_amount) {

                            info('continue hit10w 同一个用户', $user->id, $hit_user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                            $user_db = Users::getUserDb();
                            $user_db->zadd('draw_histories_block_user_ids', time(), $user->id);
                            return 0;
                        }

                        if (time() - $history->created_at < 3600 * 5) {
                            info('continue hit10w 短时间内不能爆10万', $user->id, $hit_user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                            return 0;
                        }
                    }

                    info('命中10万', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'user_rate', $user_rate_multi);
                }

            }

            $total_pay_amount_rate = mt_rand(3, 7);

        } elseif ($type == 'gift') {

            $gift_id = fetch($datum, 'gift_id');

            if ($hour <= 10) {
                return 0;
            }

            // 第一次抽奖限制
            if ($total_pay_amount < 1 && $number > 500) {
                info('continue 第一次抽奖礼物限制', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                return 0;
            }

            if ($user_total_get_amount + 1000 > $total_pay_amount) {
                info('continue gift超出支出', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'user_rate', $user_rate_multi);
                return 0;
            }

            $interval_time = time() - 3600 * 3 - mt_rand(1, 1800);
            $cur_hour = intval(date("H"));
            if ($cur_hour > 1 and $cur_hour < 9) {
                $interval_time = time() - 3600 * 6 - mt_rand(1, 1800);
            }

            $interval_time = beginOfDay();

            // 最近1小时只爆一个礼物
            $gift_hour_history = self::findFirst([
                'conditions' => 'type=:type: and created_at>=:start_at:',
                'bind' => ['type' => 'gift', 'start_at' => $interval_time],
                'order' => 'id desc']);
            if ($gift_hour_history) {
                info('continue gift_hour限制', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'gift_id', $gift_id, 'user_rate', $user_rate_multi);
                return 0;
            }

            $gift_history = self::findFirst([
                'conditions' => 'user_id = :user_id: and type=:type: and gift_id=:gift_id: and created_at>=:start_at:',
                'bind' => ['user_id' => $user->id, 'type' => 'gift', 'gift_id' => $gift_id, 'start_at' => time() - 15 * 3600 * 24],
                'order' => 'id desc']);
            if ($gift_history) {
                info('continue 已中gift', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'pool_rate', $pool_rate, 'gift_id', $gift_id, 'user_rate', $user_rate_multi);
                return 0;
            }

            $total_pay_amount_rate = mt_rand(5, 15);

        } else {
            $total_pay_amount_rate = mt_rand(5, 15);
        }

        return $total_pay_amount_rate;
    }

    static function isBlockUser($user)
    {

        if (self::isWhiteList($user)) {
            return false;
        }

        $user_db = Users::getUserDb();
        $score = $user_db->zscore('draw_histories_block_user_ids', $user->id);

        return $score > 0;
    }

    static function checkUser($user)
    {

        $device_users = Users::find(['conditions' => 'device_id = :device_id: and id!=:user_id:',
            'bind' => ['device_id' => $user->device_id, 'user_id' => $user->id]]);
        foreach ($device_users as $device_user) {

            $last_history = self::findFirst([
                'conditions' => 'user_id = :user_id:',
                'bind' => ['user_id' => $device_user->id],
                'order' => 'id desc']);

            if ($last_history) {

                info($user->id, '设备已存在用户', $device_user->id);
                $user_db = Users::getUserDb();
                $user_db->zadd('draw_histories_block_user_ids', time(), $user->id);
                break;
            }
        }

        $user_hit_10w_histories = self::find([
            'conditions' => 'type=:type: and number=:number:',
            'bind' => ['type' => 'diamond', 'number' => 100000],
            'columns' => 'id,user_id,created_at'
        ]);

        foreach ($user_hit_10w_histories as $history) {
            $hit_user = Users::findFirstById($history->user_id);
            if ($hit_user && ($hit_user->device_id == $user->device_id || $hit_user->ip == $user->ip)) {

                info($user->id, '已存在用户中10万', $device_user->id);
                $user_db = Users::getUserDb();
                $user_db->zadd('draw_histories_block_user_ids', time(), $user->id);
                break;
            }
        }

    }

    // 计算奖品
    static function calculatePrize($user, $hit_diamond = false)
    {

        $data = self::getData();
        // 必中钻石
        if ($hit_diamond) {
            
            $rate = mt_rand(1, 100);
            if ($rate < 5) {
                return $data[7];
            }
            if ($rate < 40) {
                return $data[8];
            }

            return $data[9];
        }

        $user_db = Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_diamond';
        $total_incr_diamond = $user_db->get($cache_key);
        // 系统支出
        $cache_decr_key = 'draw_history_total_amount_decr_diamond';
        $total_decr_diamond = $user_db->get($cache_decr_key);

        $cache_gift_decr_key = 'draw_history_total_amount_decr_gift';
        $total_gift_decr_diamond = $user_db->get($cache_gift_decr_key);
        //$total_decr_diamond += $total_gift_decr_diamond;


        //用户消耗钻石
        $last_history = self::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user->id],
            'order' => 'id desc']);

        if (!$last_history) {
            self::checkUser($user);
        }

        $is_block_user = self::isBlockUser($user);
        list($user_rate_multi, $total_pay_amount) = self::calUserRateMulti($user, $last_history);

        if ($is_block_user && $user_rate_multi >= 5) {
            $user_rate_multi = mt_rand(1, 5);
        }

        $random = mt_rand(1, 1000);

        foreach ($data as $datum) {

            $type = fetch($datum, 'type');
            $number = fetch($datum, 'number');

            if (fetch($datum, 'rate') * 10 * $user_rate_multi >= $random) {

                info('rate', $user->id, fetch($datum, 'name'), fetch($datum, 'rate') * 10 * $user_rate_multi, 'random', $random);

                if (self::isDayLimit($datum)) {
                    info('continue 每天个数限制', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'user_rate', $user_rate_multi);
                    continue;
                }

                // 此礼物不增加倍率
                if (fetch($datum, 'gift_id') == 73 && fetch($datum, 'rate') * 10 < $random && mt_rand(1, 100) < 70) {
                    info('continue 此礼物不增加倍率', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'user_rate', $user_rate_multi);
                    continue;
                }

                $user_total_get_amount = 0;
                if ($last_history) {
                    $user_total_get_amount = $last_history->total_diamond + $last_history->total_gift_diamond;
                }

                $opts = ['user_rate_multi' => $user_rate_multi, 'total_pay_amount' => $total_pay_amount, 'user_total_get_amount' => $user_total_get_amount,
                    'total_incr_diamond' => $total_incr_diamond, 'total_decr_diamond' => $total_decr_diamond, 'is_block_user' => $is_block_user
                ];

                $total_pay_amount_rate = self::calPayAmountRate($user, $datum, $opts);
                if (!$total_pay_amount_rate) {
                    info('continue total_pay_amount_rate限制', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'user_rate', $user_rate_multi);
                    continue;
                }

                if ($total_pay_amount && ($number > $total_pay_amount * $total_pay_amount_rate)) {
                    info('continue 累计钻石限制', $user->id, '支付', $total_pay_amount, $number, fetch($datum, 'name'), 'user_rate', $user_rate_multi);
                    continue;
                }

                $user_db = Users::getUserDb();
                $cache_key = 'draw_history_hit_num_' . date('Ymd') . '_' . fetch($datum, 'id', 0);
                $user_db->incr($cache_key);
                $cache_total_key = 'draw_history_hit_num_' . fetch($datum, 'id', 0);
                $user_db->incr($cache_total_key);

                return $datum;
            }
        }


        if (mt_rand(1, 100) > 80) {
            return $data[9];
        }

        return $data[11];
    }

    static function createHistory($user, $opts = [])
    {

        $hit_diamond = fetch($opts, 'hit_diamond', false);

        $result = self::calculatePrize($user, $hit_diamond);
        if (!$result) {
            return null;
        }

        $gift = null;
        $draw_history = new DrawHistories();
        $draw_history->user_id = $user->id;
        $draw_history->product_channel_id = $user->product_channel_id;
        $draw_history->type = fetch($result, 'type');
        $draw_history->number = fetch($result, 'number');
        $draw_history->pay_type = fetch($opts, 'pay_type');
        $draw_history->pay_amount = fetch($opts, 'pay_amount');

        $draw_history->gift_id = fetch($result, 'gift_id', 0);
        $draw_history->gift_num = fetch($result, 'gift_num', 0);
        if ($draw_history->gift_id) {
            $gift_id = fetch($result, 'gift_id', 0);
            $gift = Gifts::findFirstById($gift_id);
            $draw_history->gift_type = $gift->type;
        }

        if (!$draw_history->save()) {
            return null;
        }

        if ($draw_history->type == 'diamond') {
            $remark = '抽奖获得' . $draw_history->number . '钻石';
            $opts['remark'] = $remark;
            $target = \AccountHistories::changeBalance($user, ACCOUNT_TYPE_DRAW_INCOME, $draw_history->number, $opts);
        } elseif ($draw_history->type == 'gift') {
            if ($gift) {// 送礼物
                // 赠送权时间
                $giving_time = fetch($result, 'giving_time', 0);
                if ($giving_time) {

                } else {
                    GiftOrders::asyncCreateGiftOrder(SYSTEM_ID, [$user->id], $gift->id, ['remark' => '砸蛋赠送', 'type' => GIFT_ORDER_TYPE_ACTIVITY_LUCKY_DRAW]);
                }
            }
        } else {
            $opts = ['remark' => '抽奖获得' . $draw_history->number . '金币'];
            $target = \GoldHistories::changeBalance($user, GOLD_TYPE_DRAW_INCOME, $draw_history->number, $opts);
        }


        $hot_cache = DrawHistories::getHotWriteCache();
        if ($draw_history->number >= 1000 && $draw_history->number < 10000) {
            $hot_cache->zadd('draw_histories_1000', time(), $draw_history->id);
        }
        if ($draw_history->number >= 10000 && $draw_history->number < 100000) {
            $hot_cache->zadd('draw_histories_10000', time(), $draw_history->id);
        }
        if ($draw_history->number >= 100000) {
            $hot_cache->zadd('draw_histories_100000', time(), $draw_history->id);
        }

        return $draw_history;
    }

    function toSimpleJson()
    {
        $opts = [
            'created_at_text' => $this->created_at_text,
            'total_pay_amount' => $this->total_pay_amount,
            'pay_amount' => $this->pay_amount,
            'pay_type' => $this->pay_type,
            'total_gold' => $this->total_gold,
            'total_diamond' => $this->total_diamond,
            'total_gift_num' => $this->total_gift_num,
            'number' => $this->number,
            'type' => $this->type,
            'type_text' => $this->type_text,
            'user_nickname' => $this->user_nickname,
        ];

        if ($this->gift) {
            $opts['gift_image_small_url'] = $this->gift->image_small_url;
            $opts['gift_name'] = $this->gift->name;
        }

        return $opts;
    }


    //获取屏蔽用户分页列表
    static function findBlockUsersList($page, $per_page)
    {
        $user_db = Users::getUserDb();
        $relations_key = 'draw_histories_block_user_ids';
        $offset = $per_page * ($page - 1);
        $res = $user_db->zrevrange($relations_key, $offset, $offset + $per_page - 1);
        return $res;
    }

    //删除屏蔽用户
    static function deleteBlockUser($user_id)
    {
        if ($user_id) {
            $user_db = Users::getUserDb();
            $user_db->zrem("draw_histories_block_user_ids", $user_id);
        }
    }

}