<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/23
 * Time: 下午9:45
 */
class ProductMenus extends BaseModel
{
    static $STATUS = [STATUS_ON => '正常', STATUS_OFF => '关闭'];

    function checkFields()
    {
        if (isBlank($this->type)) {
            return [ERROR_CODE_FAIL, '类型不能为空'];
        }

        if ($this->hasChanged('type')) {
            $room_category = RoomCategories::findFirstByType($this->type);
            if (isBlank($room_category)) {
                return [ERROR_CODE_FAIL, '类型不合法'];
            }

            $product_menu = ProductMenus::findFirstByType($this->type);
            if (isPresent($product_menu)) {
                return [ERROR_CODE_FAIL, '类型不能重复'];
            }
        }

        return [ERROR_CODE_SUCCESS, ''];
    }
}