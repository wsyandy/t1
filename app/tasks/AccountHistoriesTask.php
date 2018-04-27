<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 15:17
 */

class AccountHistoriesTask extends \Phalcon\Cli\Task
{
    function testChangeBalanceAction()
    {
        $user = \Users::findLast();
        $amount = 100;
        $opts = array('remark' => '系统赠送100钻石');
        $result = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_GIVE, $amount, $opts);
        var_dump($result);
    }

    function testBuyGiftAction()
    {
        $user = \Users::findById(2);
        //$gift = \Gifts::findLast();
        //$gift_num = 1000;
        //$amount = $gift->amount * $gift_num;
        $result = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_BUY_GIFT, 1);
        var_dump($result);
    }

    function testDiamondAction()
    {
        $user = \Users::findById(2);
        echo $user->diamond . PHP_EOL;

        $user->diamond = 0;
        $user->update();
    }

    function giveAction($params)
    {
        $amount = $params[0];
        if ($amount > 100) {
            echoLine('error', $params);
            return;
        }

        unset($params[0]);
        $user_ids = $params;

        foreach ($user_ids as $user_id) {
            $opts = ['remark' => '系统赠送' . $amount . '钻石', 'operator_id' => 1];
            echoLine($user_id, $opts);
            \AccountHistories::changeBalance($user_id, ACCOUNT_TYPE_GIVE, $amount, $opts);
        }

    }

    function fixGiftOrderAction()
    {
        $sender_id = 1258752;
        $receiver_ids = [1206845];

        $gift_id = 28;
        $opts = '{"gift_num":1,"sender_current_room_id":1002353,"receiver_current_room_id":1002353,"target_id":671893,"time":1524832326,"async_verify_data":1}';
        $opts = json_decode($opts, true);
        $target_id = fetch($opts, 'target_id');
        $gift = Gifts::findFirstById($gift_id);

        unset($opts['async_verify_data']);

        $cond = [
            'conditions' => 'target_id = :target_id: and pay_type = :pay_type:',
            'bind' => ['target_id' => $target_id, 'pay_type' => $gift->pay_type],
            'order' => 'id desc'
        ];

        if ($gift->isDiamondPayType()) {
            $target = AccountHistories::findFirstById($target_id);
        } else {
            $target = GoldHistories::findFirstById($target_id);
        }

        echoLine($target->id, $target->user_id);

        $gift_order = GiftOrders::findFirst($cond);

        if ($gift_order) {
            echoLine($opts);
            return;
        }

        //GiftOrders::asyncCreateGiftOrder($sender_id, $receiver_ids, $gift_id, $opts);
    }
}