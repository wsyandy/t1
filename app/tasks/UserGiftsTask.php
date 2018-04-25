<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 04/01/2018
 * Time: 16:40
 */
class UserGiftsTask extends \Phalcon\Cli\Task
{
    function fixUserGiftNum()
    {
        $user_gifts = UserGifts::findForeach();

        foreach ($user_gifts as $user_gift) {

            if (!$user_gift->user) {
                $user_gift->delete();
                echoLine("no user", $user_gift->id, $user_gift->user_id);
                continue;
            }

            $user_id = $user_gift->user_id;
            $gift_id = $user_gift->gift_id;
            $user_gift_num = $user_gift->num;

            $num = GiftOrders::sum([
                'conditions' => 'user_id = :user_id: and gift_id = :gift_id:',
                'bind' => ['user_id' => $user_id, 'gift_id' => $gift_id],
                'column' => 'gift_num'
            ]);

            $total_amount = GiftOrders::sum([
                'conditions' => 'user_id = :user_id: and gift_id = :gift_id:',
                'bind' => ['user_id' => $user_id, 'gift_id' => $gift_id],
                'column' => 'amount'
            ]);

            if ($num != $user_gift_num || $total_amount != $user_gift->total_amount) {

                if ($total_amount > 1000000000) {
                    echoLine("long", $user_gift->id, $user_gift->user_id, $total_amount, $user_gift->total_amount, $num, $user_gift->num);
                    continue;
                }

                echoLine($user_id, $gift_id, $total_amount, $user_gift->total_amount, $num, $user_gift->num);
                $user_gift->num = $num;
                $user_gift->total_amount = $total_amount;
                $user_gift->update();
            }
        }
    }

    function fixOneUserGiftNum()
    {

        $user_id = 1075502;

        $gift_orders = GiftOrders::find([
            'conditions' => 'user_id = :user_id: and status = :status: and gift_type = :gift_type: and pay_type = :pay_type:',
            'bind' => ['user_id' => $user_id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                'gift_type' => GIFT_TYPE_COMMON
            ],
            'columns' => 'distinct gift_id'
        ]);

        foreach ($gift_orders as $gift_order) {

            //echoLine($gift_order->gift_id);
            $gift_id = $gift_order->gift_id;

            $num = GiftOrders::sum([
                'conditions' => 'user_id = :user_id: and gift_id = :gift_id:',
                'bind' => ['user_id' => $user_id, 'gift_id' => $gift_id],
                'column' => 'gift_num'
            ]);

            $total_amount = GiftOrders::sum([
                'conditions' => 'user_id = :user_id: and gift_id = :gift_id:',
                'bind' => ['user_id' => $user_id, 'gift_id' => $gift_id],
                'column' => 'amount'
            ]);

            $user_gift = UserGifts::findFirstBy(['user_id' => $user_id, 'gift_id' => $gift_id]);

            $gift = Gifts::findFirstById($gift_id);

            if (!$user_gift) {
                echoLine($gift_id, $num, $total_amount);

                $user_gift = new UserGifts();
                $user_gift->name = $gift->name;
                $user_gift->gift_id = $gift->id;
                $user_gift->user_id = $user_id;
                $user_gift->num = $num;
                $user_gift->amount = $gift->amount;
                $user_gift->total_amount = $total_amount;
                $user_gift->pay_type = $gift->pay_type;
                $user_gift->gift_type = $gift->type;
                $user_gift->save();
            }

        }
    }
}

