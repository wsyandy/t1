<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/9
 * Time: 下午8:16
 */
class GiftStats extends BaseModel
{
    static $STAT_FIELDS = [
        'gift_times' => '礼物赠送总次数',
        'gift_user' => '礼物赠送总人数',
        'gift_total' => '礼物赠送总个数'
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


    function giftTimes($gift_cond)
    {
        $this->data_hash['gift_times'] = GiftOrders::count($gift_cond);
    }

    function giftUser($gift_cond)
    {
        $gift_cond['column'] = 'gift_num';

        $this->data_hash['gift_total'] = GiftOrders::sum($gift_cond);
    }

    function giftTotal($gift_cond)
    {
        $gift_cond['column'] = 'distinct user_id';

        $this->data_hash['gift_user'] = GiftOrders::count($gift_cond);
    }

    //'operat_manager' => '推广运营经理', 'operator' => '推广运营专员'
    static function statFields($operator)
    {
//        $fields = ['device_active_num', 'total_active_num', 'register_num', 'register_rate', 'new_payment_success_total',
//            'new_paid_arpu', 'new_arpu'];
//
//        if (in_array($operator->role, ['operat_manager', 'operator'])) {
//
//            $res = [];
//
//            foreach (self::$STAT_FIELDS as $filed => $value) {
//                if (in_array($filed, $fields)) {
//                    $res[$filed] = $value;
//                }
//            }
//
//            return $res;
//        }
        return self::$STAT_FIELDS;
    }

}