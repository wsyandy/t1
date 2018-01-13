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
        $room_seat_id = $this->params('id', 0);

        if (!$room_seat_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat_lock_key = "room_seat_lock{$room_seat_id}";

        $room_seat_lock = tryLock($room_seat_lock_key, 1000);

        $room_seat = \RoomSeats::findFirstById($room_seat_id);

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位不存在');
        }

        if ($room_seat->user_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位已存在用户');
        }

        if ($this->otherUser()) {

            if (!$this->currentUser()->isRoomHost($room_seat->room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
            }

            //不能抱自己上麦
            if ($this->otherUser()->id === $this->currentUser()->id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '不能抱自己上麦');
            }

            //当前用户不在房间
            if (!$this->otherUser()->isInRoom($room_seat->room)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在房间');
            }

            //当前用户已在麦位
            if ($this->otherUser()->current_room_seat_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户已在麦位');
            }

        } else {

            if ($room_seat->isClose()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '麦位已被封');
            }

            //房主不能上自己的麦位
            if ($room_seat->room->user_id === $this->currentUser()->id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '房主不能上自己的麦位');
            }

            //当前用户已在麦位
            $current_room_seat = $this->currentUser()->current_room_seat;

            if ($current_room_seat) {
                $current_room_seat->down($this->currentUser());
                debug("change_room_seat", $current_room_seat->id, $room_seat->id, $this->currentUser()->id);
            }
        }

        // 抱用户上麦
        $room_seat->up($this->currentUser(), $this->otherUser());

        unlock($room_seat_lock);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    function downAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if ($this->otherUser() && !$this->currentUser()->isRoomHost($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->down($this->currentUser(), $this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 封麦
    function closeAction()
    {
        $room_seat_id = $this->params('id', 0);

        if (!$room_seat_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $lock = tryLock("room_seat_lock{$room_seat_id}", 1000);

        $room_seat = \RoomSeats::findFirstById($room_seat_id);

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位不存在');
        }

        if (!$this->currentUser()->isRoomHost($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->close();

        unlock($lock);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 解封
    function openAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room_seat->room)) {
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

        if (!$this->currentUser()->isRoomHost($room_seat->room)) {
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

        if (!$this->currentUser()->isRoomHost($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->microphone = true;
        $room_seat->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }


}