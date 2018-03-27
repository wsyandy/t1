<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 17/4/26
 * Time: 下午4:45
 */
trait UserWakeup
{

    function checkOfflineTaskStatus()
    {
        if (!$this->product_channel || $this->product_channel->status != STATUS_ON) {
            return false;
        }

        if ($this->isWxPlatform() && !$this->isSubscribe()) {
            return false;
        }

        if (!$this->isOfflineTaskRunning()) {
            return false;
        }

        if ($this->user_status != USER_STATUS_ON) {
            return false;
        }

        return true;
    }

    // 检测任务是否正在执行
    function isOfflineTaskRunning()
    {

        $hot_cache = Users::getHotReadCache();
        $start_async_offline_task_key = "start_async_offline_task_" . $this->id;
        $last_execute_time = $hot_cache->get($start_async_offline_task_key);
        if (!$last_execute_time) {
            return false;
        }

        return $last_execute_time;
    }

    public function startOfflineTask()
    {

        $hot_cache = Users::getHotWriteCache();
        $start_async_offline_task_key = "start_async_offline_task_" . $this->id;
        $machine_name = php_uname('n');
        $start_val = $machine_name . '|' . time();

        $last_execute_time = $this->isOfflineTaskRunning();
        debug($start_async_offline_task_key, $last_execute_time);

        // 任务超时没进程运行
        if (!$last_execute_time) {

            // 删除执行的任务
            $this->deleteExecutedOfflineTaskIds();
            // 启动任务
            $step_time = 300;
            Users::delay($step_time)->asyncLoopOfflineTask($this->id);

            debug("user_id:{$this->id}, {$this->platform}", $start_async_offline_task_key);
        } else {
            $start_val = $last_execute_time;

            debug("user_id:{$this->id}, {$this->platform}, exist task ", $start_async_offline_task_key);
        }

        // 记录启动标记
        $hot_cache->setex($start_async_offline_task_key, MAX_OFFLINE_TASK_HANG_UP_TIME, $start_val);
    }

    public function quitOfflineTask()
    {

        $hot_cache = Users::getHotWriteCache();
        // 删除启动任务标记
        $start_async_offline_task_key = "start_async_offline_task_{$this->id}";
        $hot_cache->del($start_async_offline_task_key);
        // 删除执行过的任务
        $this->deleteExecutedOfflineTaskIds();
        return true;
    }

    public function offlineTaskStepTime()
    {
        $offline_time = time() - $this->lastLoginAt();
        debug($this->id, 'offline_time', $offline_time);

        if ($offline_time > MAX_OFFLINE_TASK_HANG_UP_TIME) {
            return 60 * 60 * 24;
        }

        // 今天活跃，离线超两小时
        if ($this->last_at && date("Ymd", $this->last_at) == date('Ymd') && time() - $this->last_at > 7200) {
            return 60 * 60;
        }

        return 15 * 60;
    }

    static function asyncLoopOfflineTask($receiver_id)
    {

        $receiver = Users::findFirstById($receiver_id);
        if (!$receiver) {
            return;
        }

        // 不再关注 或者 无任务执行状态
        if (!$receiver->checkOfflineTaskStatus()) {
            // 退出任务
            debug("start_async_offline_task_quit, id:" . $receiver->id);
            $receiver->quitOfflineTask();
            return;
        }

        // 离线时间
        $now_at = time();
        $offline_time = $now_at - $receiver->lastLoginAt();
        $offline_hour = intval($offline_time / (60 * 60));
        $offline_minute = ceil($offline_time / 60);

        // 清除历史任务
        if ($offline_hour >= 48) {
            debug('quit offline task > 48 hour', $receiver->id, 'hour:', $offline_hour);
            $receiver->quitOfflineTask();
            return;
        }

        // 进入下个15分钟循环
        $step_time = $receiver->offlineTaskStepTime();

        debug($receiver->id, $offline_minute, 'step', $step_time);

        // 离线推送 22:30 - 08:00 不推送
        $cur_hour = intval(date('H'));
        if (time() > strtotime(date('Ymd 22:30:00')) || $cur_hour < 8) {
            self::delay($step_time)->asyncLoopOfflineTask($receiver_id);
            return;
        }


        self::delay($step_time)->asyncLoopOfflineTask($receiver_id);

        // 生成任务id
        $task_id = '';
        $wake_minutes = array_keys(PushMessages::$OFFLINE_TIME);
        if ($receiver->isWxPlatform()) {
            $wake_minutes = [60, 24 * 60];
        }

        foreach ($wake_minutes as $minute) {
            // 小于循环时间的一半
            if ($minute && abs($offline_minute - $minute) * 2 * 60 < $step_time) {
                $task_id = "offline_wakeup_task_id_" . $minute . "_minute_" . $receiver->id;
                break;
            }
        }

        if (empty($task_id)) {
            debug('no task', $receiver->id, $offline_time);
            return;
        }

        $is_executed = $receiver->isExecutedOfflineTask($task_id);
        if (!$is_executed) {

            info('running task', $receiver->id, ',task_id:', $task_id, ",min:", $offline_minute);

            // 保存任务
            $receiver->saveExecutedOfflineTaskId($task_id);

            if ($receiver->canPush()) {
                PushMessages::sendMessage($receiver);
            }

        } else {
            info('executed', $receiver->id, ',task_id:', $task_id, ",min:", $offline_minute);
        }
    }

    // 唤醒任务ids
    public function isExecutedOfflineTask($task_id)
    {
        if (!$task_id) {
            return true;
        }

        $key = "executed_offline_task_ids_" . $this->id;
        $hot_cache = Users::getHotWriteCache();
        $task_ids = $hot_cache->get($key);

        debug($this->id, "task_id:", $task_id, " key:", $key, " task_ids:", $task_ids);
        if ($task_ids) {
            $task_ids = json_decode($task_ids, true);
            if (in_array($task_id, $task_ids)) {
                return true;
            }
        }

        // 一天只执行一次
        if (preg_match('/(_60_minute_|_5_minute_)/', $task_id)) {
            $new_key = $key . '_' . date('Ymd');
            $task_ids = $hot_cache->get($new_key);
            if ($task_ids) {
                $task_ids = json_decode($task_ids, true);
                if (in_array($task_id, $task_ids)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function saveExecutedOfflineTaskId($task_id)
    {
        if (!$task_id) {
            return false;
        }

        $key = "executed_offline_task_ids_" . $this->id;
        $hot_cache = Users::getHotWriteCache();
        $task_ids = $hot_cache->get($key);

        debug($this->id, "save task_id:" . $task_id . " key:" . $key . "###", $task_ids);
        if ($task_ids) {
            $task_ids = json_decode($task_ids, true);
            $task_ids[] = $task_id;
        } else {
            $task_ids = array($task_id);
        }

        $hot_cache->setex($key, MAX_OFFLINE_TASK_HANG_UP_TIME, json_encode($task_ids, JSON_UNESCAPED_UNICODE));

        // 一天只发一次
        if (preg_match('/(_60_minute_|_5_minute_)/', $task_id)) {
            $key .= '_' . date('Ymd');
            $today_task_ids = $hot_cache->get($key);
            if ($today_task_ids) {
                $today_task_ids = json_decode($today_task_ids, true);
                $today_task_ids[] = $task_id;
            } else {
                $today_task_ids = [$task_id];
            }

            $end_at = endOfDay() - time();
            $hot_cache->setex($key, $end_at, json_encode($today_task_ids, JSON_UNESCAPED_UNICODE));
        }

        return true;
    }

    public function deleteExecutedOfflineTaskIds()
    {
        //不删除, 一天只发一次的任务

        $key = "executed_offline_task_ids_" . $this->id;
        $hot_cache = Users::getHotWriteCache();
        $hot_cache->del($key);

        if (isDevelopmentEnv()) {
            $key .= '_' . date('Ymd');
            $hot_cache->del($key);
        }

    }

    function pushMessage($push_message)
    {

        if ($this->isClientPlatform()) {
            if ($this->pushMessageGetui($push_message)) {
                return true;
            }

            info('Exce false_push_client', $this->id, $this->getPushContext());
            return false;
        }

        if ($this->isWxPlatform()) {
            $last_at = $this->lastLoginAt();
            if (time() - $last_at < MAX_OFFLINE_TASK_HANG_UP_TIME) {

//                $rand = mt_rand(1, 100);
//
//                if ($rand <= 50 && $push_message->text_content) {
//                    $result = $this->pushMessageKfText($push_message->generateTextContent($this));
//                } else {
//                    $result = $this->pushMessageKf($push_message);
//                }
//
//                if ($result) {
//                    $push_message->sendStat($this);
//                    return true;
//                }

            } else {
                if ($this->pushMessageTemplate($push_message)) {
                    $push_message->sendStat($this);
                    return true;
                }
            }
        }

        return false;
    }

    function pushMessageGetui($push_message)
    {

        $push_url = $push_message->getPushUrl($this);
        if (!$push_url || !$this->push_token) {
            info('false push', $this->id, $push_url, $this->push_token, $push_message->id);
            return false;
        }

        $payload = array('model' => 'user', 'created_at' => time());
        $receiver_context = $this->getPushReceiverContext();
        $push_data = ['title' => $push_message->title, 'body' => $push_message->description,
            'payload' => $payload, 'badge' => 1, 'offline' => true, 'client_url' => $push_url,
            'icon_url' => $push_message->image_url];

        debug($this->id, $this->getPushContext(), $receiver_context, $push_data);

        if (Pushers::push($this->getPushContext(), $receiver_context, $push_data)) {
            return true;
        }

        return false;
    }

    function pushMessageTemplate($push_message)
    {

        $push_url = $push_message->getPushUrl($this);
        if (!$push_url) {
            info('false push_url', $this->id, $push_message->id);
            return false;
        }

        // 模板消息
        $product_channel_material = $push_message->product;
        if (!$product_channel_material) {
            info('false template', $this->id, $this->platform, $push_message->id);
            return false;
        }

        return false;

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
            info('Exce false_template', $this->id, $this->platform, $push_message->id, 'class', get_class($product_channel_material));
            return false;
        }

        if ($data) {
            try {
                $openid = $this->openid;
                $weixin_event = new WeixinEvents($this->product_channel);
                $result = $weixin_event->sendTemplateMessage($openid, $template_short_id, $push_url, $data);
                $result = json_decode($result, true);
                if (0 == $result['errcode']) {
                    info($this->id, $this->product_channel->id, $openid, 'template 发送成功', $result, 'city_id_' . $this->city_id, 'geo_city_id_' . $this->geo_city_id);
                    return true;
                }

                info($this->id, $this->product_channel->id, $openid, 'template 发送失败', $result);

            } catch (\Exception $e) {
                info($this->id, $this->product_channel->id, 'template Exception', $e->getMessage());
            }
        }

        return false;
    }

    function pushMessageKfText($content)
    {
        $product_channel = $this->product_channel;
        $weixin_event = new WeixinEvents($product_channel);
        $openid = $this->openid;

        if ($openid) {
            $result = $weixin_event->sendTextMessage($openid, $content);
            debug($content, $this->id);

            $result = json_decode($result, true);

            if (isset($result['errcode']) && $result['errcode'] == 0) {
                info($this->id, $this->product_channel->id, $openid, $content, 'weixin_kf_text 发送成功', $result, 'city_id_' . $this->city_id, 'geo_city_id_' . $this->geo_city_id);
                return true;
            }

            info($this->id, $this->product_channel->id, $openid, $content, 'weixin_kf_text 发送成功', $result, 'city_id_' . $this->city_id, 'geo_city_id_' . $this->geo_city_id);

            // 退出任务
            if (isset($result['errcode']) && in_array($result['errcode'], array(40003, 45047, 45015, 48002, 48004))) {
                $this->quitOfflineTask();
            }
        }

        return false;
    }

    function pushMessageKf($push_message)
    {

        $push_url = $push_message->getPushUrl($this);
        if (!$push_url) {
            info('Exce false_push_url', $this->id, $push_message->id);
            return false;
        }

        $contents[] = [
            'title' => $push_message->title,
            'description' => $push_message->description,
            'url' => $push_url,
            'picurl' => $push_message->image_url
        ];

        $weixin_event = new WeixinEvents($this->product_channel);
        $openid = $this->openid;
        if ($openid) {

            $result = $weixin_event->sendNewsMessage($openid, $contents);
            $result = json_decode($result, true);

            if (isset($result['errcode']) && $result['errcode'] == 0) {
                info($this->id, $this->product_channel->id, $openid, 'weixin_kf 发送成功', $result, 'city_id_' . $this->city_id, 'geo_city_id_' . $this->geo_city_id);
                return true;
            }

            info($this->id, $this->product_channel->id, $openid, 'weixin_kf 发送成功', $result, 'city_id_' . $this->city_id, 'geo_city_id_' . $this->geo_city_id);

            // 退出任务
            if (isset($result['errcode']) && in_array($result['errcode'], array(40003, 45047, 45015, 48002, 48004))) {
                $this->quitOfflineTask();
            }
        }

        return false;
    }

    static function asyncSendOfflineMessage($device_ids)
    {

        $users = Users::findByIds($device_ids);
        foreach ($users as $user) {

            debug($user->id, 'last_at', $user->last_at);
            if ($user->canPush()) {
                PushMessages::sendMessage($user);
            }
        }
    }

    function canPush()
    {

        if ($this->isWxPlatform()) {

            if (!$this->isSubscribe() || !$this->isNormal()) {
                return false;
            }

            // 屏蔽模板消息
            if (time() - $this->lastLoginAt() > 48 * 3600 && !$this->ip) {
                return false;
            }

            // 广州 深圳 屏蔽
            if ($this->city_id == 192 || $this->city_id == 193) {
                info('false', $this->id, $this->city_id, $this->geo_city_id);
                return false;
            }

            if ($this->geo_city_id == 192 || $this->geo_city_id == 193) {
                info('false', $this->id, $this->city_id, $this->geo_city_id);
                return false;
            }
        }

        if ($this->isClientPlatform() && !$this->push_token) {
            info('false no push_token', $this->id);
            return false;
        }

        return true;
    }

    function pushFriendOnlineRemind()
    {
        $body = "你的{$this->nickname}好友已上线，赶紧去唠唠！";
        $opts = ['title' => '好友上线提醒', 'body' => $body];

        $user_db = Users::getUserDb();
        $friend_key = 'friend_list_user_id_' . $this->id;
        $user_ids = $user_db->zrevrange($friend_key, 0, 1);
        
        if (count($user_ids) > 0) {
            $user = Users::findFirstById($user_ids[0]);

            if ($user) {
                $user->push($opts);
            }
        }
    }

    function pushFollowOnlineRemind()
    {
        $body = "你关注{$this->nickname}已上线，赶紧去唠唠！";
        $opts = ['title' => '关注的人上线提醒', 'body' => $body];

        $user_db = Users::getUserDb();
        $follow_key = 'followed_list_user_id' . $this->id;
        $user_ids = $user_db->zrevrange($follow_key, 0, 1);

        if (count($user_ids) > 0) {
            $user = Users::findFirstById($user_ids[0]);

            if ($user) {
                $user->push($opts);
            }
        }

    }

    function pushFriendIntoRoomRemind()
    {
        $body = "{$this->nickname}开播啦，精彩瞬间别错过！{$this->nickname}开播就想你，不打开看看吗？";
        $client_url = "app://rooms/enter?user_id={$this->id}";
        $opts = ['title' => '好友上线开播提醒', 'body' => $body, 'client_url' => $client_url];

        $per_page = 200;
        $friend_num = $this->friendNum();

        if ($friend_num < 1) {
            info('user_id', $this->id,'friend num is 0');
            return;
        }

        $total_pages = ceil($friend_num / $per_page);
        $user_db = Users::getUserDb();

        for ($page = 1; $page <= $total_pages; $page++) {

            $users = $this->friendList($page, $per_page);

            foreach ($users as $user) {

                $key = 'push_friend_into_room_remind_' . $user->id;
                if ($user_db->setnx($key, $user->id)) {
                    $user_db->expire($key, 60 * 60);

                    info('user_id', $user->id, $opts, 'friend_num', $friend_num);
                    $user->push($opts);
                }

            }
        }

        return;


    }

}