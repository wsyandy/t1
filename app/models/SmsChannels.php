<?php

class SmsChannels extends BaseModel
{

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $SMS_TYPE = ['login' => '登录', 'market' => '营销', 'notify' => '通知'];

    static $MOBILE_OPERATOR = [MOBILE_OPERATOR_ALL => '不限', MOBILE_OPERATOR_CMCC => '移动',
        MOBILE_OPERATOR_UNICOM => '联通', MOBILE_OPERATOR_TELECOM => '电信'];

    static function getFastCacheEndpoint($id)
    {
        return self::getHotWriteCache()->endpoint;
    }

    public function gateway()
    {
        $clazz = '\smsgateway\\' . $this->clazz;
        $gateway = new $clazz($this);

        return $gateway;
    }

    function sendSms($mobile, $signature, $context = [])
    {
        $clazz = '\smsgateway\\' . $this->clazz;
        $gateway = new $clazz($this);

        $res = $gateway->sendSms($mobile, $this->template, $signature, $context);
        return $res;
    }

    function afterCreate()
    {
        $write_cache = self::getHotWriteCache();
        foreach (self::$SMS_TYPE as $type => $text) {
            $write_cache->del('sms_channel_available_ids_' . $type);
        }

        $this->addCache();
    }

    function afterUpdate()
    {
        $write_cache = self::getHotWriteCache();
        foreach (self::$SMS_TYPE as $type => $text) {
            $write_cache->del('sms_channel_available_ids_' . $type);
        }

        if ($this->hasChanged('mobile_operator') || $this->hasChanged('sms_type')
            || $this->hasChanged('product_channel_ids') || $this->hasChanged('status') || $this->hasChanged('rank')
        ) {
            $this->removeCache();
            $this->addCache();
        }

    }

    function afterDelete()
    {
        $this->removeCache();
    }

    function addCache()
    {

        $pre_keys = [];
        $prefix = "sms_channel_available_sms_type" . $this->sms_type . '_status' . $this->status;
        $pre_keys[] = $prefix . '_mobile_operator' . $this->mobile_operator;

        $product_channel_ids = $this->productChannelIdsHash();
        $result_keys = [];
        foreach ($pre_keys as $prefix) {
            if (!$product_channel_ids) {
                $result_keys[] = $prefix . '_product_channel_id-1';
            } else {
                foreach ($product_channel_ids as $product_channel_id) {
                    $result_keys[] = $prefix . '_product_channel_id' . $product_channel_id;
                }
            }
        }

        $hot_cache = self::getHotWriteCache();
        $hot_cache->pipeline();
        foreach ($result_keys as $key) {
            debug($key, $this->rank, $this->id);
            $hot_cache->zadd($key, $this->rank, $this->id);
            $hot_cache->zadd("sms_channel_available_keys_" . $this->id, $this->id, $key);
        }
        $hot_cache->exec();
    }

    function removeCache()
    {

        $hot_cache = self::getHotWriteCache();
        $keys = $hot_cache->zrange('sms_channel_available_keys_' . $this->id, 0, -1);

        $hot_cache->pipeline();
        foreach ($keys as $key) {
            debug('delete', $this->id, $key);
            $hot_cache->zrem($key, $this->id);
        }

        $hot_cache->del('sms_channel_available_keys_' . $this->id);
        $hot_cache->exec();
    }

    static function loadCache()
    {
        $sms_channels = SmsChannels::find();
        foreach ($sms_channels as $sms_channel) {
            $sms_channel->removeCache();
            $sms_channel->addCache();
        }
    }

    static function findFirstAvailable($sms_type = 'login')
    {
        $cache_key = 'sms_channel_available_ids_' . $sms_type;

        $read_cache = self::getHotReadCache();
        $ids = $read_cache->zrevrange($cache_key, 0, -1);

        if (isPresent($ids)) {
            foreach ($ids as $id) {
                $sms_channel = \SmsChannels::findFirstById($id);
                if ($sms_channel && $sms_channel->is_on) {
                    //info('sms', '缓存');
                    return $sms_channel;
                }
            }
        }
        $sms_channels = \SmsChannels::find(['conditions' => 'status = :status: and sms_type = :sms_type:',
            'bind' => ['status' => STATUS_ON, 'sms_type' => $sms_type],
            'order' => 'rank desc'
        ]);
        $select_channel = null;
        foreach ($sms_channels as $sms_channel) {
            if (!$select_channel) {
                $select_channel = $sms_channel;
            }
            $write_cache = self::getHotWriteCache();
            $write_cache->zadd($cache_key, $sms_channel->rank, $sms_channel->id);
        }
        info('sms', '非缓存');
        return $select_channel;

    }

    function mergeJson()
    {
        return [
            'product_channel_ids_num'=>$this->product_channel_ids_num
        ];
    }

    static function findAvailables($product_channel_id, $mobile_operator, $sms_type = 'login')
    {

        $cache_1min_key = 'cache_sms_channel_ids_' . $product_channel_id . '_' . $mobile_operator . '_' . $sms_type . '_' . date('YmdHi');
        $read_cache = self::getHotReadCache();
        $ids = $read_cache->get($cache_1min_key);
        if ($ids) {
            info('cache', $cache_1min_key, $ids);
            $ids = json_decode($ids, true);
            return SmsChannels::findByIds($ids);
        }


        $pre_keys[] = $prefix = "sms_channel_available_sms_type" . $sms_type . '_status1_mobile_operator' . $mobile_operator;
        $pre_keys[] = $prefix = "sms_channel_available_sms_type" . $sms_type . '_status1_mobile_operator0';
        $cache_keys = [];
        foreach ($pre_keys as $key) {
            $cache_keys[] = $key . '_product_channel_id-1';
            $cache_keys[] = $key . '_product_channel_id' . $product_channel_id;
        }

        $cache_keys = array_unique($cache_keys);
        $ids = [];
        foreach ($cache_keys as $cache_key) {
            $tmp_ids = $read_cache->zrevrange($cache_key, 0, -1, 'withscores');
            debug($cache_key, $tmp_ids);
            if ($tmp_ids) {
                foreach ($tmp_ids as $id => $v) {
                    if (!isset($ids[$id])) {
                        $ids[$id] = $v;
                    }
                }
            }
        }

        if (!$ids) {
            self::loadCache();
            info('Exce no cache', $product_channel_id, $mobile_operator, $sms_type);
            return null;
        }

        arsort($ids);
        $ids = array_keys($ids);
        if ($ids) {
            $read_cache->setex($cache_1min_key, 60, json_encode($ids, JSON_UNESCAPED_UNICODE));
        }

        return SmsChannels::findByIds($ids);
    }

    static function findMarketAvailables($product_channel_id, $mobile_operator)
    {
        return SmsChannels::findAvailables($product_channel_id, $mobile_operator, 'market');
    }

    function isOn()
    {
        return STATUS_ON === $this->status;
    }

    function productChannelIdsHash()
    {
        if ($this->product_channel_ids) {
            return explode(',', $this->product_channel_ids);
        }
        return [];
    }

    function productChannelIdsNum()
    {
        return count($this->productChannelIdsHash());
    }

}