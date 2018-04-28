<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/23
 * Time: 下午1:32
 */
class RoomCategories extends BaseModel
{
    /**
     * @type RoomCategories
     */
    private $_parent;

    static $STATUS = [STATUS_ON => '正常', STATUS_OFF => '禁用'];

    static $files = ['image' => APP_NAME . '/room_categories/image/%s'];

    function getImageUrl()
    {
        $image = $this->image;

        if ($image) {
            return StoreFile::getUrl($image);
        }

        return '';
    }

    function mergeJson()
    {
        return ['image_url' => $this->image_url];
    }

    function checkFields()
    {
        if (isBlank($this->type)) {
            return [ERROR_CODE_FAIL, '类型不能为空'];
        }

        if (isBlank($this->name)) {
            return [ERROR_CODE_FAIL, '名字不能为空'];
        }

        if ($this->hasChanged('type')) {
            $room_category = self::findFirstByType($this->type);
            if (isPresent($room_category)) {
                return [ERROR_CODE_FAIL, '类型不能重复'];
            }
        }

        return [ERROR_CODE_SUCCESS, ''];
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name
        ];
    }
}