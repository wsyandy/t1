<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/23
 * Time: 下午1:32
 */
class RoomTags extends BaseModel
{
    static $STATUS = [STATUS_ON => '正常', STATUS_OFF => '禁用'];

    function checkFields()
    {
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