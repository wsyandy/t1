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

        if ('diamond' == $this->type && $this->number >= 10000) {
            info('全服', $this->id, $this->user_id, $this->number);
            $content = '哇哦！' . $this->user->nickname . '刚刚砸出' . $this->number . '钻大奖！还不快来砸金蛋，试试手气~';
            Rooms::delay()->asyncAllNoticePush($content, ['type' => 'top_topic_message']);
        } else {

            if (isDevelopmentEnv()) {
                $content = '哇哦！' . $this->user->nickname . '刚刚砸出' . $this->number . '钻大奖！还不快来砸金蛋，试试手气~';
                Rooms::delay()->asyncAllNoticePush($content, ['type' => 'top_topic_message']);
            }
        }
    }

    function checkBalance()
    {
        $decr_history = self::findFirst([
            'conditions' => 'user_id = :user_id: and type=:type:',
            'bind' => ['user_id' => $this->user_id, 'type' => $this->type],
            'order' => 'id desc']);

        $old_total_number = 0;
        if ($decr_history) {
            $old_total_number = intval($decr_history->total_number);
        }
        $this->total_number = $old_total_number + $this->number;

        $incr_history = self::findFirst([
            'conditions' => 'user_id = :user_id: and pay_type=:pay_type:',
            'bind' => ['user_id' => $this->user_id, 'pay_type' => $this->pay_type],
            'order' => 'id desc']);

        $old_total_pay_amount = 0;
        if ($incr_history) {
            $old_total_pay_amount = intval($incr_history->total_pay_amount);
        }
        $this->total_pay_amount = $old_total_pay_amount + $this->pay_amount;

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
        $data[1] = ['id' => 2, 'type' => 'gift', 'name' => '梦境奇迹', 'number' => 68888, 'gift_id' => 1, 'rate' => 0.3, 'day_limit_num' => 1];
        $data[2] = ['id' => 3, 'type' => 'gift', 'name' => 'UFO座驾', 'number' => 18888, 'gift_id' => 1, 'rate' => 0.6, 'day_limit_num' => 2];
        $data[3] = ['id' => 4, 'type' => 'diamond', 'name' => '钻石', 'number' => 10000, 'rate' => 1.1, 'day_limit_num' => 0];
        $data[4] = ['id' => 5, 'type' => 'gift', 'name' => '光电游侠座驾', 'number' => 8888, 'gift_id' => 1, 'rate' => 1.7, 'day_limit_num' => 3];
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

    // 计算奖品
    static function calculatePrize($user, $hit_diamond = false)
    {

        $data = self::getData();
        // 必中钻石
        if ($hit_diamond) {
            return $data[9];
        }

        $user_db = Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_diamond';
        $incr_num = $user_db->get($cache_key);
        // 系统支出
        $cache_decr_key = 'draw_history_total_amount_decr_diamond';
        $decr_num = $user_db->get($cache_decr_key);

        $pool_rate = mt_rand(50, 80) / 100;
        $can_hit_diamond = false;
        if ($incr_num * $pool_rate > $decr_num) {
            $can_hit_diamond = true;
        }

        if (isDevelopmentEnv()) {
            $can_hit_diamond = true;
            $pool_rate = 1;
        }

        //用户消耗钻石
        $incr_history = self::findFirst([
            'conditions' => 'user_id = :user_id: and pay_type=:pay_type:',
            'bind' => ['user_id' => $user->id, 'pay_type' => 'diamond'],
            'order' => 'id desc']);

        // 倍率
        $user_rate_multi = 1;
        $total_pay_amount = 0;
        if ($incr_history) {
            $total_pay_amount = intval($incr_history->total_pay_amount);
            // 第一次抽奖5倍概率，开始抽奖的前5次，如果不中奖，每次增加10倍概率；
            if ($total_pay_amount < 50 && $incr_history->total_number < 10) {
                $user_rate_multi = $total_pay_amount;
                $pool_rate = 0.9;
                if ($incr_num * $pool_rate > $decr_num) {
                    $can_hit_diamond = true;
                }

            }
        } else {
            // 第一次抽奖5倍概率，开始抽奖的前5次，如果不中奖，每次增加10倍概率；
            $user_rate_multi = 5;
            $pool_rate = 0.9;
            if ($incr_num * $pool_rate > $decr_num) {
                $can_hit_diamond = true;
            }
        }

        // 老用户
        if ($can_hit_diamond && $user_rate_multi <= 1) {

            //用户获得钻石
            $decr_history = self::findFirst([
                'conditions' => 'user_id = :user_id: and (type=:type: or type=:type_gift:)',
                'bind' => ['user_id' => $user->id, 'type' => 'diamond', 'type_gift' => 'gift'],
                'order' => 'id desc']);

            $total_number = 0;
            if ($decr_history) {
                $total_number = intval($decr_history->total_number);
            }

            if ($total_pay_amount > $total_number) {
                $decr_rate = ($total_pay_amount - $total_number) / $total_pay_amount;
                if ($decr_rate * 100 > mt_rand(20, 40) && mt_rand(1, 100) < 75) {
                    $user_rate_multi = ceil(($total_pay_amount - $total_number) / mt_rand(150, 400));
                }

                info($user->id, '用户消耗', $total_pay_amount, '用户获得', $total_number, '倍率', $user_rate_multi, 'rate', $decr_rate);
            }
        }


        info('cal', $user->id, '系统收入', $incr_num, '系统支出', $decr_num, 'user_rate_multi', $user_rate_multi);

        $random = mt_rand(1, 1000);

        foreach ($data as $datum) {

            $type = fetch($datum, 'type');
            $number = fetch($datum, 'number');
            if (fetch($datum, 'rate') * 10 * $user_rate_multi > $random) {

                if ($type == 'diamond') {

                    // 10w钻过滤，后台控制是否爆
                    if ($number == 100000) {
                        continue;
                    }

                    // 第一次抽奖限制100
                    if ($type == 'diamond' && $total_pay_amount < 1 && $number > 100) {
                        info('continue1', $user->id, $number, $total_pay_amount, '支出', $decr_num + $number, $incr_num);
                        continue;
                    }

                    // 奖金池控制
                    if ($decr_num + $number > $incr_num * ($pool_rate + 0.01)) {
                        info('continue2', $user->id, $number, $total_pay_amount, '支出', $decr_num + $number, $incr_num);
                        continue;
                    }

                    // 15分钟 倍率小于15倍，只能中一次1万钻
                    if ($user_rate_multi <= 15 && $number >= 10000) {
                        $hit_1w_history = self::findFirst([
                            'conditions' => 'user_id = :user_id: and type=:type: and number>=:number: and created_at>=:start_at:',
                            'bind' => ['user_id' => $user->id, 'type' => 'diamond', 'number' => 10000, 'start_at' => time() - 900],
                            'order' => 'id desc']);
                        if ($hit_1w_history) {
                            info('continue hit1w', $user->id, $number, $total_pay_amount, '支出', $decr_num + $number, $incr_num);
                            continue;
                        }
                    }

                    $total_pay_amount_rate = mt_rand(3, 6);
                } else {
                    $total_pay_amount_rate = mt_rand(5, 15);
                    // 礼物不能重复中
                    if ($type == 'gift') {
                        $gift_id = fetch($datum, 'gift_id');
                        $gift_history = self::findFirst([
                            'conditions' => 'user_id = :user_id: and type=:type: and gift_id=:gift_id:',
                            'bind' => ['user_id' => $user->id, 'type' => 'gift', 'gift_id' => $gift_id],
                            'order' => 'id desc']);
                        if ($gift_history) {
                            info('continue gift', $user->id, $number, $total_pay_amount, '支出', $decr_num + $number, $incr_num, 'gift', $gift_id);
                            continue;
                        }

                        // 最近1小时只爆一个礼物
                        $gift_hour_history = self::findFirst([
                            'conditions' => 'type=:type:  and created_at>=:start_at:',
                            'bind' => ['type' => 'gift', 'start_at' => time() - 3600],
                            'order' => 'id desc']);
                        if ($gift_hour_history) {
                            info('continue gift_hour', $user->id, $number, $total_pay_amount, '支出', $decr_num + $number, $incr_num, 'gift', $gift_id);
                        }
                    }
                }

                if ($total_pay_amount && ($number > $total_pay_amount * $total_pay_amount_rate)) {
                    info('continue3', $user->id, $number, $total_pay_amount, '支出', $decr_num + $number, $incr_num);
                    continue;
                }

                if (self::isDayLimit($datum)) {
                    info('continue4', $user->id, $number, $total_pay_amount, '支出', $decr_num + $number, $incr_num);
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

        return end($data);
    }

    static function createHistory($user, $opts = [])
    {

        $hit_diamond = fetch($opts, 'hit_diamond', false);

        $result = self::calculatePrize($user, $hit_diamond);

        $draw_history = new DrawHistories();
        $draw_history->user_id = $user->id;
        $draw_history->product_channel_id = $user->product_channel_id;
        $draw_history->type = fetch($result, 'type');
        $draw_history->number = fetch($result, 'number');
        $draw_history->pay_type = fetch($opts, 'pay_type');
        $draw_history->pay_amount = fetch($opts, 'pay_amount');
        if (!$draw_history->save()) {
            return null;
        }

        if ($draw_history->type == 'diamond') {
            $remark = '抽奖获得' . $draw_history->number . '钻石';
            $opts['remark'] = $remark;
            $target = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DRAW_INCOME, $draw_history->number, $opts);
        } elseif ($draw_history->type == 'gift') {
            // 送礼物

        } else {
            $opts = ['remark' => '抽奖获得' . $draw_history->number . '金币'];
            $target = \GoldHistories::changeBalance($user->id, GOLD_TYPE_DRAW_INCOME, $draw_history->number, $opts);
        }

        $user_db = Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_' . $draw_history->pay_type;
        $incr_num = $user_db->incrby($cache_key, intval($draw_history->pay_amount));

        // 系统支出
        $cache_decr_key = 'draw_history_total_amount_decr_' . $draw_history->type;
        $decr_num = $user_db->incrby($cache_decr_key, intval($draw_history->number));

        if ($draw_history->type == 'diamond' && $draw_history->number == 100000) {
            $cache_hit_10w_key = 'draw_history_hit_all_notice';
            $hot_cache = Users::getHotWriteCache();
            $hot_cache->setex($cache_hit_10w_key, 3600 * 25, $draw_history->id);
            info($cache_hit_10w_key, $draw_history->id);
        }

        return $draw_history;
    }

    function toSimpleJson()
    {
        return [
            'created_at_text' => $this->created_at_text,
            'total_pay_amount' => $this->total_pay_amount,
            'pay_amount' => $this->pay_amount,
            'pay_type' => $this->pay_type,
            'total_number' => $this->total_number,
            'number' => $this->number,
            'type' => $this->type,
            'type_text' => $this->type_text,
            'user_nickname' => $this->user_nickname,
            'gift_image_small_url' => $this->gift->image_small_url,
            'gift_name' => $this->gift->name,
        ];
    }
}