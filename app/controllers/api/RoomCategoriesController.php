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
        $parent_cond = [
            'conditions' => " status = :status: and (parent_id is null or parent_id = 0)",
            'bind' => ['status' => STATUS_ON],
            'order' => 'rank desc,id desc',
        ];

        $room_categories = \RoomCategories::find($parent_cond);

        $room_categories_json = [];

        foreach ($room_categories as $room_category) {
            $children_cond = [
                'conditions' => " status = :status: and parent_id = :parent_id:",
                'bind' => ['status' => STATUS_ON, 'parent_id' => $room_category->id],
                'order' => 'rank desc,id desc',
            ];

            $second_categories = \RoomCategories::find($children_cond);

            $second_categories_json = [];

            foreach ($second_categories as $second_category) {
                $second_categories_json[] = ['id' => $second_category->id, 'name' => $second_category->name];
            }

            $room_categories_json[] = ['name' => $room_category->name, 'second_categories' => $second_categories_json];
        }


        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['room_categories' => $room_categories_json]);
    }
}