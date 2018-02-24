<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/24
 * Time: 上午10:54
 */

namespace admin;

class RoomThemesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = 30;
        $room_themes = \RoomThemes::findPagination(['order' => 'id desc'], $page, $per_page);
        $this->view->room_themes = $room_themes;
    }

    function newAction()
    {
        $room_theme = new \RoomThemes();
        $this->view->room_theme = $room_theme;
    }

    function createAction()
    {
        $room_theme = new \RoomThemes();
        $this->assign($room_theme, 'room_theme');
        if ($room_theme->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $room_theme);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('room_theme' => $room_theme->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '创建失败');
        }
    }

    function editAction()
    {
        $room_theme = \RoomThemes::findById($this->params('id'));
        $this->view->room_theme = $room_theme;
    }

    function updateAction()
    {
        $room_theme = \RoomThemes::findById($this->params('id'));
        $this->assign($room_theme, 'room_theme');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room_theme);
        if ($room_theme->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('room_theme' => $room_theme->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }
}