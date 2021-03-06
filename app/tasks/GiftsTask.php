<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 13:31
 */
require 'CommonParam.php';

class GiftsTask extends \Phalcon\Cli\Task
{
    use CommonParam;

    function testIndexAction()
    {
        $url = "http://www.chance_php.com/api/gifts";
        $body = $this->commonBody();

        $user = \Users::findLast();
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid));

        $res = httpGet($url, $body);
        var_dump($res);
    }

    function testGiveGiftAction()
    {
        $url = "http://www.chance_php.com/api/gifts";

        $body = $this->commonBody();
        $user = \Users::findLast();
        $sender = \Users::findFirst();

        $gift = \Gifts::findLast();
        $gift_num = 3;
        $body = array_merge($body, array(
                'sid' => $sender->sid, 'user_id' => $user->id,
                'gift_id' => $gift->id, 'gift_num' => $gift_num)
        );
        $res = httpPost($url, $body);
        //echo json_encode($res, JSON_UNESCAPED_UNICODE);
        echo $res;
        //var_dump($res);
    }

    function testCanGiveGiftAction()
    {
        //$user = \Users::findLast();
        $sender = \Users::findFirst();
        $gift = \Gifts::findLast();
        var_dump($sender->canGiveGift($gift, 10));
    }

    function testLockAction()
    {
        $redis = \Users::getHotWriteCache();
        $key = 'test_lock';
        $random = mt_rand(1, 1000);
        $ttl = 10;

        //$ok = $redis->set($key, $random, array('nx', 'ex' => $ttl));

        $key = 'user_gift_lock_2_4';
        echo $redis->get($key) . PHP_EOL;
        echo $redis->ttl($key) . PHP_EOL;
    }

    function fixGiftOrderUserTypeAction()
    {
        $gift_orders = GiftOrders::findForeach();

        foreach ($gift_orders as $gift_order) {
            $user = $gift_order->user;
            $sender = $gift_order->sender;
            if (!$user) {
                echoLine("user", $gift_order->id, $gift_order->user_id);
                $gift_order->delete();
            }

            if (!$sender) {
                echoLine("sender", $gift_order->id, $gift_order->sender_id);
            }

            $gift_order->receiver_user_type = $user->user_type;
            $gift_order->sender_user_type = $sender->user_type;
            $gift_order->save();
        }
    }

    function fixGiftPayTypeAction()
    {
        $gifts = Gifts::findForeach();

        foreach ($gifts as $gift) {
            echoLine($gift->pay_type, $gift->type);
            $gift->pay_type = 'diamond';
            $gift->type = 1;
            $gift->update();
        }
    }

    function fixGifOrderGiftTypeAction()
    {
        $gift_orders = GiftOrders::findForeach();

        foreach ($gift_orders as $gift_order) {
            echoLine($gift_order->toJson());
            $gift_order->gift_type = $gift_order->gift->type;
            $gift_order->update();
        }

        $user_gifts = UserGifts::findForeach();

        foreach ($user_gifts as $user_gift) {
            $user_gift->gift_type = $user_gift->gift->type;
            $user_gift->update();
        }
    }

    function fixGiftOrderTypeAction()
    {
        $gift_orders = GiftOrders::findForeach();

        foreach ($gift_orders as $gift_order) {

            if (SYSTEM_ID == $gift_order->sender_id) {
                $gift_order->type = GIFT_ORDER_TYPE_SYSTEM_SEND;
                $gift_order->update();
                continue;
            }

            if ($gift_order->user_id == $gift_order->sender_id) {
                $gift_order->type = GIFT_ORDER_TYPE_USER_BUY;
                $gift_order->update();
                continue;
            }

            if ($gift_order->user_id != $gift_order->sender_id) {
                $gift_order->type = GIFT_ORDER_TYPE_USER_SEND;
                $gift_order->update();
                continue;
            }
        }
    }
}