<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/24
 * Time: 上午10:35
 */
class RoomThemes extends BaseModel
{
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $files = ['icon' => APP_NAME . '/room_themes/icon/%s', 'image' => APP_NAME . '/room_themes/image/%s'];

    function getIconUrl()
    {
        if (isBlank($this->icon)) {
            return '';
        }

        return StoreFile::getUrl($this->icon);
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
            'icon_url' => $this->icon_url,
            'name' => $this->name,
        ];
    }

    function toJson()
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'icon_url' => $this->icon_url,
            'name' => $this->name,
            'status_text' => $this->status_text,
            'rank' => $this->rank,
        ];
    }

    /**
     * 获取有效的主题
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

