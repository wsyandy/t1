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
            $user_id = $user_gift->user_id;
            $gift_id = $user_gift->gift_id;
            $user_gift_num = $user_gift->num;

            $num = GiftOrders::sum([
                'conditions' => 'user_id = :user_id: and gift_id = :gift_id:',
                'bind' => ['user_id' => $user_id, 'gift_id' => $gift_id],
                'column' => 'gift_num'
            ]);

            if (!$user_gift->user) {
                continue;
            }

            if ($num != $user_gift_num) {

                $total_amount = GiftOrders::sum([
                    'conditions' => 'user_id = :user_id: and gift_id = :gift_id:',
                    'bind' => ['user_id' => $user_id, 'gift_id' => $gift_id],
                    'column' => 'amount'
                ]);

                if ($total_amount > 1000000000) {
                    echoLine($user_gift->id);
                    continue;
                }
                $user_gift->num = $num;
                $user_gift->total_amount = $total_amount;
                $user_gift->update();
                echoLine($num, $user_gift_num, $user_id, $gift_id);
            }
        }
    }
}

