<?php

class MarketingController extends ApplicationController
{

    //{"_url":"\/marketing","authorization_code":"186117e0685088f07b8c888710381804i3957959i","state":1}
    public function indexAction()
    {

        $id = $this->params('state');
        $marketing_config = \MarketingConfigs::findFirstById($id);
        $authorization_code = $this->params('authorization_code');
        if ($marketing_config && $authorization_code) {
            $marketing_config->authorizeToken($authorization_code);
        }

        echo '';
    }

    function isIos()
    {
        $ua = $this->request->getUserAgent();
        if (preg_match('/android/i', $ua)) {
            return false;
        }

        return true;
    }

    function reportAction()
    {

        $referer = $this->headers('Referer');

        $url_params = [];
        $visit_url = '';
        $url_opts = explode('?', $referer);
        if (count($url_opts) == 2) {
            $visit_url = $url_opts[0];
            $url_opts = explode('&', $url_opts[1]);
            foreach ($url_opts as $url_opt) {
                $opts = explode('=', $url_opt);
                $url_params[trim($opts[0])] = trim($opts[1]);
            }
        }

        info($visit_url, $url_params, $this->headers());

        //落地页URL中的click_id，对于广点通流量为URL中的qz_gdt，对于微信流量为URL中的gdt_vid
        $click_id = fetch($url_params, 'click_id');
        if (!$click_id) {
            $click_id = fetch($url_params, 'qz_gdt');
        }
        if (!$click_id) {
            $click_id = fetch($url_params, 'gdt_vid');
        }
        if (!$click_id) {
            info('false no click_id', $this->params(), $referer);
            return;
        }

        $marketing_config_id = $this->params('id'); // 也可以使用广告组id
        $marketing_config = \MarketingConfigs::findFirstById($marketing_config_id);
        if (!$marketing_config) {
            info('false no marketing_config', $this->params());
            return;
        }

        $access_token = $marketing_config->getToken();
        $timestamp = time();
        $nonce = randStr(20);

        $url = "https://api.e.qq.com/v1.0/user_actions/add?access_token={$access_token}&timestamp={$timestamp}&nonce={$nonce}";

        $user_action_set_id = $marketing_config->android_user_action_set_id;
        if ($this->isIos()) {
            $user_action_set_id = $marketing_config->ios_user_action_set_id;
        }

        $body = [
            'account_id' => $marketing_config->gdt_account_id,
            'actions' => [[
                'user_action_set_id' => $user_action_set_id,
                'url' => $referer,
                'action_time' => time(),
                'action_type' => 'COMPLETE_ORDER',
                'trace' => ['click_id' => $click_id]
            ]]
        ];

        $response = httpPost($url, $body);
        info($referer, $body, $response->raw_body);
        $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //次日留存
    function startAppAction()
    {

        $muid = $this->params('muid');//设备id，由IMEI（Android应用）md5生成，或是由IDFA（iOS应用）md5生成；
        $click_time = $this->params('click_time');//点击发生的时间，由腾讯社交广告系统生成，取值为标准时间戳，秒级别
        $click_id = $this->params('click_id');//腾讯社交广告后台生成的点击id，腾讯社交广告系统中标识用户每次点击生成的唯一标识
        $app_type = $this->params('app_type'); //app类型；取值为 android或ios（联盟Android为unionandroid）；注意是小写；根据广告主在腾讯社交广告（e.qq.com）创建转化时提交的基本信息关联；
        $appid = $this->params('appid');//Android应用为应用宝移动应用的id，或者iOS应用在Apple App Store的id；创建转化时，需填入此appid
        $advertiser_id = $this->params('advertiser_id');//广告主在腾讯社交广告（e.qq.com）的账户id

        $marketing_config = \MarketingConfigs::findFirstByGdtAccountId($advertiser_id);
        if (!$marketing_config) {
            info('false no marketing_config', $this->params());
            return;
        }

        if (!Devices::getMarketingStartAppMuid($muid)) {
            info('false muid no has', $muid, 'app_type', $app_type);
            return;
        }


        $access_token = $marketing_config->getToken();
        $timestamp = time();
        $nonce = randStr(20);
        $url = "https://api.e.qq.com/v1.0/user_actions/add?access_token={$access_token}&timestamp={$timestamp}&nonce={$nonce}";


        if ($app_type == 'ios') {
            $user_action_set_id = $marketing_config->ios_user_action_set_id;
            $user_data = ['hash_idfa' => $muid];
        } else {
            $user_action_set_id = $marketing_config->android_user_action_set_id;
            $user_data = ['hash_imei' => $muid];
        }

        $body = [
            'account_id' => $marketing_config->gdt_account_id,
            'user_action_set_id' => $user_action_set_id,
            'actions' => [
                'action_time' => time(),
                'user_id' => $user_data,
                'action_type' => 'START_APP',
                'trace' => ['click_id' => $click_id]
            ]
        ];

        $response = httpPost($url, $body);
        info($body, $response->raw_body);

        $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

}