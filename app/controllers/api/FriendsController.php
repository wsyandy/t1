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
    //我的好友列表 new=1 新的好友列表
    function indexAction()
    {
        $new = $this->params('new', 0);
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);
        $friend_num = $this->currentUser()->friend_num;
        $new_friend_num = $this->currentUser()->new_friend_num;
        $users = $this->currentUser()->friendList($page, $per_page, $new);

        $num = [];
        $num['friend_num'] = $friend_num;
        $num['new_friend_num'] = $new_friend_num;
        if ($users) {
            $res = $users->toJson('users', 'toRelationJson');
            $opts = array_merge($res, $num);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $opts);
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $num);
    }

    //添加好友
    function createAction()
    {
        $self_introduce = $this->params('self_introduce');
        $opts = [];

        $opts['self_introduce'] = $self_introduce;

        if ($this->currentUser()->isFriend($this->otherUser())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已添加好友');
        }

        $this->currentUser()->addFriend($this->otherUser(), $opts);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '添加成功');
    }

    //删除好友
    function destroyAction()
    {
        $this->currentUser()->deleteFriend($this->otherUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
    }

    //同意
    function agreeAction()
    {
        $this->currentUser()->agreeAddFriend($this->otherUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '添加成功');
    }

    //清空新的朋友信息
    function clearAction()
    {
        $this->currentUser()->clearAddFriendInfo();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}