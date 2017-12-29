<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:48
 */

namespace api;

class FriendsController extends BaseController
{
    //我的好友列表 type=1 好友列表 type=2 新的好友列表
    function indexAction()
    {
        $type = $this->params('type', 1);
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);
        $users = $this->currentUser()->friendList($page, $per_page, $type);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toRelationJson'));
    }

    //添加好友
    function createAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $this->currentUser()->addFriend($this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '添加成功');
    }

    //删除好友
    function destroyAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $this->currentUser()->deleteFriend($this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
    }

    //同意
    function agreeAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $this->currentUser()->agreeAddFriend($this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '添加成功');
    }

    //清空新的朋友信息
    function clearAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $this->currentUser()->clearAddFriendInfo();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}