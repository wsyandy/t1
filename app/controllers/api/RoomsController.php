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
    //黑名单列表
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $users = $this->currentUser()->blackList($page, $per_page);

        if ($users) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toRelationJson'));
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //创建房间
    function createAction()
    {
        $name = $this->params('name');
        if(isBlank($name))
        {
            $this->renderJSON(ERROR_CODE_FAIL, '名称错误');
        }
        $room = \Rooms::createRoom($this->currentUser(), $name);
        if ($room) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', array('id' => $room->id,'name' => $room->name,'chat' => $room->chat));
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

}