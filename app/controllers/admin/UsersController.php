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

    function resetPasswordAction()
    {
        if ($this->request->isPost()) {
            $user = \Users::findFirstById($this->params('id'));
            $password = $this->params('password');
            if (!isBlank($password)) {
                if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '请设置6~16位的密码');
                }
                $user->password = md5($password);
            }
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $user);
            $user->update();
            $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功');
        } else {
            $this->view->id = $this->params('id');
        }
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

    /**
     * 发送系统消息
     */
    function sendMessageAction()
    {
         $user = \Users::findById($this->params('id'));
         if ($this->request->isPost()) {
             $content = $this->params('content');
             $content_type = CHAT_CONTENT_TYPE_TEXT;
             $chat = \Chats::sendSystemMessage($user->id, $content_type, $content);
             if ($chat) {
                 return $this->renderJSON(
                     ERROR_CODE_SUCCESS, '发送成功',
                     array('chat' => $chat->toJson())
                 );
             } else {
                 return $this->renderJSON(ERROR_CODE_FAIL, '发送失败');
             }
         }
         $this->view->user = $user;
    }

    /**
     * 个推测试
     */
    function getuiAction()
    {
        $receiver = \Users::findById($this->params('receiver_id'));
        if ($this->request->isPost()) {
            $result = \GeTuiMessages::testPush($receiver, $this->params('title'), $this->params('body'),
                $this->params('client_url'));
            if ($result) {
                $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');
            } else {
                $this->renderJSON(ERROR_CODE_FAIL, '发送失败');
            }

        }
        $this->view->receiver = $receiver;
    }
}