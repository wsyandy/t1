<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/8
 * Time: 下午4:18
 */
namespace admin;

class BroadcastsController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('room');

        if (isset($cond['conditions'])) {
            $cond['conditions'] .= " and theme_type = " . ROOM_THEME_TYPE_BROADCAST;
        } else {
            $cond['conditions'] = "theme_type = " . ROOM_THEME_TYPE_BROADCAST;
        }

        $name = $this->params('name');
        $cond['conditions'] .= " and name like '%$name%' ";

        $page = 1;
        $total_page = 1;
        $per_page = 30;
        $total_entries = $total_page * $per_page;
        $cond['order'] = "id desc";
        $rooms = \Rooms::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->rooms = $rooms;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function onlineAction()
    {
        $room_id = $this->params('room_id');
        $room = \Rooms::findFirstById($room_id);
        if (isBlank($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '此房间不存在');
        }
        if ($this->request->isPost()) {
            if (STATUS_ON == $room->online_status) {
                return $this->renderJSON(ERROR_CODE_FAIL, '已经在线');
            }
            $user = $room->user;
            if (isBlank($user)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '房主不存在');
            }
            //如果进入其他房间时 用户身上有房间 先退出房间
            if ($user->current_room && $user->current_room->id != $room_id) {
                $user->current_room->exitRoom($user());
                //如果进入其他房间时 用户身上有麦位 先下麦位
                if ($user->current_room_seat) {
                    $user->current_room_seat->down($user);
                }
            }
            $room->enterRoom($user);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '上线成功', ['redirect_url' => '/admin/broadcasts']);
        }
        $this->view->room_id = $room_id;
    }

    function offlineAction()
    {
        $room_id = $this->params('room_id');
        $room = \Rooms::findFirstById($room_id);
        if (isBlank($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '此房间不存在');
        }
        if ($this->request->isPost()) {
            if (STATUS_OFF == $room->online_status) {
                return $this->renderJSON(ERROR_CODE_FAIL, '已经下线');
            }
            $user = $room->user;
            if (isBlank($user)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '房主不存在');
            }
            if (!$user->isInRoom($room)) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '下线成功', ['error_url' => '/admin/broadcasts']);
            }

            $room->exitRoom($user);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '下线成功', ['error_url' => '/admin/broadcasts']);
        }
        $this->view->room_id = $room_id;
    }

    function compileRoomAction()
    {
        $room_id = $this->params('room_id');
        $room = \Rooms::findFirstById($room_id);
        if ($this->request->isPost()) {
            $this->assign($room, 'room');
            if (!$room->lock) {
                $room->password = '';
            }
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room);
            if ($room->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '编辑成功');
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '编辑失败');
            }
        }
        $this->view->room = $room;
        $this->view->room_id = $room_id;
        $this->view->lock = [true => '有锁', false => '无锁'];
    }

    function compileUserAction()
    {
        $user_id = $this->params('user_id');
        $user = \Users::findFirstById($user_id);
        if ($this->request->isPost()) {
            $sex = $this->params('user[sex]');
            $nickname = $this->params('user[nickname]');
            $avatar = $this->file('user[avatar]');

            $user->sex = $sex;
            $user->nickname = $nickname;
            if ($avatar) {
                $user->updateAvatar($avatar);
            }
//            $this->assign($user, 'user');

            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $user);
            if ($user->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '编辑成功');
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '编辑失败');
            }
        }
        $this->view->user_id = $user_id;
        $this->view->user = $user;
    }

    function compileRoomSeatAction()
    {
        $seat_id = $this->params('seat_id');
        $room_seat = \RoomSeats::findFirstById($seat_id);
        if ($this->request->isPost()) {
            $room_id = $room_seat->room_id;
            $room = \Rooms::findFirstById($room_id);
            if (!$room || $room != ROOM_THEME_TYPE_BROADCAST) {
                return $this->renderJSON(ERROR_CODE_FAIL, '麦位不存在或此房间不是电台');
            }

            $status = $this->params('room_seat[status]');
            $microphone = $this->params('room_seat[microphone]');

            if ($status == STATUS_ON && $microphone) {
                return $this->renderJSON(ERROR_CODE_FAIL, '电台房间，麦位解封状态下，麦克风必须禁止');
            }

            $room_seat->status = $status;
            $room_seat->microphone = $microphone;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room_seat);
            if ($room_seat->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '编辑成功');
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '编辑失败');
            }
        }
        $this->view->seat_id = $seat_id;
        $this->view->room_seat = $room_seat;
        $this->view->microphone = [true => '允许', false => '禁止'];
    }
}