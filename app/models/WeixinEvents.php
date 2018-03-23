<?php

class WeixinEvents extends WeixinBaseEvents
{

    function beforeEvent($openid, $opts = [])
    {
        $event = fetch($opts, 'event');
        $this->user = null;
        if ($event != 'subscribe') {
            $this->user = Users::findFirstByOpenid($this->product_channel, $openid);
        }
    }

    function afterEvent($openid, $opts = [])
    {
        debug($openid, $opts);
    }

    static function asyncBeforeEvent($product_channel_id, $openid, $opts = [])
    {
        debug($product_channel_id, $openid, $opts);
    }

    static function asyncAfterEvent($product_channel_id, $openid, $opts = [])
    {
        debug($product_channel_id, $openid, $opts);
    }

    function receiveText($openid, $content)
    {
        return 'success';
    }

    function receiveImage($openid, $pic_url, $media_id)
    {
        return 'success';
    }

    function receiveVoice($openid, $media_id, $format, $recognition)
    {
        // TODO: Implement receiveVoice() method.
    }

    function receiveVideo($openid, $media_id, $thumb_media_id)
    {
        // TODO: Implement receiveVideo() method.
    }

    function receiveShortvideo($openid, $media_id, $thumb_media_id)
    {
        // TODO: Implement receiveShortvideo() method.
    }

    function receiveLocation($openid, $location_x, $location_y, $scale, $label)
    {
        // TODO: Implement receiveLocation() method.
    }

    function receiveLink($openid, $title, $description, $url)
    {
        // TODO: Implement receiveLink() method.
    }

    function subscribeMessageText()
    {

        if ($this->product_channel->weixin_welcome) {
            $content = $this->product_channel->weixin_welcome;
        } else {
            $content = '欢迎关注' . $this->product_channel->name;
        }

        return ['msg_type' => 'text', 'content' => $content];
    }

    function eventSubscribe($openid, $event_key, $ticket, $info = null)
    {

        $this->user = \Users::registerByOpenid($this->product_channel, $openid, $info);
        if ($this->user && $this->user->id) {
            $this->user->subscribe = USER_SUBSCRIBE;
            $this->user->update();

            \Stats::delay()->record('user', 'subscribe', $this->user->getStatAttrs());
        }

        return $this->subscribeMessageText();
    }

    function eventUnsubscribe($openid)
    {

        if ($this->user && $this->user->id) {
            if ($this->product_channel && $this->product_channel->isWhiteList($this->user->nickname)) {
                debug('clear white list', $this->user->id, $this->user->openid);
                $this->user->openid = '';
                $this->user->mobile = '';
                $this->user->id_no = '';
            }

            $this->user->subscribe = USER_UNSUBSCRIBE;
            $this->user->update();

            \Stats::delay()->record('user', 'unsubscribe', $this->user->getStatAttrs());
        } else {
            info('false not find user', $openid);
        }
    }

    function eventScan($openid, $event_key, $ticket)
    {
    }

    function eventLocation($openid, $latitude, $longitude, $precision)
    {
    }

    function eventClick($openid, $event_key)
    {
    }

    function eventView($openid, $event_key)
    {
    }

    function tmpQrcode($scene_id, $expire = 604800)
    {

        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $this->getAccessToken();

        $body = ["expire_seconds" => $expire, "action_name" => "QR_SCENE",
            "action_info" => ["scene" => ["scene_id" => $scene_id]]];
        $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        $resp = $this->weixinHttpPost($url, $body);
        $body = $resp->body;
        $data = json_decode($body, true);
        if (!isset($data['ticket'])) {
            return '';
        }

        $ticket = $data['ticket'];
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
    }

    function generateLimitQrcodeByFr($fr)
    {
        $scene_str = $fr;
        return $this->limitQrcode($scene_str);
    }

    function limitQrcode($scene_str)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->getAccessToken();
        $body = ["action_name" => "QR_LIMIT_STR_SCENE", "action_info" => ["scene" => ["scene_str" => $scene_str]]];
        $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        $resp = $this->weixinHttpPost($url, $body);
        $body = $resp->body;
        $data = json_decode($body, true);
        if (!isset($data['ticket'])) {
            return '';
        }
        $ticket = $data['ticket'];
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($ticket);
    }

    function auth($return_url, $scope = 'snsapi_base')
    {
        // 获取授权
        $return_url = urlencode($return_url);
        $response = self::di('response');

        $auth_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->product_channel->weixin_appid . '&redirect_uri=' . $return_url .
            '&response_type=code&scope=' . $scope . '&state=callback#wechat_redirect';

        info('发起验证授权', $auth_url);
        $response->redirect($auth_url);
        return;
    }

}