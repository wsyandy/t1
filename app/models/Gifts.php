<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/2
 * Time: 下午10:13
 */
class Gifts extends BaseModel
{

    //礼物支付类型
    static $PAY_TYPE = ['gold' => '金币', 'diamond' => '钻石'];

    //礼物类型 暂定
    static $TYPE = [1 => '普通礼物', 2 => '幸运礼物', 3 => '座驾'];

    //礼物状态
    static $STATUS = [GIFT_STATUS_ON => '有效', GIFT_STATUS_OFF => '无效'];

    //图片文件
    static $files = ['image' => APP_NAME . '/gifts/image/%s', 'big_image' => APP_NAME . '/gifts/big_image/%s',
        'dynamic_image' => APP_NAME . '/gifts/dynamic_image/%s'];

    static function getCacheEndPoint()
    {
        $config = self::di('config');
        $endpoints = $config->cache_endpoint;
        return explode(',', $endpoints)[0];
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'image_big_url' => $this->image_big_url,
            'name' => $this->name,
            'amount' => $this->amount,
            'pay_type' => $this->pay_type,
            'dynamic_image_url' => $this->dynamic_image_url
        ];
    }

    function toJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'rank' => $this->rank,
            'status_text' => $this->status_text,
            'image_small_url' => $this->image_small_url,
            'image_big_url' => $this->image_big_url,
            'dynamic_image_url' => $this->dynamic_image_url
        ];
    }

    function getDynamicImageUrl()
    {
        if (isBlank($this->dynamic_image)) {
            return '';
        }

        return StoreFile::getUrl($this->dynamic_image);
    }

    function getImageUrl()
    {
        if (isBlank($this->image)) {
            return '';
        }

        return StoreFile::getUrl($this->image);
    }

    function getImageSmallUrl()
    {
        if (isBlank($this->image)) {
            return '';
        }

        return StoreFile::getUrl($this->image) . '@!small';
    }

    function getImageBigUrl()
    {
        if (isBlank($this->image) && isBlank($this->big_image)) {
            return '';
        }
        if (isPresent($this->big_image)) {
            return \StoreFile::getUrl($this->big_image);
        }
        return StoreFile::getUrl($this->image) . '@!big';
    }

    function beforeCreate()
    {
        if (isBlank($this->pay_type)) {
            $this->pay_type = 'diamond';
        }
    }

    function isInvalid()
    {
        return $this->status == GIFT_STATUS_OFF;
    }

    /**
     * 获取所有的有效礼物，这里先做一个限制，最多100个
     * @return PaginationModel
     */
    static function findValidList()
    {
        $conditions = [
            'conditions' => "status = :status:",
            'bind' => [
                'status' => GIFT_STATUS_ON
            ],
            'order' => 'amount asc'];
        $page = 1;
        $per_page = 100;

        return \Gifts::findPagination($conditions, $page, $per_page);
    }

    static function generateNotifyData($opts)
    {
        $gift = fetch($opts, 'gift');
        $gift_num = fetch($opts, 'gift_num');
        $user = \Users::findById($opts['user_id']);
        $sender = fetch($opts, 'sender');
        $data = [];
        if ($gift) {
            $data = array_merge($data, $gift->toSimpleJson());
            $data['num'] = $gift_num;
            $data['user_id'] = $user->id;
            $data['user_nickname'] = $user->nickname;
            $data['sender_id'] = $sender->id;
            $data['sender_nickname'] = $sender->nickname;
        }
        return $data;

    }
}