<?php

class SmsHistories extends BaseModel
{

    /**
     * @type SmsChannels
     */
    private $_sms_channel;

    /**
     * @type ProductChannels
     */
    private $_product_channel;

    static $SEND_STATUS = [SMS_HISTORY_SEND_STATUS_WAIT => '等待',
        SMS_HISTORY_SEND_STATUS_SUCCESS => '成功',
        SMS_HISTORY_SEND_STATUS_FAIL => '失败'
    ];

    static $AUTH_STATUS = [SMS_HISTORY_AUTH_STATUS_WAIT => '等待验证',
        SMS_HISTORY_AUTH_STATUS_SUCCESS => '验证成功'
    ];

    // 营销短信页面模板
    static $MARKET_PAGE_TEMPLATES = ['wakeup', 'sms', 'wakeup2', 'wakeup3'];

    function beforeCreate()
    {
        $this->sms_token = md5(uniqid() . 'sms_token' . $this->content);
    }

    static function asyncSendMarket($mobile, $sms_channel, $content)
    {

        if (is_numeric($sms_channel)) {
            $sms_channel = SmsChannels::findFirstById($sms_channel);
        }

        // 指定发送通道
        if ($sms_channel) {
            // 指定签名
            $signature = $sms_channel->signature;
            $signature = trim($signature);
            $context = ['content' => $content];
            $result = $sms_channel->sendSms($mobile, $signature, $context);

            info('result', $mobile, 'sms_channel_' . $sms_channel->id, $sms_channel->clazz, $signature, $result, $content);

            $status = fetch($result, 'send_status');
            if ($status == SMS_HISTORY_SEND_STATUS_SUCCESS) {
                return true;
            }
        }

        return false;
    }

    static function marketChannelStat($sms_channel, $stat_at)
    {

        if (!is_numeric($stat_at)) {
            $stat_at = strtotime($stat_at);
        }

        $device_db = Devices::getDeviceDb();
        $channel_send_success_key = 'sms_wakeup_success_' . date('Ymd', $stat_at) . '_sms_channel_' . $sms_channel->id;
        $channel_send_fail_key = 'sms_wakeup_fail_' . date('Ymd', $stat_at) . '_sms_channel_' . $sms_channel->id;
        $channel_auth_key = 'sms_wakeup_auth_success_' . date('Ymd', $stat_at) . '_sms_channel_' . $sms_channel->id;

        debug($channel_send_success_key);

        $success_num = $device_db->zcard($channel_send_success_key);
        $fail_num = $device_db->zcard($channel_send_fail_key);
        $auth_num = $device_db->zcard($channel_auth_key);
        $send_num = $success_num + $fail_num;
        $rate = 0;
        if ($success_num) {
            $rate = sprintf("0.3f", $auth_num / $success_num);
        }
        $amount = $send_num * 0.05;

        return [$send_num, $success_num, $auth_num, $rate, $amount];
    }


    static function sendAuthCode($product_channel, $mobile, $sms_type, $opts = [])
    {

        if (!isMobile($mobile)) {
            return [ERROR_CODE_FAIL, '手机号码不正确', ''];
        }

        //测试手机号
        if ($mobile == '13912345678') {
            return [ERROR_CODE_SUCCESS, '发送成功', 'test'];
        }

        $user_id = fetch($opts, 'user_id');
        $device_id = fetch($opts, 'device_id');
        $cache_key = '';
        if ($user_id) {
            $cache_key = 'send_auth_code_user_' . $user_id;
        }
        if ($device_id) {
            $cache_key = 'send_auth_code_device_' . $device_id;
        }

        $hot_cache = self::getHotWriteCache();

        if ($cache_key) {
            $num = $hot_cache->incr($cache_key);
            debug($cache_key, $num, $opts);

            if (isProduction()) {
                if ($num > 5) {
                    info('new_log Block', $product_channel->name, $mobile, $opts);
                    return [ERROR_CODE_FAIL, '请求验证码频繁', ''];
                }
                if ($num == 1) {
                    $hot_cache->expire($cache_key, 12 * 60 * 60);
                }
            } else {
                if ($num > 15) {
                    info('new_log Block', $product_channel->name, $mobile, $opts);
                    return [ERROR_CODE_FAIL, '请求验证码频繁', ''];
                }
                $hot_cache->expire($cache_key, 3 * 60);
            }
        }

        $sms_histories = self::find([
            'conditions' => 'product_channel_id = :product_channel_id: and mobile = :mobile: and sms_type = :sms_type:',
            'bind' => ['product_channel_id' => $product_channel->id, 'mobile' => $mobile, 'sms_type' => $sms_type],
            'order' => 'id desc',
            'limit' => 10
        ]);
        $send_num = count($sms_histories);
        if ($send_num > 0 && isProduction()) {
            $last_sms_history = current($sms_histories);
            if (time() - $last_sms_history->created_at <= 60) {
                return [ERROR_CODE_FAIL, '手机验证太频繁，稍后再试', ''];
            }
            if ($send_num > 5) {
                $median_created_at = $sms_histories[intval($send_num / 2)]->created_at;
                if (time() - $median_created_at < 60 * 60 * 12) {
                    return [ERROR_CODE_FAIL, '手机验证太频繁，稍后再试', ''];
                }
            }
        }


        $auth_code = "";
        for ($i = 0; $i < 4; $i++) {
            $auth_code .= strval(mt_rand(0, 9));
        }

        $sms_history = new \SmsHistories();
        $sms_history->product_channel_id = $product_channel->id;
        $sms_history->mobile = $mobile;
        $sms_history->auth_status = SMS_HISTORY_AUTH_STATUS_WAIT;
        $sms_history->send_status = SMS_HISTORY_SEND_STATUS_WAIT;

        // 短信模板
        //$auth_sms_content = $product_channel->auth_sms;
        $auth_sms_content = '验证码: %s，有效时间10分钟';

        $sms_history->content = sprintf($auth_sms_content, $auth_code);
        $sms_history->context = json_encode(['auth_code' => $auth_code, 'timeout' => 10]);
        $sms_history->expired_at = time() + 10 * 60;
        $sms_history->sms_type = $sms_type;

        $sms_history->syncSendAuthCode();

        $sms_history->save();

        $auth_type = fetch($opts, 'auth_type');
        info('new_log', $mobile, $auth_code, $sms_history->sms_token, $product_channel->name, $auth_type, $opts);

        return [ERROR_CODE_SUCCESS, '发送成功', $sms_history->sms_token];

    }

    //$error_reason = [-1 => '已超时,请重新获取验证码', -2 => "已超时,请重新获取验证码", -3 => '已超时,请重新获取验证码',
    //-4 => '已超时,请重新获取验证码!', -5 => '验证码错误'
    //];
    static function checkAuthCode($product_channel, $mobile, $auth_code, $sms_token, $opts = [])
    {

        //单独验证
        if ($mobile == '13912345678' && $auth_code == '1234') {
            return [ERROR_CODE_SUCCESS, '验证成功'];
        }

        $hot_cache = self::getHotWriteCache();

        $auth_type = fetch($opts, 'auth_type');
        if (!$auth_code || !$sms_token) {
            info('new_log no_sms_token', $mobile, $auth_code, $sms_token, $product_channel->name, $auth_type, $opts);
            return [ERROR_CODE_FAIL, '已超时,请重新获取验证码'];
        }

        $sms_history = \SmsHistories::findFirst([
            'conditions' => 'product_channel_id = :product_channel_id: and mobile =:mobile: and sms_type = :sms_type:',
            'bind' => ['product_channel_id' => $product_channel->id, 'mobile' => $mobile, 'sms_type' => 'login'],
            'order' => 'id desc'
        ]);

        if (!$sms_history || $sms_history->sms_token !== $sms_token) {
            info('new_log sms_token', $mobile, $auth_code, $sms_token, $product_channel->name, $auth_type, $opts);
            return [ERROR_CODE_FAIL, '已超时,请重新获取验证码'];
        }

        if ($sms_history->expired_at < time()) {
            info('new_log expired_at', $mobile, $auth_code, $sms_token, $product_channel->name, $auth_type, $opts);
            return [ERROR_CODE_FAIL, '已超时,请重新获取验证码'];
        }

        $cache_key = 'sms_history_try_num_' . $sms_history->id;
        $num = $hot_cache->incr($cache_key);
        if ($num > 3 && isProduction()) {
            info('new_log Block_try_num', $cache_key, $mobile, $product_channel->name, $auth_type, $opts);
            return [ERROR_CODE_FAIL, '已超时,请重新获取验证码'];
        }
        $hot_cache->expire($cache_key, 15 * 60);

        if (SMS_HISTORY_SEND_STATUS_WAIT != $sms_history->auth_status) {

            $result = false;
            $context = json_decode($sms_history->context, true);
            if (strval($context['auth_code']) === $auth_code) {
                $result = true;
            }

            if ($result) {
                info('new_log 双击', $mobile, $auth_code, $sms_token, $sms_history->id, $product_channel->name, $auth_type, $opts);
                return [ERROR_CODE_SUCCESS, '验证成功'];
            }

            info('new_log auth_status', $mobile, $auth_code, $sms_token, $sms_history->id, $product_channel->name, $auth_type, $opts);

            return [ERROR_CODE_FAIL, '已超时,请重新获取验证码!'];
        }

        $result = false;
        $context = json_decode($sms_history->context, true);
        if (strval($context['auth_code']) === $auth_code) {
            $result = true;
        }

        if ($result) {
            $sms_history->auth_status = SMS_HISTORY_AUTH_STATUS_SUCCESS;
            $sms_history->save();
            return [ERROR_CODE_SUCCESS, '验证成功'];
        }

        info('new_log auth_status_false', $mobile, $auth_code, $sms_token, $sms_history->id, $product_channel->name, $auth_type, $opts);

        return [ERROR_CODE_FAIL, '验证码错误'];
    }

    function syncSendAuthCode()
    {

        // 指定发送通道
        $sms_channel = $this->sms_channel;
        if (!$sms_channel) {
            $mobile_operator = mobileOperator($this->mobile);
            $sms_channels = \SmsChannels::findAvailables($this->product_channel->id, $mobile_operator, $this->sms_type);
        } else {
            $sms_channels = [$sms_channel];
        }

        foreach ($sms_channels as $sms_channel) {

            // 指定签名
            $signature = $sms_channel->signature;
            $signature = trim($signature);
            if (!$signature) {
                $signature = $this->product_channel->sms_sign;
            }

            $context = ['content' => $this->content];
            if ($sms_channel->template) {
                $context = json_decode($this->context, true);
            }

            $result = $sms_channel->sendSms($this->mobile, $signature, $context);

            info('result', $this->product_channel_id, $this->mobile, $sms_channel->id, $sms_channel->clazz, $sms_channel->mobile_operator_text, $signature, $result);

            $status = fetch($result, 'send_status');
            $response = fetch($result, 'response');
            $this->sms_channel_id = $sms_channel->id;
            if ($status) {
                $this->send_status = SMS_HISTORY_SEND_STATUS_SUCCESS;
            } else {
                $this->send_status = SMS_HISTORY_SEND_STATUS_FAIL;
            }

            if ($this->send_status == SMS_HISTORY_SEND_STATUS_SUCCESS) {
                // 发送成功
                break;
            }
        }
    }

    function asyncAfterCreate()
    {

        if ($this->send_status != SMS_HISTORY_SEND_STATUS_WAIT) {
            return;
        }

        // 指定发送通道
        $sms_channel = $this->sms_channel;
        if (!$sms_channel) {
            $mobile_operator = mobileOperator($this->mobile);
            $sms_channels = \SmsChannels::findAvailables($this->product_channel->id, $mobile_operator, $this->sms_type);
        } else {
            $sms_channels = [$sms_channel];
        }

        foreach ($sms_channels as $sms_channel) {

            // 指定签名
            $signature = $sms_channel->signature;
            $signature = trim($signature);
            if (!$signature) {
                $signature = $this->product_channel->sms_sign;
            }

            $context = ['content' => $this->content];
            if ($sms_channel->template) {
                $context = json_decode($this->context, true);
            }

            $result = $sms_channel->sendSms($this->mobile, $signature, $context);

            info('result', $this->product_channel_id, $this->mobile, $sms_channel->id, $sms_channel->clazz, $sms_channel->mobile_operator_text, $signature, $result);

            $status = fetch($result, 'send_status');
            $response = fetch($result, 'response');
            $this->sms_channel_id = $sms_channel->id;
            if ($status) {
                $this->send_status = SMS_HISTORY_SEND_STATUS_SUCCESS;
            } else {
                $this->send_status = SMS_HISTORY_SEND_STATUS_FAIL;
            }

            if ($this->send_status == SMS_HISTORY_SEND_STATUS_SUCCESS) {
                // 发送成功
                break;
            }
        }

        $this->save();
    }


}