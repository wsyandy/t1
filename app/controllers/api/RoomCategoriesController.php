<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/23
 * Time: 下午1:34
 */

namespace api;

class RoomCategoriesController extends BaseController
{
    function indexAction()
    {
        $children_cond = [
            'conditions' => " status = :status: and parent_id is not null and parent_id != 0",
            'bind' => ['status' => STATUS_ON],
            'order' => 'rank desc,id desc',
        ];


        $room_categories = \RoomCategories::find($children_cond);

        $room_categories_json = [];

        foreach ($room_categories as $room_category) {
            $room_categories_json[] = ['id' => $room_category->id, 'name' => $room_category->name];
        }


        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['room_categories' => $room_categories_json]);
    }
}