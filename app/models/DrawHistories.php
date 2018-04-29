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

    static $TYPE = ['gold' => '金币', 'diamond' => '钻石', 'gift' => '礼物'];

    static $PAY_TYPE = ['gold' => '金币', 'diamond' => '钻石'];

    function beforeCreate()
    {
        return $this->checkBalance();
    }

    function afterCreate()
    {

        if ('diamond' == $this->type && $this->number >= 10000) {
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

    static function getData()
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

    // 计算奖品
    static function calculatePrize($user, $hit_diamond = false)
    {

        // 必中钻石
        if ($hit_diamond) {
            return ['type' => 'diamond', 'name' => '钻石', 'number' => 10, 'rate' => 26.6];
        }

        $user_db = Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_diamond';
        $incr_num = $user_db->get($cache_key);
        // 系统支出
        $cache_decr_key = 'draw_history_total_amount_decr_diamond';
        $decr_num = $user_db->get($cache_decr_key);


        $pool_rate = mt_rand(70, 80) / 100;
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
            // 第一次抽奖10倍概率，开始抽奖的前5次，如果不中奖，每次增加10倍概率；
            if ($total_pay_amount < 50 && $incr_history->total_number < 10) {
                $user_rate_multi = $total_pay_amount;
                $pool_rate = 0.90;
                if ($incr_num * $pool_rate > $decr_num) {
                    $can_hit_diamond = true;
                }

            }
        } else {
            // 第一次抽奖10倍概率，开始抽奖的前5次，如果不中奖，每次增加10倍概率；
            $user_rate_multi = 10;
            $pool_rate = 0.90;
            if ($incr_num * $pool_rate > $decr_num) {
                $can_hit_diamond = true;
            }
        }

        // 老用户
        if ($can_hit_diamond && $user_rate_multi <= 1) {

            //用户获得钻石
            $decr_history = self::findFirst([
                'conditions' => 'user_id = :user_id: and type=:type:',
                'bind' => ['user_id' => $user->id, 'type' => 'diamond'],
                'order' => 'id desc']);

            $total_number = 0;
            if ($decr_history) {
                $total_number = intval($decr_history->total_number);
            }

            if ($total_pay_amount > $total_number) {
                $decr_rate = ($total_pay_amount - $total_number) / $total_pay_amount;
                if ($decr_rate * 100 > mt_rand(15, 30) && mt_rand(1, 100) < 75) {
                    $user_rate_multi = ceil(($total_pay_amount - $total_number) / mt_rand(50, 300));
                }

                info($user->id, '用户消耗', $total_pay_amount, '用户获得', $total_number, '倍率', $user_rate_multi, 'rate', $decr_rate);
            }
        }


        info('cal', $user->id, '系统收入', $incr_num, '系统支出', $decr_num, 'user_rate_multi', $user_rate_multi);

        $random = mt_rand(1, 1000);
        $data = self::getData();
        foreach ($data as $datum) {
            if ($can_hit_diamond) {
                if (fetch($datum, 'rate') * 10 * $user_rate_multi > $random) {

                    if (fetch($datum, 'type') == 'diamond' && (fetch($datum, 'number') > $total_pay_amount * 3
                            || fetch($datum, 'number') <= 10000 && fetch($datum, 'number') > $total_pay_amount * 5
                            || $decr_num + fetch($datum, 'number') > $incr_num * $pool_rate)
                    ) {
                        info('continue', $user->id, fetch($datum, 'number'), $total_pay_amount, '支出', $decr_num + fetch($datum, 'number'), $incr_num);
                        // 大于支出的2倍
                        continue;
                    }

                    return $datum;
                }
            } else {
                // 只能命中金币
                if (fetch($datum, 'rate') * 10 > $random && fetch($datum, 'type') == 'gold') {
                    return $datum;
                }
            }
        }

        return ['type' => 'gold', 'name' => '金币', 'number' => 50, 'rate' => 100];
    }

    static function createHistory($user, $opts = [])
    {

        $hit_diamond = fetch($opts, 'hit_diamond', false);

        $result = self::calculatePrize($user, $hit_diamond);

        info($user->id, $result);

        $draw_history = new DrawHistories();
        $draw_history->user_id = $user->id;
        $draw_history->product_channel_id = $user->product_channel_id;
        $draw_history->type = fetch($result, 'type');
        $draw_history->number = fetch($result, 'number');
        $draw_history->pay_type = fetch($opts, 'pay_type');
        $draw_history->pay_amount = fetch($opts, 'pay_amount');
        $draw_history->save();

        if ($draw_history->type == 'diamond') {
            $remark = '抽奖获得' . $draw_history->number . '钻石';
            $opts['remark'] = $remark;
            $target = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DRAW_INCOME, $draw_history->number, $opts);
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
            'user_nickname' => $this->user_nickname
        ];
    }
}