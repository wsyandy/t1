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
        if ($name) {
            if (isset($cond['conditions'])) {
                $cond['conditions'] .= " and name like '%$name%' ";
            } else {
                $cond['conditions'] = " name like '%$name%' ";
            }
        }
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $cond['order'] = "id desc";
        $rooms = \Rooms::findPagination($cond, $page, $per_page);
        $this->view->rooms = $rooms;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
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

            $hot_cache = \Users::getHotReadCache();
            $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $user->online_token;
            $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
            $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $user_id));

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
            $server = \PushSever::send('push', $intranet_ip, 9508, $payload);
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
        $audios = \Audios::find($cond = ['conditions' => 'status = :status:', 'bind' => ['status' => STATUS_ON],
            'order' => 'rank desc'
        ]);

        if ($this->request->isPost()) {
            $audio_id = $this->params('audio_id');
            $room->audio_id = $audio_id;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room);
            if ($room->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '',['redirect_url' => '/admin/rooms']);
            }
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
        $this->view->id = $id;
        $this->view->audios = $audios;
        //        $this->view->room = $room;
    }
}