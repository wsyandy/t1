<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/13
 * Time: 下午2:49
 */
class MenTask extends \Phalcon\Cli\Task
{

    function testAction()
    {
//        $user = Users::findFirstById(31421);
//        $user->union_id = 0;
//        $user->union_type = 0;
//        $user->sid = $user->generateSid('s');
//        $user->mobile = 13212345671;
//        $user->user_status = USER_STATUS_ON;
//        $user->save();
//        echoLine($user->id);
//

//        $union = Unions::findFirstById(3);
//        $union_host = Users::findFirstById(31428);
//        $union->refuseJoinUnion($union_host, $user);

//        $db = Users::getUserDb();
//        $db->zrem($union->generateRefusedUsersKey(), $user->id);
//        $db->zrem($union->generateUsersKey(), $user->id);
//        $db->zrem($union->generateNewApplyNumKey(), $user->id);


//        $db->zrem($union->generateCheckUsersKey(), $user->id);
//        $db->zrem($union->generateAllApplyExitUsersKey(), $user->id);
//        $db->zrem($union->generateApplyExitUsersKey(), $user->id);

//        $db->zrem($union->generateCheckUsersKey(), $user->id);
//        $db->zrem($union->generateUsersKey(), $user->id);
//        foreach ([31422, 31423, 31426, 31427, 31428] as $a) {
//            $db->zadd($union->generateUsersKey(), time(), $a);
//        }

//        $keywords = '13a2';
//        $rs = preg_match('/^[0-9]*$/', $keywords);
//        echoLine($rs);
//        $fee_type = I_GOLD_HISTORY_FEE_TYPE_BUY_GOLD;
//        if (!in_array($fee_type, array_keys(IGoldHistories::$FEE_TYPE))) {
//            echoLine('fee_type is false', $fee_type);
//        }

        $click_id = "54321";
        $convid = "n855_1_104654";
        $token = "aa6f20cecdf0cf948ac01b52febf7469";

        $params = $convid . $click_id;
        $str = hash_hmac("sha1", $params, $token, true);
        $sign = urlencode(base64_encode($str));
        echoLine($sign);
    }

    function insertUserAction()
    {

        $users = Users::findByIds([31429, 31399, 31346, 31310]);
        foreach ($users as $user) {
//            $user->product_channel_id = 2;
//            $user->sid = $user->generateSid('s');
            $user->user_status = USER_STATUS_ON;
            $user->save();
        }

//        $device = Devices::findFirstById(211);
//        $device->product_channel_id = 2;
//        $device->save();


    }


    function fixUserLevelAction()
    {
        $gift_orders = GiftOrders::find([
            'conditions' => 'product_channel_id = :product_channel_id:',
            'bind' => ['product_channel_id' => 3]
        ]);

        foreach ($gift_orders as $gift_order) {
            echoLine($gift_order->id, $gift_order->user_id, $gift_order->sender_id);
            Users::updateExperienceByInternational($gift_order->id);
        }
    }

    function fixGiftAction()
    {
        $gifts = Gifts::find([
            'conditions' => 'pay_type = :pay_type:',
            'bind' => ['pay_type' => GIFT_PAY_TYPE_I_GOLD]
        ]);

        foreach ($gifts as $gift) {
            $gift->abroad = 1;
            $gift->save();
            echoLine('id', $gift->id);
        }
    }


}
