<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/2
 * Time: 下午7:53
 */

namespace api;


class RoomSeatsController extends BaseController
{

    function upAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        //当前用户不在房间
        if ($this->otherUser()) {

            if (!$this->currentUser()->isRoomSeatHost($room_seat)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
            }

            if (!$this->otherUser()->isInRoom($room_seat->room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在房间');
            }
        }

        if ($this->otherUser() && $this->otherUser()->current_room_seat_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户已在麦位');
        }

        // 抱用户上麦
        $room_seat->up($this->currentUser(), $this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    function downAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if ($this->otherUser() && !$this->currentUser()->isRoomSeatHost($room_seat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->down($this->currentUser(), $this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 封麦
    function closeAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomSeatHost($room_seat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->close();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 解封
    function openAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomSeatHost($room_seat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->open();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 禁麦
    function closeMicrophoneAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomSeatHost($room_seat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->microphone = false;
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 取消禁麦
    function openMicrophoneAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomSeatHost($room_seat)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->microphone = true;
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }


}