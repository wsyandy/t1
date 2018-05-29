<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/8
 * Time: 下午5:59
 */

namespace admin;


class RoomsController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('room');
        $name = $this->params('name');
        $hot = $this->params('hot', 0);
        $status = $this->params('room[status_eq]', '');
        $product_channel_id = $this->params('room[product_channel_id_eq]');
        $user_type = $this->params('room[user_type_eq]');
        $theme_type = $this->params('room[theme_type_eq]', '');
        $id = $this->params('room[id_eq]');
        $uid = $this->params('room[uid_eq]');
        $union_id = $this->params('union_id', 0);
        $user_id = $this->params('user_id', 0);
        $user_uid = $this->params('user_uid', 0);

        if ($user_uid) {
            $user = \Users::findFirstByUid($user_uid);
            if (isPresent($user) && isBlank($user_id)) {
                $user_id = $user->id;
            }
        }

        if (isset($cond['conditions'])) {
            $cond['conditions'] .= " and user_id > 0";
        } else {
            $cond['conditions'] = " user_id > 0";
        }

        if ($name) {
            $cond['conditions'] .= " and name like '%$name%' ";
        }

        if ($hot) {
            $cond['conditions'] .= " and hot = 1 ";
        }

        if ($union_id) {
            $cond['conditions'] .= " and union_id = " . $union_id;
        }

        if ($user_id) {
            $cond['conditions'] .= " and user_id = " . $user_id;
        }

        $page = 1;
        $total_page = 1;
        $per_page = 30;
        $total_entries = $total_page * $per_page;
        $cond['order'] = "last_at desc, user_type asc, id desc";
        $rooms = \Rooms::findPagination($cond, $page, $per_page, $total_entries);

        $types = \Rooms::$TYPES;
        $type_arr = [];
        foreach ($rooms as $room) {
            $type_arr = explode(',', $room->types);
            $arr = [];

            foreach ($type_arr as $v) {
                if ($v)
                    array_push($arr, $types[$v]);

            }


            $room->types = implode(',', $arr);
        }
        $this->view->rooms = $rooms;
        $this->view->hot = $hot;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->total_entries = \Rooms::count($cond);
        $this->view->status = $status != '' ? intval($status) : '';
        $this->view->product_channel_id = $product_channel_id ? intval($product_channel_id) : '';
        $this->view->user_type = $user_type ? intval($user_type) : '';
        $this->view->theme_type = $theme_type != '' ? intval($theme_type) : '';
        $this->view->id = $id ? intval($id) : '';
        $this->view->uid = $uid ? intval($uid) : '';
        $this->view->union_id = $union_id ? intval($union_id) : '';
        $this->view->name = $name;
        $this->view->user_id = $user_id ? $user_id : '';
        $this->view->user_uid = $user_uid ? $user_uid : '';
        $this->view->boom_config = \BoomConfigs::getBoomConfig();
        $this->view->online_room_num = \Rooms::count(['conditions' => 'online_status = ' . STATUS_ON]);
        $this->view->status_on_room_num = \Rooms::count(['conditions' => 'online_status = ' . STATUS_ON]);
        $this->view->types = \Rooms::$TYPES;
    }

    function editAction()
    {
        $room = \Rooms::findFirstById($this->params('id'));
        $res = \BoomConfigs::findByConditions(['status' => STATUS_ON]);
        $boom_configs = ['' => '请选择'];

        foreach ($res as $item) {
            $boom_configs[$item->id] = $item->name;
        }

        $this->view->room = $room;
        $this->view->boom_configs = $boom_configs;
    }

    function updateAction()
    {
        $room = \Rooms::findFirstById($this->params('id'));
        $this->assign($room, 'room');

        $hot_room_score_ratio = $this->params('room[hot_room_score_ratio]');
        $room->setHotRoomScoreRatio($hot_room_score_ratio);

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room);
        if ($room->update()) {
            $room->getTypesName($room->types);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '编辑成功', ['room' => $room->toDetailJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '编辑失败');
        }
    }

    //在线用户
    function onlineUsersAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 8);

        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $users = $room->findUsers($page, $per_page);

        $this->view->users = $users;
        $this->view->room_id = $room_id;
    }


    //真实在线用户
    function onlineRealUsersAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $users = $room->findTotalRealUsers();

        $this->view->users = $users;
        $this->view->room_id = $room_id;
    }

    //麦位
    function roomSeatsAction()
    {
        $room_id = $this->params('id', 0);
        $room_seats = \RoomSeats::findByRoomId($room_id);
        $this->view->room_seats = $room_seats;
    }

    function detailAction()
    {
        $room = \Rooms::findFirstById($this->params('id'));
        $this->view->room = $room;
    }


    function audioAction()
    {
        $id = $this->params('id', 0);
        $room = \Rooms::findFirstById($id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if ($this->request->isPost()) {

            $audio_id = $this->params('room[audio_id]');
            $theme_type = $this->params('room[theme_type]');

            if ($theme_type != ROOM_THEME_TYPE_BROADCAST && $audio_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '只有设置电台才能选择音频');
            }

            $room->audio_id = $audio_id;
            $room->theme_type = $theme_type;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room);
            if ($room->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '');
            }
        }

        $audios = \Audios::find($cond = ['conditions' => 'status = :status:', 'bind' => ['status' => STATUS_ON],
            'order' => 'rank desc'
        ]);

        $audios_collection = ['' => '请选择'];

        foreach ($audios as $audio) {
            $audios_collection[$audio->id] = $audio->name;
        }

        $this->view->id = $id;
        $this->view->audios = $audios_collection;
        $this->view->room = $room;
    }

    function addUserAgreementAction()
    {
        $id = $this->params('id');
        $room = \Rooms::findFirstById($id);

        if ($this->request->isPost()) {

            $user_agreement_num = $this->params('user_agreement_num');
            $user_num = $user_agreement_num - $room->user_agreement_num;

            $room->user_agreement_num = $user_agreement_num;
            if ($room->update()) {
                if ($user_num > 0) {
                    \Rooms::delay()->addUserAgreement($room->id, $user_num);
                } else {
                    \Rooms::delay()->deleteUserAgreement($room->id, abs($user_num));
                }

                return $this->renderJSON(ERROR_CODE_SUCCESS, '编辑成功', ['room' => $room->toDetailJson()]);
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }

        $this->view->room = $room;
    }

    function deleteUserAgreementAction()
    {
        $id = $this->params('id');
        $room = \Rooms::findFirstById($id);
        $room->user_agreement_num = 0;

        if ($room->update()) {
            \Rooms::delay()->deleteUserAgreement($room->id);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['room' => $room->toDetailJson()]);
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '清除失败');
    }

    function autoHotAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);
        $new = $this->params('new', 0);
        $hot_cache = \Users::getHotWriteCache();

        $hot_room_list_key = \Rooms::generateHotRoomListKey();

        if ($new) {
            $hot_room_list_key = \Rooms::getTotalRoomListKey();
        }

        $room_ids = $hot_cache->zrevrange($hot_room_list_key, 0, -1);

        $rooms = \Rooms::findByIds($room_ids);
        $types = \Rooms::$TYPES;
        $type_arr = [];

        foreach ($rooms as $room) {

            if ($room->hot == STATUS_ON) {
                $room->auto_hot = 0;
            } else {
                $room->auto_hot = 1;
            }

            $type_arr = explode(',', $room->types);
            $arr = [];
            foreach ($type_arr as $v) {
                $arr[] = $types[$v];
            }
            $room->types = implode(',', $arr);
        }

        $pagination = new \PaginationModel($rooms, $hot_cache->zcard($hot_room_list_key), $page, $per_page);
        $pagination->clazz = 'Rooms';


        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->rooms = $pagination;
        $this->view->total_entries = $rooms->total_entries;
        $this->view->hot = 1;
        $this->view->boom_config = \BoomConfigs::getBoomConfig();
    }

    function typesAction()
    {
        $id = $this->params('id');
        $room = \Rooms::findFirstById($id);
        $types = \Rooms::$TYPES;
        $all_select_types = explode(',', $room->types);
        $this->view->id = $id;
        $this->view->types = $types;
        $this->view->all_select_types = $all_select_types;
    }

    function updateTypesAction()
    {
        $id = $this->params('id');
        $room = \Rooms::findFirstById($id);
        $types = $this->params('types');

        if ($types) {
            $room->types = implode(',', $types);
        } else {
            $room->types = '';
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room);
        if ($room->update()) {
            $room->getTypesName($room->types);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['room' => $room->toDetailJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }

    function gameWhiteListAction()
    {
        $id = $this->params('id');
        $hot_cache = \Rooms::getHotWriteCache();

        $key = "room_game_white_list";
        if ($id && $hot_cache->zscore($key, $id) > 0) {
            $room_id_list = [$id];
        } else {
            $room_id_list = $hot_cache->zrange($key, 0, -1);
        }
        $this->view->room_id_list = $room_id_list;
    }

    function addGameWhiteListAction()
    {
        if ($this->request->isPost()) {
            $id = $this->params('id');

            $room = \Rooms::findFirstById($id);

            if (!$room) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            \Rooms::addGameWhiteList($id);

            return $this->response->redirect('/admin/rooms/game_white_list');
        }
    }

    function deleteGameWhiteListAction()
    {
        $id = $this->params('id');
        \Rooms::deleteGameWhiteList($id);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/rooms/game_white_list']);
    }

    function shieldConfigAction()
    {
        $hot_cache = \Rooms::getHotWriteCache();
        $room_id = $this->params('id');
        $shield_province_key = 'room_shield_provinces_room_id_' . $room_id;
        $shield_city_key = 'room_shield_cities_room_id_' . $room_id;

        $shield_province_ids = $hot_cache->zrange($shield_province_key, 0, -1);
        $shield_city_ids = $hot_cache->zrange($shield_city_key, 0, -1);

        if ($this->request->isPost()) {

            $province_ids = $this->params('province_ids', []);
            $city_ids = $this->params('city_ids', []);

            $all_provinces = \Provinces::findForeach();
            $all_cities = \Cities::findForeach();

            foreach ($all_provinces as $all_province) {

                $key = "room_shield_province_id_" . $all_province->id;

                if (in_array($all_province->id, $province_ids)) {
                    $hot_cache->zadd($key, time(), $room_id);
                    $hot_cache->zadd($shield_province_key, time(), $all_province->id);
                } else {
                    $hot_cache->zrem($key, $room_id);
                    $hot_cache->zrem($shield_province_key, $all_province->id);
                }
            }

            foreach ($all_cities as $all_city) {

                $key = "room_shield_city_id_" . $all_city->id;

                if (in_array($all_city->id, $city_ids)) {
                    $hot_cache->zadd($key, time(), $room_id);
                    $hot_cache->zadd($shield_city_key, time(), $all_city->id);
                } else {
                    $hot_cache->zrem($key, $room_id);
                    $hot_cache->zrem($shield_city_key, $all_city->id);
                }
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        $this->view->provinces = \Cities::getAllCities();
        $this->view->city_ids_list = $shield_city_ids;
        $this->view->room_id = $room_id;
    }

    function forbiddenToHotAction()
    {
        $id = $this->params('id');
        $room = \Rooms::findFirstById($id);


        if ($this->request->isPost()) {

            $forbidden_reason = $this->params('forbidden_reason');
            $forbidden_time = $this->params('forbidden_time');

            if (!$forbidden_reason) {
                return $this->renderJSON(ERROR_CODE_FAIL, '禁止原因不能为空');
            }

            $opts = ['forbidden_reason' => $forbidden_reason, 'forbidden_time' => $forbidden_time, 'operator' => $this->currentOperator()];
            \Rooms::addForbiddenList($room, $opts);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '失败');
        }

        $this->view->room = $room;
    }

    function forbiddenToHotRecordsAction()
    {
        $id = $this->params('id');
        $room = \Rooms::findFirstById($id);
        $record_key = "room_forbidden_records_room_id_" . $room->id;
        $user_db = \Users::getUserDb();
        $records = $user_db->zrevrange($record_key, 0, -1, 'withscores');

        $this->view->records = $records;
    }

    function forbiddenToHotListAction()
    {
        $hot_cache = \Rooms::getHotWriteCache();
        $key = "room_forbidden_to_hot_list";
        $page = $this->params('page');

        $room_ids = $hot_cache->zrange($key, 0, -1);

        if ($room_ids) {

            $rooms = \Rooms::findByIds($room_ids);

            foreach ($rooms as $room) {

                if (!$room->isForbiddenHot()) {
                    \Rooms::remForbiddenList($room);
                }
            }
        }


        if (count($room_ids) > 0) {
            $cond = ['conditions' => 'id in (' . implode(',', $room_ids) . ")"];
        } else {
            $cond = ['conditions' => 'id < 1'];
        }

        $rooms = \Rooms::findPagination($cond, $page, 30);

        $this->view->rooms = $rooms;
    }

    function remForbiddenListAction()
    {
        $id = $this->params('id');
        $room = \Rooms::findFirstById($id);


        if ($this->request->isPost()) {

            $opts = ['operator' => $this->currentOperator()];

            \Rooms::remForbiddenList($room, $opts);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }
    }

    function hotSearchKeywrodsAction()
    {
        $hot_cache = \Rooms::getHotWriteCache();
        $key = 'room_hot_search_keywords_list';
        $keywords = $hot_cache->zrange($key, 0, -1);
        $total_entries = $hot_cache->zcard($key);
        $objects = [];

        foreach ($keywords as $keyword) {
            $room = new \Rooms();
            $room->keyword = $keyword;
            $room->rank = $hot_cache->zscore($key, $keyword);
            $objects[] = $room;
        }

        $pagination = new \PaginationModel($objects, $total_entries, 1, $total_entries);
        $pagination->clazz = 'Rooms';

        $this->view->rooms = $pagination;
    }

    function newHotSearchKeywrodsAction()
    {
        $keyword = $this->params('room[keyword]');
        $rank = $this->params('room[rank]');
        $hot_cache = \Rooms::getHotWriteCache();

        if ($this->request->isPost()) {

            $rank = intval($rank);

            if (!$keyword) {
                return $this->renderJSON(ERROR_CODE_FAIL, '名称不能为空');
            }

            if (!$rank) {
                return $this->renderJSON(ERROR_CODE_FAIL, '排序不能为空');
            }

            $hot_cache->zadd("room_hot_search_keywords_list", $rank, $keyword);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/rooms/hot_search_keywrods']);
        }

        $room = new \Rooms();
        $room->keyword = $keyword;
        $room->rank = $hot_cache->zscore('room_hot_search_keywords_list', $keyword);
        $this->view->room = $room;
    }

    function deleteSearchKeywrodsAction()
    {
        if ($this->request->isPost()) {
            $hot_cache = \Rooms::getHotWriteCache();
            $keyword = $this->params('room[keyword]');
            $hot_cache->zrem("room_hot_search_keywords_list", $keyword);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/rooms/hot_search_keywrods']);
        }
    }

    function hotRoomScoreAction()
    {
        $room_id = $this->params('id');
        $user_db = \Users::getUserDb();
        $key = 'hot_room_score_record_room_id_' . $room_id;

        $scores = $user_db->hgetall($key);

        $this->view->scores = $scores;
    }

    function hotRoomAmountScoreAction()
    {
        //统计时间段房间流水 10分钟为单位
        $hot_cache = \Users::getHotWriteCache();
        $room_id = $this->params('id');
        $time = time();
        $scores = [];

        for ($i = 0; $i < 50; $i++) {

            $minutes = date("YmdHi", $time);
            $interval = intval(intval($minutes) % 10);
            $minutes_start = $minutes - $interval;
            $minutes_end = $minutes + (10 - $interval);
            $minutes_stat_key = "room_stats_send_gift_amount_minutes_" . $minutes_start . "_" . $minutes_end . "_room_id" . $room_id;
            $amount = $hot_cache->get($minutes_stat_key);
            $scores[$minutes_start . "_" . $minutes_end] = $amount;

            $time -= 600;
        }

        $this->view->scores = $scores;
    }

    function hotRoomNumScoreAction()
    {
        $room_id = $this->params('id');
        //统计时间段房间流水 10分钟为单位
        $hot_cache = \Users::getHotWriteCache();

        $time = time();
        $scores = [];

        for ($i = 0; $i < 50; $i++) {

            $minutes = date("YmdHi", $time);
            $interval = intval(intval($minutes) % 10);
            $minutes_start = $minutes - $interval;
            $minutes_end = $minutes + (10 - $interval);
            $minutes_stat_key = "room_stats_send_gift_num_minutes_" . $minutes_start . "_" . $minutes_end . "_room_id" . $room_id;
            $num = $hot_cache->get($minutes_stat_key);
            $scores[$minutes_start . "_" . $minutes_end] = $num;

            $time -= 600;
        }

        $this->view->scores = $scores;
    }

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

        $other_user_id = $this->params('user_id');
        $room_seat_user_lock_key = "room_seat_user_lock{$other_user_id}";
        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000);
        $other_user = \Users::findFirstById($other_user_id);
        $room->kickingRoom($other_user, 30);
        ////$room->pushExitRoomMessage($other_user, $other_user->current_room_seat_id);
        unlock($room_seat_user_lock);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '踢出成功');
    }


    function sendTopicMsgAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = "send_topic_msg";
            $sender_id = $this->params('sender_id');
            $content = $this->params('content');
            $content_type = $this->params('content_type');


            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }

            $body = ['action' => $action, 'user_id' => $sender->id, 'nickname' => $sender->nickname, 'sex' => $sender->sex,
                'avatar_url' => $sender->avatar_url, 'avatar_small_url' => $sender->avatar_small_url, 'content' => $content,
                'channel_name' => $room->channel_name, 'content_type' => $content_type
            ];


            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }

        $this->view->user_id = $user_id;

        $this->view->content_types = ['personage' => '个人', 'red_packet' => '红包', 'pk' => 'pk结果', 'blasting_gift' => '爆礼物'];
        $this->view->room = $room;
    }

    function enterRoomAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = "enter_room";
            $sender_id = $this->params('sender_id');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            if ($sender->isInAnyRoom()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户已在房间');
            }

            $room->enterRoom($sender);
            $body = ['action' => $action, 'user_id' => $sender->id, 'nickname' => $sender->nickname, 'sex' => $sender->sex,
                'avatar_url' => $sender->avatar_url, 'avatar_small_url' => $sender->avatar_small_url, 'channel_name' => $room->channel_name
            ];

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }

        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function exitRoomAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = "exit_room";
            $sender_id = $this->params('sender_id');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }

            $current_room_seat_id = $sender->current_room_seat_id;
            $body = ['action' => $action, 'user_id' => $sender->id, 'channel_name' => $room->channel_name];

            $room->exitRoom($sender, false);

            $current_room_seat = \RoomSeats::findFirstById($current_room_seat_id);
            if ($current_room_seat) {
                $body['room_seat'] = $current_room_seat->toSimpleJson();
            }

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }

        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function sendGiftAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = "send_gift";
            $sender_id = $this->params('sender_id');
            $gift_id = $this->params('gift_id');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }

            $gift = \Gifts::findFirstById($gift_id);
            if (!$gift) {
                return $this->renderJSON(ERROR_CODE_FAIL, '此礼物不存在');
            }

            $data = $gift->toSimpleJson();
            $data['num'] = mt_rand(1, 20);
            $data['sender_id'] = $sender->id;
            $data['sender_nickname'] = $sender->nickname;
            $data['sender_room_seat_id'] = $sender->current_room_seat_id;
            $data['receiver_id'] = $user->id;
            $data['receiver_nickname'] = $user->nickname;
            $data['receiver_room_seat_id'] = $user->current_room_seat_id;

            $body = ['action' => $action, 'notify_type' => 'bc', 'channel_name' => $room->channel_name, 'gift' => $data];


            $payload = ['body' => $body, 'fd' => $receiver_fd];


            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }
        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function upAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = "up";
            $sender_id = $this->params('sender_id');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }

            if ($sender->current_room_seat_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户已在麦位');
            }

            $room_seat = \RoomSeats::findFirst(['conditions' => 'room_id = ' . $room->id . " and (user_id = 0 or user_id is null)"]);
            $room_seat->up($sender);
            $body = ['action' => $action, 'channel_name' => $room->channel_name, 'room_seat' => $room_seat->toSimpleJson()];

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }

        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function downAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = "down";
            $sender_id = $this->params('sender_id');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }

            if (!$sender->current_room_seat_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在麦位');
            }
            $current_room_seat = $sender->current_room_seat;
            $current_room_seat->down($sender);
            $body = ['action' => $action, 'channel_name' => $room->channel_name, 'room_seat' => $current_room_seat->toSimpleJson()];

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }

        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function hangUpAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = "hang_up";
            $sender_id = $this->params('sender_id');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }
            if (!$sender->isCalling()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户没有进行中的通话');
            }

            $voice_call = \VoiceCalls::getVoiceCallByUserId($sender_id);

            if ($voice_call->sender_id != $user_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户id错误');
            }

            $voice_call->changeStatus(CALL_STATUS_HANG_UP);
            $body = ['action' => $action, 'user_id' => $sender_id, 'receiver_id' => $user_id, 'channel_name' => $voice_call->call_no];

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }

        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function roomNoticeAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = "room_notice";
            $sender_id = $this->params('sender_id');
            $content = $this->params('content');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }

            $body = ['action' => $action, 'channel_name' => $room->channel_name, 'content' => $content];

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }

        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function redPacketAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = 'red_packet';
            $sender_id = $this->params('sender_id');
            $red_packet_num = $this->params('num');
            $url = $this->params('url');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }
            $body = ['action' => $action, 'red_packet' => ['num' => $red_packet_num, 'client_url' => $url]];

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }
        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function pkAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = 'pk';
            $sender_id = $this->params('sender_id');
            $left_pk_user_id = $this->params('left_pk_user_id');
            $left_pk_user_score = $this->params('left_pk_user_score');
            $right_pk_user_id = $this->params('right_pk_user_id');
            $right_pk_user_score = $this->params('right_pk_user_score');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();


            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }
            $body = ['action' => $action, 'pk_history' => [
                'left_pk_user' => ['id' => $left_pk_user_id, 'score' => $left_pk_user_score],
                'right_pk_user' => ['id' => $right_pk_user_id, 'score' => $right_pk_user_score]
            ]
            ];

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }
        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function boomGiftAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = 'boom_gift';
            $url = $this->params('url');
            $expire_at = $this->params('expire_at', date('Y-m-d H:i:s', strtotime('+3 minutes')));
            $svga_image_url = $this->params('svga_image_url');
            $total_value = $this->params('total_value');
            $current_value = $this->params('current_value');


            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $body = ['action' => $action, 'boom_gift' => ['expire_at' => strtotime($expire_at), 'client_url' => $url, 'svga_image_url' => $svga_image_url,
                'total_value' => $total_value, 'current_value' => $current_value, 'show_rank' => 1000000, 'render_type' => 'svga',
                'image_color' => 'orange']
            ];


            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }
        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }

    function sinkNoticeAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = 'sink_notice';
            $sender_id = $this->params('sender_id');
            $url = $this->params('url');
            $content = $this->params('content');
            $title = $this->params('title');

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();


            if (!$sender->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
            }

            $body = ['action' => $action, 'title' => $title, 'content' => $content, 'client_url' => $url];


            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $result = \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }
        $this->view->user_id = $user_id;
        $this->view->room = $room;
    }
}