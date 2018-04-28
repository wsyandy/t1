<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/28
 * Time: 上午10:18
 */
namespace admin;
class ProvinceStatsController extends BaseController
{
    function daysAction()
    {
        $start_at = $this->params('start_at', date('Y-m-d', strtotime('-1 day')));
        $start_at_time = beginOfDay(strtotime($start_at));
        $end_at_time = endOfDay(strtotime($start_at));
        $product_channel_id = $this->params('product_channel_id', '-1');
        $partner_id = $this->params('partner_id', 0);
        $platform = $this->params('platform', '-1');

        $cond = ['conditions' => 'time_type = :time_type: and stat_at >= :start_at: and stat_at <= :end_at: and 
            product_channel_id=:product_channel_id: and platform=:platform: and partner_id = :partner_id:',
            'bind' => ['time_type' => STAT_DAY, 'start_at' => $start_at_time, 'end_at' => $end_at_time,
                'product_channel_id' => $product_channel_id, 'platform' => $platform, 'partner_id' => $partner_id],
            'order' => 'province_id asc'
        ];


        $province_stats = \ProvinceStats::find($cond);

        $new_province_stats = [];
        $total_province_stat = new \ProvinceStats();
        $total_province_stat->province_name = '汇总';

        $total_data_hash = [];
        foreach ($province_stats as $province_stat) {
            if (!$province_stat->data) {
                continue;
            }
            $new_province_stats[] = $province_stat;

            $data_hash = json_decode($province_stat->data, true);
            foreach ($data_hash as $k => $v) {
                if (isset($total_data_hash[$k])) {
                    $total_data_hash[$k] += doubleval($v);
                } else {
                    $total_data_hash[$k] = doubleval($v);
                }
            }
        }

        $total_province_stat->data = json_encode($total_data_hash, JSON_UNESCAPED_UNICODE);
        $total_province_stat->calculate();

        array_unshift($new_province_stats, $total_province_stat);


        $this->view->province_stats = $new_province_stats;
        $this->view->start_at = $start_at;
        $this->view->end_at = date('Y-m-d');
        $this->view->stat_fields = \ProvinceStats::$STAT_FIELDS;
        $this->view->provinces = \Provinces::find();
        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc', 'columns' => 'id,name']);
        $this->view->product_channel_id = intval($product_channel_id);
        $this->view->partners = \Partners::find(['order' => 'id desc', 'columns' => 'id,name']);
        $this->view->partner_id = $partner_id;
        $this->view->platform = $platform;
        $this->view->platforms = \ProvinceStats::$PLATFORMS;
    }

}