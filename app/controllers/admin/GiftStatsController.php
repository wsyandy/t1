<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/10
 * Time: 上午10:13
 */

namespace admin;
class GiftStatsController extends BaseController
{
    function daysAction()
    {
        $stat_at = $this->params('stat_at', date('Y-m-d'));

        $start_at = beginOfDay(strtotime($stat_at));
        $end_at = endOfDay(strtotime($stat_at));

        $gift_id = $this->params('gift_id');
        $product_channel_id = $this->params('product_channel_id', 1);

        $cond = ['conditions' => ' product_channel_id  = :product_channel_id: ' . 'and stat_at >= :start_at: and stat_at <= :end_at:',
            'bind' => ['product_channel_id' => $product_channel_id, 'start_at' => $start_at, 'end_at' => $end_at], 'order' => 'gift_id desc'];

        if ($gift_id) {
            $cond['conditions'] .= ' and gift_id = :gift_id:';
            $cond['bind']['gift_id'] = $gift_id;
        }

        $gift_stats = \GiftStats::find($cond);

        $new_gift_stats = [];

        foreach ($gift_stats as $gift_stat) {

            $rank = 0;
            $data = $gift_stat->data;

            if ($data) {
                $data = json_decode($data, true);
                $rank = fetch($data, 'gift_total', 0);
            }

            $gift_stat->rank = $rank;
        }

        usort($new_gift_stats, function ($a, $b) {

            if ($a->rank == $b->rank) {
                return 0;
            }

            return $a->rank > $b->rank ? -1 : 1;
        });

        $stat_fields = \GiftStats::statFields($this->currentOperator());

        debug($product_channel_id, $gift_id);

        $this->view->gift_stats = $new_gift_stats;
        $this->view->stat_at = $stat_at;
        $this->view->product_channel_id = intval($product_channel_id);
        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc', 'columns' => 'id,name']);
        $this->view->gifts = \Gifts::find(['order' => ' id desc', 'columns' => 'id,name']);
        $this->view->gift_id = $gift_id;
        $this->view->stat_fields = $stat_fields;
    }

}