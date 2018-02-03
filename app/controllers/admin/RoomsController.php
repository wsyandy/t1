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

    function enterRoomAction()
    {
        $users = \Users::findForeach(['conditions' => 'user_type = ' . USER_TYPE_SILENT]);
        $room_id = $this->params('room_id');
        debug($room_id);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '此房间不存在');
        }
        if ($this->request->isPost()) {
            $user_id = $this->params('user_id');
            $user = \Users::findById($user_id);
            if (!$user) {
                return $this->renderJSON(ERROR_CODE_FAIL, '虚拟用户不存在');
            }

            $room_host = $room->user;
            if (!$room_host) {
                return $this->renderJSON(ERROR_CODE_FAIL, '房主不存在');
            }

            $hot_cache = \Users::getHotReadCache();
            $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $room_host->online_token;
            $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
            $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $room_host->id));

            debug($user_id, $room->channel_name);
            $body = ['action' => 'enter_room', 'user_id' => $user_id, 'nickname' => $user->nickname, 'sex' => $user->sex,
                'avatar_url' => $user->avatar_url, 'avatar_small_url' => $user->avatar_small_url, 'channel_name' => $room->channel_name
            ];

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $server = \PushSever::send('push', $intranet_ip, 9508, $payload);
            return $this->renderJSON(ERROR_CODE_FAIL, '发送成功');
        }
        $this->view->room = $room;
        $this->view->users = $users;
    }

    function giveGiftAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $gifts = \Gifts::findValidList();
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不在房间');
        }
        $senders = $room->findUsers(1, 100);
        if ($this->request->isPost()) {
            $gift = \Gifts::findFirstById($this->params('gift_id'));
            if (!$gift) {
                return $this->renderJSON(ERROR_CODE_FAIL, '此礼物不存在');
            }

            $sender = \Users::findById($this->params('sender_id'));
            if (!$sender) {
                return $this->renderJSON(ERROR_CODE_FAIL, '发送者不存在');
            }

            $hot_cache = \Users::getHotReadCache();
            $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $user->online_token;
            $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
            $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $user_id));

            $data = $gift->toSimpleJson();
            $data['num'] = $this->params('num', 1);
            debug($sender->id, $user->id, $room->channel_name, $data);
            $body = ['action' => 'send_gift', 'sender_room_seat_id' => $sender->current_room_seat_id, 'receiver_room_seat_id' => $user->current_room_seat_id,
                'sender_nickname' => $sender->nickname, 'receiver_nickname' => $user->nickname, 'notify_type' => 'bc',
                'sender_id' => $sender->id, 'receiver_id' => $user->id, 'channel_name' => $room->channel_name, 'gift' => $data
            ];
            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $server = \PushSever::send('push', $intranet_ip, 9508, $payload);
            return $this->renderJSON(ERROR_CODE_FAIL, '发送成功');
        }
        $this->view->user = $user;
        $this->view->gifts = $gifts;
        $this->view->senders = $senders;
    }


    function sendTopicMsgAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findById($user_id);
        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '房间不存在');
        }
        if ($this->request->isPost()) {
            $content = $this->params("content", '房主666');
            $hot_cache = \Users::getHotReadCache();
            $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $user->online_token;
            $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
            $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $user_id));

            debug($user->id, $room->channel_name, $content);
            $body = ['action' => 'send_topic_msg', 'user_id' => $user_id, 'nickname' => $user->nickname, 'sex' => $user->sex,
                'avatar_url' => $user->avatar_url, 'avatar_small_url' => $user->avatar_small_url, 'content' => $content,
                'channel_name' => $room->channel_name
            ];

            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $server = \PushSever::send('push', $intranet_ip, 9508, $payload);
            return $this->renderJSON(ERROR_CODE_FAIL, '发送成功');
        }
        $this->view->user = $user;
    }
}