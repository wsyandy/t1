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

        $hot_cache = \Users::getHotWriteCache();

        //防止多个用户并发抢占麦位
        if (!$hot_cache->set("room_seat_lock{$room_seat->id}", 1, ['NX', 'EX' => 1])) {
            return $this->renderJSON(ERROR_CODE_FAIL, '房间已有用户');
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

            $key = "room_seat_operation{$room_seat->id}_user{$this->currentUser()->id}";

            if (!$hot_cache->set($key, 1, ['NX', 'PX' => 500])) {
                return $this->renderJSON(ERROR_CODE_FAIL, '操作频繁');
            }

            //房主不能上自己的麦位
            if ($room_seat->room->user_id === $this->currentUser()->id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '房主不能上自己的麦位');
            }

            if ($room_seat->user_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '麦位已存在用户');
            }

            //当前用户已在麦位
            if ($this->currentUser()->current_room_seat_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户已在麦位');
            }
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

        if ($this->otherUser() && !$this->currentUser()->isRoomHost($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $hot_cache = \Users::getHotWriteCache();
        $key = "room_seat_operation{$room_seat->id}_user{$this->currentUser()->id}";

        if (!$this->otherUser() && !$hot_cache->set($key, 1, ['NX', 'PX' => 500])) {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作频繁');
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

        if (!$this->currentUser()->isRoomHost($room_seat->room)) {
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