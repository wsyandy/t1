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

    // Signaling Key 用于登录信令系统;
    function signalingKeyAction()
    {
        $key = $this->currentProductChannel()->getSignalingKey($this->currentUser()->id);
        $app_id = $this->currentProductChannel()->getImAppId();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['app_id' => $app_id, 'signaling_key' => $key]);
    }

    //Channel Key 用于加入频道;
    function channelKeyAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $key = $this->currentProductChannel()->getChannelKey($room->channel_name, $this->currentUser()->id);
        $app_id = $this->currentProductChannel()->getImAppId();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['app_id' => $app_id, 'channel_key' => $key]);
    }

    function indexAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 8);
        $hot = intval($this->params('hot', 0));
        $user_id = $this->currentUserId();

        //限制搜索条件
        $cond = [
            'conditions' => 'online_status = ' . STATUS_ON . ' and status = ' . STATUS_ON,
            'order' => 'last_at desc, user_type asc'
        ];

//        $ip = $this->remoteIp();
//        $ip_list = "permit_ip_list";
//        $hot_cache = \Users::getHotWriteCache();
//        $ips = $hot_cache->zrange($ip_list, 0, -1);

//        if (count($ips) > 0) {
//            if (in_array($ip, $ips)) {
//            }
//        }

        if (STATUS_ON == $hot) {
            $rooms = \Rooms::searchHotRooms($this->currentUser(), $page, $per_page);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $rooms->toJson('rooms', 'toSimpleJson'));
        }

        //热门条件
        if (STATUS_ON == $hot) {
            $cond['conditions'] .= ' and hot = ' . $hot;
        } else {
            $cond['conditions'] .= ' and user_id <> ' . $user_id;
        }

        $rooms = \Rooms::findPagination($cond, $page, $per_page);

        if ($rooms->total_entries < 2) {

            $cond = [
                'conditions' => 'online_status = ' . STATUS_ON . ' and status = ' . STATUS_ON,
                'order' => 'last_at desc, user_type asc'
            ];

            info("no_hot_rooms", $this->currentUser()->sid);

            $rooms = \Rooms::findPagination($cond, $page, $per_page);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $rooms->toJson('rooms', 'toSimpleJson'));
    }

    //创建房间
    function createAction()
    {
        $name = $this->params('name');
        if (isBlank($name)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstByUserId($this->currentUser()->id);
        if ($room) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '已创建', ['id' => $room->id,
                'name' => $room->name, 'channel_name' => $room->channel_name]);
        }

        $room = \Rooms::createRoom($this->currentUser(), $name);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['id' => $room->id,
            'name' => $room->name, 'channel_name' => $room->channel_name]);
    }

    //进入房间
    function enterAction()
    {
        $room_id = $this->params('id', 0); // 进入指定房间
        $password = $this->params('password', '');
        $user_id = $this->params('user_id', 0); // 进入指定用户所在的房间

        if ($user_id) {

            $user = \Users::findFirstById($user_id);

            if (!$user || $user->current_room_id < 1) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户已不在房间');
            }

            $room = $user->current_room;
        } else {
            $room = \Rooms::findFirstById($room_id);

            if (!$room) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
            }

            $user = $this->currentUser();
        }

        //如果不是房主
        if (!$user->isRoomHost($room)) {

            //房主不在房间且当前用户不在房间
//            if (!$room->user->isInRoom($room) && !$this->currentUser()->isInRoom($room)) {
//                return $this->renderJSON(ERROR_CODE_FAIL, '房主不在房间');
//            }

            //房间内没有人
            if ($room->user_num < 1) {
                return $this->renderJSON(ERROR_CODE_FAIL, '房间内没有用户');
            }
        }

        if ($room->isForbidEnter($this->currentUser())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您被禁止禁入房间,请稍后尝试');
        }

        $current_user_id = $this->currentUser()->id;
        $current_room_id = $this->currentUser()->current_room_id;
        //房间加锁并且不是房主且用户不在这个房间检验密码

        if (!$room->checkFilterUser($current_user_id)) {
            if ($room->lock && $room->user_id != $current_user_id && $current_room_id != $room->id && $room->password != $password) {
                return $this->renderJSON(ERROR_CODE_FORM, '密码错误');
            }
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['id' => $room->id,
            'name' => $room->name, 'channel_name' => $room->channel_name]);
    }

    //更新房间信息
    function updateAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room->updateRoom($this->params());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }

    // 进入房间获取信息
    function detailAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }


        //如果进入其他房间时 用户身上有房间 先退出房间
        $current_room = $this->currentUser()->current_room;

        info($this->currentUser()->sid, $this->currentUser()->current_room_id, $room_id);

        if ($current_room && $current_room->id != $room_id) {
            info($this->currentUser()->sid, $current_room->id, $room_id);
            $current_room->exitRoom($this->currentUser());
        }

        $room->enterRoom($this->currentUser());

        $key = $this->currentProductChannel()->getChannelKey($room->channel_name, $this->currentUser()->id);
        $app_id = $this->currentProductChannel()->getImAppId();

        $res = $room->toJson();
        $res['channel_key'] = $key;
        $res['app_id'] = $app_id;
        $res['user_chat'] = $this->currentUser()->canChat($room);
        $res['system_tips'] = $this->currentProductChannel()->system_news;
        $res['user_role'] = $this->currentUser()->user_role;
        $res['user_car_gift'] = $this->currentUser()->getUserCarGift();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', $res);
    }

    //房间基本信息
    function basicInfoAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room->toBasicJson());
    }

    function exitAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isInRoom($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户已不在房间');
        }

        $room->exitRoom($this->currentUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function lockAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $password = $this->params('password');

        if (!$password) {
            return $this->renderJSON(ERROR_CODE_FAIL, '密码不能为空');
        }

        $room->lock($password);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function unlockAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room->unlock();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    // 公屏设置
    function openChatAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room->chat = true;
        $room->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function closeChatAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room->chat = false;
        $room->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function usersAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 8);

        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $users = $room->findUsers($page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', $users->toJson('users', 'toSimpleJson'));
    }


    // 踢出房间
    function kickingAction()
    {
        $room_id = $this->params('id', 0);

        if (!$room_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '房间不存在');
        }

        if (!$this->currentUser()->canKickingUser($room, $this->otherUser())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $other_user_id = $this->otherUserId();
        $room_seat_user_lock_key = "room_seat_user_lock{$other_user_id}";
        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000);
        $other_user = $this->otherUser(true);
        $room->kickingRoom($other_user);
        unlock($room_seat_user_lock);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['id' => $room->id,
            'name' => $room->name, 'channel_name' => $room->channel_name]);
    }

    function openUserChatAction()
    {
        $room_id = $this->params('id', 0);

        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->canManagerRoom($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $this->otherUser()->setChat($room, true);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function closeUserChatAction()
    {
        $room_id = $this->params('id', 0);

        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->canManagerRoom($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $this->otherUser()->setChat($room, false);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    //异常离线上报 暂时用不到
    function offlineAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstById($this->params('id', 0));

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        info("room_offline", $this->otherUser()->sid, $room->id);
        $room->exitRoom($this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //添加管理员
    function addManagerAction()
    {
        $id = $this->params('id');
        $duration = intval($this->params('duration')); //时长 -1, 1, 3,24
        $user_id = $this->otherUserId();

        if (!$id || !$duration) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstById($id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '房间信息错误');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '权限不足');
        }

        if ($this->otherUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '不能设置自己为管理员');
        }

        if ($this->otherUser()->isManager($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户已经是管理员');
        }

        if ($room->manager_num >= 2) {
            return $this->renderJSON(ERROR_CODE_FAIL, '管理员已满');
        }

        $room->addManager($user_id, $duration);

        $res['user_id'] = $user_id;
        $res['deadline'] = $room->calculateUserDeadline($user_id);
        $res['is_permanent'] = $this->otherUser()->isPermanentManager($room);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    //删除管理员
    function deleteManagerAction()
    {
        $id = $this->params('id');

        if (!$id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstById($id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '房间信息错误');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '权限不足');
        }

        $room->deleteManager($this->otherUserId());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //更新管理员
    function updateManagerAction()
    {
        $id = $this->params('id');
        $duration = intval($this->params('duration')); //时长 -1, 1, 3,24
        $user_id = $this->otherUserId();

        if (!$id || !$duration) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstById($id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '房间信息错误');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '权限不足');
        }

        if (!$this->otherUser()->isManager($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户已不是管理员');
        }

        $room->updateManager($this->otherUserId(), $duration);

        $res['user_id'] = $user_id;
        $res['deadline'] = $room->calculateUserDeadline($user_id);
        $res['is_permanent'] = $this->otherUser()->isPermanentManager($room);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    function managersAction()
    {
        $id = $this->params('id');

        if (!$id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstById($id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '房间信息错误');
        }

        $managers = $room->findManagers();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['managers' => $managers]);
    }

    function setThemeAction()
    {
        $room_id = $this->params('id');
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '无效的房间');
        }

        $room_theme_id = $this->params('room_theme_id');
        $room_theme = \RoomThemes::findFirstById($room_theme_id);
        if (!$room_theme || $room_theme->status != STATUS_ON) {
            return $this->renderJSON(ERROR_CODE_FAIL, '无效的主题');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room->room_theme_id = $room_theme_id;
        $room->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['theme_image_url' => $room_theme->theme_image_url]);
    }

    function closeThemeAction()
    {
        $room_id = $this->params('id');
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '无效的房间');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限', '');
        }

        $room->room_theme_id = 0;
        $room->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }
}