<?php

class WeixinEvents extends WeixinBaseEvents
{

    function beforeEvent($openid, $opts = [])
    {
        $event = fetch($opts, 'event');
        $this->user = null;
        if ($event != 'subscribe') {
            $this->user = Users::findFirstByOpenid($this->product_channel, $openid);
//            if ($this->user && time() - $this->user->event_at > 120) {
//                $this->user->subscribe = USER_SUBSCRIBE;
//                $this->user->event_at = time();
//                $this->user->update();
//                debug('update user event_at', $this->user->id, $this->user->event_at);
//            }
        }
    }

//    function afterEvent($openid, $opts = [])
//    {
//        debug($openid, $opts);
//    }
//
//    static function asyncBeforeEvent($product_channel_id, $openid, $opts = [])
//    {
//        debug($product_channel_id, $openid, $opts);
//    }

    static function asyncAfterEvent($product_channel_id, $openid, $opts = [])
    {
        return;

        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $user = \Users::findFirstByOpenid($product_channel, $openid);
        if ($user && time() - $user->event_at > 60) {
            $user->event_at = time();
            $user->update();
            debug('update user event_at', $user->id, $user->event_at);
        }
    }

    function receiveText($openid, $content)
    {
        if (!$this->user || !$this->user->canPush()) {
            return 'success';
        }

        $hot_cache = Users::getHotWriteCache();
        if ($hot_cache->get('weixin_receive_text_' . $this->user->id)) {
            return 'success';
        }

        $expire_at = endOfDay() - time();
        $hot_cache->setex('weixin_receive_text_' . $this->user->id, $expire_at, 1);

        $domain = $this->product_channel->weixin_domain;
        $protocol = "http://";

//        if (isProduction()) {
//            $protocol = "https://";
//        }

        $domain = $protocol . $domain;

        $recommend_url = $domain . '/wx/products/recommend';
        $product_url = $domain . '/wx/products';
        $strategy_url = $domain . '/wx/loan_strategies';
        $credit_url = $domain . '/wx/credit_cards';
        $about_url = $domain . '/wx/users/about_us';
        $content = <<<EOF
您是需要贷款吗？

我要贷款-><a href="{$recommend_url}">极速贷款</a>

不知道选择那家？-><a href="{$product_url}">贷款大全</a>

不知道如何贷款？-><a href="{$strategy_url}">贷款攻略</a>

我想办理信用卡-><a href="{$credit_url}">办理信用卡</a>

更多问题-><a href="{$about_url}">联系客服</a>
EOF;

        return ['msg_type' => 'text', 'content' => $content];
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
        $domain = $this->product_channel->weixin_domain;
        $protocol = "http://";
//        if (isProduction()) {
//            $protocol = "https://";
//        }

        $domain = $protocol . $domain;
        $name = $this->product_channel->weixin_name;

        $content = <<<EOF
 亲，你终于来啦~里面请~ 我们为您精选了海量贷款产品！
 
【热门免息贷】纯信用贷<a href="{$domain}/wx/products/search?amount_min=100&amount_max=2000">👉点我</a>

【小额极速贷】快速下款<a href="{$domain}/wx/products/search?amount_min=2000&amount_max=10000">👉点我</a>

 申请更多贷款产品<a href="{$domain}/wx/products/recommend">👉贷款大全</a>
EOF;

        return ['msg_type' => 'text', 'content' => $content];
    }

    function subscribeMessageText2()
    {
        $domain = $this->product_channel->weixin_domain;
        $protocol = "http://";
//        if (isProduction()) {
//            $protocol = "https://";
//        }

        $domain = $protocol . $domain;
        $name = $this->product_channel->weixin_name;

        $content = <<<EOF
 Hi，{$name}超千万用户的信赖选择，最新最全的贷款口子，极速下款，最高额度50万元！资金和福利已备好，就等您来~  
 
 最新贷款口子<a href="{$domain}/wx/products/search?amount_max=2000">☞立即申请</a>
 
 疯狂极速下款<a href="{$domain}/wx/products/search?amount_min=2001&amount_max=5000">☞立即申请</a>  
 
 更多贷款产品和福利，<a href="{$domain}/wx/products/recommend">☞戳我！</a>
EOF;

        return ['msg_type' => 'text', 'content' => $content];
    }

    function subscribeMessageText3()
    {
        $domain = $this->product_channel->weixin_domain;
        $protocol = "http://";
//        if (isProduction()) {
//            $protocol = "https://";
//        }

        $domain = $protocol . $domain;
        $name = $this->product_channel->weixin_name;

        $content = <<<EOF
您好，欢迎关注{$name}！
急用钱？找我们！
我们精心为您准备了十几个好下款的新口子！
<a href="{$domain}/wx/products/recommend?new_star=1">点我极速借钱>></a>
EOF;

        return ['msg_type' => 'text', 'content' => $content];
    }

    function subscribeMessageText4()
    {
        $domain = $this->product_channel->weixin_domain;
        $protocol = "http://";
//        if (isProduction()) {
//            $protocol = "https://";
//        }

        $domain = $protocol . $domain;
        $name = $this->product_channel->weixin_name;

        $content = <<<EOF
嗨！终于等到您！
极速借款，最快一小时到账！
<a href="{$domain}/wx/products/recommend">👉点我立即借钱>></a>
EOF;

        return ['msg_type' => 'text', 'content' => $content];
    }

    function subscribeMessageText5()
    {
        $domain = $this->product_channel->weixin_domain;
        $protocol = "http://";
//        if (isProduction()) {
//            $protocol = "https://";
//        }

        $domain = $protocol . $domain;
        $name = $this->product_channel->weixin_name;

        $content = <<<EOF
您好，欢迎关注{$name}！
急用钱？找我们！
我们精心为您准备了放款快、好下款的新口子！
<a href="{$domain}/wx/products/recommend">点我立即申请>></a>
EOF;

        return ['msg_type' => 'text', 'content' => $content];
    }

    function parseEventKey($event_key)
    {
        if ($event_key) {
            if (preg_match('/^qrscene_10/', $event_key)) {
                $parent_user_id = str_replace('qrscene_10', '', $event_key);
                $parent_user_id = intval($parent_user_id);
                return ['parent_user_id' => $parent_user_id];
            } elseif (preg_match('/^qrscene_20/', $event_key)) {

            } elseif (preg_match('/^qrscene_30/', $event_key)) {

            } else {
                $fr = str_replace('qrscene_', '', $event_key);
                return ['fr' => $fr];
            }

        }
        return [];
    }

    function eventSubscribe($openid, $event_key, $ticket, $info = null)
    {

        $key_res = $this->parseEventKey($event_key);
        $info['fr'] = fetch($key_res, 'fr');
        $this->user = \Users::registerByOpenid($this->product_channel, $openid, $info);

        if ($this->user) {

            $this->user->subscribe = USER_SUBSCRIBE;
            $this->user->update();

            \Stats::delay()->record('user', 'subscribe', $this->user->getStatAttrs());
        }

        $product_channel_code = $this->product_channel->code;

        if (in_array($product_channel_code, ['money'])) {
            return $this->subscribeMessageText5();
        }

        return $this->subscribeMessageText();
    }

    function eventUnsubscribe($openid)
    {
        //$user = Users::findFirstByOpenid($this->product_channel, $openid);
        if ($this->user) {
            if ($this->product_channel && $this->product_channel->isWhiteList($this->user->nickname)) {
                debug('clear white list');
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

    function generateTmpQrcodeByUserId($user_id, $expire = 604800)
    {
        $scene_id = '10' . $user_id;
        $scene_id = intval($scene_id);
        return $this->tmpQrcode($scene_id, $expire);
    }

    function generateTmpQrcodeBySourceId($source_id, $expire = 604800)
    {
        $scene_id = '20' . $source_id;
        $scene_id = intval($scene_id);
        return $this->tmpQrcode($scene_id, $expire);
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