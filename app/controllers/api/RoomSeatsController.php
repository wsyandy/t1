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

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function downAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function closeAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }


    function openAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function setMicrophoneAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }


}