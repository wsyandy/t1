<?php

class WapController extends \ApplicationController
{
    function beforeAction($dispatcher)
    {

//        if (!$this->isHttps()) {
//            $url = $this->getFullUrl();
//            info('not_https', $url);
//        }

        if (!$this->request->isAjax()) {
            $action_name = \Phalcon\Text::uncamelize($dispatcher->getActionName());
            if ($action_name == 'mobile_auth') {
                info("false_mobile_auth", $this->remoteIp(), $this->request->getUserAgent(), $this->headers());
                return false;
            }

            if (!$this->isHttps()) {
                info("false_not_https", $this->request->getHttpHost(), $this->params(), $this->remoteIp(), $this->request->getUserAgent(), $this->headers());
            }

            $this->logWap();
        }
    }

    function platformVersion()
    {
        $ua = $this->request->getUserAgent();
        $version = '1.0.0';
        if (preg_match('/android\s+([^;]+);/i', $ua, $result)) {
            $version = $result[1];
        }

        if (preg_match('/iPhone OS\s+([^)]+)\)/i', $ua, $result)) {
            $version = $result[1];
            $version = explode(' ', $version)[0];
            $version = str_replace('_', '.', $version);
        }

        return $version;
    }

    public function queryWord()
    {
        $referer = $this->headers("Referer");
        //info("referer:", $referer);

        if (!$referer || count(explode('?', $referer, 2)) != 2) {
            return '';
        }

        $attrs = [];
        list($tmp, $query) = explode('?', $referer, 2);
        debug("query:{$query}");
        $querys = explode('&', $query);
        foreach ($querys as $kv) {

            if (count(explode('=', $kv, 2)) != 2) {
                continue;
            }

            list($k, $v) = explode('=', $kv, 2);
            if ($k && $v && preg_match('/^(q|keyword|word|search|kw|wd|w|p|query)$/i', $k)) {
                $value = urldecode($v);
                //info('word:', $value);
                $encode = mb_detect_encoding($value, ["ASCII", "UTF-8", "GB2312", "GBK", "BIG5"]);
                if ('UTF-8' != $encode) {
                    $iconv_value = iconv($encode, "UTF-8", $value);
                    info('iconv utf-8', $encode, $k, $value, $iconv_value);
                    continue;
                }
                $attrs[$k] = $value;
            }
        }

        $result = null;
        foreach ($attrs as $k => $v) {
            if (strlen($v) < 150) {
                $result = $v;
            }

            if (preg_match('/keyword/i', $k)) {
                $result = $v;
                break;
            }
        }

        if (isBlank($result)) {
            $result = current($attrs);
        }

        if ($result && preg_match('/app/i', $result)) {
            info($result, $attrs, $referer, $this->params());
        }

        return trim($result);
    }

    public function firstUrl()
    {

        $uri = $this->getPath();
        $id = $this->params('id');
        if ($id) {
            $uri .= '?id=' . $id;
        }

        return $uri;
    }

    public function logWap()
    {
        $ua = $this->request->getUserAgent();
        if (!preg_match('/iphone|android/i', $ua)) {
            return;
        }

        $first_url = $this->session->get('first_url');
        if ($first_url && isProduction()) {
            info("first_url已存在", $first_url);
            return;
        }

        $uri = $this->getPath();
        $first_url = $this->firstUrl();
        info('set_first_url:', $first_url);
        $this->session->set('first_url', $first_url);

        $sem = $this->params('fr');
        if (!$sem && $this->params('id')) {
            $soft_version = \SoftVersions::findFirstById($this->params('id'));
            if ($soft_version) {
                $sem = $soft_version->built_in_fr;
            }
        }

        $encode = mb_detect_encoding($sem, ["ASCII", "UTF-8", "GB2312", "GBK", "BIG5"]);
        //info("from_sem", $sem, $encode);
        if ('EUC-CN' == $encode || !$sem) {
            info('false no_sem', $sem, $this->params());
            return;
        }

        $partner = \Partners::findFirstByFrHotCache($sem);
        if (!$partner) {
            return;
        }

        $visit_at = strtotime(date('Y-m-d'));
        $wap_visit = \WapVisits::findFirst([
            'conditions' => 'visit_at=:visit_at: and uri=:uri: and sem=:sem:',
            'bind' => ['visit_at' => $visit_at, 'uri' => $uri, 'sem' => $sem]
        ]);

        if (!$wap_visit) {
            $wap_visit = new \WapVisits();
            $wap_visit->visit_at = $visit_at;
            $wap_visit->uri = $uri;
            $wap_visit->visit_num = 1;
            $wap_visit->sem = $sem;
            $wap_visit->save();
        } else {
            $wap_visit->visit_num += 1;
            $wap_visit->update();
        }

        \WapVisitHistories::delay(1)->updateVisitNumByWapVisit($wap_visit->id, $this->remoteIp());

        $this->session->set('wap_visit_id', $wap_visit->id);
        $wap_visit_uuid_key = "wap_visit_uuid_{$wap_visit->id}";
        $wap_visit_uuid_value = $this->session->get($wap_visit_uuid_key);
        if (!$wap_visit_uuid_value) {
            $this->session->set($wap_visit_uuid_key, $wap_visit->generateUuid());
        }

        $word = $this->queryWord();
        //info($sem, 'word:', $word);

        if ($partner && $word) {

            if (strlen($word) > 150) {
                info('false, word len > 150,', $sem, $word, $this->params());
                return;
            }

            $word_visit = \WordVisits::findFirst([
                'conditions' => 'visit_at=:visit_at: and sem=:sem: and word=:word:',
                'bind' => ['visit_at' => $visit_at, 'sem' => $sem, 'word' => $word]
            ]);
            if (!$word_visit) {
                $word_visit = new \WordVisits();
                $word_visit->visit_at = $visit_at;
                $word_visit->sem = $sem;
                $word_visit->word = $word;
                $word_visit->visit_num = 1;
                $word_visit->save();
            } else {
                $word_visit->visit_num += 1;
                $word_visit->update();
            }

            $this->session->set('word_visit_id', $word_visit->id);
            $word_visit_uuid_key = "word_visit_uuid_{$word_visit->id}";
            $word_visit_uuid_value = $this->session->get($word_visit_uuid_key);
            if (!$word_visit_uuid_value) {
                $this->session->set($word_visit_uuid_key, $word_visit->generateUuid());
            }
            \WordVisitHistories::delay(1)->updateVisitNumByWordVisit($word_visit->id, $this->remoteIp());
        }
    }

    function isIos()
    {
        $ua = $this->request->getUserAgent();
        if (preg_match('/android/i', $ua)) {
            return false;
        }

        return true;
    }

    function downloadUrl($id)
    {
        $ua = $this->request->getUserAgent();
        if (preg_match('/android/i', $ua) && $this->isHttps()) {
            $platform_version = $this->platformVersion();
            if (version_compare('5.0', $platform_version, '>=')) {
                $host = $this->getHost();
                return 'http://' . $host . '/soft_versions/' . $id;
            }
        }

        return '/soft_versions/' . $id;
    }

    function semAction()
    {
        $soft_version_id = $this->params('id');
        $pic_id = $this->params('pic_id', 0);

        $soft_version = \SoftVersions::findFirstById($soft_version_id);
        if (!$soft_version) {
            return false;
        }
        $fr = $this->params('fr');
        if (!$fr) {
            $fr = $soft_version->built_in_fr;
        }

        $product_channel_name = $soft_version->product_channel->name;
        if ($soft_version_id == 426) {
            $product_channel_name = '极速借款';
        }

        $this->view->fr = $fr;
        $this->view->download_url = $this->downloadUrl($soft_version_id);
        $this->view->pic_id = $pic_id;
        $this->view->soft_version = $soft_version;
        $this->view->product_channel = $soft_version->product_channel;
        $this->view->product_channel_name = $product_channel_name;
        if ($pic_id > 4) {
            $this->pick("wap/sem{$pic_id}");
        }
    }

    function smsSemAction()
    {
        $soft_version_id = $this->params('id');
        $fr = $this->params('fr');
        $pic_id = $this->params('pic_id', 1);

        $soft_version = \SoftVersions::findFirstById($soft_version_id);
        if (!$soft_version) {
            return false;
        }
        if (!$fr) {
            $fr = $soft_version->built_in_fr;
        }

        $product_channel_name = $soft_version->product_channel->name;
        if ($soft_version_id == 427) {
            $product_channel_name = '借款';
        }

        $this->view->download_url = $this->downloadUrl($soft_version_id);
        $this->view->soft_version_id = $soft_version_id;
        $this->view->fr = $fr;
        $this->view->product_channel = $soft_version->product_channel;
        $this->view->product_channel_name = $product_channel_name;
        $this->view->soft_version = $soft_version;

        if ($pic_id > 1) {
            $this->pick("wap/sms_sem{$pic_id}");
        }
    }

    function mobileAuthAction()
    {
        if (!$this->request->isAjax()) {
            return false;
        }
        $it = $this->params('it', 0);
        if (!$it) {
            $image_token = $this->params('image_token');

            if (!$image_token) {
                info("image_token_error", $this->remoteIp(), $this->request->getUserAgent(), $this->headers());
                return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
            }

            $user_captcha_code = $this->params('captcha_code', '');
            $hot_cache = Users::getHotReadCache();
            $captcha_code = $hot_cache->get('image_token_' . $image_token);
            if (strtolower($user_captcha_code) != strtolower($captcha_code)) {
                info("user_captcha_code error", $image_token);
                return $this->renderJSON(ERROR_CODE_FAIL, '图片验证码错误');
            }
        }

        $soft_version_id = $this->params('soft_version_id');
        $soft_version = \SoftVersions::findFirstById($soft_version_id);
        if (!$soft_version) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $fr = $this->params('fr');
        if (!$fr) {
            $fr = $soft_version->built_in_fr;
        }

        $product_channel = $soft_version->product_channel;
        $mobile = $this->params('mobile');
        if (!isMobile($mobile)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '手机号码不正确');
        }

        $user = \Users::findFirstByMobile($product_channel, $mobile);
        if ($user) {
            info('已注册', $soft_version_id, $fr, $product_channel->code, $mobile, 'user_fr', $user->fr);
            return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '你已注册，请登录！');
        }

        $auth_code = $this->params('auth_code', '');
        if (!$auth_code) {
            list($error_code, $error_reason, $sms_token) = \SmsHistories::sendAuthCode($product_channel, $mobile,
                'login', ['auth_type' => 'sem', 'fr' => $fr, 'soft_version_id' => $soft_version_id]);
            if ($error_code == ERROR_CODE_SUCCESS) {
                $this->session->set('sms_token', $sms_token);
            }

            return $this->renderJSON($error_code, $error_reason, ['sms_token' => $sms_token]);
        }

        // 验证
        $sms_token = $this->params('sms_token');
        if (!$sms_token) {
            $sms_token = $this->session->get('sms_token');
        }

        list($error_code, $error_reason) = \SmsHistories::checkAuthCode($product_channel, $mobile, $auth_code, $sms_token,
            ['auth_type' => 'sem', 'fr' => $fr, 'soft_version_id' => $soft_version_id]);
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }

        $sms_sem_history = new \SmsSemHistories();
        $sms_sem_history->mobile = $mobile;
        $sms_sem_history->fr = $fr;
        $partner = \Partners::findFirstByFrHotCache($fr);
        if ($partner) {
            $sms_sem_history->partner_id = $partner->id;
        }
        $sms_sem_history->soft_version_id = $soft_version_id;
        $sms_sem_history->product_channel_id = $product_channel->id;
        $sms_sem_history->status = AUTH_WAIT;
        $sms_sem_history->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '验证成功');
    }

    function smsTouTiaoAction()
    {
        $soft_version_id = $this->params('id');
        $soft_version = \SoftVersions::findFirstById($soft_version_id);

        $pic_id = $this->params('pic_id', 1);

        if (!$soft_version) {
            return false;
        }

        $fr = $soft_version->built_in_fr;
        if (!$fr) {
            $fr = $this->params('fr');
        }

        $toutiao_convert_no = $soft_version->toutiao_convert_no;
        $this->view->download_url = $this->downloadUrl($soft_version_id);
        $this->view->soft_version_id = $soft_version_id;
        $this->view->fr = $fr;
        $this->view->product_channel = $soft_version->product_channel;
        $this->view->product_channel_name = $soft_version->product_channel->name;
        $this->view->soft_version = $soft_version;
        $this->view->toutiao_convert_no = $toutiao_convert_no;

        if ($pic_id > 1) {
            $this->pick("wap/sms_tou_tiao{$pic_id}");
        }
    }

    // 腾讯社交广告h5
    function marketingAction()
    {
        $soft_version_id = $this->params('id');
        $soft_version = \SoftVersions::findFirstById($soft_version_id);
        $pic_id = $this->params('pic_id', 1);

        if (!$soft_version) {
            return false;
        }

        $fr = $this->params('fr');
        if (!$fr) {
            $fr = $soft_version->built_in_fr;
        }
        $product_channel = $soft_version->product_channel;
        $name = '';

        $marketing_config_id = $soft_version->marketing_config_id;
        $this->view->download_url = $this->downloadUrl($soft_version_id);
        $this->view->soft_version_id = $soft_version_id;
        $this->view->fr = $fr;
        $this->view->product_channel = $product_channel;
        $this->view->product_channel_name = $soft_version->product_channel->name;
        $this->view->soft_version = $soft_version;
        $this->view->marketing_config_id = $marketing_config_id;
        $this->view->name = $name;

        if ($pic_id > 1) {
            $this->pick("wap/marketing{$pic_id}");
        }
    }


}