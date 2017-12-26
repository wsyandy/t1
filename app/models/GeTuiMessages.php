<?php

class GeTuiMessages extends BaseModel
{
    /**
     * @type ProductChannels
     */
    private $_product_channel;
    /**
     * @type PushMessages
     */
    private $_push_message;
    /**
     * @type Operators
     */
    private $_operator;

    static $STATUS = array(STATUS_ON => '有效', STATUS_OFF => '无效');
    static $SEND_STATUS = [SEND_STATUS_WAIT => '等待发送', SEND_STATUS_SUBMIT => '提交发送', SEND_STATUS_PROGRESS => '发送中', SEND_STATUS_SUCCESS => '发送成功', SEND_STATUS_STOP => '终止发送'];

    function mergeJson()
    {
        $send_at_text = '';
        if ($this->send_at) {
            $send_at_text = $this->send_at_text;
        }

        $data = ['product_channel_name' => $this->product_channel_name, 'send_at_text' => $send_at_text, 'operator_username' => $this->operator_username];

        return $data;
    }

    function isProvincePass($device)
    {
        if ($this->province_ids && $device->province_id) {
            return in_array($device->province_id, explode(',', $this->province_ids));
        }

        return true;
    }

    function isPass($device)
    {
        if (!$this->isProvincePass($device)) {
            return false;
        }

        return true;
    }

    static function crontabSendKf($ge_tui_message_id)
    {
        $ge_tui_message = self::findFirstById($ge_tui_message_id);
        $product_channel = $ge_tui_message->product_channel;

        if ($product_channel->status != STATUS_ON) {
            return;
        }

        $group_key = 'devices_active_group_' . $product_channel->id;
        $hot_cache = Users::getHotWriteCache();
        // 7 - 15
        list($offline_start_day, $offline_end_day) = explode('-', $ge_tui_message->offline_day);

        $begin_of_day = time() - $offline_end_day * 60 * 60 * 24;
        $begin_of_day = beginOfDay($begin_of_day);
        $end_of_day = time() - $offline_start_day * 60 * 60 * 24;
        $end_of_day = endOfDay($end_of_day);

        debug($offline_start_day, $offline_end_day, date('Ymd', $begin_of_day), date('Ymd', $end_of_day));

        $device_ids = $hot_cache->zrangebyscore($group_key, $begin_of_day, $end_of_day, array('limit' => array(0, 1000000)));
        $total_num = count($device_ids);

        // 发送记录
        $ge_tui_message->send_at = time();
        $ge_tui_message->send_status = SEND_STATUS_PROGRESS;
        $ge_tui_message->remark = '1小时后查看发送统计，预估发送人数: ' . $total_num;
        $ge_tui_message->update();

        info($ge_tui_message_id, $group_key, 'total_user_count', $total_num);

        $per_page = 200;
        $loop_num = ceil($total_num / $per_page);
        if ($loop_num < 1) {
            self::delay(10)->statSendGeTuiKf($ge_tui_message_id);
            return;
        }

        $hot_cache->setex('ge_tui_message_send_loop_num_' . $ge_tui_message_id, 60 * 60 * 2, $loop_num);

        $offset = 0;
        $max_delay_at = 0;
        for ($i = 0; $i < $loop_num; $i++) {
            $slice_ids = array_slice($device_ids, $offset, $per_page);
            $offset += $per_page;

            $delay_at = mt_rand(1, 3000);
            if (isDevelopmentEnv()) {
                $delay_at = 1;
            }

            if ($max_delay_at < $delay_at) {
                $max_delay_at = $delay_at;
            }
            debug('page', $i, 'offset', $offset, $total_num);

            self::delay($delay_at)->asyncSendKf($slice_ids, $ge_tui_message_id);
        }

        $max_delay_at += 300;
        info("统计statSendGeTuiKf", $max_delay_at, $product_channel->id, $ge_tui_message_id);
        self::delay($max_delay_at)->statSendGeTuiKf($ge_tui_message_id);
    }

    static function statSendGeTuiKf($ge_tui_message_id)
    {
        $ge_tui_message = self::findFirstById($ge_tui_message_id);
        $product_channel_id = $ge_tui_message->product_channel_id;

        $hot_cache = self::getHotReadCache();
        $success_key = 'send_ge_tui_message_success_num_' . $ge_tui_message_id . '_' . $product_channel_id;
        $success_num = $hot_cache->get($success_key);
        $fail_key = 'send_ge_tui_message_fail_num_' . $ge_tui_message_id . '_' . $product_channel_id;
        $fail_num = $hot_cache->get($fail_key);
        $send_num = $success_num + $fail_num;
        $success_rate = 0;
        if ($send_num) {
            $success_rate = intval($success_num * 100 / $send_num);
        }

        $info = "发送人数:{$send_num}, 成功人数:{$success_num}, 失败人数:{$fail_num}, 成功率:{$success_rate}";
        info($ge_tui_message->id, $info);

        if ($hot_cache->get('ge_tui_message_send_loop_num_' . $ge_tui_message_id)) {
            self::delay(600)->statSendGeTuiKf($ge_tui_message_id);
        } else {
            $ge_tui_message->send_status = SEND_STATUS_SUCCESS;
            $hot_cache->del($success_key);
            $hot_cache->del($fail_key);
        }

        $ge_tui_message->remark = $info;
        $ge_tui_message->update();
    }


    static function asyncSendKf($device_ids, $ge_tui_message_id)
    {
        $hot_cache = self::getHotWriteCache();
        $hot_cache->decr('ge_tui_message_send_loop_num_' . $ge_tui_message_id);

        $ge_tui_message = self::findFirstById($ge_tui_message_id);

        if ($ge_tui_message->send_status == SEND_STATUS_STOP) {
            info('终止任务', $ge_tui_message_id);
            return;
        }

        $push_message = $ge_tui_message->push_message;
        if (!$push_message) {
            info('false 内容为空', $ge_tui_message_id);
            return;
        }

        $hot_cache = self::getHotWriteCache();
        $devices = Devices::findByIds($device_ids);
        $send_count = 0;
        foreach ($devices as $device) {
            if ($device && $ge_tui_message->isPass($device)) {
                $send_count++;
                $is_success = $ge_tui_message->sendGeTui($device, $push_message);
                debug('send', $send_count, $device->id, $is_success);
                if ($is_success) {
                    $success_key = 'send_ge_tui_message_success_num_' . $ge_tui_message_id . '_' . $ge_tui_message->product_channel_id;
                    $hot_cache->incrby($success_key, 1);
                    $hot_cache->expire($success_key, 7200);
                    // stat
                    $ge_tui_message->push_message->sendStat($device);
                } else {
                    $fail_key = 'send_ge_tui_message_fail_num_' . $ge_tui_message_id . '_' . $ge_tui_message->product_channel_id;
                    $hot_cache->incrby($fail_key, 1);
                    $hot_cache->expire($fail_key, 7200);
                }
            } else {
                debug('false pass', $device->id, $ge_tui_message_id);
            }
        }
    }

    function sendGeTui($device, $push_message)
    {

        if ($this->send_status == SEND_STATUS_STOP) {
            info('终止任务', $this->id);
            return false;
        }

        if (!$device->canPush()) {
            info('can_push', $device->id);
            return false;
        }

        if ($device->pushMessage($push_message)) {
            info("send success", $device->id, $this->id, $push_message->id);
            return true;
        }

        return false;
    }

}