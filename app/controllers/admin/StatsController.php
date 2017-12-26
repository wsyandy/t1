<?php

namespace admin;

class StatsController extends BaseController
{

    function hoursAction()
    {

        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $province_id = $this->params('province_id', '-1');
        $partner_id = $this->params('partner_id', '-1');
        $product_channel_id = $this->params('product_channel_id', '-1');
        $platform = $this->params('platform', '-1');

        $province_id = intval($province_id);
        $partner_id = intval($partner_id);
        $product_channel_id = intval($product_channel_id);

        $stat_at = strtotime($stat_at);

        $start_at = beginOfDay($stat_at);
        $end_at = endOfDay($stat_at);

        $partners = $this->currentOperator()->getPartners();
        if ($this->currentOperator()->isLimitPartners()) {
            if (!$partners) {
                $this->renderJSON(ERROR_CODE_FAIL, '无权限');
                return;
            }

            if ($partner_id < 1) {
                $partner_id = $partners[0]->id;
            } else {
                $partner_operator = \PartnerOperators::findFirstByPartnerId($partner_id);
                if (!$partner_operator) {
                    $this->renderJSON(ERROR_CODE_FAIL, '无权限');
                    return;
                }
            }
        }

        debug($this->currentOperator()->role, $partner_id);

        $cond = ['conditions' => 'version_code=:version_code: and sex=:sex: and province_id = :province_id: and product_channel_id  = :product_channel_id: ' .
            'and platform = :platform: and partner_id = :partner_id: and time_type = :time_type: and stat_at >= :start_at: and stat_at <= :end_at:',
            'bind' => ['version_code' => -1, 'sex' => -1, 'province_id' => $province_id, 'product_channel_id' => $product_channel_id, 'platform' => $platform,
                'partner_id' => $partner_id, 'time_type' => STAT_HOUR, 'start_at' => $start_at, 'end_at' => $end_at], 'order' => 'id asc'];

        info($cond);

        $stats = \Stats::find($cond);
        debug("stats_num", count($stats));
        $hour_array = array();
        for ($i = 0; $i < 24; $i++) {
            if ($i < 10) {
                $hour = "0" . $i;
            } else {
                $hour = $i;
            }
            array_push($hour_array, $hour);
        }

        $stat_fields = \Stats::$STAT_FIELDS;

        $this->view->stats = $stats;
        $this->view->stat_at = date('Y-m-d', $stat_at);
        $this->view->hour_array = $hour_array;
        $this->view->data_array = $stat_fields;
        $this->view->product_channel_id = $product_channel_id;
        $this->view->province_id = $province_id;
        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc', 'columns' => 'id,name']);
        $this->view->partners = $partners;
        $this->view->partner_id = $partner_id;
        $this->view->platforms = \Stats::$PLATFORM;
        $this->view->platform = $platform;
    }

    function daysAction()
    {
        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $year = $this->params('year', date('Y'));
        $month = $this->params('month', date('m'));
        $province_id = $this->params('province_id', '-1');
        $partner_id = $this->params('partner_id', '-1');
        $product_channel_id = $this->params('product_channel_id', '-1');
        $platform = $this->params('platform', '-1');
        $province_id = intval($province_id);
        $partner_id = intval($partner_id);

        if (intval($month) < 10) {
            $month = '0' . intval($month);
        }

        $stat_date = strtotime($year . "-" . $month . "-01");
        $start_at = beginOfMonth($stat_date);
        $end_at = endOfMonth($stat_date);

        $partners = $this->currentOperator()->getPartners();
        if ($this->currentOperator()->isLimitPartners()) {
            if (!$partners) {
                $this->renderJSON(ERROR_CODE_FAIL, '无权限');
                return;
            }

            if ($partner_id < 1) {
                $partner_id = $partners[0]->id;
            } else {
                $partner_operator = \PartnerOperators::findFirstByPartnerId($partner_id);
                if (!$partner_operator) {
                    $this->renderJSON(ERROR_CODE_FAIL, '无权限');
                    return;
                }
            }
        }

        debug($this->currentOperator()->role, $partner_id);

        $cond = ['conditions' => 'version_code=:version_code: and sex=:sex: and province_id = :province_id: and product_channel_id  = :product_channel_id: ' .
            'and platform = :platform: and partner_id = :partner_id: and time_type = :time_type: and stat_at >= :start_at: and stat_at <= :end_at:',
            'bind' => ['version_code' => -1, 'sex' => -1, 'province_id' => $province_id, 'product_channel_id' => $product_channel_id, 'platform' => $platform,
                'partner_id' => $partner_id, 'time_type' => STAT_DAY, 'start_at' => $start_at, 'end_at' => $end_at], 'order' => 'id asc'];

        info($cond);

        $stats = \Stats::find($cond);

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

        $this->view->stats = $stats;
        $this->view->stat_at = $stat_at;
        $this->view->year = intval($year);
        $this->view->month = intval($month);
        $this->view->year_array = $year_array;
        $this->view->day_array = $day_array;
        $this->view->product_channel_id = intval($product_channel_id);
        $this->view->province_id = $province_id;
        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc', 'columns' => 'id,name']);
        $this->view->partners = $partners;
        $this->view->partner_id = $partner_id;
        $this->view->platforms = \Stats::$PLATFORM;
        $this->view->platform = $platform;
        $this->view->data_array = \Stats::$STAT_FIELDS;
    }

}