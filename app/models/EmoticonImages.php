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

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'duration' => $this->duration,
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'dynamic_image_url' => $this->dynamic_image_url
        ];
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

    //是否存在 code 或 rank 相同的表情
    function isRepeating()
    {
        $cond = [];
        $cond['conditions'] = "(code = :code: or rank = :rank:)  and id != :id:";
        $cond['bind'] = [
            'id' => $this->id,
            'rank' => $this->rank,
            'code' => $this->code
        ];
        return self::findFirst($cond);
    }

    /**
     * 获取有效的表情
     * @return PaginationModel
     */
    static function findValidList($page, $per_page = 10)
    {
        $cond = [
            'conditions' => 'status = :status:',
            'bind' => [
                'status' => STATUS_ON
            ],
            'order' => 'rank desc'
        ];
        return self::findPagination($cond, $page, $per_page);
    }
}