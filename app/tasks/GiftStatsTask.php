<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/10
 * Time: 上午11:23
 */
class GiftStatsTask extends \Phalcon\Cli\Task
{
    function dayAction()
    {
        $time = time() - 1800;

        $stat_at = beginOfDay($time); // 零点

        $end_at = endOfDay($time);

        $basic_gift_cond = ['conditions' => 'created_at >= :start: and created_at < :end:' . ' and status = :status:',
            'bind' => ['start' => $stat_at, 'end' => $end_at, 'status' => GIFT_ORDER_STATUS_SUCCESS]];

        $gift_orders = GiftOrders::find($basic_gift_cond);

        $product_channel_ids = [-1];
        $gift_ids = [-1];

        foreach ($gift_orders as $gift_order) {
            if (!in_array($gift_order->product_channel_id, $product_channel_ids)) {
                $product_channel_ids[] = $gift_order->product_channel_id;
            }

            if (!in_array($gift_order->gift_id, $gift_ids)) {
                $gift_ids[] = $gift_order->gift_id;
            }
        }

        $fields = GiftStats::$STAT_FIELDS;

        foreach ($product_channel_ids as $product_channel_id) {

            foreach ($gift_ids as $gift_id) {
                echoLine($product_channel_id . '--' . $gift_id);

                $day_conds = ['stat_at' => $stat_at, 'product_channel_id' => $product_channel_id, 'gift_id' => $gift_id];


                $stat = GiftStats::findFirstBy($day_conds);

                if (!$stat) {
                    $stat = new GiftStats();
                    foreach ($day_conds as $key => $value) {
                        $stat->$key = $value;
                    }
                }

                $gift_cond = $basic_gift_cond;

                if ($product_channel_id > 0) {
                    $gift_cond['conditions'] .= " and product_channel_id = $product_channel_id ";
                }

                if ($gift_id > 0) {
                    $gift_cond['conditions'] .= " and gift_id = $gift_id  ";
                }

                foreach ($fields as $method_name => $text_name) {
                    $method_name = Phalcon\Text::camelize($method_name);
                    $method_name = lcfirst($method_name);
                    if (method_exists($stat, $method_name)) {
                        $stat->$method_name($gift_cond);
                    }
                }

                $stat->data = json_encode($stat->data_hash, JSON_UNESCAPED_UNICODE);


                if (!$stat->needSave()) {
                    debug('false needSave continue', $day_conds, $stat->data_hash);
                    continue;
                }

                $stat->save();
            }
        }
    }
}