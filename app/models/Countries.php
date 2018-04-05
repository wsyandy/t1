<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/04/04
 * Time: 下午18:08
 */

class Countries extends BaseModel
{
    static $FIXED = array(0 => '否', 1 => '是');

    static $LANGUAGE = array('en' => '英文', 'zh-CN' => '中文', 'zh-TW' => '繁体', 'fr' => '法语', 'ru' => '俄语', 'es' => '西班牙语',
        'ja' => '日语', 'de' => '德语', 'pt' => '葡葡萄牙语', 'vi' => '越南语');
    static $COUNTRY_NAME = array('越南' => 'vn', '中国' => 'cn');

    static $STATUS = [STATUS_ON => '启用', STATUS_OFF => '禁用'];

    static $files = ['image' => APP_NAME . '/countries/image/%s'];


    static function codeByIp($ip)
    {
        if (isBlank($ip)) {
            return null;
        }
        $country_name = \IPLocation::findCountry($ip);
        return fetch(\Countries::$COUNTRY_NAME, $country_name);
    }

    static function findByCode($code)
    {
        $country = \Countries::findFirstByCode($code);
        if ($country) {
            return $country;
        }

        return null;
    }

    function mergeJson()
    {
        return [
            'image_small_url' => $this->image_small_url,
            'status_text' => $this->status_text
        ];
    }

    function imageSmallUrl()
    {
        return StoreFile::getUrl($this->image) . '@!small';
    }
}