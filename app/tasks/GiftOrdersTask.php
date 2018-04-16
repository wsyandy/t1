<?php

class GiftOrdersTask extends \Phalcon\Cli\Task
{
    function checkHiCoinsAction()
    {
        $user_id = 1012820;

        $gift_orders = GiftOrders::find([
            'conditions' => 'user_id = :user_id: and status = :status: and gift_type = :gift_type: and pay_type = :pay_type:',
            'bind' => ['user_id' => $user_id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                'gift_type' => GIFT_TYPE_COMMON
            ]
        ]);


        foreach ($gift_orders as $gift_order) {
            $hi_coin_history = HiCoinHistories::findFirstBy(['gift_order_id' => $gift_order->id, 'user_id' => $user_id]);

            if (!$hi_coin_history) {
                echoLine($gift_order->id);
            }
        }


        $gift_orders = GiftOrders::find([
            'conditions' => 'status = :status: and gift_type = :gift_type: and pay_type = :pay_type: and created_at >= :start: and created_at <= :end:',
            'bind' => ['status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                'gift_type' => GIFT_TYPE_COMMON, 'start' => beginOfMonth(), 'end' => endOfMonth()
            ],
            'columns' => 'id,user_id'
        ]);


        foreach ($gift_orders as $gift_order) {
            $hi_coin_history = HiCoinHistories::findFirstBy(['gift_order_id' => $gift_order->id, 'user_id' => $gift_order->user_id]);

            if (!$hi_coin_history) {
                echoLine($gift_order->id);
            }
        }
    }
}