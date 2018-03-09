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

        if (isset($cond['conditions'])) {
            $cond['conditions'] .= " and user_id > 0";
        } else {
            $cond['conditions'] = " user_id > 0";
        }

        if ($name) {
            $cond['conditions'] .= " and name like '%$name%' ";
        }

        $page = 1;
        $total_page = 1;
        $per_page = 30;
        $total_entries = $total_page * $per_page;
        $cond['order'] = "id desc";
        $rooms = \Rooms::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->rooms = $rooms;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function editAction()
    {
        $room = \Rooms::findFirstById($this->params('id'));
        $this->view->room = $room;
    }

    function updateAction()
    {
        $room = \Rooms::findFirstById($this->params('id'));
        $this->assign($room, 'room');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room);
        if ($room->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '编辑成功', ['error_url' => '/admin/rooms?room[id_eq]=' . $room->id]);
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

    function sendMsgAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        if ($this->request->isPost()) {
            $action = $this->params('action');
            $sender_id = $this->params('sender_id');
            $gift_id = $this->params('gift_id');
            $content = $this->params('content');
            debug($action, $sender_id, $gift_id, $content);

            $sender = \Users::findById($sender_id);
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            if ($action == 'enter_room') {

                if ($sender->isInAnyRoom()) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户已在房间');
                }

                $room->enterRoom($sender);
                $body = ['action' => $action, 'user_id' => $sender->id, 'nickname' => $sender->nickname, 'sex' => $sender->sex,
                    'avatar_url' => $sender->avatar_url, 'avatar_small_url' => $sender->avatar_small_url, 'channel_name' => $room->channel_name
                ];
            }

            if ($action == 'send_topic_msg') {

                if (!$sender->isInRoom($room)) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
                }

                $body = ['action' => $action, 'user_id' => $sender->id, 'nickname' => $sender->nickname, 'sex' => $sender->sex,
                    'avatar_url' => $sender->avatar_url, 'avatar_small_url' => $sender->avatar_small_url, 'content' => $content,
                    'channel_name' => $room->channel_name
                ];
            }

            if ($action == 'send_gift') {

                if (!$sender->isInRoom($room)) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
                }

                $gift = \Gifts::findFirstById($gift_id);
                if (!$gift) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '此礼物不存在');
                }

                $data = $gift->toSimpleJson();
                $data['num'] = mt_rand(1, 20);;
                $data['sender_id'] = $sender->id;
                $data['sender_nickname'] = $sender->nickname;
                $data['sender_room_seat_id'] = $sender->current_room_seat_id;
                $data['receiver_id'] = $user->id;
                $data['receiver_nickname'] = $user->nickname;
                $data['receiver_room_seat_id'] = $user->current_room_seat_id;

                $body = ['action' => $action, 'notify_type' => 'bc', 'channel_name' => $room->channel_name, 'gift' => $data];
            }

            if ($action == 'exit_room') {

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
            }

            if ($action == 'down') {

                if (!$sender->isInRoom($room)) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
                }

                if (!$sender->current_room_seat_id) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户不在麦位');
                }
                $current_room_seat = $sender->current_room_seat;
                $current_room_seat->down($sender);
                $body = ['action' => $action, 'channel_name' => $room->channel_name, 'room_seat' => $current_room_seat->toSimpleJson()];
            }

            if ($action == 'up') {

                if (!$sender->isInRoom($room)) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
                }

                if ($sender->current_room_seat_id) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户已在麦位');
                }

                $room_seat = \RoomSeats::findFirst(['conditions' => 'room_id = ' . $room->id . " and (user_id = 0 or user_id is null)"]);
                $room_seat->up($sender);
                $body = ['action' => $action, 'channel_name' => $room->channel_name, 'room_seat' => $room_seat->toSimpleJson()];
            }

            if ($action == 'hang_up') {

                if (!$sender->isCalling()) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户没有进行中的通话');
                }

                $voice_call = \VoiceCalls::getVoiceCallByUserId($sender_id);

                if ($voice_call->sender_id != $user_id) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户id错误');
                }

                $voice_call->changeStatus(CALL_STATUS_HANG_UP);
                $body = ['action' => $action, 'user_id' => $sender_id, 'receiver_id' => $user_id, 'channel_name' => $voice_call->call_no];
            }

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            info($payload);
            \services\SwooleUtils::send('push', $intranet_ip, 9508, $payload);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }
        $this->view->user_id = $user_id;
        $this->view->actions = ['send_topic_msg' => '发公屏消息', 'enter_room' => '进房间', 'send_gift' => '送礼物', 'up' => '上麦',
            'down' => '下麦', 'exit_room' => '退出房间', 'hang_up' => '挂断电话'
        ];
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

    function earningsAction()
    {
        $room_ids = \Rooms::roomIncomeList();
        echoLine($room_ids);
        $rooms = \Rooms::findByIds($room_ids);

        $this->view->rooms = $rooms;
    }

    function earningsDetailAction()
    {
        $room_id = $this->params('id');
        $room = \Rooms::findFirstById($room_id);
//        if ($room) {
//            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
//        }

        $results = [];

        for ($i = 0; $i < 7; $i++) {
            $start_at = beginOfDay(time() - $i * 60 * 60 * 24);
            $end_at = endOfDay(time() - $i * 60 * 60 * 24);

            $results[date('Ymd', $start_at)][0] = $room->getDayAmount($start_at, $end_at);
            $results[date('Ymd', $start_at)][1] = $start_at;
            $results[date('Ymd', $start_at)][2] = $end_at;
        }

        $this->view->room_id = $room_id;
        $this->view->results = $results;
    }
}