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

        //-1代表全部
        $product_channel_ids = [-1];
        $gift_ids = [];

        $product_channels = \ProductChannels::find(['order' => ' id desc', 'column' => 'id']);
        foreach ($product_channels as $product_channel) {
            $product_channel_ids[] = $product_channel->id;
        }

        $gifts = \Gifts::find(['order' => ' id desc', 'column' => 'id']);
        foreach ($gifts as $gift) {
            $gift_ids[] = $gift->id;
        }

        $fields = GiftStats::$STAT_FIELDS;

        foreach ($product_channel_ids as $product_channel_id) {

            foreach ($gift_ids as $gift_id) {

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
                    debug('false needSave continue', $gift_cond, $stat->data_hash);
                    continue;
                }

                $stat->save();
            }
        }
    }
}