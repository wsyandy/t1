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
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 1000, 'rate' => 1];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 500, 'rate' => 2];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 100, 'rate' => 5];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 30, 'rate' => 15];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 10, 'rate' => 15];
        $data[] = ['type' => 'gold', 'name' => '金币', 'number' => 1000, 'rate' => 30];
        $data[] = ['type' => 'gold', 'name' => '金币', 'number' => 100, 'rate' => 43.4];

        return $data;
    }

    // 计算奖品
    static function calculatePrize($user)
    {
        $user_db = Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_total_amount_incr_diamond';
        $incr_num = $user_db->get($cache_key);
        // 系统支出
        $cache_decr_key = 'draw_total_amount_decr_diamond';
        $decr_num = $user_db->get($cache_decr_key);

        $hit_diamond = false;
        // 最多拿出30%
        if ($incr_num * 0.3 > $decr_num) {
            $hit_diamond = true;
        }

        $total_pay_amount = 0;
        // 倍率
        $user_rate_multi = 1;
        if ($hit_diamond) {

            //用户获得钻石
            $decr_history = self::findFirst([
                'conditions' => 'user_id = :user_id: and type=:type:',
                'bind' => ['user_id' => $user->id, 'type' => 'diamond'],
                'order' => 'id desc']);

            $total_number = 0;
            if ($decr_history) {
                $total_number = intval($decr_history->total_number);
            }

            //用户消耗钻石
            $incr_history = self::findFirst([
                'conditions' => 'user_id = :user_id: and pay_type=:pay_type:',
                'bind' => ['user_id' => $user->id, 'pay_type' => 'diamond'],
                'order' => 'id desc']);

            if ($incr_history) {
                $total_pay_amount = intval($incr_history->total_pay_amount);
            }

            // 超过支出
            if ($total_pay_amount < $total_number) {
                $hit_diamond = false;
            }

            if ($total_pay_amount > 100 && $total_pay_amount > $total_number * mt_rand(2, 5) && mt_rand(1, 100) < 90) {
                $user_rate_multi = ceil(($total_pay_amount - $total_number) / 100);
            }

            info($user->id, '用户消耗', $total_pay_amount, '用户获得', $total_number, '倍率', $user_rate_multi);
        }


        info('cal', $user->id, '系统收入', $incr_num, '系统支出', $decr_num, 'user_rate_multi', $user_rate_multi);

        $random = mt_rand(1, 1000);
        $data = self::getData();
        foreach ($data as $datum) {
            if ($hit_diamond) {
                if (fetch($datum, 'rate') * 10 * $user_rate_multi > $random) {

                    if (fetch($datum, 'type') == 'diamond' && fetch($datum, 'number') > $total_pay_amount * 2) {
                        // 大于支出的2倍
                        continue;
                    }

                    return $datum;
                }
            } else {
                // 只能命中金币
                if (fetch($datum, 'rate') * 10 * $user_rate_multi > $random && fetch($datum, 'type') == 'gold') {
                    return $datum;
                }
            }
        }

        return ['type' => 'gold', 'name' => '金币', 'number' => 100, 'rate' => 43.4];
    }

    static function createHistory($user, $opts = [])
    {

        $result = self::calculatePrize($user);

        info($user->id, $result);

        $draw_history = new DrawHistories();
        $draw_history->user_id = $user->id;
        $draw_history->product_channel_id = $user->product_channel_id;
        $draw_history->type = fetch($result, 'type');
        $draw_history->number = fetch($result, 'number');
        $draw_history->pay_type = fetch($opts, 'pay_type');
        $draw_history->pay_amount = fetch($opts, 'pay_amount');
        $draw_history->save();

        if($draw_history->type == 'diamond'){
            $remark = '抽奖获得' . $draw_history->number . '钻石';
            $opts['remark'] = $remark;
            $target = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DRAW_INCOME, $draw_history->number, $opts);
        }else{
            $opts = ['remark' => '抽奖获得' . $draw_history->number.'金币'];
            $target = \GoldHistories::changeBalance($user->id, GOLD_TYPE_DRAW_INCOME, $draw_history->number, $opts);
        }

        $user_db = Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_total_amount_incr_' . $draw_history->pay_type;
        $incr_num = $user_db->incrby($cache_key, intval($draw_history->pay_amount));

        // 系统支出
        $cache_decr_key = 'draw_total_amount_decr_' . $draw_history->type;
        $decr_num = $user_db->incrby($cache_decr_key, intval($draw_history->number));

        return $draw_history;
    }

    function toSimpleJson()
    {
        return [
            'created_at_text' => $this->created_at_text,
            'pay_amount' => $this->pay_amount,
            'type' => $this->type,
            'type_text' => $this->type_text,
            'user_nickname' => $this->user_nickname
        ];
    }
}