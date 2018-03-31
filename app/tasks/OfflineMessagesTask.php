<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 04/01/2018
 * Time: 16:40
 */
class OfflineMessagesTask extends \Phalcon\Cli\Task
{

    // offline_day 客户端个推唤醒
    function clientWakeUpAction($params)
    {

        if (count($params) < 1) {
            echoLine('error', $params);
            return;
        }

        set_time_limit(0);

        $offline_days = array_values($params);

        $product_channels = ProductChannels::find();
        foreach ($product_channels as $product_channel) {
            if ($product_channel->status != STATUS_ON) {
                echoLine('blocked', $product_channel->id);
                continue;
            }

            $group_key = 'client_users_active_group_' . $product_channel->id;
            $this->sendClientWakeUp($group_key, $offline_days);
        }
    }

    function sendClientWakeUp($group_key, $offline_days)
    {

        info('group_key', $group_key, 'offline_days', $offline_days);
        $hot_cache = Users::getHotWriteCache();
        foreach ($offline_days as $offline_day) {

            $offline_day_at = time() - $offline_day * 60 * 60 * 24;
            $begin_of_day = beginOfDay($offline_day_at);
            $end_of_day = endOfDay($offline_day_at);

            $user_ids = $hot_cache->zrangebyscore($group_key, $begin_of_day, $end_of_day, array('limit' => array(0, 1000000)));
            info($group_key, date('Ymd H:i:s', $begin_of_day), date('Ymd H:i:s', $end_of_day), 'user_ids_count', count($user_ids));

            $total = count($user_ids);
            $per_page = 100;
            $offset = 0;
            $loop_num = ceil($total / $per_page);
            for ($i = 0; $i < $loop_num; $i++) {
                $slice_ids = array_slice($user_ids, $offset, $per_page);
                $offset += $per_page;
                if (isDevelopmentEnv()) {
                    Users::delay(mt_rand(1, 60))->asyncSendOfflineMessage($slice_ids);
                } else {
                    Users::delay(mt_rand(1, 5400))->asyncSendOfflineMessage($slice_ids);
                }
            }
        }

    }
}

