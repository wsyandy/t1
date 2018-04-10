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
        $year = $this->params('year', date('Y'));
        $month = $this->params('month', date('m'));
        $gift_id = $this->params('gift_id', '-1');
        $product_channel_id = $this->params('product_channel_id', '-1');

        if (intval($month) < 10) {
            $month = '0' . intval($month);
        }

        $stat_date = strtotime($year . "-" . $month . "-01");
        $start_at = beginOfMonth($stat_date);
        $end_at = endOfMonth($stat_date);


        $cond = ['conditions' => ' product_channel_id  = :product_channel_id: ' . ' and gift_id = :gift_id:' . 'and stat_at >= :start_at: and stat_at <= :end_at:',
            'bind' => ['product_channel_id' => $product_channel_id, 'gift_id' => $gift_id, 'start_at' => $start_at, 'end_at' => $end_at], 'order' => 'id asc'];

        info($cond);

        $gift_stats = \GiftStats::find($cond);

        $year_array = array();
        for ($i = date('Y'); $i >= 2016; $i--) {
            $year_array[$i] = $i;
        }

        //每月天数数组array('d'=>'Y-m-d')
        $day_array = array();
        $month_max_day = date('d', $end_at);//获取当前月份最大的天数
        for ($i = 1; $i <= $month_max_day; $i++) {
            if ($i < 10) {
                $day = "0" . $i;
            } else {
                $day = $i;
            }
            $day_array[$day] = $year . "-" . $month . "-" . $day;
        }

        $stat_fields = \GiftStats::statFields($this->currentOperator());

        $this->view->gift_stats = $gift_stats;
        $this->view->stat_at = $stat_at;
        $this->view->year = intval($year);
        $this->view->month = intval($month);
        $this->view->year_array = $year_array;
        $this->view->day_array = $day_array;
        $this->view->product_channel_id = intval($product_channel_id);
        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc', 'columns' => 'id,name']);
        $this->view->gift_ids = \Gifts::find(['order' => ' id desc', 'columns' => 'id,name']);
        $this->view->gift_id = $gift_id;
        $this->view->data_array = $stat_fields;
    }

}