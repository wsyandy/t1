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

        if ($hot == STATUS_ON) {
            //热门房间从缓存中拉取
            $rooms = \Rooms::searchHotRooms($this->currentUser(), $page, $per_page);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $rooms->toJson('rooms', 'toSimpleJson'));

        }

        $follow = $this->params('follow');

        if (STATUS_ON == $follow) {

            $user_ids = $this->currentUser()->followUserIds();

            if (count($user_ids) < 1) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['rooms' => []]);
            }
        }

        $rooms = \Rooms::search($this->currentUser(), $this->currentProductChannel(), $page, $per_page, $this->params());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $rooms->toJson('rooms', 'toSimpleJson'));
    }

    //创建房间
    function createAction()
    {
        $name = $this->params('name');
        if (isBlank($name)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $opts = ['name' => $name];

        $room = \Rooms::findFirstByUserId($this->currentUser()->id);
        if ($room) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '已创建', ['id' => $room->id,
                'name' => $room->name, 'channel_name' => $room->channel_name]);
        }

        $room_tag_ids = $this->params('room_tag_ids');

        //还要判断是否符合规则
        if (isPresent($room_tag_ids)) {
            $opts['room_tag_ids'] = $room_tag_ids;
        }

        $room = \Rooms::createRoom($this->currentUser(), $opts);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['id' => $room->id,
            'uid' => $room->uid, 'name' => $room->name, 'channel_name' => $room->channel_name]);
    }

    //进入房间
    function enterAction()
    {
        $room_id = $this->params('id', 0); // 进入指定房间
        $password = $this->params('password', '');
        $user_id = $this->params('user_id', 0); // 进入指定用户所在的房间

        if ($room_id) {

            $room = \Rooms::findFirstById($room_id);

            if (!$room) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
            }

        } else {

            $user = \Users::findFirstById($user_id);

            if (!$user || $user->current_room_id < 1) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户已不在房间');
            }

            $room = $user->current_room;
        }

        //如果不是房主
//        if (!$this->currentUser()->isRoomHost($room)) {

        //房主不在房间且当前用户不在房间
//            if (!$room->user->isInRoom($room) && !$this->currentUser()->isInRoom($room)) {
//                return $this->renderJSON(ERROR_CODE_FAIL, '房主不在房间');
//            }

        //房间内没有人
//            if ($room->user_num < 1) {
//                return $this->renderJSON(ERROR_CODE_FAIL, '房间内没有用户');
//            }
//        }

        if ($room->isForbidEnter($this->currentUser())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您被禁止禁入房间,请稍后尝试');
        }

        $current_user_id = $this->currentUser()->id;
        $current_room_id = $this->currentUser()->current_room_id;

        //房间加锁并且不是房主且用户不在这个房间检验密码 从h5进入
        if (!$room->checkFilterUser($current_user_id)) {
            if ($room->lock && $room->user_id != $current_user_id && $current_room_id != $room->id && $room->password != $password) {
                return $this->renderJSON(ERROR_CODE_FORM, '密码错误');
            }
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['id' => $room->id,
            'uid' => $room->uid, 'name' => $room->name, 'channel_name' => $room->channel_name]);
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
        if ($current_room && $current_room->id != $room_id) {
            info('Exce exit', $this->currentUser()->id, $current_room->id, $room_id);
            $current_room->exitRoom($this->currentUser());
        }

        $room->enterRoom($this->currentUser());

        $key = $this->currentProductChannel()->getChannelKey($room->channel_name, $this->currentUser()->id);
        $app_id = $this->currentProductChannel()->getImAppId();
        $signaling_key = $this->currentProductChannel()->getSignalingKey($this->currentUser()->id);

        $hot_cache = \Users::getHotWriteCache();
        $cache_key = 'push_into_room_remind_' . $this->currentUser()->id;
        if (!$hot_cache->get($cache_key)) {
            $hot_cache->setex($cache_key, 300, time());
            \Users::delay()->pushIntoRoomRemind($this->currentUser()->id);
        }

        $res = $room->toJson();
        $res['channel_key'] = $key;
        $res['signaling_key'] = $signaling_key;
        $res['app_id'] = $app_id;
        $res['user_chat'] = $this->currentUser()->canChat($room);
        $res['system_tips'] = $this->currentProductChannel()->system_news;
        $res['user_role'] = $this->currentUser()->user_role;

        //自定义菜单栏，实际是根据对应不同的版本号进行限制，暂时以线上线外为限制标准
        $root = $this->getRoot();
        //活动列表
        $product_channel_id = $this->currentProductChannelId();
        $platform = $this->context('platform');
        $platform = 'client_' . $platform;

        $show_game = true;

        $res['menu_config'] = $room->getRoomMenuConfig($show_game, $root, $room_id);
        //if ($room->user->isCompanyUser() || in_array($room->id, \Rooms::getGameWhiteList())) {
        //  $show_game = true;
        //}

//        if ($show_game) {
//            $menu_config[] = ['show' => true, 'title' => '游戏', 'url' => 'url://m/games?room_id=' . $room_id, 'icon' => $root . 'images/room_menu_game.png'];
//            $res['menu_config'] = $menu_config;
//        }

        $game_history = $room->getGameHistory();
        if ($game_history) {
            $res['game'] = ['url' => 'url://m/games/tyt?game_id=' . $game_history->game_id, 'icon' => $root . 'images/go_game.png'];
        }

        $pk_history = $room->getPkHistory();
        if (isDevelopmentEnv() && $pk_history) {
            $res['pk_history'] = $pk_history->toSimpleJson();
        }


        if (isDevelopmentEnv()) {
            $res['red_packet'] = ['num' => 2, 'url' => 'url://m/games'];
        }

        $activities = \Activities::findRoomActivities($this->currentUser(), ['product_channel_id' => $product_channel_id, 'platform' => $platform,
            'type' => ACTIVITY_TYPE_ROOM]);

        if ($activities) {
            $res['activities'] = $activities;
        }

        $user_car_gift = $this->currentUser()->getUserCarGift();
        if ($user_car_gift) {
            $res['user_car_gift'] = $user_car_gift->toSimpleJson();
        }

        //房间分类信息
        $room_tag_ids = $room->room_tag_ids;

        $res['room_tag_ids'] = [];
        if (isPresent($room_tag_ids)) {

            $room_tag_ids = explode(',', $room_tag_ids);

            foreach ($room_tag_ids as $room_tag_id) {
                $res['room_tag_ids'][] = intval($room_tag_id);
            }
        }

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

        if ($room->manager_num >= 10) {
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

    function typesAction()
    {
        $types = [
            ['name' => '热门', 'type' => 'hot', 'value' => 1],
            ['name' => '最新', 'type' => 'new', 'value' => 1],
            ['name' => '开黑', 'type' => 'gang_up', 'value' => 1],
            ['name' => '交友', 'type' => 'friend', 'value' => 1],
            ['name' => '娱乐', 'type' => 'amuse', 'value' => 1],
            ['name' => '唱歌', 'type' => 'sing', 'value' => 1],
            ['name' => '电台', 'type' => 'broadcast', 'value' => 1],
            ['name' => '关注', 'type' => 'follow', 'value' => 1],
        ];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['types' => $types]);
    }

    //发公屏消息上报
    function sendMessageAction()
    {
        $content = $this->params('content');
        $content_type = $this->params('content_type');

        if (isDevelopmentEnv() && isBlank($content)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '内容不能为空');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');
    }

    function searchAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

        $keyword = $this->params('keyword', null);
        $type = $this->params('type');

        //关键词和类型不能同时为空
        if (is_null($keyword) && isBlank($type)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $cond = [];

        if (!is_null($keyword)) {

            //临时兼容
            $room_category = \RoomCategories::findFirstByName($keyword);

            \SearchHistories::delay()->createHistory($keyword, 'room');

            if ($room_category && $room_category->type) {
                $cond['conditions'] = " room_category_types like :room_category_types:";
                $cond['bind']['room_category_types'] = "%," . $room_category->type . ",%";
            } else {
                $cond['conditions'] = 'name like :name:';
                $cond['bind']['name'] = '%' . $keyword . '%';
                $cond['bind']['room_category_names'] = '%' . $keyword . '%';
            }

        } elseif ($type) {

            if ($type == 'follow') {

                $user_ids = $this->currentUser()->followUserIds();

                if (count($user_ids) > 0) {
                    $cond['conditions'] = " user_id in (" . implode(',', $user_ids) . ") ";
                } else {
                    return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['rooms' => []]);
                }

            } elseif ($type == 'new') {

                if (isset($cond['conditions'])) {
                    $cond['conditions'] .= " last_at >= :last_at:";
                } else {
                    $cond['conditions'] = " last_at >= :last_at:";
                }

                $cond['bind']['last_at'] = time() - 15 * 60;
                $cond['order'] = 'last_at desc,user_type asc';

            } else {
                $cond['conditions'] = " room_category_types like :room_category_types:";
                $cond['bind']['room_category_types'] = "%," . $type . ",%";
            }
        }

        //限制搜索条件

        if (isset($cond['conditions'])) {
            $cond['conditions'] .= " and online_status = :online_status: and status = :status:";
        } else {
            $cond['conditions'] = " online_status = :online_status: and status = :status:";
        }

        $cond['bind']['online_status'] = STATUS_ON;
        $cond['bind']['status'] = STATUS_ON;
        if (!isset($cond['order'])) {
            $cond['order'] = 'last_at desc,user_type asc';
        }

        $shield_room_ids = $this->currentUser()->getShieldRoomIds();

        if ($shield_room_ids) {
            $cond['conditions'] .= " and id not in (" . implode(",", $shield_room_ids) . ")";
        }

        debug("room_search_cond", $cond);

        $rooms = \Rooms::findPagination($cond, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $rooms->toJson('rooms', 'toSimpleJson'));
    }

    function hotSearchAction()
    {
        $keywords = ['球球', '王者', '绝地', '终结者', '处对象', '音乐', '电台', '第五人格', 'les', '关注'];

        $hot_cache = \Rooms::getHotWriteCache();
        $res = $hot_cache->zrevrange("room_hot_search_keywords_list", 0, -1);

        if (count($res) > 2) {
            $keywords = $res;
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['keywords' => $keywords]);
    }

    function hotAction()
    {
        $top = $this->params('top');
        $hot = $this->params('hot');
        $gang_up = $this->params('gang_up');
        $gang_up_category = $this->params('gang_up_category');

        $top_rooms_json = [];
        $hot_rooms_json = [];
        $gang_up_rooms_json = [];
        $gang_up_category_json = [];

        if (STATUS_ON == $top) {
            $top_rooms = \Rooms::searchTopRoom();
            $top_rooms_json = $top_rooms->toJson('top_rooms', 'toSimpleJson');
        }

        if (STATUS_ON == $hot) {

            if (isInternalIp($this->remoteIp()) || $this->currentUser()->isCompanyUser()) {
                $hot_rooms = \Rooms::newSearchHotRooms($this->currentUser(), 1, 9);
            } else {
                $hot_rooms = \Rooms::searchHotRooms($this->currentUser(), 1, 9);
            }

            $hot_rooms_json = $hot_rooms->toJson('hot_rooms', 'toSimpleJson');
        }

        if (STATUS_ON == $gang_up) {
            $gang_up_rooms_json = \Rooms::searchGangUpRooms($this->currentUser(), 1, 4);
        }

        if (STATUS_ON == $gang_up_category) {

            $room_category = \RoomCategories::findFirstByType('gang_up');
            if (isPresent($room_category)) {
                $gang_up_categories = \RoomCategories::find(
                    [
                        'conditions' => " status = :status: and parent_id = :parent_id:",
                        'bind' => ['status' => STATUS_ON, 'parent_id' => $room_category->id],
                        'order' => 'rank desc,id desc',
                    ]
                );

                foreach ($gang_up_categories as $item) {
                    $gang_up_category_json[] = ['name' => $item->name, 'type' => $item->type, 'image_small_url' => $item->image_url];
                }
            }
        }

        $res['gang_up_categories'] = $gang_up_category_json;
        $res['gang_up_rooms'] = fetch($gang_up_rooms_json, 'gang_up_rooms');
        $res['hot_rooms'] = fetch($hot_rooms_json, 'hot_rooms');
        $res['top_rooms'] = fetch($top_rooms_json, 'top_rooms');

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    function recommendAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 12);
        $rooms = \Rooms::search($this->currentUser(), $this->currentProductChannel(), $page, $per_page);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $rooms->toJson('rooms', 'toSimpleJson'));
    }

    function matchAction()
    {
        $total_room_id_key = \Rooms::getTotalRoomUserNumListKey();

        $hot_cache = \Users::getHotWriteCache();
        $total_room_ids = $hot_cache->zrange($total_room_id_key, 0, -1);
        $user = $this->currentUser();
        $current_user_room_id = $user->room_id;

        if ($current_user_room_id && in_array($current_user_room_id, $total_room_ids)) {
            unset($total_room_ids[array_search($current_user_room_id, $total_room_ids)]);
        }

        $room = null;

        if (isPresent($total_room_ids)) {
            $room_id = $total_room_ids[array_rand($total_room_ids)];
            $room = \Rooms::findFirstById($room_id);
        }

        if (!$room || $room->lock) {

            $cond = [
                'conditions' => 'online_status = ' . STATUS_ON . ' and status = ' . STATUS_ON .
                    ' and user_id != :user_id: and product_channel_id = :product_channel_id: and lock = :lock:',
                'bind' => ['user_id' => $user->id, 'product_channel_id' => $user->product_channel_id, 'lock' => 'false'],
            ];

            $room = \Rooms::findFirst($cond);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['id' => $room->id]);
    }

    function initiatePkAction()
    {
        $room_id = $this->params('id');

        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $left_pk_user_id = $this->params('left_pk_user_id');
        $right_pk_user_id = $this->params('right_pk_user_id');
        $pk_type = $this->params('pk_type'); //send_gift_user send_gift_amount
        $pk_time = $this->params('pk_time'); //
        $cover = $this->params('cover', 0);

        $opts = ['left_pk_user_id' => $left_pk_user_id, 'right_pk_user_id' => $right_pk_user_id, 'pk_type' => $pk_type, 'pk_time' => $pk_time, 'cover' => $cover, 'room_id' => $room_id];

        list($pk_history, $error_code, $error_reason) = \PkHistories::createHistory($this->currentUser(), $opts);

        if ($pk_history) {
            return $this->renderJSON($error_code, $error_reason, $pk_history->toSimpleJson());
        }

        return $this->renderJSON($error_code, $error_reason);

    }

    function pkHistoriesAction()
    {
        $room_id = $this->params('id');

        $room = \Rooms::findFirstById($room_id);
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $cond = [
            'conditions' => 'room_id = :room_id:',
            'bind' => ['room_id' => $room_id],
            'order' => 'id desc'
        ];

        $pk_histories = \PkHistories::findPagination($cond, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $pk_histories->toJson('pk_histories', 'toSimpleJson'));
    }
}