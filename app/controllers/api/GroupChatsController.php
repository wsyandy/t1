<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/31
 * Time: 15:22
 */

namespace api;


class GroupChatsController extends BaseController
{
    //创建群聊
    function createAction()
    {
        $name = $this->params('name');
        $introduce = $this->params('introduce');
        $avatar = $this->params('avatar');

        if (isBlank($name) || isBlank($introduce) || isBlank($avatar)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请输入正确的群资料信息');
        }

        $opts = [
            'name'=>$name,
            'introduce'=>$introduce,
            'avatar'=>$avatar,
        ];

        $group_chat = \GroupChats::createGroupChat($this->currentUser(), $opts);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['group_chat'=>$group_chat->toJson()]);

    }

    //修改群聊信息
    function updateAction()
    {
        $group_chat_id = $this->params('id', 0);
        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_chat->updateRoom($this->params());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }
}