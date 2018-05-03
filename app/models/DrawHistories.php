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

        if ($this->number >= 10000 || 'gift' == $this->type || isDevelopmentEnv()) {

            $content = '';
            if ('diamond' == $this->type) {
                $content = '哇哦！' . $this->user->nickname . '刚刚砸出' . $this->number . '钻大奖！还不快来砸金蛋，试试手气~';
            }

            if ('gift' == $this->type) {
                $content = '哇哦！' . $this->user->nickname . '刚刚砸出' . $this->gift->name . '大奖！还不快来砸金蛋，试试手气~';
            }

            if ($content) {
                info('全服', $this->id, $this->type, $this->user_id, $this->number);
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

    static function getData2()
    {
        $data = [];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 100000, 'rate' => 0.1];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 10000, 'rate' => 0.5];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 1000, 'rate' => 1.6];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 500, 'rate' => 3.6];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 100, 'rate' => 6.6];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 30, 'rate' => 15.6];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 10, 'rate' => 26.6];
        $data[] = ['type' => 'gold', 'name' => '金币', 'number' => 200, 'rate' => 56.6];
        $data[] = ['type' => 'gold', 'name' => '金币', 'number' => 50, 'rate' => 100];

        return $data;
    }

    static function getData()
    {
        $data = [];
        $data[0] = ['id' => 1, 'type' => 'diamond', 'name' => '钻石', 'number' => 100000, 'rate' => 0.1, 'day_limit_num' => 1];
        if (isProduction()) {
            $data[1] = ['id' => 2, 'type' => 'gift', 'name' => '梦境奇迹座驾', 'number' => 35000, 'rate' => 0.3, 'gift_id' => 73, 'gift_num' => 1, 'day_limit_num' => 1];
        } else {
            $data[1] = ['id' => 2, 'type' => 'gift', 'name' => '梦境奇迹座驾', 'number' => 35000, 'rate' => 0.3, 'gift_id' => 142, 'gift_num' => 1, 'day_limit_num' => 1];
        }
        if (isProduction()) {
            $data[2] = ['id' => 3, 'type' => 'gift', 'name' => 'UFO座驾', 'number' => 12000, 'rate' => 0.6, 'gift_id' => 33, 'gift_num' => 1, 'day_limit_num' => 2];
        } else {
            $data[2] = ['id' => 3, 'type' => 'gift', 'name' => 'UFO座驾', 'number' => 12000, 'rate' => 0.6, 'gift_id' => 61, 'gift_num' => 1, 'day_limit_num' => 2];
        }
        $data[3] = ['id' => 4, 'type' => 'diamond', 'name' => '钻石', 'number' => 10000, 'rate' => 1.1, 'day_limit_num' => 0];
        if (isProduction()) {
            $data[4] = ['id' => 5, 'type' => 'gift', 'name' => '光电游侠座驾', 'number' => 5000, 'rate' => 1.7, 'gift_id' => 57, 'gift_num' => 1, 'day_limit_num' => 3];
        } else {
            $data[4] = ['id' => 5, 'type' => 'gift', 'name' => '光电游侠座驾', 'number' => 5000, 'rate' => 1.7, 'gift_id' => 63, 'gift_num' => 1, 'day_limit_num' => 3];
        }
        $data[5] = ['id' => 6, 'type' => 'diamond', 'name' => '钻石', 'number' => 1000, 'rate' => 2.7, 'day_limit_num' => 0];
        $data[6] = ['id' => 7, 'type' => 'diamond', 'name' => '钻石', 'number' => 500, 'rate' => 4.7, 'day_limit_num' => 0];
        $data[7] = ['id' => 8, 'type' => 'diamond', 'name' => '钻石', 'number' => 100, 'rate' => 7.7, 'day_limit_num' => 0];
        $data[8] = ['id' => 9, 'type' => 'diamond', 'name' => '钻石', 'number' => 30, 'rate' => 15.7, 'day_limit_num' => 0];
        $data[9] = ['id' => 10, 'type' => 'diamond', 'name' => '钻石', 'number' => 10, 'rate' => 27.7, 'day_limit_num' => 0];
        $data[10] = ['id' => 11, 'type' => 'gold', 'name' => '金币', 'number' => 100, 'rate' => 57.7, 'day_limit_num' => 0];
        $data[11] = ['id' => 12, 'type' => 'gold', 'name' => '金币', 'number' => 50, 'rate' => 100, 'day_limit_num' => 0];

        return $data;
    }

    static function isDayLimit($data)
    {
        $id = fetch($data, 'id');
        $day_limit_num = fetch($data, 'day_limit_num');
        $user_db = Users::getUserDb();
        $cache_key = 'draw_history_hit_num_' . date('Ymd') . '_' . $id;
        $num = $user_db->get($cache_key);
        if ($day_limit_num && $num >= $day_limit_num) {
            info('limit', $cache_key, $num, $day_limit_num);
            return true;
        }

        return false;
    }

    static function calUserRateMulti($user)
    {

        // 倍率
        $user_rate_multi = 1;
        $total_pay_amount = 0;
        $total_get_amount = 0;

        //用户消耗钻石
        $history = self::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user->id],
            'order' => 'id desc']);

        if ($history) {
            $total_pay_amount = intval($history->total_pay_amount);
            $total_get_amount = $history->total_diamond + $history->total_gift_diamond;

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
                if ($decr_rate * 100 > mt_rand(20, 45) && mt_rand(1, 100) < 75) {
                    $user_rate_multi = ceil(($total_pay_amount - $total_get_amount) / mt_rand(100, 400));
                    if ($user_rate_multi > 100) {
                        $user_rate_multi = 100;
                    }
                }
            }
        }

        info($user->id, '用户消耗', $total_pay_amount, '用户获得', $total_get_amount, '倍率', $user_rate_multi);

        return [$user_rate_multi, $total_pay_amount];
    }

    static function calPayAmountRate($user, $datum, $opts)
    {

        $pool_rate = mt_rand(55, 85) / 100;
        $user_rate_multi = fetch($opts, 'user_rate_multi');
        $total_pay_amount = fetch($opts, 'total_pay_amount');
        $total_incr_diamond = fetch($opts, 'total_incr_diamond');
        $total_decr_diamond = fetch($opts, 'total_decr_diamond');

        $type = fetch($datum, 'type');
        $number = fetch($datum, 'number');

        if ($type == 'diamond') {

            // 10w钻过滤，后台控制是否爆
            if ($number == 100000) {
                return 0;
            }

            // 第一次抽奖限制100
            if ($total_pay_amount < 1 && $number > 100) {
                info('continue1', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond);
                return 0;
            }

            // 奖金池控制
            if ($total_pay_amount > 50 && $total_decr_diamond + $number > $total_incr_diamond * ($pool_rate + 0.01)) {
                info('continue2', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond);
                return 0;
            }

            $hit_num = self::count([
                'conditions' => 'user_id = :user_id: and (type=:type: or type=:type1:) and created_at>=:start_at:',
                'bind' => ['user_id' => $user->id, 'type' => 'diamond', 'type1' => 'gift', 'start_at' => time()],
                'order' => 'id desc']);

            if ($hit_num >= 3) {
                info('continue hit_num', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond);
                return 0;
            }

            // 15分钟 倍率小于15倍，只能中一次1万钻
            // && $user_rate_multi < 30
            if ($number >= 10000) {
                $hit_1w_history = self::findFirst([
                    'conditions' => 'user_id = :user_id: and type=:type: and number>=:number: and created_at>=:start_at:',
                    'bind' => ['user_id' => $user->id, 'type' => 'diamond', 'number' => 10000, 'start_at' => time() - 600],
                    'order' => 'id desc']);
                if ($hit_1w_history) {
                    info('continue hit1w', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond);
                    return 0;
                }
            }

            $total_pay_amount_rate = mt_rand(3, 6);

        } elseif ($type == 'gift') {

            // 第一次抽奖限制100
            if ($total_pay_amount < 1 && $number > 500) {
                info('continue1', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond);
                return 0;
            }

            $gift_id = fetch($datum, 'gift_id');
            $gift_history = self::findFirst([
                'conditions' => 'user_id = :user_id: and type=:type: and gift_id=:gift_id:',
                'bind' => ['user_id' => $user->id, 'type' => 'gift', 'gift_id' => $gift_id],
                'order' => 'id desc']);
            if ($gift_history) {
                info('continue gift', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond, 'gift', $gift_id);
                return 0;
            }

            // 最近1小时只爆一个礼物
            $gift_hour_history = self::findFirst([
                'conditions' => 'type=:type:  and created_at>=:start_at:',
                'bind' => ['type' => 'gift', 'start_at' => time() - 3600],
                'order' => 'id desc']);
            if ($gift_hour_history) {
                info('continue gift_hour', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond, 'gift', $gift_id);
                return 0;
            }

            $total_pay_amount_rate = mt_rand(5, 12);

        } else {
            $total_pay_amount_rate = mt_rand(3, 15);
        }

        return $total_pay_amount_rate;
    }

    // 计算奖品
    static function calculatePrize($user, $hit_diamond = false)
    {

        $data = self::getData();
        // 必中钻石
        if ($hit_diamond) {
            if (mt_rand(1, 100) < 85) {
                return $data[9];
            }

            return $data[8];
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
        $total_decr_diamond += $total_gift_decr_diamond;


        // 计算用户倍率
        list($user_rate_multi, $total_pay_amount) = self::calUserRateMulti($user);

        info('cal', $user->id, '系统收入', $total_incr_diamond, '系统支出', $total_decr_diamond, 'user_rate_multi', $user_rate_multi);

        $random = mt_rand(1, 1000);

        foreach ($data as $datum) {

            $type = fetch($datum, 'type');
            $number = fetch($datum, 'number');
            if (fetch($datum, 'rate') * 10 * $user_rate_multi >= $random) {

                info('rate', $user->id, fetch($datum, 'rate') * 10 * $user_rate_multi, 'random', $random);

                $opts = ['user_rate_multi' => $user_rate_multi, 'total_pay_amount' => $total_pay_amount,
                    'total_incr_diamond' => $total_incr_diamond, 'total_decr_diamond' => $total_decr_diamond
                ];

                $total_pay_amount_rate = self::calPayAmountRate($user, $datum, $opts);
                if (!$total_pay_amount_rate) {
                    continue;
                }

                if ($total_pay_amount && ($number > $total_pay_amount * $total_pay_amount_rate)) {
                    info('continue3', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond);
                    continue;
                }

                if (self::isDayLimit($datum)) {
                    info('continue4', $user->id, $number, $total_pay_amount, '支出', $total_decr_diamond + $number, $total_incr_diamond);
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

        return null;
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
            $target = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DRAW_INCOME, $draw_history->number, $opts);
        } elseif ($draw_history->type == 'gift') {
            if ($gift) {// 送礼物
                GiftOrders::asyncCreateGiftOrder(SYSTEM_ID, [$user->id], $gift->id, ['remark' => '砸蛋赠送']);
            }
        } else {
            $opts = ['remark' => '抽奖获得' . $draw_history->number . '金币'];
            $target = \GoldHistories::changeBalance($user->id, GOLD_TYPE_DRAW_INCOME, $draw_history->number, $opts);
        }

        $user_db = Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_' . $draw_history->pay_type;
        $total_incr_diamond = $user_db->incrby($cache_key, intval($draw_history->pay_amount));

        // 系统支出: 金币，钻石，礼物
        $cache_decr_key = 'draw_history_total_amount_decr_' . $draw_history->type;
        $total_decr_diamond = $user_db->incrby($cache_decr_key, intval($draw_history->number));

        if ($draw_history->type == 'diamond' && $draw_history->number == 100000
            || $draw_history->type == 'gift' && $draw_history->gift_id == 73
        ) {

            $cache_hit_10w_key = 'draw_history_hit_all_notice';
            $hot_cache = Users::getHotWriteCache();
            $hot_cache->setex($cache_hit_10w_key, 3600 * 25, $draw_history->id);
            info($cache_hit_10w_key, $draw_history->id);
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

    function fixData()
    {
        $history = self::findFirst([
            'conditions' => 'user_id = :user_id: and id<:cur_id: and type=:type:',
            'bind' => ['user_id' => $this->user_id, 'cur_id' => $this->id, 'type' => $this->type],
            'order' => 'id desc']);

        $old_total_gold = 0;
        $old_total_diamond = 0;
        $old_total_gift_diamond = 0;
        $old_total_gift_num = 0;
        if ($history) {
            $old_total_gold = $history->total_gold;
            $old_total_diamond = $history->total_diamond;
            $old_total_gift_diamond = $history->total_gift_diamond;
            $old_total_gift_num = $history->total_gift_num;
        }

        if ($this->type == 'gold') {
            $this->total_gold = $old_total_gold + $this->number;
        }
        if ($this->type == 'diamond') {
            $this->total_diamond = $old_total_diamond + $this->number;
        }
        if ($this->type == 'gift') {
            $this->total_gift_diamond = $old_total_gift_diamond + $this->number;
            $this->total_gift_num = $old_total_gift_num + $this->gift_num;
        }

        $this->update();
    }

    function fixData2()
    {
        if ($this->type == 'gold') {
            $diamond_history = self::findFirst([
                'conditions' => 'user_id = :user_id: and id<:cur_id: and type=:type:',
                'bind' => ['user_id' => $this->user_id, 'cur_id' => $this->id, 'type' => 'diamond'],
                'order' => 'id desc']);
            if ($diamond_history) {
                $this->total_diamond = $diamond_history->total_diamond;
            }

        }
        if ($this->type == 'diamond') {
            $gold_history = self::findFirst([
                'conditions' => 'user_id = :user_id: and id<:cur_id: and type=:type:',
                'bind' => ['user_id' => $this->user_id, 'cur_id' => $this->id, 'type' => 'gold'],
                'order' => 'id desc']);
            if ($gold_history) {
                $this->total_gold = $gold_history->total_gold;
            }
        }

        $this->update();
    }
}