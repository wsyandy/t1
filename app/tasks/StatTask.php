<?php

class StatTask extends \Phalcon\Cli\Task
{

    function getConds()
    {
        $stat_db = Stats::getStatDb();
        $all_stat_key = 'stats_keys_' . date('Ymd', (time() - 1800));
        $total = $stat_db->zcard($all_stat_key);
        info($all_stat_key, 'total', $total);

        $stat_keys = $stat_db->zrevrange($all_stat_key, 0, -1);
        $stat_keys = array_unique($stat_keys);

        $preg_str = "/platform(.*)_version_code(.*)_product_channel_id(.*)_partner_id(.*)_province_id(.*)_sex(.*)/";

        $conds = [];
        foreach ($stat_keys as $stat_key) {

            preg_match($preg_str, $stat_key, $matches);

            $cond['platform'] = $matches[1];
            $cond['version_code'] = $matches[2];
            $cond['product_channel_id'] = intval($matches[3]);
            $cond['partner_id'] = intval($matches[4]);
            $cond['province_id'] = intval($matches[5]);
            $cond['sex'] = intval($matches[6]);

            $conds[] = $cond;
        }

        return $conds;
    }

    function hourAction()
    {
        $conds = $this->getConds();
        $stat_at = strtotime(date('Ymd H:00:00', (time() - 1800)));
        $fields = Stats::$STAT_FIELDS;

        foreach ($conds as $cond) {

            $hour_conds = ['time_type' => STAT_HOUR, 'stat_at' => $stat_at, 'platform' => $cond['platform'], 'version_code' => $cond['version_code'],
                'province_id' => $cond['province_id'], 'product_channel_id' => $cond['product_channel_id'], 'partner_id' => $cond['partner_id'], 'sex' => $cond['sex']];

            $stat = Stats::findFirstBy($hour_conds);
            if (!$stat) {
                $stat = new Stats();
                $stat->time_type = STAT_HOUR;
                $stat->stat_at = $stat_at;
                foreach ($cond as $k => $v) {
                    $stat->$k = $v;
                }
            }

            foreach ($fields as $method_name => $text_name) {
                $method_name = Phalcon\Text::camelize($method_name);
                $method_name = lcfirst($method_name);
                if (method_exists($stat, $method_name)) {
                    $stat->$method_name();
                }
            }

            if (!$stat->needSave()) {
                debug('false needSave continue', $cond, $stat->data_hash);
                continue;
            }

            $stat->data = json_encode($stat->data_hash, JSON_UNESCAPED_UNICODE);
            if (!$stat->hasChanged('data') && md5($stat->was('data')) == md5($stat->data)) {
                debug('continue data not modify', $cond);
                continue;
            }

            $stat->save();
        }

    }

    function dayAction()
    {

        $conds = $this->getConds();
        $fields = Stats::$STAT_FIELDS;
        $stat_at = strtotime(date('Ymd 00:00:00', (time() - 1800))); // 零点

        foreach ($conds as $cond) {

            $day_conds = ['time_type' => STAT_DAY, 'stat_at' => $stat_at, 'platform' => $cond['platform'], 'version_code' => $cond['version_code'],
                'province_id' => $cond['province_id'], 'product_channel_id' => $cond['product_channel_id'], 'partner_id' => $cond['partner_id'], 'sex' => $cond['sex']];

            $stat = Stats::findFirstBy($day_conds);

            if (!$stat) {
                $stat = new Stats();
                $stat->time_type = STAT_DAY;
                $stat->stat_at = $stat_at;
                foreach ($cond as $k => $v) {
                    $stat->$k = $v;
                }
            }

            foreach ($fields as $method_name => $text_name) {
                $method_name = Phalcon\Text::camelize($method_name);
                $method_name = lcfirst($method_name);
                if (method_exists($stat, $method_name)) {
                    $stat->$method_name();
                }
            }

            if (!$stat->needSave()) {
                debug('continue', $cond);
                continue;
            }

            $stat->data = json_encode($stat->data_hash, JSON_UNESCAPED_UNICODE);
            if (!$stat->hasChanged('data') && md5($stat->was('data')) == md5($stat->data)) {
                debug('continue data not modify');
                continue;
            }

            $stat->save();
        }

    }

    function getPartConds($opts)
    {

        $total_page = $opts[0];
        $page = $opts[1];

        $stat_db = Stats::getStatDb();
        $all_stat_key = 'stats_keys_' . date('Ymd', (time() - 1800));
        $total = $stat_db->zcard($all_stat_key);

        $per_page = ceil($total / $total_page);
        $offset = ($page - 1) * $per_page;
        if ($total < $offset) {
            $offset = 0;
        }

        info($all_stat_key, 'total', $total, $total_page, $page, $per_page, $offset);

        $stat_keys = $stat_db->zrevrange($all_stat_key, $offset, $offset + $per_page - 1);
        $stat_keys = array_unique($stat_keys);

        $preg_str = "/platform(.*)_version_code(.*)_product_channel_id(.*)_partner_id(.*)_province_id(.*)_sex(.*)/";

        $conds = [];
        foreach ($stat_keys as $stat_key) {

            preg_match($preg_str, $stat_key, $matches);

            $cond['platform'] = $matches[1];
            $cond['version_code'] = $matches[2];
            $cond['product_channel_id'] = intval($matches[3]);
            $cond['partner_id'] = intval($matches[4]);
            $cond['province_id'] = intval($matches[5]);
            $cond['sex'] = intval($matches[6]);

            $conds[] = $cond;
        }

        return $conds;
    }

    // 3 1
    // 3 2
    // 3 3
    function dayPartAction($params)
    {
        if (count($params) != 2) {
            echoLine('error', $params);
            return;
        }

        $conds = $this->getPartConds($params);
        $fields = Stats::$STAT_FIELDS;
        $stat_at = strtotime(date('Ymd 00:00:00', (time() - 1800))); // 零点

        foreach ($conds as $cond) {

            $day_conds = ['time_type' => STAT_DAY, 'stat_at' => $stat_at, 'platform' => $cond['platform'], 'version_code' => $cond['version_code'],
                'province_id' => $cond['province_id'], 'product_channel_id' => $cond['product_channel_id'], 'partner_id' => $cond['partner_id']];

            $stat = Stats::findFirstBy($day_conds);
            if (!$stat) {
                $stat = new Stats();
                $stat->time_type = STAT_DAY;
                $stat->stat_at = $stat_at;
                foreach ($cond as $k => $v) {
                    $stat->$k = $v;
                }
            }

            foreach ($fields as $method_name => $text_name) {
                $method_name = Phalcon\Text::camelize($method_name);
                $method_name = lcfirst($method_name);
                if (method_exists($stat, $method_name)) {
                    $stat->$method_name();
                }
            }

            if (!$stat->needSave()) {
                debug('continue', $cond);
                continue;
            }

            $stat->data = json_encode($stat->data_hash, JSON_UNESCAPED_UNICODE);

            $stat->save();
        }

    }

    function delKeysAction()
    {

        $stat_at = beginOfDay(time() - 60 * 60 * 24 * 3);
        $end_at = endOfDay($stat_at);

        $sys_db = Stats::getStatDb();

        $all_stat_key = 'stats_keys_' . date('Ymd', $stat_at);
        $sys_db->zclear($all_stat_key);

        $hour_start_key = 'stats_' . date("YmdH", $stat_at) . '_user_a';
        $hour_end_key = 'stats_' . date("YmdH", $end_at) . '_user_z';

        while (true) {
            $keys = $sys_db->zlist($hour_start_key, $hour_end_key, 50000);
            echoLine(date("c"), $hour_start_key, $hour_end_key, 'hour count keys:', count($keys));
            if (count($keys) < 2) {
                echoLine($hour_start_key, $hour_end_key, 'hour break');
                break;
            }

            $this->clearKeys($keys);
        }

        $day_start_key = 'stats_' . date("Ymd", $stat_at) . '_user_a';
        $day_end_key = 'stats_' . date("Ymd", $end_at) . '_user_z';

        while (true) {
            $keys = $sys_db->zlist($day_start_key, $day_end_key, 50000);
            echoLine(date("c"), $day_start_key, $day_end_key, 'day count keys:', count($keys));
            if (count($keys) < 2) {
                echoLine($day_start_key, $day_end_key, 'day break');
                break;
            }

            $this->clearKeys($keys);
        }

    }

    function delKeys2Action($params)
    {

        $stat_at = beginOfDay(strtotime($params[0]));
        $end_at = endOfDay(strtotime($params[0]));

        $sys_db = Stats::getStatDb();

        $all_stat_key = 'stats_keys_' . date('Ymd', $stat_at);
        $sys_db->zclear($all_stat_key);

        $hour_start_key = 'stats_' . date("YmdH", $stat_at) . '_user_a';
        $hour_end_key = 'stats_' . date("YmdH", $end_at) . '_user_z';

        while (true) {
            $keys = $sys_db->zlist($hour_start_key, $hour_end_key, 50000);
            echoLine(date("c"), $hour_start_key, $hour_end_key, 'hour count keys:', count($keys));
            if (count($keys) < 2) {
                echoLine($hour_start_key, $hour_end_key, 'hour break');
                break;
            }

            $this->clearKeys($keys);
        }

        $day_start_key = 'stats_' . date("Ymd", $stat_at) . '_user_a';
        $day_end_key = 'stats_' . date("Ymd", $end_at) . '_user_z';

        while (true) {
            $keys = $sys_db->zlist($day_start_key, $day_end_key, 50000);
            echoLine(date("c"), $day_start_key, $day_end_key, 'day count keys:', count($keys));
            if (count($keys) < 2) {
                echoLine($day_start_key, $day_end_key, 'day break');
                break;
            }

            $this->clearKeys($keys);
        }

    }

    function clearKeys($keys)
    {

        $sys_db = Stats::getStatDb();
        foreach ($keys as $i => $key) {
            $sys_db->zclear($key);
            if (!preg_match('/(_ip|_new_total|_num_new_total|_new_total_target|_target)$/', $key)) {
                $sys_db->del($key . '_num');
                $sys_db->del($key . '_num_new_total');
            }

            if ($i > 0 && $i % 3000 == 0) {
                sleep(1);
            }
        }
    }

    function configAction()
    {
        $endpoints = Stats::config('stat_db');
        echoLine($endpoints);
    }

    // 每小时的57分统计, 订单个数占比
    function activeUserNumAction()
    {

        $product_channels = \ProductChannels::find(['columns' => 'id']);
        $product_channel_ids = [-1];
        foreach ($product_channels as $product_channel) {
            $product_channel_ids[] = $product_channel->id;
        }

        $platforms = \Stats::$PLATFORM;

        $stat_at = time() - 1000;

        $week = date('N', $stat_at);
        $day = date('Ymd', $stat_at);
        $month = date('Ym', $stat_at);
        $start_week_day = date('Ymd', strtotime($day . ' -' . ($week - 1) . ' day'));
        $end_week_day = date('Ymd', strtotime($start_week_day . ' +6 day'));

        $time_periods = [
            $day => [beginOfDay($stat_at), endOfDay($stat_at)],
            $start_week_day . '_' . $end_week_day => [beginOfDay(strtotime($start_week_day)), endOfDay(strtotime($end_week_day))],
            $month => [beginOfMonth($stat_at), endOfMonth($stat_at)]
        ];

        $stat_db = Stats::getStatDb();
        foreach ($product_channel_ids as $product_channel_id) {
            foreach ($platforms as $platform => $text) {
                foreach ($time_periods as $time_key => $time_period) {

                    $cache_key = "active_users_num_product_channel_id{$product_channel_id}_platform{$platform}_{$time_key}";

                    $find_cond['conditions'] = 'last_at>=:start_at: and last_at<=:end_at:';
                    $find_cond['bind'] = ['start_at' => $time_period[0], 'end_at' => $time_period[1]];
                    if ($product_channel_id != -1) {
                        $find_cond['conditions'] .= ' and product_channel_id=:product_channel_id:';
                        $find_cond['bind']['product_channel_id'] = $product_channel_id;
                    }
                    if ($platform != -1) {
                        $find_cond['conditions'] .= ' and platform=:platform:';
                        $find_cond['bind']['platform'] = $platform;
                    }

                    $total = Users::count($find_cond);
                    if ($total < 1) {
                        continue;
                    }

                    $stat_db->set($cache_key, strval($total));

                    echoLine($cache_key, $total);
                }
            }
        }
    }

}