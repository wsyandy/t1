<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/9
 * Time: 下午8:16
 */
class GiftStats extends BaseModel
{
    /**
     * @type Gifts
     */
    private $_gift;

    static $STAT_FIELDS = [
        'gift_num' => '礼物赠送总次数',
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


    function giftNum($gift_cond)
    {
        $this->data_hash['gift_num'] = GiftOrders::count($gift_cond);
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

    static function statFields($operator)
    {
        return self::$STAT_FIELDS;
    }

}