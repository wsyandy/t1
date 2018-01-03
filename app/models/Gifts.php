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
        if (isBlank($this->image)) {
            return '';
        }

        return StoreFile::getUrl($this->image) . '@!big';
    }
}