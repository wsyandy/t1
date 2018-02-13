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

        $user_id = $this->params("user[id_eq]");
        $mobile = $this->params("user[mobile_eq]");
        $user_type = $this->params("user[user_type_eq]");

        if (!$user_id && !$mobile && !$user_type) {
            if (isset($cond['conditions'])) {
                $cond['conditions'] .= " and user_type = " . USER_TYPE_ACTIVE;
            } else {
                $cond['conditions'] = "user_type = " . USER_TYPE_ACTIVE;
            }
        }

        $cond['order'] = 'id desc';
        $users = \Users::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->users = $users;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->user_types = \UserEnumerations::$USER_TYPE;
    }

    function avatarAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);

        if (isPresent($this->params('avatar_auth')) && intval($this->params('avatar_auth') == AUTH_SUCCESS)) {
            $users = \Users::findAuthedUsers($page, $per_page);
        } else {
            $users = \Users::findWaitAuthUsers($page, $per_page);
        }
        $this->view->users = $users;
    }

    function authAction()
    {
        $user_id = $this->params('id');
        $user = \Users::findById($user_id);
        if ($user) {
            $user->changeAvatarAuth($this->params('avatar_auth'));
            $user->removeFromWaitAuthList();
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '审核成功');
    }

    function batchUpdateAvatarAction()
    {
        $users = \Users::findByIds($this->params('ids'));
        foreach ($users as $user) {
            $user->changeAvatarAuth($this->params('avatar_auth'));
            $user->removeFromWaitAuthList();
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '审核成功');
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
        $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['user' => $user->toJson()]);
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

    function selectAvatarAction()
    {
        $user_id = 1;
        $this->view->user_id = $user_id;
    }

    function avatarInfoAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 30);
        $user_id = 1;
        $auth_type = $this->params('auth_type');
        $auth_status = $this->params('auth_status');
        $cond = ['conditions' => 'user_id =' . $user_id, 'order' => 'id asc'];
        $hot_cache = \Albums::getHotWriteCache();
        $auth_ids = [];

        if ($auth_type) {
            $auth_ids = $hot_cache->zrange("albums_auth_type_{$auth_type}_list_user_id_" . $user_id, 0, -1);

            debug($auth_ids, $auth_ids);
            if (count($auth_ids) > 0) {
                $cond['conditions'] .= ' and id in (' . implode(',', $auth_ids) . ')';
            }
        }

        if ($auth_status) {
            $cond['conditions'] .= " and auth_status = $auth_status";

            if (AUTH_SUCCESS == $auth_status) {
                $ids = $hot_cache->zrange("albums_auth_type_total_list_user_id_" . $user_id, 0, -1);

                if (count($auth_ids) > 0) {
                    $ids = array_diff($ids, $auth_ids);
                }

                if (count($ids) > 0) {
                    $cond['conditions'] .= ' and id not in (' . implode(',', $ids) . ')';
                }
            }
        }

        debug($cond);
        $albums = \Albums::findPagination($cond, $page, $per_page);

        $this->view->albums = $albums;
        $this->view->user_id = $user_id;
        $this->view->auth_status = $auth_status;
    }
}