<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/28
 * Time: 下午2:53
 */

namespace admin;

class RoomCategoryKeywordsController extends BaseController
{
    function indexAction()
    {
        $room_category_id = $this->params('room_category_id');
        $cond = ['conditions' => 'room_category_id = :room_category_id:', 'bind' => ['room_category_id' => $room_category_id]];
        $page = $this->params('page');
        $room_category_keywords = \RoomCategoryKeywords::findPagination($cond, $page, 30);
        $this->view->room_category_id = $room_category_id;
        $this->view->room_category_keywords = $room_category_keywords;
    }

    function newAction()
    {
        $room_category_id = $this->params('room_category_id');

        $room_category_keyword = new \RoomCategoryKeywords();
        $room_category_keyword->room_category_id = $room_category_id;

        $this->view->room_category_keyword = $room_category_keyword;
    }

    function createAction()
    {
        $room_category_keyword = new \RoomCategoryKeywords();
        $this->assign($room_category_keyword, 'room_category_keyword');

        if ($room_category_keyword->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',  ['room_category_keyword' => $room_category_keyword->toJson()]);
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '');
    }
}