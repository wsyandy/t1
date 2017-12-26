<?php

class SourcesController extends \ApplicationController
{

    /** 存储结构，字段可选
     * {
     * source 点击来源, muid: 密文的imei/idfa,imei转小写md5, idfa转大写md5;
     * imei: 明文, idfa: 明文, fr: 推广渠道, code: 产品code,
     * click_time: 点击时间, app_type: 手机系统, click_id: 点击id, appid: 应用的id,
     * advertiser_id: 广告id,  callback: 回调地址
     * }
     *
     * 不同产品不同推广渠道需要自定义参数：
     * code: 产品渠道code, 例如：现金超人code=money
     * fr: 推广渠道fr, 例如：腾讯feed信息流fr=tengxun_feedxinxi_01
     *
     * 注意：安卓的推广fr是可选的
     */

    // 头条
    public function activeAction()
    {
        $attrs = ['source' => 'toutiao'];
        foreach (['idfa', 'imei', 'fr', 'code', 'os', 'click_time', 'callback'] as $key) {
            $attrs[$key] = $this->params($key);
        }

        if (!$attrs['idfa'] && !$attrs['imei']) {
            echo 'ok';
            return;
        }

        // 毫秒
        $attrs['click_time'] = intval($attrs['click_time']);
        if ($attrs['click_time']) {
            $attrs['click_time'] = intval($attrs['click_time'] / 1000);
        } else {
            $attrs['click_time'] = time();
        }

        // 1表示ios, 0 表示安卓
        $app_type = $attrs['os'] == 1 ? 'ios' : 'android';
        $attrs['app_type'] = $app_type;

        // imei是md5， idfa是原值
        $muid = $attrs['imei'] ? $attrs['imei'] : md5(strtoupper($attrs['idfa']));
        $muid = strtolower($muid);
        $attrs['muid'] = $muid;

        $hot_cache = \Devices::getHotWriteCache();

        $new_click_key = 'new_click_ad_event_' . $attrs['code'] . '_muid_' . $muid;
        $hot_cache->setex($new_click_key, 60 * 60 * 72, json_encode($attrs, JSON_UNESCAPED_UNICODE));
        info('set', $new_click_key, $attrs);

        echo 'ok';
        return;
    }

    // 广点通
    public function gdtClickAction()
    {

        $this->response->setContentType('application/json', 'utf-8');
        $muid = $this->params('muid');
        if (!$muid) {
            echo json_encode(['ret' => -1, 'msg' => 'muid为空'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $hot_cache = \Devices::getHotWriteCache();
        $source = $this->params('source', 'gdt');
        $attrs = ['source' => $source];

        $fields = ['muid', 'click_time', 'click_id', 'appid', 'advertiser_id', 'app_type', 'fr', 'code'];
        foreach ($fields as $field) {
            $attrs[$field] = $this->params($field);
        }

        $new_click_key = 'new_click_ad_event_' . $attrs['code'] . '_muid_' . $muid;
        $hot_cache->setex($new_click_key, 60 * 60 * 72, json_encode($attrs, JSON_UNESCAPED_UNICODE));
        info('set', $new_click_key, $attrs);

        echo json_encode(['ret' => 0, 'msg' => '接收成功'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // 阿里汇川 UC
    public function ucClickAction()
    {

        $attrs = ['source' => 'uc'];
        foreach (['idfa', 'imei', 'fr', 'code', 'os', 'click_time', 'callback'] as $key) {
            $attrs[$key] = $this->params($key);
        }

        if (!$attrs['idfa'] && !$attrs['imei']) {
            echo 'ok';
            return;
        }

        // 毫秒
        $attrs['click_time'] = intval($attrs['click_time']);
        if ($attrs['click_time']) {
            $attrs['click_time'] = intval($attrs['click_time'] / 1000);
        } else {
            $attrs['click_time'] = time();
        }

        $app_type = $attrs['os'] == 1 ? 'android' : 'ios';
        $attrs['app_type'] = $app_type;

        if ($attrs['callback']) {
            $attrs['callback'] = urldecode($attrs['callback']);
        }

        $muid = $attrs['imei'] ? $attrs['imei'] : $attrs['idfa'];
        $muid = strtolower($muid);
        $attrs['muid'] = $muid;


        $hot_cache = \Devices::getHotWriteCache();
        $new_click_key = 'new_click_ad_event_' . $attrs['code'] . '_muid_' . $muid;
        $hot_cache->setex($new_click_key, 60 * 60 * 72, json_encode($attrs, JSON_UNESCAPED_UNICODE));
        info('set', $new_click_key, $attrs);

        echo 'ok';
        return;
    }

    // xxx.com/sources/mm_click?code=money&fr=xxx&idfa=[IDFA]&os=[OS]&ts=[TS]&callback=[CALLBACK]&ua=[UA]&lbs=[LBS]
    // xxx.com/sources/mm_click?code=money&fr=xxx&imei=[IMEI]&os=[OS]&ts=[TS]&callback=[CALLBACK]&ua=[UA]&lbs=[LBS]
    public function mmClickAction()
    {
        $attrs = ['source' => 'momo'];
        foreach (['fr', 'code', 'os', 'ts', 'idfa', 'imei', 'callback', 'ua', 'lbs'] as $key) {
            $attrs[$key] = $this->params($key);
        }

        if (!$attrs['idfa'] && !$attrs['imei']) {
            echo 'ok';
            return;
        }

        $attrs['click_time'] = intval($attrs['ts']);
        if (!$attrs['click_time']) {
            $attrs['click_time'] = time();
        }

        // 1表示ios, 0 表示安卓 2-wp
        $app_type = $attrs['os'] == 1 ? 'ios' : 'android';
        $attrs['app_type'] = $app_type;

        //imei 陌陌6.2以下是明文， 6.2及以上是md5
        if ($attrs['imei'] && strlen($attrs['imei']) != 32) {
            $attrs['imei_plain'] = $attrs['imei'];
            $attrs['imei'] = strtolower(md5(strtolower($attrs['imei'])));
        }

        //idfa 陌陌6.2.4以下是加密的idfa, 6.2.4及以上是原值
        if ($attrs['idfa'] && strlen($attrs['idfa']) != 32) {
            $attrs['idfa_plain'] = $attrs['idfa'];
            $attrs['idfa'] = strtolower(md5(strtoupper($attrs['idfa'])));
        }

        $muid = $attrs['imei'] ? $attrs['imei'] : $attrs['idfa'];
        $muid = strtolower($muid);
        $attrs['muid'] = $muid;

        $hot_cache = \Devices::getHotWriteCache();

        $new_click_key = 'new_click_ad_event_' . $attrs['code'] . '_muid_' . $muid;
        $hot_cache->setex($new_click_key, 60 * 60 * 72, json_encode($attrs, JSON_UNESCAPED_UNICODE));
        info('set', $new_click_key, $attrs);

        echo 'ok';
        return;
    }

    public function baiduClickAction()
    {
        $attrs = ['source' => 'baidu'];
        foreach (['fr', 'code', 'os', 'click_time', 'idfa', 'imei', 'ip', 'pid', 'uid', 'aid', 'click_id', 'callback_url', 'akey', 'sign'] as $key) {
            $attrs[$key] = $this->params($key);
        }

        if (!$attrs['idfa'] && !$attrs['imei']) {
            echo 'ok';
            return;
        }

        // 毫秒
        $attrs['click_time'] = intval($attrs['click_time']);
        if ($attrs['click_time']) {
            $attrs['click_time'] = intval($attrs['click_time'] / 1000);
        } else {
            $attrs['click_time'] = time();
        }

        // 1表示ios, 2 表示安卓
        if ($attrs['idfa']) {
            $app_type = 'ios';
        } else {
            $app_type = 'android';
        }
        $attrs['app_type'] = $app_type;

        if ($attrs['imei'] && strlen($attrs['imei']) != 32) {
            $attrs['imei_plain'] = $attrs['imei'];
            $attrs['imei'] = strtolower(md5(strtolower($attrs['imei'])));
        }
        if ($attrs['idfa'] && strlen($attrs['idfa']) != 32) {
            $attrs['idfa_plain'] = $attrs['idfa'];
            $attrs['idfa'] = strtolower(md5(strtoupper($attrs['idfa'])));
        }

        $muid = $attrs['imei'] ? $attrs['imei'] : $attrs['idfa'];
        $muid = strtolower($muid);
        $attrs['muid'] = $muid;

        $hot_cache = \Devices::getHotWriteCache();

        $new_click_key = 'new_click_ad_event_' . $attrs['code'] . '_muid_' . $muid;
        $hot_cache->setex($new_click_key, 60 * 60 * 72, json_encode($attrs, JSON_UNESCAPED_UNICODE));
        info('set', $new_click_key, $attrs);

        echo 'ok';
        return;
    }


}

