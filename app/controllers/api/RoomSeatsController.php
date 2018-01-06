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

        $user_id = $this->params('user_id');

        $this->currentUser()->current_room_seat_id = $room_seat->id;
        $this->currentUser()->update();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['user' => $this->currentUser()->toBasicJson()]);
    }

    function downAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $user_id = $this->params('user_id');

        $this->currentUser()->current_room_seat_id = 0;
        $this->currentUser()->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['user' => $this->currentUser()->toBasicJson()]);
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

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
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

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function closeMicrophoneAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat->microphone = false;
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function openMicrophoneAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat->microphone = true;
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

}