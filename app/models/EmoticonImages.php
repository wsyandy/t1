<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/30
 * Time: 下午3:56
 */
class EmoticonImages extends BaseModel
{
    //表情状态
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    //图片文件
    static $files = ['image' => 'emoticon_images/image/%s', 'dynamic_image' => 'emoticon_images/dynamic_image/%s'];

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

    function toJson()
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'name' => $this->name,
            'code' => $this->code,
            'status_text' => $this->status_text,
            'rank' => $this->rank,
            'duration' => $this->duration,
            'dynamic_image_url' => $this->dynamic_image_url
        ];
    }

    function isRepeating()
    {
        $cond = [];
        $cond['conditions'] = "(code = :code: or rank = :rank:)  and id != :id:";
        $cond['bind'] = [
            'id' => $this->id,
            'rank' => $this->rank,
            'code' => $this->code
        ];
        return $emoticon_image = \EmoticonImages::findFirst($cond);
    }
}