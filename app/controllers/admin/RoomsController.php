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

            if ($action == 'send_topic_msg') {
                $body = ['action' => 'send_topic_msg', 'user_id' => $sender->id, 'nickname' => $sender->nickname, 'sex' => $sender->sex,
                    'avatar_url' => $sender->avatar_url, 'avatar_small_url' => $sender->avatar_small_url, 'content' => $content,
                    'channel_name' => $room->channel_name
                ];
            }

            if ($action == 'enter_room') {
                $body = ['action' => 'enter_room', 'user_id' => $sender->id, 'nickname' => $sender->nickname, 'sex' => $sender->sex,
                    'avatar_url' => $sender->avatar_url, 'avatar_small_url' => $sender->avatar_small_url, 'channel_name' => $room->channel_name
                ];
            }

            if ($action == 'give_gift') {
                if ($sender->current_room_id != $room->id) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '发送者必须在此房间');
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

                $body = ['action' => 'send_gift', 'notify_type' => 'bc', 'channel_name' => $room->channel_name, 'gift' => $data];
            }


            $payload = ['body' => $body, 'fd' => $receiver_fd];

            $server = \PushSever::send('push', $intranet_ip, 9508, $payload);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');

        }
        $this->view->user_id = $user_id;
        $this->view->actions = ['send_topic_msg' => '发公屏消息', 'enter_room' => '进房间', 'give_gift'=>'送礼物'];
        $this->view->room = $room;
    }
}