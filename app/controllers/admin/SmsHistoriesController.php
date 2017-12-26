<?php

namespace admin;


class SmsHistoriesController extends BaseController
{

    function indexAction()
    {
        $cond = $this->getConditions('sms_history');
        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $cond['order'] = 'id desc';
        $sms_histories = \SmsHistories::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->sms_histories = $sms_histories;
    }

    function pushStatAction()
    {

        $hot_cache = \Users::getHotWriteCache();
        $key = 'push_stat_wakeup_mobile_cache_market';

        $data = $hot_cache->get($key);
        if ($data) {
            $this->view->results = json_decode($data, true);
            return;
        }

        $results = [];
        for ($i = 0; $i < 15; $i++) {
            $start_at = beginOfDay(time() - $i * 60 * 60 * 24);
            $end_at = endOfDay(time() - $i * 60 * 60 * 24);

            $device_db = \Devices::getDeviceDb();
            $mobile_operators = \SmsChannels::$MOBILE_OPERATOR;
            $send_total = 0;
            $send_success_total = 0;
            $auth_success_total = 0;
            foreach ($mobile_operators as $mobile_operator => $v) {

                if ($mobile_operator < 1) {
                    continue;
                }

                $send_success_key = 'sms_wakeup_success_' . date('Ymd', $start_at) . '_' . $mobile_operator;
                $send_fail_key = 'sms_wakeup_fail_' . date('Ymd', $start_at) . '_' . $mobile_operator;
                $auth_key = 'sms_wakeup_auth_success_' . date('Ymd', $start_at) . '_' . $mobile_operator;
                $send_success_num = $device_db->zcard($send_success_key);
                $send_fail_num = $device_db->zcard($send_fail_key);
                $auth_num = $device_db->zcard($auth_key);

                $send_total += $send_success_num;
                $send_total += $send_fail_num;
                $send_success_total += $send_success_num;
                $auth_success_total += $auth_num;

                $results[date('Ymd', $start_at) . '_' . $v][0] = $send_success_num + $send_fail_num;
                $results[date('Ymd', $start_at) . '_' . $v][1] = $send_success_num;
                $results[date('Ymd', $start_at) . '_' . $v][2] = $auth_num;

                $order_mobile_key = $auth_key . '_order_mobiles';
                $order_mobile_num = $device_db->zcard($order_mobile_key);
                $total_order_num = $device_db->zcard($order_mobile_key . '_product_num');
                $results[date('Ymd', $start_at) . '_' . $v][3] = $order_mobile_num;
                $rate = 0;
                if ($auth_num) {
                    $rate = sprintf("%0.2f", $order_mobile_num / $auth_num);
                }
                $results[date('Ymd', $start_at) . '_' . $v][4] = $rate;
                $results[date('Ymd', $start_at) . '_' . $v][5] = $total_order_num;
                $avg = 0;
                if ($order_mobile_num) {
                    $avg = sprintf("%0.2f", $total_order_num / $order_mobile_num);
                }
                $results[date('Ymd', $start_at) . '_' . $v][6] = $avg;
            }

            $results[date('Ymd', $start_at) . '_汇总'][0] = $send_total;
            $results[date('Ymd', $start_at) . '_汇总'][1] = $send_success_total;
            $sms_wakeup_auth_success_total = 'sms_wakeup_auth_success_' . date('Ymd', $start_at) . '_total';
            $sms_wakeup_auth_success_total_num = $device_db->get($sms_wakeup_auth_success_total);
            $sms_wakeup_auth_success_total_num = intval($sms_wakeup_auth_success_total_num);
            $results[date('Ymd', $start_at) . '_汇总'][2] = $sms_wakeup_auth_success_total_num . '次数';
            $results[date('Ymd', $start_at) . '_汇总'][3] = 0;
            $results[date('Ymd', $start_at) . '_汇总'][4] = 0;
            $results[date('Ymd', $start_at) . '_汇总'][5] = 0;
            $results[date('Ymd', $start_at) . '_汇总'][6] = 0;
        }

        $hot_cache->setex($key, 600, json_encode($results, JSON_UNESCAPED_UNICODE));

        $this->view->results = $results;
    }

    function channelStatAction()
    {
        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $sms_channels = \SmsChannels::find(['conditions' => 'sms_type=:sms_type:', 'bind' => ['sms_type' => 'market'], 'order' => 'id desc']);

        foreach ($sms_channels as $sms_channel) {
            list($send_num, $success_num, $auth_num, $rate, $amount) = \SmsHistories::marketChannelStat($sms_channel, $stat_at);
            $sms_channel->send_num = $send_num;
            $sms_channel->success_num = $success_num;
            $sms_channel->auth_num = $auth_num;
            $sms_channel->rate = $rate;
            $sms_channel->amount = $amount;
        }

        $this->view->sms_channels = $sms_channels;
        $this->view->stat_at = $stat_at;
    }

    function loginStatAction()
    {
        $product_channel_id = $this->params('product_channel_id', 0);
        $hot_cache = \Users::getHotWriteCache();
        $key = 'push_stat_wakeup_mobile_cache_login_' . $product_channel_id;
        $data = $hot_cache->get($key);
        $this->view->product_channel_id = intval($product_channel_id);
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        if ($data) {
            $this->view->results = json_decode($data, true);
            return;
        }

        $results = [];
        for ($i = 0; $i < 15; $i++) {
            $start_at = beginOfDay(time() - $i * 60 * 60 * 24);
            $end_at = endOfDay(time() - $i * 60 * 60 * 24);
            if ($product_channel_id) {
                $conds = ['conditions' => 'sms_type=:sms_type: and created_at>=:start_at: and created_at<=:end_at: and product_channel_id=:product_channel_id:',
                    'bind' => ['sms_type' => 'login', 'start_at' => $start_at, 'end_at' => $end_at, 'product_channel_id' => $product_channel_id],
                    'columns' => 'distinct mobile'];
            } else {
                $conds = ['conditions' => 'sms_type=:sms_type: and created_at>=:start_at: and created_at<=:end_at:',
                    'bind' => ['sms_type' => 'login', 'start_at' => $start_at, 'end_at' => $end_at],
                    'columns' => 'distinct mobile'];
            }
            $results[date('Ymd', $start_at)][0] = \SmsHistories::count($conds);
            $conds['conditions'] .= ' and send_status=:send_status:';
            $conds['bind']['send_status'] = SMS_HISTORY_SEND_STATUS_SUCCESS;
            $results[date('Ymd', $start_at)][1] = \SmsHistories::count($conds);
            $conds['conditions'] .= ' and auth_status=:auth_status:';
            $conds['bind']['auth_status'] = SMS_HISTORY_AUTH_STATUS_SUCCESS;
            $results[date('Ymd', $start_at)][2] = \SmsHistories::count($conds);
        }
        $hot_cache->setex($key, 600, json_encode($results, JSON_UNESCAPED_UNICODE));
        $this->view->results = $results;
    }

    function loginHourStatAction()
    {

        $day = $this->params('day', date('Y-m-d'));
        $product_channel_id = $this->params('product_channel_id', 0);

        $this->view->product_channel_id = intval($product_channel_id);
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->day = $day;

        $hot_cache = \Users::getHotWriteCache();
        $key = 'push_stat_wakeup_mobile_cache_login_' . $product_channel_id . '_' . $day;
        $data = $hot_cache->get($key);
        if ($data && isProduction()) {
            $this->view->results = json_decode($data, true);
            return;
        }

        $hour = 23;
        if (date("Y-m-d", strtotime($day)) == date('Y-m-d')) {
            $hour = date('H');
        }
        $results = [];
        for ($i = $hour; $i >= 0; $i--) {

            $start_at = beginOfDay(strtotime($day)) + $i * 3600;
            $end_at = beginOfDay(strtotime($day)) + ($i + 1) * 3600;

            if ($product_channel_id) {
                $conds = ['conditions' => 'sms_type=:sms_type: and created_at>=:start_at: and created_at<=:end_at: and product_channel_id=:product_channel_id:',
                    'bind' => ['sms_type' => 'login', 'start_at' => $start_at, 'end_at' => $end_at, 'product_channel_id' => $product_channel_id],
                    'columns' => 'distinct mobile'];
            } else {
                $conds = ['conditions' => 'sms_type=:sms_type: and created_at>=:start_at: and created_at<=:end_at:',
                    'bind' => ['sms_type' => 'login', 'start_at' => $start_at, 'end_at' => $end_at],
                    'columns' => 'distinct mobile'];
            }

            $results[date('YmdH', $start_at)][0] = \SmsHistories::count($conds);

            $conds['conditions'] .= ' and send_status=:send_status:';
            $conds['bind']['send_status'] = SMS_HISTORY_SEND_STATUS_SUCCESS;
            $results[date('YmdH', $start_at)][1] = \SmsHistories::count($conds);

            $conds['conditions'] .= ' and auth_status=:auth_status:';
            $conds['bind']['auth_status'] = SMS_HISTORY_AUTH_STATUS_SUCCESS;

            $results[date('YmdH', $start_at)][2] = \SmsHistories::count($conds);
        }

        $hot_cache->setex($key, 200, json_encode($results, JSON_UNESCAPED_UNICODE));

        $this->view->results = $results;
    }


    function blackListAction()
    {
        $hot_cache = \Devices::getDeviceDb();
        $key = "mobile_black_list";
        $mobiles = $hot_cache->zrevrange($key, 0, 100);
        $this->view->mobiles = $mobiles;
    }

    function addBlackListAction()
    {

        if ($this->request->isPost()) {
            $mobile = $this->params('mobile');
            if (!$mobile) {
                $this->renderJSON(ERROR_CODE_FAIL, '手机号码不能为空');
                return;
            }

            $hot_cache = \Devices::getDeviceDb();
            $key = "mobile_black_list";
            $hot_cache->zadd($key, time(), $mobile);

            $this->response->redirect('/admin/sms_histories/black_list');
            return;
        }
    }

    function deleteBlackMobileAction()
    {
        $mobile = $this->params('mobile');
        $hot_cache = \Devices::getDeviceDb();
        $key = "mobile_black_list";
        $hot_cache->zrem($key, $mobile);

        $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/sms_histories/black_list']);
    }

}