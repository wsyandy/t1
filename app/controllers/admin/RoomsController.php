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
        $page = $this->params('page');
        $per_page = $this->params('per_page');

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
}