<?php

class WeixinKefuMessages extends BaseModel
{

    /**
     * @type ProductChannels
     */
    private $_product_channel;
    /**
     * @type Operators
     */
    private $_operator;

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $SEND_STATUS = [SEND_STATUS_WAIT => '等待发送', SEND_STATUS_SUBMIT => '提交发送', SEND_STATUS_PROGRESS => '发送中', SEND_STATUS_SUCCESS => '发送成功', SEND_STATUS_STOP => '终止发送'];

    function mergeJson()
    {
        $send_at_text = '';
        if ($this->send_at) {
            $send_at_text = $this->send_at_text;
        }

        $data = [
            'product_channel_name' => $this->product_channel_name,
            'send_at_text' => $send_at_text,
            'push_message_num' => $this->push_message_num,
            'operator_username' => $this->operator_username];

        return $data;
    }

    function getPushMessageNum()
    {
        $push_message_ids = [];
        if ($this->push_message_ids) {
            $push_message_ids = explode(',', $this->push_message_ids);
        }
        return count($push_message_ids);
    }

    function isProvincePass($user)
    {
        if ($this->province_ids && $user->province_id) {
            return in_array($user->province_id, explode(',', $this->province_ids));
        }

        return true;
    }

    function isPass($user)
    {
        if (!$user->isSubscribe() || !$user->isNormal()) {
            return false;
        }

        $event_at = $user->event_at;
        if (time() - $event_at > 48 * 60 * 60) {
            return false;
        }

        if (!$this->isProvincePass($user)) {
            return false;
        }

        return true;
    }

    public function getNews($product_channel)
    {
        $push_message_ids = $this->push_message_ids;
        $push_message_ids = explode(',', $push_message_ids);
        $push_messages = \PushMessages::findByIds($push_message_ids);

        $contents = [];
        foreach ($push_messages as $push_message) {

            $url = $push_message->getWeixinPushUrlByProductChannel($product_channel);
            if (!$url) {
                continue;
            }

            $content = ['title' => $push_message->title,
                'description' => $push_message->description,
                'url' => $url,
                'picurl' => $push_message->getImageUrl()];

            $contents[] = $content;
        }

        return $contents;
    }

    static function crontabSendKf($weixin_kefu_message_id)
    {

        $weixin_kefu_message = WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        $product_channel_id = $weixin_kefu_message->product_channel_id;
        $weixin_kefu_message->send_at = time();
        $weixin_kefu_message->send_status = SEND_STATUS_PROGRESS;
        $weixin_kefu_message->remark = '1小时后查看发送统计';
        $weixin_kefu_message->update();

        $group_key = 'weixin_users_active_group_' . $product_channel_id;
        $hot_cache = Users::getHotWriteCache();
        $begin_of_day = time() - 60 * 60 * 48;
        $end_of_day = time();

        $user_ids = $hot_cache->zrangebyscore($group_key, $begin_of_day, $end_of_day, array('limit' => array(0, 1000000)));
        $total_num = count($user_ids);
        info($weixin_kefu_message_id, $group_key, 'total_user_count', $total_num);
        $per_page = 200;
        $loop_num = ceil($total_num / $per_page);

        if ($loop_num < 1) {
            \WeixinKefuMessages::delay(10)->statSendWeixinKfMessage($weixin_kefu_message_id);
            return;
        }

        $hot_cache->setex('weixin_kefu_message_send_loop_num_' . $weixin_kefu_message_id, 60 * 60 * 2, $loop_num);
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

            \WeixinKefuMessages::delay($delay_at)->asyncSendKf($slice_ids, $weixin_kefu_message_id);
        }

        $delay_at = $max_delay_at + 300;
        \WeixinKefuMessages::delay($delay_at)->statSendWeixinKfMessage($weixin_kefu_message_id);
    }

    static function statSendWeixinKfMessage($weixin_kefu_message_id)
    {

        $weixin_kefu_message = WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        $product_channel_id = $weixin_kefu_message->product_channel_id;

        $hot_cache = self::getHotWriteCache();

        $success_key = 'send_weixin_kefu_message_success_num_' . $weixin_kefu_message_id . '_' . $product_channel_id;
        $success_num = $hot_cache->get($success_key);
        $fail_key = 'send_weixin_kefu_message_fail_num_' . $weixin_kefu_message_id . '_' . $product_channel_id;
        $fail_num = $hot_cache->get($fail_key);

        $send_num = $success_num + $fail_num;
        $success_rate = 0;
        if ($send_num) {
            $success_rate = intval($success_num * 100 / $send_num);
        }

        $info = "发送人数:{$send_num}, 成功人数:{$success_num}, 失败人数:{$fail_num}, 成功率:{$success_rate}";
        info($weixin_kefu_message->id, $info);

        if ($hot_cache->get('weixin_kefu_message_send_loop_num_' . $weixin_kefu_message_id)) {
            self::delay(600)->statSendWeixinKfMessage($weixin_kefu_message_id);
        } else {
            $weixin_kefu_message->send_status = SEND_STATUS_SUCCESS;
            $hot_cache->del($success_key);
            $hot_cache->del($fail_key);
        }

        $weixin_kefu_message->remark = $info;
        $weixin_kefu_message->update();
    }

    static function asyncSendKf($user_ids, $weixin_kefu_message_id)
    {

        $hot_cache = self::getHotWriteCache();
        $hot_cache->decr('weixin_kefu_message_send_loop_num_' . $weixin_kefu_message_id);

        $new_user_ids = $user_ids;
        if (!is_array($user_ids)) {
            $new_user_ids = [$user_ids];
        }

        $weixin_kefu_message = WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        if ($weixin_kefu_message->send_status == SEND_STATUS_STOP) {
            info('终止任务', $weixin_kefu_message_id);
            return;
        }

        $contents = $weixin_kefu_message->getNews($weixin_kefu_message->product_channel);
        if (!$contents) {
            info('内容为空', $weixin_kefu_message_id);
            return;
        }

        info($weixin_kefu_message_id, $contents);

        $users = Users::findByIds($new_user_ids);
        foreach ($users as $user) {
            if ($user && $weixin_kefu_message->isPass($user)) {
                $is_success = $weixin_kefu_message->sendKf($user, $contents);
                $hot_cache = self::getHotWriteCache();
                if ($is_success) {
                    $success_key = 'send_weixin_kefu_message_success_num_' . $weixin_kefu_message_id . '_' . $weixin_kefu_message->product_channel_id;
                    $hot_cache->incrby($success_key, 1);
                    $hot_cache->expire($success_key, 7200);
                } else {
                    $fail_key = 'send_weixin_kefu_message_fail_num_' . $weixin_kefu_message_id . '_' . $weixin_kefu_message->product_channel_id;
                    $hot_cache->incrby($fail_key, 1);
                    $hot_cache->expire($fail_key, 7200);
                }

            } else {
                debug('pass false', $user->id);
            }
        }

    }

    function sendKf($user, $contents)
    {

        if ($this->send_status == SEND_STATUS_STOP) {
            info('终止任务', $this->id);
            return false;
        }

        $product_channel = $this->product_channel;
        if ($product_channel->status != STATUS_ON) {
            return false;
        }

        if (!$user->canPush()) {
            debug('false can push', $user->id);
            return false;
        }

        $weixin_event = new WeixinEvents($product_channel);
        $openid = $user->openid;
        $send_result = null;
        try {

            $send_result = $weixin_event->sendNewsMessage($openid, $contents);
            $send_result = json_decode($send_result, true);
            if ($send_result['errcode'] == 0) {
                info($user->id, $user->product_channel->code, $openid, '发送成功');
                return true;
            }

            info($user->id, $user->product_channel->code, $openid, '发送失败!, errcode:', $send_result['errcode']);
            return false;

        } catch (\Exception $e) {
            info('Exception', $e->getMessage());
            return false;
        }
    }

}