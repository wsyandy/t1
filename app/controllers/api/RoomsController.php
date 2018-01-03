<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:47
 */

namespace api;

class RoomsController extends BaseController
{

    //创建房间
    function createAction()
    {
        $name = $this->params('name');
        if(isBlank($name))
        {
            $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }
        $room = \Rooms::createRoom($this->currentUser(), $name);
        if ($room) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', array('id' => $room->id,'name' => $room->name,'chat' => $room->chat));
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

    //更新房间信息
    function updateAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }
        $room->updateRoom($this->params());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }

}