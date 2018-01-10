<?php

namespace admin;

class UsersController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('user');
        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $cond['order'] = 'id desc';
        $users = \Users::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->users = $users;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function editAction()
    {
        $user = \Users::findFirstById($this->params('id'));
        $this->view->user = $user;
    }

    function updateAction()
    {
        $user = \Users::findFirstById($this->params('id'));
        $this->assign($user, 'user');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $user);
        $user->update();
        $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('user' => $user->toJson()));
    }

    function detailAction()
    {
        $user = \Users::findFirstById($this->params('id'));
        $this->view->user = $user;
    }

    function basicAction()
    {
        $user = \Users::findFirstById($this->params('id'));
        $devices = \Devices::findBy(['user_id' => $user->id]);
        $this->view->devices = $devices;
        $this->view->user = $user;
    }

    //随机添加好友
    function addFriendsAction()
    {
        $id = $this->params('id', 0);
        $current_user = \Users::findFirstById($id);
        if (!$current_user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }

        $users = \Users::find(['conditions' => 'id != ' . $id, 'limit' => 100]);

        foreach ($users as $user) {
            $current_user->addFriend($user, ['self_introduce' => '您好']);
        }

        $key = 'friend_total_list_user_id_' . $id;

        $user_db = \Users::getUserDb();
        $user_ids = $user_db->zrange($key, 0, -1);
        debug($user_ids);
        $agree_num = 0;
        foreach ($user_ids as $user_id) {
            $user = \Users::findFirstById($user_id);
            $num = mt_rand(1, 100);
            if ($num <= 10 && $user) {
                $current_user->agreeAddFriend($user);
                $agree_num++;
            }
        }

        if ($agree_num == 0) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作失败');
        } else {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '添加成功');
        }
    }

    //好友列表
    function friendListAction()
    {
        $id = $this->params('id', 0);
        $user = \Users::findFirstById($id);
        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }

        $new = $this->params('new', 0);
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);
        $users = $user->friendList($page, $per_page, $new);
        $this->view->users = $users;
    }

    //随机关注
    function followAction()
    {
        $id = $this->params('id', 0);
        $current_user = \Users::findFirstById($id);
        if (!$current_user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }

        $users = \Users::find(['conditions' => 'id !=' . $id, 'limit' => 20]);
        $follow_num = 0;
        foreach ($users as $user) {
            $current_user->follow($user);
            $follow_num++;
        }
        if ($follow_num == 0) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作失败');
        } else {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '添加成功');
        }
    }

    //我关注的人
    function followersAction()
    {
        $id = $this->params('id', 0);
        $user = \Users::findFirstById($id);
        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }

        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $users = $user->followList($page, $per_page);
        $this->view->users = $users;
    }
}