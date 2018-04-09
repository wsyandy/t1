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

        $stat_fields = \Stats::statFields($this->currentOperator());

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

        $stat_fields = \Stats::statFields($this->currentOperator());

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
        $this->view->data_array = $stat_fields;
    }

    function statRoomTimeAction()
    {
        $per_page = $this->params('per_page', 30);
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $stat_at = strtotime($stat_at);
        beginOfDay($stat_at);

        if ($user_id) {
            $user_ids = [$user_id];
        } else {
            $total_key = \Users::generateStatRoomTimeKey('total', $stat_at);
            $db = \Users::getUserDb();
            $total_entries = $db->zcard($total_key);
            $offset = $per_page * ($page - 1);
            $user_ids = $db->zrevrange($total_key, $offset, $offset + $per_page - 1);
        }

        $users = \Users::findByIds($user_ids);

        foreach ($users as $user) {
            $user->audience_time = $user->getAudienceTimeByDate($stat_at);
            $user->broadcaster_time = $user->getBroadcasterTimeByDate($stat_at);
            $user->host_broadcaster_time = $user->getHostBroadcasterTimeByDate($stat_at);
        }

        $pagination = new \PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';
        $this->view->stat_at = date('Y-m-d', $stat_at);
        $this->view->users = $pagination;
        $this->view->user_id = $user_id;
    }

    function partnersAction()
    {

        $product_channel_id = $this->params('product_channel_id', '-1');
        $export = $this->params('export', 0);
        $start_stat_at = $this->params('start_stat_at', date('Y-m-d'));
        $end_stat_at = $this->params('end_stat_at', date('Y-m-d'));

        $start_at = beginOfDay(strtotime($start_stat_at));
        $end_at = endOfDay(strtotime($end_stat_at));
        if ($start_at > $end_at) {
            $this->renderJSON(ERROR_CODE_FAIL, '时间非法');
            return;
        }

        if ($end_at - $start_at > 60 * 60 * 24 * 30) {
            $this->renderJSON(ERROR_CODE_FAIL, '时间跨度最大30天');
            return;
        }
        $product_channel = null;
        if ($product_channel_id > 0) {
            $product_channel = \ProductChannels::findFirstById($product_channel_id);
        }

        $partners = $this->currentOperator()->getPartners();

        $new_partners = [];
        $new_stats = [];
        $export_data = [];
        foreach ($partners as $partner) {
            $partner->show_rank = 0;

            $cond = [
                'conditions' => 'version_code=:version_code: and sex=:sex: and province_id=:province_id: and product_channel_id=:product_channel_id: ' .
                    'and platform=:platform: and partner_id = :partner_id: and time_type = :time_type: and stat_at >= :start_at: and stat_at <= :end_at:',
                'bind' => ['version_code' => -1, 'sex' => -1, 'province_id' => -1, 'product_channel_id' => $product_channel_id, 'platform' => -1,
                    'partner_id' => $partner->id, 'time_type' => STAT_DAY, 'start_at' => $start_at, 'end_at' => $end_at]];

            $stats = \Stats::find($cond);

            $new_stat = null;
            foreach ($stats as $stat) {
                if ($new_stat) {
                    $new_data = json_decode($new_stat->data, true);
                    $data = json_decode($stat->data, true);
                    foreach ($data as $k => $v) {
                        if (isset($new_data[$k])) {
                            $new_data[$k] += $v;
                        } else {
                            $new_data[$k] = $v;
                        }
                    }
                    $new_stat->data = json_encode($new_data, JSON_UNESCAPED_UNICODE);
                } else {
                    $new_stat = new \Stats();
                    foreach ($stat->toData() as $k => $v) {
                        $new_stat->$k = $v;
                    }
                }
            }

            if ($new_stat) {

                foreach (\Stats::$STAT_PARTNER_FIELDS as $method_name => $text) {
                    if (preg_match('/(_average|_rate)$/', $method_name)) {
                        $method_name = \Phalcon\Text::camelize($method_name);
                        $method_name = lcfirst($method_name);
                        $new_stat->data_hash = json_decode($new_stat->data, true);
                        $new_stat->$method_name();
                        $new_stat->data = json_encode($new_stat->data_hash, JSON_UNESCAPED_UNICODE);
                    }
                }

                // 导出
                if ($export) {
                    $new_stat->data_hash = json_decode($new_stat->data, true);
                    if ($product_channel) {
                        $export_data[] = [$product_channel->name, $partner->name, $partner->fr, $new_stat->data_hash['device_active_num'], $new_stat->data_hash['subscribe_num'],
                            $new_stat->data_hash['register_num'], $new_stat->data_hash['register_rate']];
                    } else {
                        $export_data[] = ['全部', $partner->name, $partner->fr, $new_stat->data_hash['device_active_num'], $new_stat->data_hash['subscribe_num'],
                            $new_stat->data_hash['register_num'], $new_stat->data_hash['register_rate']];
                    }
                }

                $new_stats[] = $new_stat;
                $data_hash = json_decode($new_stat->data, true);
                $partner->show_rank = fetch($data_hash, 'register_num');
            }

            $new_partners[] = $partner;
        }

        if ($export_data) {
            $titles = ['产品渠道', "推广渠道", "fr", "激活设备数", "微信关注数", "注册数量", "注册率%"];
            $temp_name = 'export_fr_stat_' . date('Ymd', $start_at) . '_' . date('Ymd', $end_at) . '_' . time() . '.xls';
            $uri = writeExcel($titles, $export_data, $temp_name, true);

            if ($uri) {
                $export_history = new \ExportHistories();
                $export_history->operator_id = $this->currentOperator()->id;
                $export_history->name = '渠道统计';
                $export_history->table_name = 'PartnerStats';
                $export_history->conditions = json_encode(['product_channel_id' => $product_channel_id,
                    'time_type' => STAT_DAY, 'start_at' => $start_at, 'end_at' => $end_at], JSON_UNESCAPED_UNICODE);
                $export_history->download_num = 0;
                $export_history->file = $uri;
                $export_history->save();
                \OperatingRecords::logAfterCreate($this->currentOperator(), $export_history);
                $this->response->redirect('/admin/export_histories/download?id=' . $export_history->id);
            }

            //\Partners::delay(120)->deleteExportFile(APP_ROOT . 'public' . $uri);
            //$this->response->redirect($uri);

            $this->view->disable();
            return;
        }

        usort($new_partners, function ($a, $b) {

            if ($a->show_rank == $b->show_rank) {
                return 0;
            }
            return $a->show_rank > $b->show_rank ? -1 : 1;
        });

        $this->view->stats = $new_stats;
        $this->view->start_stat_at = $start_stat_at;
        $this->view->end_stat_at = $end_stat_at;
        $this->view->partners = $new_partners;
        $this->view->stat_fields = \Stats::statPartnerFields($this->currentOperator());
        $this->view->export_status = [STATUS_OFF => '否', STATUS_ON => '是'];
        $this->view->export = $export;
        $this->view->product_channel_id = intval($product_channel_id);
        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc', 'columns' => 'id,name']);

    }

}