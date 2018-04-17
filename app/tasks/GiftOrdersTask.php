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
                'gift_type' => GIFT_TYPE_COMMON, 'start' => beginOfDay(strtotime('-1 day')), 'end' => endOfDay()
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

    function fixGiftOrdersAction()
    {
        $gift_order_ids = [289739, 289998, 303991, 304576, 307077, 310210,
            309782, 310267, 322167, 323055, 324129, 325509, 325533, 326327, 330062, 334703, 210756, 221973,
            232450, 241867, 212474, 134265, 112181, 129563, 192817, 264272, 264712, 269915, 272508, 278114, 281565, 284934,
            349690, 356507, 360032, 187921, 202558, 208721, 171945, 156282, 366499, 367774
        ];

        foreach ($gift_order_ids as $gift_order_id) {

            $gift_order = GiftOrders::findFirstById($gift_order_id);
//            $sender = $gift_order->sender;
//            $user = $gift_order->user;
            $room = $gift_order->room;
            $receiver_union_id = $gift_order->receiver_union_id;

            if ($receiver_union_id) {
                echoLine($gift_order->receiver_union_id);
            }
//            if ($room) {
//
//                $cond = [
//                    'conditions' => 'status = :status: and gift_type = :gift_type: and pay_type = :pay_type: and room_id = :room_id:',
//                    'bind' => ['status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND, 'room_id' => $room->id,
//                        'gift_type' => GIFT_TYPE_COMMON
//                    ],
//                    'column' => 'amount'
//                ];
//
//                $amount = GiftOrders::sum($cond);
//
//                if ($room->getAmount() != $amount) {
//                   // echoLine($gift_order_id, $room->getAmount(), $room->id, $amount);
//                }
//
//                $stat_at = date("Ymd", $gift_order->created_at);
//                $day_income = $room->getDayIncome($stat_at);
//                $income = GiftOrders::sum([
//                    'conditions' => 'status = :status: and gift_type = :gift_type: and pay_type = :pay_type: and created_at >= :start: and created_at <= :end: and room_id = :room_id:',
//                    'bind' => ['status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
//                        'gift_type' => GIFT_TYPE_COMMON, 'start' => beginOfDay(strtotime($stat_at)), 'end' => endOfDay(strtotime($stat_at)),
//                        'room_id' => $room->id
//                    ],
//                    'column' => 'amount'
//                ]);
//
//                if ($income != $day_income) {
//                    echoLine($gift_order_id, $income, $day_income, $room->id, $gift_order->amount);
//                }
//            }
        }
    }
}