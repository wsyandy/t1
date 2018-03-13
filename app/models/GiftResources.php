<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/13
 * Time: 下午4:28
 */

class GiftResources extends BaseModel
{
    static $status = [STATUS_ON => '正常', STATUS_OFF => '过期'];

    function resourceFileUrl()
    {
        return StoreFile::getUrl($this->resource_file);
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'resource_file_url' => $this->resourceFileUrl()
        ];
    }
}