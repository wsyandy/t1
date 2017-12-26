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

}