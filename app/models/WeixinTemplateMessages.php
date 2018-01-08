<?php

class WeixinTemplateMessages extends BaseModel
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

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];
    static $SEND_STATUS = [SEND_STATUS_WAIT => '等待发送', SEND_STATUS_SUBMIT => '提交发送', SEND_STATUS_PROGRESS => '发送中', SEND_STATUS_SUCCESS => '发送成功', SEND_STATUS_STOP => '终止发送'];
    static $PLATFORMS = ['weixin_ios' => '微信ios', 'weixin_android' => '微信安卓'];
    static $NEED_FILTER_CONDITIONS = [STATUS_OFF => '否', STATUS_ON => '是'];

    function mergeJson()
    {
        $send_at_text = '';
        if ($this->send_at) {
            $send_at_text = $this->send_at_text;
        }

        $data = ['product_channel_name' => $this->product_channel_name, 'send_at_text' => $send_at_text, 'operator_username' => $this->operator_username];

        return $data;
    }

    function isBindMobilePass($user)
    {
        if (STATUS_ON == $this->bind_mobile && !$user->mobile) {
            return false;
        }

        if (STATUS_OFF == $this->bind_mobile && $user->mobile) {
            return false;
        }

        return true;
    }

    function isProvincePass($user)
    {
        if ($this->province_ids && $user->province_id) {
            return in_array($user->province_id, explode(',', $this->province_ids));
        }

        return true;
    }

    function isPlatformPass($user)
    {
        debug($user->platform, $this->platforms);

        if ($this->platforms && $user->platform) {
            return in_array($user->platform, explode(',', $this->platforms));
        }

        return true;
    }

    function isPass($user)
    {
        if (!$user->isSubscribe() || !$user->isNormal()) {
            return false;
        }

        if (!$this->isPlatformPass($user)) {
            return false;
        }

        if (!$this->isProvincePass($user)) {
            return false;
        }

        return true;
    }

    static function crontabSendKf($weixin_template_message_id)
    {

        $weixin_template_message = self::findFirstById($weixin_template_message_id);
        $product_channel = $weixin_template_message->product_channel;
        if ($product_channel->status != STATUS_ON) {
            return;
        }

        $group_key = 'weixin_users_active_group_' . $product_channel->id;
        $hot_cache = Users::getHotWriteCache();
        // 7 - 15
        list($offline_start_day, $offline_end_day) = explode('-', $weixin_template_message->offline_day);

        $begin_of_day = time() - $offline_end_day * 60 * 60 * 24;
        $begin_of_day = beginOfDay($begin_of_day);
        $end_of_day = time() - $offline_start_day * 60 * 60 * 24;
        $end_of_day = endOfDay($end_of_day);

        debug($offline_start_day, $offline_end_day, date('Ymd', $begin_of_day), date('Ymd', $end_of_day));

        $user_ids = $hot_cache->zrangebyscore($group_key, $begin_of_day, $end_of_day, array('limit' => array(0, 1000000)));
        $total_num = count($user_ids);

        // 发送记录
        $weixin_template_message->send_at = time();
        $weixin_template_message->send_status = SEND_STATUS_PROGRESS;
        $weixin_template_message->remark = '1小时后查看发送统计，预估发送人数: ' . $total_num;
        $weixin_template_message->update();

        info($weixin_template_message_id, $group_key, 'total_user_count', $total_num);

        $per_page = 200;
        $loop_num = ceil($total_num / $per_page);
        if ($loop_num < 1) {
            self::delay(10)->statSendWeixinKfTemplate($weixin_template_message_id);
            return;
        }

        $hot_cache->setex('weixin_template_message_send_loop_num_' . $weixin_template_message_id, 60 * 60 * 2, $loop_num);

        $offset = 0;
        $max_delay_at = 0;
        for ($i = 0; $i < $loop_num; $i++) {
            $slice_ids = array_slice($user_ids, $offset, $per_page);
            $offset += $per_page;

            $delay_at = mt_rand(1, 3000);
            if (isDevelopmentEnv()) {
                $delay_at = 1;
            }

            if ($max_delay_at < $delay_at) {
                $max_delay_at = $delay_at;
            }
            debug('page', $i, 'offset', $offset, $total_num);

            self::delay($delay_at)->asyncSendKf($slice_ids, $weixin_template_message_id);
        }

        $max_delay_at += 300;
        info("统计statSendWeixinKfTemplate", $max_delay_at, $product_channel->id, $weixin_template_message_id);
        self::delay($max_delay_at)->statSendWeixinKfTemplate($weixin_template_message_id);
    }

    static function statSendWeixinKfTemplate($weixin_template_message_id)
    {

        $weixin_template_message = WeixinTemplateMessages::findFirstById($weixin_template_message_id);
        $product_channel_id = $weixin_template_message->product_channel_id;

        $hot_cache = self::getHotReadCache();
        $success_key = 'send_weixin_template_message_success_num_' . $weixin_template_message_id . '_' . $product_channel_id;
        $success_num = $hot_cache->get($success_key);
        $fail_key = 'send_weixin_template_message_fail_num_' . $weixin_template_message_id . '_' . $product_channel_id;
        $fail_num = $hot_cache->get($fail_key);
        $send_num = $success_num + $fail_num;
        $success_rate = 0;
        if ($send_num) {
            $success_rate = intval($success_num * 100 / $send_num);
        }

        $info = "发送人数:{$send_num}, 成功人数:{$success_num}, 失败人数:{$fail_num}, 成功率:{$success_rate}";
        info($weixin_template_message->id, $info);

        if ($hot_cache->get('weixin_template_message_send_loop_num_' . $weixin_template_message_id)) {
            self::delay(600)->statSendWeixinKfTemplate($weixin_template_message_id);
        } else {
            $weixin_template_message->send_status = SEND_STATUS_SUCCESS;
            $hot_cache->del($success_key);
            $hot_cache->del($fail_key);
        }

        $weixin_template_message->remark = $info;
        $weixin_template_message->update();
    }


    static function asyncSendKf($user_ids, $weixin_template_message_id)
    {


        $hot_cache = self::getHotWriteCache();
        $hot_cache->decr('weixin_template_message_send_loop_num_' . $weixin_template_message_id);

        $weixin_template_message = self::findFirstById($weixin_template_message_id);
        if ($weixin_template_message->send_status == SEND_STATUS_STOP) {
            info('终止任务', $weixin_template_message_id);
            return;
        }

        $template_data = $weixin_template_message->getTemplateData();
        if (!$template_data) {
            info('false 内容为空', $weixin_template_message_id);
            return;
        }

        $product_channel_material = $weixin_template_message->getProductChannelMaterial($weixin_template_message->product_channel);
        $need_filter_conditions = $weixin_template_message->need_filter_conditions;

        info($weixin_template_message_id, $template_data, $product_channel_material, $need_filter_conditions);

        $hot_cache = self::getHotWriteCache();
        $users = Users::findByIds($user_ids);
        $send_count = 0;
        foreach ($users as $user) {
            if ($user && $weixin_template_message->isPass($user)) {

                if ($need_filter_conditions && $product_channel_material->filterConditions($user)) {
                    info("weixin_template_filter_conditions", $product_channel_material->id, $user->id, $need_filter_conditions);
                    continue;
                }

                $send_count++;
                $is_success = $weixin_template_message->sendTemplate($user, $template_data);
                debug('send', $send_count, $user->id, $is_success);
                if ($is_success) {
                    $success_key = 'send_weixin_template_message_success_num_' . $weixin_template_message_id . '_' . $weixin_template_message->product_channel_id;
                    $hot_cache->incrby($success_key, 1);
                    $hot_cache->expire($success_key, 7200);
                    // stat
                    $weixin_template_message->push_message->sendStat($user);
                } else {
                    $fail_key = 'send_weixin_template_message_fail_num_' . $weixin_template_message_id . '_' . $weixin_template_message->product_channel_id;
                    $hot_cache->incrby($fail_key, 1);
                    $hot_cache->expire($fail_key, 7200);
                }
            } else {
                debug('false pass', $user->id, $weixin_template_message_id);
            }
        }
    }

    //获取对应的产品
    function getProductChannelMaterial()
    {
        $push_message = $this->push_message;
        // 模板消息
        $product_channel_material = $push_message->getProductChannelMaterial($this->product_channel);

        return $product_channel_material;
    }

    //模板编号：OPENTM410241677
    //模板名称：贷款申请通知
    function getTemplateData()
    {

        $push_message = $this->push_message;
        // 模板消息
        $product_channel_material = $push_message->getProductChannelMaterial($this->product_channel);
        if (!$product_channel_material) {
            info('false template product_channel_material', $this->id, $push_message->id);
            return null;
        }

        $push_url = $push_message->getWeixinPushUrlByProductChannel($this->product_channel);
        if (!$push_url) {
            info('false template push_url', $this->id, $push_message->id);
            return null;
        }

        $template_short_id = '';
        $data = [];
        if (is_a($product_channel_material, 'Products')) {
            $template_short_id = 'OPENTM410241677';
            $period = $product_channel_material->period_min . '-' . $product_channel_material->period_max . $product_channel_material->period_type_text;
            $amount = $product_channel_material->amount_min . '-' . $product_channel_material->amount_max;
            $data = [
                'first' => ['value' => $push_message->title, 'color' => '#459ae9'],
                'keyword1' => ['value' => $product_channel_material->name, 'color' => '#459ae9'],
                'keyword2' => ['value' => date('Y-m-d H:i:s'), 'color' => '#459ae9'],
                'keyword3' => ['value' => $amount, 'color' => '#459ae9'],
                'keyword4' => ['value' => $period, 'color' => '#459ae9'],
                'remark' => ['value' => $push_message->description, 'color' => '#459ae9']
            ];

        } else {
            info('false template', $this->id, $push_message->id, 'class', get_class($product_channel_material));
            return null;
        }

        return [$template_short_id, $push_url, $data];
    }

    function sendTemplate($user, $template_data)
    {

        if ($this->send_status == SEND_STATUS_STOP) {
            info('终止任务', $this->id);
            return false;
        }

        if (!$user->canPush()) {
            info('false can_push', $user->id);
            return false;
        }

        $openid = $user->openid;
        if ($template_data && $openid) {
            try {
                $weixin_event = new WeixinEvents($user->product_channel);
                $result = $weixin_event->sendTemplateMessage($openid, $template_data[0], $template_data[1], $template_data[2]);
                $result = json_decode($result, true);
                if (0 == $result['errcode']) {
                    info($user->id, $user->product_channel->code, $openid, 'template 发送成功', $result);
                    return true;
                } else {
                    info($user->id, $user->product_channel->code, $openid, 'template 发送失败', $result);
                }

            } catch (\Exception $e) {
                info($user->id, $user->product_channel->code, 'template Exception', $e->getMessage());
            }
        }

        return false;
    }

}