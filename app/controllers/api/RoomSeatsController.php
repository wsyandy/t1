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

        // 抱用户上麦
        $room_seat->up($this->currentUser(), $this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toUserJson());
    }

    function downAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat->down($this->currentUser(), $this->otherUser());
        
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toUserJson());
    }

    // 封麦
    function closeAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat->status = STATUS_OFF;
        $room_seat->user_id = 0; // 设为旁听
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toUserJson());
    }

    // 解封
    function openAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat->status = STATUS_ON;
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toUserJson());
    }

    function closeMicrophoneAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat->microphone = false;
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toUserJson());
    }

    function openMicrophoneAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat->microphone = true;
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toUserJson());
    }

}