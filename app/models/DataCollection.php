<?php

/**
 * 同步数据类
 * Header头增加2个字段：InitVal, EncodeVal
 *String key = "hdfs2kafka";  // 二次加密
 *InitVal    //随机数
 *EncodeVal    //加密验证: md5(md5(InitVal) + key)
 */
class DataCollection extends BaseModel
{
    static $_only_cache = true;

    static function getToken()
    {
        $secret_key = 'hdfs2kafka';
        $initval = uniqid(mt_rand());
        $token = md5(md5($initval) . $secret_key);
        return [$initval, $token];
    }

    static function asyncData($model, $action, $opts = [])
    {
        $url = \DataCollection::config('data_collection_endpoints');

        $params = array_merge(['model' => $model, 'action' => $action, 'action_at' => time()], $opts);
        $data['body'] = $params;
        $push_data = json_encode($data, JSON_UNESCAPED_UNICODE);

        list($initval, $token) = self::getToken();
        $header = [
            'InitVal' => $initval,
            'EncodeVal' => $token,
            'Content-Type' => 'application/json'
        ];
        try {
            $res = httpPost($url, $push_data, $header);
            info('推送地址=>' . $url, '推送数据=>' . $push_data);
            info('返回结果=>' . $res->raw_body);

        } catch (\Exception $e) {
            //每次发生异常+1
            warn("Exce ==", $url, $data, $e->getMessage());
            $hot_cache = DataCollection::getHotWriteCache();
            $hot_cache->incr('push_data_exception');
            $hot_cache->expire('push_data_exception', 300);
        }
    }

    static function syncData($model, $action, $opts = [])
    {

        if (isProduction()) {
            return;
        }

        $url = self::config('data_collection_endpoints');
        if (!$url) {
            return;
        }

        $hot_cache = \DataCollection::getHotWriteCache();
        $num = $hot_cache->get('push_data_exception');

        //如果五分钟之内发生超过10次异常
        if ($num > 10) {
            info("Exce push_data_exception", $num);
            return;
        }

        self::delay()->asyncData($model, $action, $opts);
    }
}