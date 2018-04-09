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
        $union_id = $this->params('union_id', 0);
        $user_id = $this->params('user_id', 0);

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
        $this->view->rooms = $rooms;
        $this->view->hot = $hot;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->total_entries = \Rooms::count($cond);
        $this->view->status = $status != '' ? intval($status) : '';
        $this->view->product_channel_id = $product_channel_id ? intval($product_channel_id) : '';
        $this->view->user_type = $user_type ? intval($user_type) : '';
        $this->view->theme_type = $theme_type != '' ? intval($theme_type) : '';
        $this->view->id = $id ? intval($id) : '';
        $this->view->union_id = $union_id ? intval($union_id) : '';
        $this->view->name = $name;
        $this->view->user_id = $user_id ? $user_id : '';
        $this->view->online_room_num = \Rooms::count(['conditions' => 'online_status = ' . STATUS_ON]);
        $this->view->status_on_room_num = \Rooms::count(['conditions' => 'online_status = ' . STATUS_ON]);
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

            if ($action == 'room_notice') {

                if (!$sender->isInRoom($room)) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '用户不在此房间');
                }

                $body = ['action' => $action, 'channel_name' => $room->channel_name, 'content' => $content];
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
            'down' => '下麦', 'exit_room' => '退出房间', 'hang_up' => '挂断电话', 'room_notice' => '房间信息通知'
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

    function addUserAgreementAction()
    {
        $id = $this->params('id');
        $room = \Rooms::findFirstById($id);

        if ($this->request->isPost()) {

            $user_agreement_num = $this->params('user_agreement_num');
            $room->user_agreement_num = $user_agreement_num;

            if ($room->update()) {
                \Rooms::delay()->addUserAgreement($room->id);
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
        $per_page = $this->params('per_page', 10);
        $rooms = \Rooms::searchHotRooms(null, $page, $per_page);

        foreach ($rooms as $room) {
            if ($room->hot == STATUS_ON) {
                $room->auto_hot = 0;
            } else {
                $room->auto_hot = 1;
            }
        }

        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->rooms = $rooms;
        $this->view->total_entries = $rooms->total_entries;
        $this->view->hot = 1;
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
        debug("---", $id);


        if ($types) {
            $room->types = implode(',', $types);
        } else {
            $room->types = '';
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room);
        if ($room->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/rooms']);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }
}