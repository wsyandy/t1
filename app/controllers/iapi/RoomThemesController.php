<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/24
 * Time: 下午4:05
 */

namespace iapi;

class RoomThemesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 100);
        $room_themes = \RoomThemes::findValidList($this->currentUser(), $page, $per_page);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_themes->toJson('room_themes', 'toSimpleJson'));
    }
}