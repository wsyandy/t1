<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/20
 * Time: 上午11:32
 */
class AccountBanks extends BaseModel
{
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];
    static $files = ['icon' => APP_NAME . '/account_banks/%s'];
    static $BANK_CODE = [
        'ICBC' => '工商银行',
        'ABC' => '农业银行',
        'CMB' => '招商银行',
        'CCB' => '建设银行',
        'BCCB' => '北京银行',
        'BJRCB' => '北京农业商业银行',
        'BOC' => '中国银行',
        'COMM' => '交通银行',
        'CMBC' => '民生银行',
        'BOS' => '上海银行',
        'CBHB' => '渤海银行',
        'CEB' => '光大银行',
        'CIB' => '兴业银行',
        'CITIC' => '中信银行',
        'CZB' => '浙商银行',
        'GDB' => '广发银行',
        'HKBEA' => '东亚银行',
        'HXB' => '华夏银行',
        'HZCB' => '杭州银行',
        'NJCB' => '南京银行',
        'PINGAN' => '平安银行',
        'PSBC' => '邮政储蓄银行',
        'SDB' => '深圳发展银行',
        'SPDB' => '浦发银行',
        'SRCB' => '上海农业商业银行'
    ];

    function toSimpleJson()
    {
        return ['id' => $this->id, 'code' => $this->code, 'status' => $this->status, 'status_text' => $this->status_text,
            'rank' => $this->rank, 'name' => $this->name, 'icon_url' => $this->icon_url,
            'icon_small_url' => $this->icon_small_url, 'icon_big_url' => $this->icon_big_url];
    }

    function getIconUrl($size = null)
    {

        if (isBlank($this->icon)) {
            return null;
        }
        $url = StoreFile::getUrl($this->icon);
        if ($size) {
            $url .= "@!" . $size;
        }
        return $url;
    }

    function getIconSmallUrl()
    {
        return $this->getIconUrl('small');
    }

    function getIconBigUrl()
    {
        return $this->getIconUrl('big');
    }
}