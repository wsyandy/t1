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
        $join_type = $this->params('join_type');
        $avatar_file = $this->file('avatar_file');

        if (isBlank($name) || isBlank($introduce) || isBlank($avatar_file)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请输入正确的群资料信息');
        }

        $opts = [
            'name' => $name,
            'introduce' => $introduce,
            'join_type' => $join_type
        ];

        $res = \GroupChats::findFirstByUserId($this->currentUserId());
//        if ($res && $res->status == STATUS_ON) {
//            return $this->renderJSON(ERROR_CODE_FAIL, '已有创建的群');
//        }

        if ($res && $res->status == STATUS_OFF) {
            $opts['status'] = STATUS_ON;
            $res->updateGroupChat($opts);
            $res->updateAvatar($avatar_file);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['group_chat' => $res]);
        }

        $group_chat = \GroupChats::createGroupChat($this->currentUser(), $opts);
        $group_chat->updateAvatar($avatar_file);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['group_chat' => $group_chat->toDataJson()]);

    }

    //修改群聊信息
    function updateAction()
    {
        $group_chat_id = $this->params('id', 0);

        $avatar_file = $this->file('avatar_file');
        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        if ($avatar_file) {
            $group_chat->updateAvatar($avatar_file);
        }

        $group_chat->updateGroupChat($this->params());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功', ['group_chat' => $group_chat->toDataJson()]);
    }

    //搜索群
    function searchAction()
    {
        $keyword = $this->params('keyword');

        if (intval($keyword) > 0) {
            $cond['conditions'] = 'uid = :uid:';
            $cond['bind']['uid'] = $keyword;
        } else {
            $cond['conditions'] = 'name like :name:';
            $cond['bind']['name'] = '%' . $keyword . '%';
        }


        $group_chats = \GroupChats::find($cond);

        if (!$group_chats) {
            return $this->renderJSON(ERROR_CODE_FAIL, '未找到该群！');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', $group_chats->toJson('group_chats', 'toDataJson'));

    }

    //加入群(加入队列)
    function addGroupChatAction()
    {
        $group_chat_id = $this->params('id');

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if ($group_chat->isGroupMember($this->currentUserId())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您已经在此群中!');
        }

        if ($this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您已是本群群主!');
        }

        if ($group_chat->join_type == 'review') {
            $group_chat->reviewJoinGroupChat($this->currentUserId());

            return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功,请等待审核!', ['user' => $this->currentUser()->toGroupChatJson()]);
        }

        if ($group_chat->join_type == 'all') {
            $group_chat->addMyGroups($this->currentUserId(), $group_chat_id);
            $group_chat->joinGroupChat($this->currentUserId());
            $res['user'] = $this->currentUser()->toGroupChatJson();
            $res['user_chat'] = $group_chat->canChat($this->currentUserId());

            return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功', $res);
        }


    }

    //退出群(移出队列)
    function quitGroupChatAction()
    {
        $group_chat_id = $this->params('id');
        $user_id = $this->currentUserId();
        $group_chat = \GroupChats::findFirstById($group_chat_id);

        if ($this->currentUser()->isGroupChatHost(\GroupChats::findFirstByUserId($user_id))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '群主不能退群!');
        }

        if (!$group_chat->isGroupMember($user_id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '当前用户不在群内!');
        }

        if ($group_chat->isGroupManager($user_id)) {
            $group_chat->remManagerGroupChat($user_id);
        }


        $group_chat->kickGroupChat($user_id);
        $group_chat->remMyGroup($user_id, $group_chat_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '退出成功');
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

        if ($group_chat->isGroupMember($user_id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '当前用户已经在群内!');
        }

        $group_chat->joinGroupChat($user_id);
        $group_chat->addMyGroups($user_id, $group_chat_id);

        $user = \Users::findFirstById($user_id);
        $user->user_chat = $group_chat->canChat($user_id);
        $res['user'] = $user->toGroupChatJson();


        return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功', $res);
    }

    //添加管理员
    function addManagerAction()
    {
        $user_id = $this->params('user_id');    //邀请管理员的id
        $group_chat_id = $this->params('id');   //当前群的id

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        if (!$group_chat->isGroupMember($user_id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '该用户不在群内！');
        }

        if ($group_chat->isGroupManager($user_id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '该用户已经是本群管理员！');
        }

        $group_chat->managerGroupChat($user_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功');
    }

    //删除管理员
    function deleteManagerAction()
    {
        $user_id = $this->params('user_id');
        $group_chat_id = $this->params('id');   //当前群的id

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }
        if (!$group_chat->isGroupManager($user_id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '该用户不是本群管理员');
        }

        $group_chat->remManagerGroupChat($user_id);

        return $this->renderJSON(ERROR_CODE_FAIL, '删除成功');
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

        if (!$group_chat->isGroupMember($user_id)) {
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

    //解散群
    function disbandAction()
    {
        $group_chat_id = $this->params('id');
        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }
        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_chat->status = STATUS_OFF;
        $group_chat->update();
        $group_chat->remAllGroupMembers();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '解散成功');
    }

    //设置加群方式
    function setJoinTypeAction()
    {
        $group_chat_id = $this->params('id');
        $join_type = $this->params('join_type');

        $group_chat = \GroupChats::findFirstById($group_chat_id);
        if (!$group_chat || !$join_type) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }
        if (!$this->currentUser()->isGroupChatHost($group_chat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }
        $group_chat->join_type = $join_type;
        $group_chat->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '设置成功');
    }

    //全员解禁与禁言
    function openChatAction()
    {
        $group_chat_id = $this->params('id');
        $group_chat = \GroupChats::findFirstById($group_chat_id);

        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isGroupChatHost($group_chat) && !$group_chat->isGroupManager($this->currentUserId())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_chat->chat = true;
        $group_chat->update();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function closeChatAction()
    {
        $group_chat_id = $this->params('id');
        $group_chat = \GroupChats::findFirstById($group_chat_id);

        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isGroupChatHost($group_chat) && !$group_chat->isGroupManager($this->currentUserId())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_chat->chat = false;
        $group_chat->update();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    //单个用户解禁与禁言
    function openUserChatAction()
    {
        $group_chat_id = $this->params('id');
        $user_id = $this->params('user_id');
        $group_chat = \GroupChats::findFirstById($group_chat_id);

        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isGroupChatHost($group_chat) && !$group_chat->isGroupManager($this->currentUserId())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_chat->setChat(true, $user_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function closeUserChatAction()
    {
        $group_chat_id = $this->params('id');
        $user_id = $this->params('user_id');
        $group_chat = \GroupChats::findFirstById($group_chat_id);

        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isGroupChatHost($group_chat) && !$group_chat->isGroupManager($this->currentUserId())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $group_chat->setChat(false, $user_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    //用户进入群聊
    function entranceAction()
    {
        $group_chat_id = $this->params('id');
        $group_chat = \GroupChats::findFirstById($group_chat_id);
        $user = $this->currentUser();

        if (!$group_chat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$user->isGroupChatHost($group_chat) && !$group_chat->isGroupMember($user->id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不在群内');
        }

        if ($user->isGroupChatHost($group_chat)) {
            $role = USER_ROLE_HOST_BROADCASTER;  //5
        }
        if ($group_chat->isGroupMember($user->id)) {
            $role = USER_ROLE_NO;               //0
        }
        if ($group_chat->isGroupManager($user->id)) {
            $role = USER_ROLE_MANAGER;          // 10
        }
        $user->role = $role;
        $user->user_chat = $group_chat->canChat($user->id);
        $user_json = $user->toGroupChatJson();
        $res['group_chat'] = $group_chat->toDataJson();
        $res['user'] = $user_json;

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', $res);

    }

    //搜索群成员
    function searchUsersAction()
    {
        $nickname = $this->params('nickname');
        $group_chat_id = $this->params('id');

        if (isBlank($nickname) || isBlank($group_chat_id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $cond['conditions'] = 'nickname like :nickname:';
        $cond['bind']['nickname'] = '%' . $nickname . '%';
        $users = \Users::find($cond);

        $users_json = $users->toJson('users', 'toGroupChatJson');
        $group_chat = \GroupChats::findById($group_chat_id);

        $members = [];
        foreach ($users_json['users'] as $user) {
            if ($group_chat->isGroupMember($user['id'])) {
                $user_role = new \Users();
                if ($user_role->isGroupChatHost($group_chat)) {
                    $role = USER_ROLE_HOST_BROADCASTER;  //5
                }
                if ($group_chat->isGroupMember($user['id'])) {
                    $role = USER_ROLE_NO;               //0
                }
                if ($group_chat->isGroupManager($user['id'])) {
                    $role = USER_ROLE_MANAGER;          // 10
                }

                $members[] = [
                    'id' => $user['id'],
                    'nickname' => $user['nickname'],
                    'avatar_small_url' => $user['avatar_small_url'],
                    'role' => $role,
                ];
            }
        }


        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['users' => $members]);
    }

    //查看所有群成员
    function membersInfoAction()
    {
        $group_chat_id = $this->params('id');
        $group_chat = \GroupChats::findFirstById($group_chat_id);

        $per_page = $this->params('per_page', 10);
        $page = $this->params('page');
        $offset = $per_page * ($page - 1);

        $group_host_id = $group_chat->user_id;
        $group_manager_ids = $group_chat->getAllGroupManagers();
        $group_member_ids = $group_chat->getAllGroupMembers();

        $host_id = [];
        array_push($host_id, $group_host_id);
        $total_all_user_ids = array_merge($host_id, $group_manager_ids, $group_member_ids);
        $total = count($total_all_user_ids);

        $user_ids = array_slice($total_all_user_ids, $offset, $per_page);

        $users = \Users::findByIds($user_ids);

        foreach ($users as $user) {
            if ($user->isGroupChatHost($group_chat)) {
                $role = USER_ROLE_HOST_BROADCASTER;  //5
            }
            if ($group_chat->isGroupMember($user->id)) {
                $role = USER_ROLE_NO;               //0
            }
            if ($group_chat->isGroupManager($user->id)) {
                $role = USER_ROLE_MANAGER;          // 10
            }
            $user->role = $role;
        }
        $users = new \PaginationModel($users, $total, $page, $per_page);
        $users->clazz = "Users";

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toGroupChatJson'));
    }

    //查看单个群成员
    function getMemberInfoAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findFirstById($user_id);
        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['user' => $user]);
    }

    //发群消息
    function sendMsgAction()
    {
        $group_chat_id = $this->params('id');
        $content = $this->params('content');
        $content_type = $this->params('content_type'); // text image voice
        $file = $this->params('file');

        if (isDevelopmentEnv() && isBlank($content) && 'text' == $content_type) {
            return $this->renderJSON(ERROR_CODE_FAIL, '内容不能为空');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');
    }

    //我的群聊
    function myGroupChatsAction()
    {
        $group_chat = new \GroupChats();
        $total_group_chat_ids = $group_chat->getMyGroupIds($this->currentUserId());
        $total = count($total_group_chat_ids);
        $per_page = $this->params('per_page', 10);
        $page = $this->params('page');
        $offset = $per_page * ($page - 1);

        $group_chat_ids = array_slice($total_group_chat_ids, $offset, $per_page);
        $group_chats = \GroupChats::findByIds($group_chat_ids);
        $group_chats = new \PaginationModel($group_chats, $total, $page, $per_page);
        $group_chats->clazz = 'GroupChats';
        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', $group_chats->toJson('group_chats', 'toDataJson'));
    }


}