<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/23
 * Time: 下午1:34
 */

namespace xcx;

class RoomTagsController extends BaseController
{
    function indexAction()
    {
        $cond = [
            'conditions' => " status = :status:",
            'bind' => ['status' => STATUS_ON],
            'order' => 'rank desc,id desc',
        ];


        $room_tags = \RoomTags::find($cond);

        $room_tags_json = [];

        foreach ($room_tags as $room_tag) {
            $room_tags_json[] = ['id' => $room_tag->id, 'name' => $room_tag->name];
        }


        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['room_tags' => $room_tags_json]);
    }
}