<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/4
 * Time: 下午8:34
 */

class ActivitiesTask extends \Phalcon\Cli\Task
{
    //清明活动
    function qingMingAction($opts)
    {
        if (count($opts) < 3) {
            info("参数错误");
            return;
        }

        $gift_id = $opts[0];
        $start = $opts[1];
        $end = $opts[2];

        info($opts);

        $start = beginOfDay(strtotime($start));
        $end = endOfDay(strtotime($end));
        $time = time();

        if (isProduction() && ($time < $start || $time >= $end + 60)) {
            info("activity is over");
            return;
        }

        if ($time > $end) {
            $time = $end;
        }

        $hot_cache = Activities::getHotWriteCache();
        $db = Users::getUserDb();

        $stat_time_key = 'qing_ming_activity_stat_time';
        $last_stat_time = $hot_cache->get($stat_time_key);

        info($last_stat_time);

        if (!$last_stat_time) {
            $last_stat_time = $start;
        }

        $hot_cache->set($stat_time_key, $time);

        $gift_orders = GiftOrders::find(['conditions' => 'gift_id = :gift_id: and created_at >= :start: and created_at < :end:' .
            ' and status = :status:',
            'bind' => ['gift_id' => $gift_id, 'start' => $last_stat_time, 'end' => $time, 'status' => GIFT_ORDER_STATUS_SUCCESS]]);

        foreach ($gift_orders as $gift_order) {
            $charm_key = "qing_ming_activity_charm_list_" . date("Ymd", $start) . "_" . date("Ymd", $end);
            $wealth_key = "qing_ming_activity_wealth_list_" . date("Ymd", $start) . "_" . date("Ymd", $end);
            info($gift_order->id, $gift_order->user_id, $gift_order->sender_id, $gift_order->amount, $charm_key, $wealth_key);

            if (!$gift_order->user->isCompanyUser()) {
                $db->zincrby($charm_key, $gift_order->amount, $gift_order->user_id);
            }

            if (!$gift_order->sender->isCompanyUser()) {
                $db->zincrby($wealth_key, $gift_order->amount, $gift_order->sender_id);
            }
        }
    }

    //重置活动奖品数量
    function resetPrizeNumAction()
    {
        $prize_types = [2 => 10, 4 => 10, 6 => 10, 7 => 100, 8 => 10];

        foreach ($prize_types as $prize_type => $num) {
            $key = 'lucky_draw_prize_' . $prize_type;
            $cache = \Users::getHotReadCache();
            $res = $cache->get($key);
            info($res, $prize_type);
            $cache->set($key, $num);
        }
    }
}