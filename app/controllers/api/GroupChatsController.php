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
        $avatar_file = $this->file('avatar_file');

        if (isBlank($name) || isBlank($introduce) || isBlank($avatar_file)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请输入正确的群资料信息');
        }

        $opts = [
            'name' => $name,
            'introduce' => $introduce,
        ];

        $group_chat = \GroupChats::createGroupChat($this->currentUser(), $opts);
        $group_chat->updateAvatar($avatar_file);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功');

    }

    //修改群聊信息
    function updateAction()
    {
        $group_chat_id = $this->params('id', 0);

        $avatar_file = $this->params('avatar_file');
        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }
        $group_chat->updateAvatar($avatar_file);
        $group_chat->updateGroupChat($this->params());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }

    //加入群聊
    function addGroupChatAction()
    {
        $group_chat_id = $this->params('id');

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if ($this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您已是本群群主!');
        }

        if ($group_chat->join_type == 'review') {
            $group_chat->reviewJoinGroupChat($this->currentUserId());

            return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功,请等待审核!',['user'=>$this->currentUser(),'status'=>2]);
        }

        if ($group_chat->join_type == 'all') {
            $group_chat->joinGroupChat($this->currentUserId());

            return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功',['user'=>$this->currentUser(),'status'=>1]);
        }



    }

    //群主邀请
    function hostInviteAction()
    {
        $user_id = $this->params('user_id');    //邀请用户的id
        $group_chat_id = $this->params('id');   //当前群的id

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_chat->joinGroupChat($user_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功');
    }

    //加入管理员
    function addManagerAction()
    {
        $user_id = $this->params('user_id');    //邀请管理员的id
        $group_chat_id = $this->params('id');   //当前群的id

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }
        $group_member_ids = $group_chat->getAllGroupMembers();

        if(!in_array($user_id,$group_member_ids)){
            return $this->renderJSON(ERROR_CODE_FAIL, '该用户不在群内！');
        }

        $group_chat->managerGroupChat($user_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功');
    }

    //踢出群聊
    function kickAction()
    {
        $user_id = $this->params('user_id');
        $group_chat_id = $this->params('id');

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$this->currentUser()->isGroupChatHost($group_chat) && !$group_chat->isGroupManager($this->currentUserId())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_member_ids = $group_chat->getAllGroupMembers();
        if(!in_array($user_id,$group_member_ids)){
            return $this->renderJSON(ERROR_CODE_FAIL, '该用户不在群内！');
        }

        $group_chat->kickGroupChat($user_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    //审核入群
    function reviewJoinAction()
    {
        $user_id = $this->params('user_id');
        $group_chat_id = $this->params('id');

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$this->currentUser()->isGroupChatHost($group_chat) && !$group_chat->isGroupManager($this->currentUserId())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_chat->remReviewGroupChat($user_id);
        $group_chat->joinGroupChat($user_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功');

    }
}