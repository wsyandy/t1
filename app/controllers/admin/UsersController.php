<?php

namespace admin;

class UsersController extends BaseController
{
    function indexAction()
    {
        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $user_id = $this->params("user[id_eq]");
        $mobile = $this->params("user[mobile_eq]");
        $user_type = $this->params("user[user_type_eq]");
        $user_status = $this->params("user[user_status_eq]");
        $nickname = $this->params("nickname");
        $product_channel_id = $this->params("user[product_channel_id_eq]");

        $cond = $this->getConditions('user');
        $cond['order'] = 'id desc';

        if ($nickname) {

            if (isset($cond['conditions'])) {
                $cond['conditions'] .= " and nickname like '%$nickname%'";
            } else {
                $cond['conditions'] = "nickname like '%$nickname%'";
            }
        }


        $users = \Users::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->users = $users;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->user_types = \UserEnumerations::$USER_TYPE;
        $this->view->user_type = intval($user_type);
        $this->view->user_status = $user_status == '' ? '' : intval($user_status);
        $this->view->mobile = $mobile;
        $this->view->user_id = $user_id;
        $this->view->nickname = $nickname;
        $this->view->product_channel_id = intval($product_channel_id);
    }

    function avatarAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);

        if (isPresent($this->params('avatar_status')) && intval($this->params('avatar_status') == AUTH_SUCCESS)) {
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
            $user->changeAvatarAuth($this->params('avatar_status'));
            $user->removeFromWaitAuthList();
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '审核成功');
    }

    function batchUpdateAvatarAction()
    {
        $users = \Users::findByIds($this->params('ids'));
        foreach ($users as $user) {
            $user->changeAvatarAuth($this->params('avatar_status'));
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
        $current_room = $user->current_room;
        $current_room_seat_id = $user->current_room_seat_id;

        if ($user->hasChanged('user_status')
            && ($user->user_status == USER_STATUS_BLOCKED_ACCOUNT || $user->user_status == USER_STATUS_BLOCKED_DEVICE) && $current_room
        ) {

            // 加上声网踢出频道
            $current_room->exitRoom($user, true);
            ////$current_room->pushExitRoomMessage($user, $current_room_seat_id, true);
        }

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
            $title = $this->params('title');
            $image_url = $this->params('image_url');
            $content_type = $this->params('content_type', 'text/plain');
            $url = $this->params('url');

            $attrs = [
                'sender_id' => SYSTEM_ID,
                'receiver_id' => $user->id,
                'content' => $content,
                'content_type' => $content_type,
                'image_url' => $image_url,
                'title' => $title,
                'url' => $url
            ];

            $chat = \Chats::createChat($attrs);

            //$chat = \Chats::sendSystemMessage($user->id, $content_type, $content);
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
        $title = $this->params('title');
        $body = $this->params('body');
        $client_url = $this->params('client_url');
        $show_type = $this->params('show_type', '');

        $opts = ['title' => $title, 'body' => $body, 'client_url' => $client_url, 'show_type' => $show_type];

        if ($this->request->isPost()) {
            $result = \GeTuiMessages::testPush($receiver, $opts);
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
            } else {
                $cond['conditions'] .= ' and id in (null)';
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

    function unbindThirdAccountAction()
    {
        $id = $this->params('id');
        $user = \Users::findFirstById($id);

        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $user->third_name = 'jiebang';
        $user->third_unionid = uniqid() . time();
        $user->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '解绑成功');
    }

    function joinCompanyAction()
    {
        $id = $this->params('id');
        $user = \Users::findFirstById($id);
        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $user->organisation = USER_ORGANISATION_COMPANY;
        $user->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '加入成功');


    }

    function companyUserAction()
    {
        $cond = $this->getConditions('company_user');
        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $user_id = $this->params("company_user[id_eq]");
        $mobile = $this->params("company_user[mobile_eq]");
        $user_type = $this->params("company_user[user_type_eq]");

        if (!$user_id && !$mobile && !$user_type) {
            if (isset($cond['conditions'])) {
                $cond['conditions'] .= " and user_type = " . USER_TYPE_ACTIVE;
            } else {
                $cond['conditions'] = "user_type = " . USER_TYPE_ACTIVE;
            }
        }
        $cond['order'] = 'id desc';

        $id = $this->params('id');

        if ($id) {
            $cond['conditions'] = ' id = ' . $id;
        }

        $cond['conditions'] .= ' and organisation = ' . USER_ORGANISATION_COMPANY;

        $company_users = \Users::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->users = $company_users;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->user_types = \UserEnumerations::$USER_TYPE;
    }

    //转换身份，公司员工转换为个人，仅测试环境可供使用
    function clearCompanyUserAction()
    {
        if (isProduction() && $this->currentOperator()->id != 11) {
            return $this->renderJSON(ERROR_CODE_FAIL, '正式环境不支持');
        }

        $id = $this->params('id');
        $user = \Users::findFirstById($id);
        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $user->organisation = USER_ORGANISATION_PERSONAGE;
        $user->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
    }

    function dayRankListAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 100);
        $type = $this->params('type', 'wealth');
        $stat_at = $this->params('stat_at', date("Y-m-d"));
        $product_channel_id = $this->params('product_channel_id');
        $opts = ['date' => date("Ymd", strtotime($stat_at)), 'product_channel_id' => $product_channel_id];

        $users = \Users::findFieldRankList('day', $type, $page, $per_page, $opts);
        $this->view->users = $users;
        $this->view->stat_at = $stat_at;
        $this->view->types = ['charm' => '魅力榜', 'wealth' => '财富榜'];
        $this->view->type = $type;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channel_id = intval($product_channel_id);
    }

    function weekRankListAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 100);
        $type = $this->params('type', 'wealth');
        $stat_at = $this->params('stat_at', date("Y-m-d", beginOfWeek()));

        $start = date("Ymd", strtotime($stat_at));
        $end = date("Ymd", strtotime($start) + 6 * 86400);

        $product_channel_id = $this->params('product_channel_id');

        $opts = ['start' => $start, 'end' => $end, 'product_channel_id' => $product_channel_id];

        $users = \Users::findFieldRankList('week', $type, $page, $per_page, $opts);
        $this->view->users = $users;
        $this->view->types = ['charm' => '魅力榜', 'wealth' => '财富榜'];
        $this->view->type = $type;
        $this->view->stat_at = $stat_at;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channel_id = intval($product_channel_id);
    }

    function totalRankListAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 100);
        $type = $this->params('type', 'wealth');

        $product_channel_id = $this->params('product_channel_id');

        $opts = ['product_channel_id' => $product_channel_id];

        $users = \Users::findFieldRankList('total', $type, $page, $per_page, $opts);
        $this->view->users = $users;
        $this->view->types = ['charm' => '魅力榜', 'wealth' => '财富榜'];
        $this->view->type = $type;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channel_id = intval($product_channel_id);
    }

    function reservedAction()
    {
        $good_no_uid = 'user_good_no_uid_list';
        $id = $this->params('id');
        $user_db = \Users::getUserDb();

        if ($id) {

            if (!$user_db->zscore($good_no_uid, $id)) {
                $user_ids = [];
            } else {
                $user_ids[] = $id;
            }

            $total_entries = count($user_ids);

        } else {
            $page = $this->params('page', 1);
            $per_page = $this->params('per_page', 100);

            $total_entries = $user_db->zcard($good_no_uid);

            $offset = $per_page * ($page - 1);
            $user_ids = $user_db->zrevrange($good_no_uid, $offset, $offset + $per_page - 1);
        }

        $objects = [];

        foreach ($user_ids as $user_id) {
            $user = new \Users();
            $user->uid = $user_id;
            $objects[] = $user;
        }

        $pagination = new \PaginationModel($objects, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        $this->view->users = $pagination;
    }

    //离线24小时用户唤醒统计
    function wakeupStatAction()
    {
        $wake_up_user_days_key = "wake_up_user_days_key_product_channel_id1";
        $user_db = \Users::getUserDb();
        $wake_up_days = $user_db->zrange($wake_up_user_days_key, 0, -1);
        $datas = [];
        $product_channel_id = 1;

        foreach ($wake_up_days as $stat_at) {

            $send_user_stat_key = "wake_up_user_send_gift_stat_key_product_channel_id$product_channel_id" . $stat_at;
            $data = $user_db->hgetall($send_user_stat_key);

            if ($data) {
                $data['stat_at'] = $stat_at;
                $datas[] = $data;
            }
        }

        $this->view->datas = $datas;
        $this->view->product_channel_id = $product_channel_id;
    }

    //添加屏蔽附近的人
    function addBlockedNearbyUserAction()
    {
        if ($this->request->isPost()) {

            $user_uid = $this->params('user_uid');
            if (!$user_uid) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            $user = \Users::findFirstByUid($user_uid);
            if ($user) {
                $hot_cache = \Users::getHotWriteCache();
                $key = "blocked_nearby_user_list";
                $hot_cache->zadd($key, time(), $user->id);
                $user->delGeoHashRank();
            }

            return $this->response->redirect('/admin/users/blocked_nearby_user_list');
        }
    }

    function blockedNearbyUserListAction()
    {
        $user = null;
        $user_uid = $this->params('user_uid');
        if ($user_uid) {
            $user = \Users::findFirstByUid($user_uid);
        }

        $hot_cache = \Users::getHotWriteCache();
        $key = "blocked_nearby_user_list";

        if ($user && $hot_cache->zscore($key, $user->id) > 0) {
            $user_ids = [$user->id];
        } else {
            $user_ids = $hot_cache->zrange($key, 0, -1);
        }

        $page = $this->params('page');
        if (!$user_ids) {
            $cond = ['conditions' => 'id < 1'];
        } else {
            $cond = ['conditions' => 'id in (' . implode(',', $user_ids) . ")"];
        }

        $this->view->users = \Users::findPagination($cond, $page, 30);
    }

    function deleteBlockedNearbyUserAction()
    {

        $user_uid = $this->params('user_uid');
        $user = \Users::findFirstByUid($user_uid);
        if ($user) {

            $hot_cache = \Users::getHotWriteCache();
            $key = "blocked_nearby_user_list";
            $hot_cache->zrem($key, $user->id);

            $geo_hash = new \geo\GeoHash();
            $hash = $geo_hash->encode($user->latitude, $user->longitude);
            info($user->id, $user->latitude, $user->longitude, $hash);

            if ($hash) {
                $user->geo_hash = $hash;
            }

            $user->update();
        }

        $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/users/blocked_nearby_user_list']);
    }

    function resetUidAction()
    {
        if ($this->request->isPost()) {

            $user = \Users::findFirstById($this->params('id'));
            $uid = $this->params('uid');

            if (isBlank($uid)) {
                return $this->renderJSON(ERROR_CODE_FAIL, 'id不能为空');
            }

            $exit_user = \Users::findFirstByUid($uid);

            if ($exit_user) {
                return $this->renderJSON(ERROR_CODE_FAIL, 'id已经被占用');
            }

            $user->uid = $uid;

            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $user);

            $user->update();
            $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功');
        } else {
            $this->view->id = $this->params('id');
        }
    }

    function addSelectGoodNumAction()
    {
        $goom_num = $this->params('good_num');

        if (!$goom_num) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $user_db = \Users::getUserDb();
        $user_db->zadd("select_good_no_list", $goom_num, $goom_num);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function deleteSelectGoodNumAction()
    {
        $goom_num = $this->params('good_num');

        if (!$goom_num) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $user_db = \Users::getUserDb();
        $user_db->zrem("select_good_no_list", $goom_num);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function selectGoodNoListAction()
    {
        $id = $this->params('id');
        $user_db = \Users::getUserDb();
        $good_no_uid = 'select_good_no_list';

        if ($id) {

            if (!$user_db->zscore($good_no_uid, $id)) {
                $user_ids = [];
            } else {
                $user_ids[] = $id;
            }

            $total_entries = count($user_ids);
        } else {
            $page = $this->params('page', 1);
            $per_page = $this->params('per_page', 100);

            $total_entries = $user_db->zcard($good_no_uid);

            $offset = $per_page * ($page - 1);
            $user_ids = $user_db->zrevrange($good_no_uid, $offset, $offset + $per_page - 1);
        }

        $objects = [];

        foreach ($user_ids as $user_id) {
            $user = new \Users();
            $user->uid = $user_id;
            $objects[] = $user;
        }

        $pagination = new \PaginationModel($objects, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        $this->view->users = $pagination;
    }

    function deleteUserLoginInfoAction()
    {
        $user_id = $this->params('id');

        if (isBlank($user_id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }
        $user = \Users::findFirstById($user_id);
        $user->mobile = '';
        $user->login_type = '';
        $user->third_name = '';
        $user->third_unionid = '';
        $user->update();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '清除成功');
    }

    //许愿墙中奖记录
    function wishLuckHistoriesAction()
    {

        $product_channel_id = $this->params('product_channel_id', 1);
        info('产品渠道ID', $product_channel_id);
        $page = $this->params('page', 1);
        $wish_luck_histories = \WishHistories::generateLuckyUserList($product_channel_id);
        $per_page = 20;

        $wish_luck_users = \Users::findByUsersListForWish($wish_luck_histories, $page, $per_page);
        $this->view->wish_luck_users = $wish_luck_users;
        $this->view->product_channel_id = $product_channel_id;
        $this->view->all_product_channels = \ProductChannels::find(['order' => 'id asc', 'columns' => 'id,name']);
        $this->view->product_channel_id = $product_channel_id;

    }

}