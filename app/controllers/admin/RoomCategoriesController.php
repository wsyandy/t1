<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/23
 * Time: 下午7:45
 */
namespace admin;

class RoomCategoriesController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('room_category');
        $page = $this->params('page');
        $cond['order'] = 'rank desc,id desc';
        if (isset($cond['conditions'])) {
            $cond['conditions'] .= ' and parent_id is null or parent_id = 0';
        } else {
            $cond['conditions'] = ' parent_id is null or parent_id = 0';
        }
        $room_categories = \RoomCategories::findPagination($cond, $page);
        $this->view->room_categories = $room_categories;
    }

    function newAction()
    {
        $room_category = new \RoomCategories();
        $room_category->status = STATUS_ON;
        $parent_id = intval($this->params('parent_id'));
        if ($parent_id) {
            $room_category->parent_id = $parent_id;
        }
        $this->view->room_category = $room_category;
    }

    function createAction()
    {
        $room_category = new \RoomCategories();
        $this->assign($room_category, 'room_category');


        if ($room_category->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $room_category);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['room_category' => $room_category->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

    function editAction()
    {
        $room_category = \RoomCategories::findFirstById($this->params('id'));
        $this->view->room_category = $room_category;
    }

    function updateAction()
    {
        $room_category = \RoomCategories::findFirstById($this->params('id'));
        $this->assign($room_category, 'room_category');

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room_category);
        if ($room_category->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['room_category' => $room_category->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

    function childrenAction()
    {
        $parent_id = $this->params('parent_id');
        $room_categories = \RoomCategories::findByParentId($parent_id);

        $this->view->parent_id = $parent_id;
        $this->view->room_categories = $room_categories;
    }
}