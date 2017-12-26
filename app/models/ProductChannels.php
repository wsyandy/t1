<?php

class ProductChannels extends BaseModel
{
    static $STATUS = [STATUS_OFF => '关闭', STATUS_ON => '正常'];
    static $SUPPORT_API_MODEL = [STATUS_OFF => '不支持', STATUS_ON => '支持'];

    static $files = array('avatar' => APP_NAME . '/product_channels/avatar/%s',
        'weixin_qrcode' => APP_NAME . '/product_channels/weixin_qrcode/%s');

    static function getCacheEndpoint($id)
    {
        return self::getHotWriteCache()->endpoint;
    }

    function beforeUpdate()
    {
        if ($this->hasChanged('weixin_domain')) {
            $write_cache = self::getHotWriteCache();
            $domain = $this->was('weixin_domain');
            $write_cache->del('product_channel_weixin_domain_' . $domain);
        }

        if ($this->hasChanged('touch_domain')) {
            $write_cache = self::getHotWriteCache();
            $domain = $this->was('touch_domain');
            $write_cache->del('product_channel_touch_domain_' . $domain);
        }

        if ($this->hasChanged('code')) {
            $write_cache = self::getHotWriteCache();
            $code = $this->was('code');
            $write_cache->del('product_channel_code_' . $code);
        }
    }

    static function asyncCopyTo($src_product_channel_id, $dest_product_channel_id)
    {
        $product_channel = ProductChannels::findFirstById($src_product_channel_id);
        $product_channel->copyTo($dest_product_channel_id);
    }

    function copyTo($dest_product_channel_id)
    {

        $products = Products::find(['conditions' => 'product_channel_id=:product_channel_id:',
            'bind' => ['product_channel_id' => $this->id]]);
        foreach ($products as $product) {
            if (Products::findFirst(['conditions' => 'product_channel_id=:product_channel_id: and product_id=:product_id:',
                'bind' => ['product_channel_id' => $dest_product_channel_id, 'product_id' => $product->product_id]])
            ) {
                debug($product->id);
                continue;
            }
            $new_product = $product->cloneNewObject();
            $new_product->product_channel_id = $dest_product_channel_id;
            $new_product->save();
        }

        $product_channel_banners = ProductChannelBanners::find(['conditions' => 'product_channel_id=:product_channel_id:',
            'bind' => ['product_channel_id' => $this->id]]);
        if ($product_channel_banners) {
            foreach ($product_channel_banners as $product_channel_banner) {

                $cond = ['conditions' => 'product_channel_id=:product_channel_id: and banner_id=:banner_id:',
                    'bind' => ['product_channel_id' => $dest_product_channel_id, 'banner_id' => $product_channel_banner->banner_id],
                    'order' => 'id desc'];
                if (ProductChannelBanners::findFirst($cond)) {
                    debug($product_channel_banner->id);
                    continue;
                }

                $new_product_channel_banner = new ProductChannelBanners();
                $new_product_channel_banner->product_channel_id = $dest_product_channel_id;
                $new_product_channel_banner->banner_id = $product_channel_banner->banner_id;
                $new_product_channel_banner->save();
            }
        }
    }

    function mergeJson()
    {
        return ['avatar_url' => $this->getAvatarUrl(), 'avatar_small_url' => $this->getAvatarSmallUrl()];
    }

    static function getWeixinThemes()
    {

        $weixin_themes = ['dolls' => '全民抓娃娃'];
        $themes = [];
        foreach (glob(APP_ROOT . 'app/views/wx/*') as $filename) {
            $basename = basename($filename);
            $themes[$basename] = fetch($weixin_themes, $basename, $basename);
        }
        return $themes;
    }

    static function getTouchThemes()
    {
        $touch_themes = ['dolls' => '全民抓娃娃'];
        $themes = [];
        foreach (glob(APP_ROOT . 'app/views/touch/*') as $filename) {
            $basename = basename($filename);
            $themes[$basename] = fetch($touch_themes, $basename, $basename);
        }
        return $themes;
    }

    static function getWebThemes()
    {

        $web_themes = ['dolls' => '全民抓娃娃'];
        $themes = [];
        foreach (glob(APP_ROOT . 'app/views/web/*') as $filename) {
            $basename = basename($filename);
            $themes[$basename] = fetch($web_themes, $basename, $basename);
        }
        return $themes;
    }

    static function findFirstByWeixinDomain($domain)
    {
        $read_cache = self::getHotReadCache();

        $id = $read_cache->get('product_channel_weixin_domain_' . $domain);
        if (!$id) {
            $product_channel = self::findFirstBy(['weixin_domain' => $domain]);
            if ($product_channel) {
                $write_cache = self::getHotWriteCache();
                $write_cache->setex('product_channel_weixin_domain_' . $domain, 60 * 60 - 1, $product_channel->id);
            }
        } else {
            $product_channel = self::findFirstById($id);
        }

        return $product_channel;
    }

    static function findFirstByTouchDomain($domain)
    {
        $read_cache = self::getHotReadCache();
        $id = $read_cache->get('product_channel_touch_domain_' . $domain);
        if (!$id) {
            $product_channel = self::findFirstBy(['touch_domain' => $domain]);
            if ($product_channel) {
                $write_cache = self::getHotWriteCache();
                $write_cache->setex('product_channel_touch_domain_' . $domain, 60 * 60 - 1, $product_channel->id);
            }
        } else {
            $product_channel = self::findFirstById($id);
        }

        return $product_channel;
    }

    static function findFirstByWebDomain($domain)
    {
        $read_cache = self::getHotReadCache();
        $id = $read_cache->get('product_channel_web_domain_' . $domain);
        if (!$id) {
            $product_channel = self::findFirstBy(['web_domain' => $domain]);
            if ($product_channel) {
                $write_cache = self::getHotWriteCache();
                $write_cache->setex('product_channel_web_domain_' . $domain, 60 * 60 - 1, $product_channel->id);
            }
        } else {
            $product_channel = self::findFirstById($id);
        }

        return $product_channel;
    }

    static function findFirstByCodeHotCache($code)
    {
        $write_cache = self::getHotWriteCache();
        $read_cache = self::getHotReadCache();
        $product_channel_id = $read_cache->get('product_channel_code_' . $code);

        $product_channel = null;
        if (!$product_channel_id) {
            $product_channel = self::findFirstByCode($code);
            if ($product_channel) {
                $write_cache->set('product_channel_code_' . $code, $product_channel->id);
            }
        } else {
            $product_channel = self::findFirstById($product_channel_id);
        }

        return $product_channel;
    }


    function isWhiteList($nickname)
    {
        if (!$this->weixin_white_list) {
            return false;
        }

        $weixin_white_list = explode(',', $this->weixin_white_list);
        debug($nickname, $weixin_white_list);
        return in_array($nickname, $weixin_white_list);
    }

    function isBlackList($nickname)
    {
        if (!$this->weixin_black_list) {
            return false;
        }

        $weixin_black_list = explode(',', $this->weixin_black_list);
        foreach ($weixin_black_list as $black) {
            if (mb_stristr($nickname, $black)) {
                info('黑名单', $black, $nickname);
                return true;
            }
        }

        return false;
    }

    function getPushContext($platform)
    {
        $keys = array('app_id', 'app_key', 'app_secret', 'app_master_secret');
        $prefix = $platform . '_';

        $context = array();
        foreach ($keys as $key) {
            $field = $prefix . $key;
            $context[$key] = $this->$field;
        }

        return $context;
    }

    function getAvatarUrl($size = null)
    {

        if (isBlank($this->avatar)) {
            return null;
        }
        $url = StoreFile::getUrl($this->avatar);
        if ($size) {
            $url .= "@!" . $size;
        }
        return $url;
    }

    function getAvatarSmallUrl()
    {
        return $this->getAvatarUrl('small');
    }

    function getWeixinQrcodeUrl($size = null)
    {
        if (isBlank($this->weixin_qrcode)) {
            return null;
        }
        $url = StoreFile::getUrl($this->weixin_qrcode);

        if ($url) {
            $url .= "@!small";
        }

        return $url;
    }

    function getLimitQrcodeUrl($fr = 'app')
    {
        $hot_cache = self::getHotWriteCache();
        $limit_qrcode_url_key = "limit_qrcode_url_" . $this->code . '_' . $fr;
        $url = $hot_cache->get($limit_qrcode_url_key);
        if (!$url) {
            $url = $this->generateLimitQrcodeByFr($fr);
            $hot_cache->set($limit_qrcode_url_key, $url);
        }

        return $url;
    }

    function generateLimitQrcodeByFr($fr = 'app')
    {
        $weixin_event = new WeixinEvents($this);
        $url = $weixin_event->generateLimitQrcodeByFr($fr);
        $hot_cache = self::getHotWriteCache();
        $limit_qrcode_url_key = "limit_qrcode_url_" . $this->code . '_' . $fr;
        $hot_cache->set($limit_qrcode_url_key, $url);

        return $url;
    }

    function toAboutJson()
    {
        return [
            'name' => $this->name,
            'company_name' => $this->company_name,
            'service_phone' => $this->service_phone,
            'weixin_name' => $this->weixin_name,
            'cooperation_weixin' => $this->cooperation_weixin,
            'cooperation_email' => $this->cooperation_email,
            'cooperation_phone_number' => $this->cooperation_phone_number,
            'official_website' => $this->official_website,
            'avatar' => $this->avatar_url,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'weixin_limit_qrcode' => $this->weixin_qrcode_url,
            'weixin_no' => $this->weixin_no
        ];
    }

    function getRegisterJumpUrl($platform)
    {
        debug($platform);
        $url = $platform . "_register_jump_url";
        return $this->$url;
    }

}