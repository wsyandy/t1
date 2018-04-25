<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/23
 * Time: 下午7:45
 */

namespace admin;

class RoomTagsController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('room_tag');
        $page = $this->params('page');
        $cond['order'] = 'rank desc,id desc';
        if (isset($cond['conditions'])) {
            $cond['conditions'] .= ' and parent_id is null or parent_id = 0';
        } else {
            $cond['conditions'] = ' parent_id is null or parent_id = 0';
        }
        $room_tags = \RoomTags::findPagination($cond, $page);
        $this->view->room_tags = $room_tags;
    }

    function newAction()
    {
        $room_tag = new \RoomTags();
        $room_tag->status = STATUS_ON;
        $parent_id = intval($this->params('parent_id'));
        if ($parent_id) {
            $room_tag->parent_id = $parent_id;
        }
        $this->view->room_tag = $room_tag;
    }

    function createAction()
    {
        $room_tag = new \RoomTags();
        $this->assign($room_tag, 'room_tag');

        list($error_code, $error_reason) = $room_tag->checkFields();

        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        if ($room_tag->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $room_tag);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['room_tag' => $room_tag->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

    function editAction()
    {
        $room_tag = \RoomTags::findFirstById($this->params('id'));
        $this->view->room_tag = $room_tag;
    }

    function updateAction()
    {
        $room_tag = \RoomTags::findFirstById($this->params('id'));
        $this->assign($room_tag, 'room_tag');
        list($error_code, $error_reason) = $room_tag->checkFields();

        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room_tag);
        if ($room_tag->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['room_tag' => $room_tag->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

    function childrenAction()
    {
        $parent_id = $this->params('parent_id');
        $room_tags = \RoomTags::findByParentId($parent_id);

        $this->view->parent_id = $parent_id;
        $this->view->room_tags = $room_tags;
    }
}