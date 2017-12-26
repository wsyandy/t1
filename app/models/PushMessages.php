<?php

class PushMessages extends BaseModel
{
    /**
     * @type Products
     */
    private $_product;

    static $files = ['image' => APP_NAME . '/push_messages/image/%s'];

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $OFFLINE_TIME = [0 => '不限', 5 => '离线5分钟', 60 => '离线1小时', 12 * 60 => '离线12小时', 24 * 60 => '离线24小时',
        36 * 60 => '离线36小时', 44 * 60 => '离线44小时', 72 * 60 => '离线72小时', 7 * 24 * 60 => '离线1周',
        14 * 24 * 60 => '离线2周', 21 * 24 * 60 => '离线3周', 30 * 24 * 60 => '离线1月'];

    static $PLATFORMS = ['' => '不限', 'client_ios' => '客户端ios', 'client_android' => '客户端安卓', 'weixin_ios' => '微信ios',
        'weixin_android' => '微信安卓'];


    function afterCreate()
    {
    }

    function mergeJson()
    {
        return ['image_url' => $this->getImageUrl(), 'image_small_url' => $this->getImageSmallUrl()];
    }

    function getImageUrl()
    {
        $url = StoreFile::getUrl($this->image);
        return $url;
    }

    function getImageSmallUrl()
    {
        $url = StoreFile::getUrl($this->image);
        $url .= "@!small";
        return $url;
    }

    /**
     * @param $receiver
     * @return bool
     */
    function canSend($receiver)
    {

        if ($this->status != STATUS_ON) {
            return false;
        }

        if (!$this->getPushUrl($receiver)) {
            return false;
        }

        return true;
    }

    function isRepeat($receiver)
    {

        $clazz = get_class($receiver);
        $push_key = 'send_push_messages_' . strtolower($clazz) . '_' . $receiver->id;
        $hot_cache = PushMessages::getHotWriteCache();
        $score = $hot_cache->zscore($push_key, $this->id);
        if ($score) {
            return true;
        }

        return false;
    }

    static function calOfflineTime($receiver)
    {

        // 离线时间
        $now_at = time();
        $offline_time = $now_at - $receiver->lastLoginAt();
        $offline_minute = ceil($offline_time / 60);

        $step_time = $receiver->offlineTaskStepTime();
        foreach (self::$OFFLINE_TIME as $minute => $v) {
            // 小于循环时间的一半
            if ($minute && abs($offline_minute - $minute) * 2 * 60 < $step_time) {
                debug($receiver->id, get_class($receiver), $offline_minute, $step_time, $minute);
                return $minute;
            }
        }

        return 0;
    }

    static function findMessages($receiver)
    {

        if ($receiver->isClientPlatform()) {
            $platform = '%client_' . $receiver->platform . '%';
        } else {
            $platform = '%' . $receiver->platform . '%';
        }

        $offline_minute = self::calOfflineTime($receiver);
        $conditions[] = ' offline_time=:offline_time: or offline_time=0 ';
        $bind['offline_time'] = $offline_minute;

        $conditions[] = " (platforms like :platform: or platforms like '') ";
        $bind['platform'] = $platform;

        $conditions[] = ' (product_channel_ids like :product_channel_id: or product_channel_ids = "") ';
        $bind['product_channel_id'] = '%,' . $receiver->product_channel_id . ',%';

        $conditions[] = ' status=:status: ';
        $bind['status'] = STATUS_ON;

        $conds['conditions'] = implode(' and ', $conditions);
        $conds['bind'] = $bind;
        $conds['order'] = 'offline_time desc, rank desc';

        debug($receiver->id, get_class($receiver), $conds);
        // rank 倒序, 剔重，循环
        $push_messages = PushMessages::find($conds);

        return $push_messages;
    }

    static function sendMessage($receiver)
    {

        $clazz = get_class($receiver);
        $push_key = 'send_push_messages_' . strtolower($clazz) . '_' . $receiver->id;
        $hot_cache = PushMessages::getHotWriteCache();

        $push_messages = self::findMessages($receiver);

        $repeat_push_messages = [];
        foreach ($push_messages as $push_message) {
            // 微信客服接口可能发送多条
            if ($push_message->canSend($receiver)) {

                if ($push_message->isRepeat($receiver)) {
                    $repeat_push_messages[] = $push_message;
                } else {

                    $hot_cache->zadd($push_key, time(), $push_message->id);
                    $hot_cache->expire($push_key, 30 * 24 * 60 * 60);
                    debug('match message', $clazz, $receiver->id, $push_message->id);
                    $receiver->pushMessage($push_message);
                    return;
                }
            }
        }

        // 重发
        if ($repeat_push_messages) {
            $hot_cache->del($push_key);
            foreach ($repeat_push_messages as $key => $repeat_push_message) {
                if ($repeat_push_message->canSend($receiver)) {
                    $hot_cache->zadd($push_key, time(), $repeat_push_message->id);
                    $hot_cache->expire($push_key, 30 * 24 * 60 * 60);
                    $receiver->pushMessage($repeat_push_message);
                    return;
                }
            }
        }
    }

    function getPushUrl($receiver)
    {

        $push_url = $this->url;
        if ($push_url) {
            return $push_url;
        }

        $product = Products::findFirstById($this->product_id);
        if (!$product) {
            info('productl not exists', $this->product_id);
            return null;
        }

        if ($product->filterConditions($receiver)) {
            info('continue filter_conditions', $product->id);
            return null;
        }

        if ($receiver->isClientPlatform()) {
            $push_url = "app://products/detail?id=" . $product->id;
        } elseif ($receiver->isWxPlatform()) {
            $domain = $receiver->product_channel->weixin_domain;
            $push_url = self::getProtocol() . $domain . '/wx/products/detail?id=' . $product->id;
        } elseif ($receiver->isTouchPlatform()) {
            $domain = $receiver->product_channel->touch_domain;
            $push_url = self::getProtocol() . $domain . '/touch/products/detail?id=' . $product->id;
        }

        if (!$push_url) {
            return '';
        }

        if ($push_url && preg_match('/\?/', $push_url)) {
            $push_url .= '&tn=' . $this->tracker_no;
        } else {
            $push_url .= '?tn=' . $this->tracker_no;
        }

        info('ok', $this->url, $receiver->id, $receiver->platform, $push_url);

        return $push_url;
    }

    static function getProtocol()
    {
        $protocol = "http://";
//        if (isProduction()) {
//            $protocol = "https://";
//        }

        return $protocol;
    }

    private function stat($user, $type)
    {
        return;
        $clazz = get_class($user);
        $clazz = strtolower($clazz);
        $day = date("Ymd");
        $device_db = \Devices::getDeviceDb();
        $cache_keys[] = 'stat_' . $day . '_' . $type . '_' . $clazz . '_push_message_id' . $this->id . '_product_channel_id-1_platform-1';
        $cache_keys[] = 'stat_' . $day . '_' . $type . '_' . $clazz . '_push_message_id' . $this->id . '_product_channel_id' . $user->product_channel_id . '_platform-1';
        $cache_keys[] = 'stat_' . $day . '_' . $type . '_' . $clazz . '_push_message_id' . $this->id . '_product_channel_id-1_platform' . $user->platform;
        $cache_keys[] = 'stat_' . $day . '_' . $type . '_' . $clazz . '_push_message_id' . $this->id . '_product_channel_id' . $user->product_channel_id . '_platform' . $user->platform;

        foreach ($cache_keys as $cache_key) {
            $device_db->zadd($cache_key, time(), $user->id);
        }
    }

    function sendStat($user)
    {
        if ($user) {
            $this->stat($user, 'send');
        }
    }

    static function receiveStat($user, $tn)
    {

        $hot_cache = self::getHotWriteCache();
        $clazz = get_class($user);
        $clazz = strtolower($clazz);
        $day = date("Ymd");
        $cache_key = 'stat_' . $day . '_receive_' . $clazz . '_' . $user->id . '_' . $tn;
        if (!$hot_cache->get($cache_key)) {
            $opts = ['tn' => $tn];
            if (is_a($user, 'Users')) {
                $opts['user_id'] = $user->id;
            } else {
                $opts['device_id'] = $user->id;
            }

            self::delay()->asyncReceiveStat($opts);
        }
    }

    //['user_id', 'device_id', 'tn']
    static function asyncReceiveStat($opts)
    {
        $tn = fetch($opts, 'tn');
        $user = null;
        if (isset($opts['user_id']) && $opts['user_id']) {
            $user = Users::findFirstById($opts['user_id']);
        }
        if (isset($opts['device_id']) && $opts['device_id']) {
            $user = Devices::findFirstById($opts['device_id']);
        }
        if (!$user || !$tn) {
            return;
        }

        $hot_cache = self::getHotWriteCache();
        $clazz = get_class($user);
        $clazz = strtolower($clazz);
        $day = date("Ymd");
        $cache_key = 'stat_' . $day . '_receive_' . $clazz . '_' . $user->id . '_' . $tn;
        if ($user && !$hot_cache->get($cache_key)) {
            $push_message = self::findFirstByTrackerNo($tn);
            if ($push_message) {
                $hot_cache->setex($cache_key, endOfDay() - time(), $push_message->id);
                $push_message->stat($user, 'receive');
            }
        }
    }

    static function statList($conds, $page = 1, $per_page = 100)
    {
        $results = [];
        $materials = self::findPagination(['order' => 'status desc, id desc'], $page, $per_page);
        foreach ($materials as $material) {
            $results[$material->id] = $material->monthStat($conds);
        }

        return $results;
    }

    function monthStat($conds)
    {
        $clazz = fetch($conds, 'clazz');
        $year = fetch($conds, 'year', date('Y'));
        $month = fetch($conds, 'month', date('m'));
        $product_channel_id = fetch($conds, 'product_channel_id', -1);
        $platform = fetch($conds, 'platform', -1);

        $device_db = \Devices::getDeviceDb();
        $results = [];
        $month_max_day = date('t', strtotime($year, '-' . $month . '-01'));
        for ($i = 1; $i <= $month_max_day; $i++) {
            if ($i < 10) {
                $day = $year . "-" . $month . "-0" . $i;
            } else {
                $day = $year . "-" . $month . "-" . $i;
            }

            $day = date("Ymd", strtotime($day));
            $cache_send_key = 'stat_' . $day . '_send_' . $clazz . '_push_message_id' . $this->id . '_product_channel_id' . $product_channel_id . '_platform' . $platform;
            $cache_receive_key = 'stat_' . $day . '_receive_' . $clazz . '_push_message_id' . $this->id . '_product_channel_id' . $product_channel_id . '_platform' . $platform;
            $pv = $device_db->zcard($cache_send_key);
            $uv = $device_db->zcard($cache_receive_key);
            debug($cache_send_key, $pv, $cache_receive_key, $uv);
            $results[date("d", strtotime($day))] = [intval($pv), intval($uv)];
        }

        return $results;
    }

    function generateTextContent($receiver)
    {
        $product_channel = $receiver->product_channel;
        $text_content = $this->text_content;

        if (!$text_content) {
            info("text_content is null");
            return null;
        }

        $product = $this->getProductChannelMaterial($product_channel);
        $amount = $product->amount_max;
        $push_url = $this->getPushUrl($receiver);
        $protocol = self::getProtocol();
        $weixin_domain = $product_channel->weixin_domain;
        $url = $protocol . $weixin_domain . '/wx/products/recommend?tn=' . $this->tracker_no;
        $product_channel_name = <<<EOF
<a href="$url">$product_channel->weixin_name</a>
EOF;


        $text_content = str_replace(['%amount%', '%product_url%', '%product_channel_name%'], [$amount, $push_url, $product_channel_name], $text_content);

        debug($text_content);
        return $text_content;
    }

}