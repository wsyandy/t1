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

        $current_user_id = $this->currentUserId();
        $other_user_id = $this->otherUserId();

        $hot_cache = \RoomSeats::getHotWriteCache();
        $room_seat_lock_key = "room_seat_lock{$room_seat_id}";
        $room_seat_user_lock_key = "room_seat_user_lock{$current_user_id}";
        $room_seat_up_user_lock_key = "room_seat_up_user_lock{$current_user_id}";

        if ($other_user_id) {
            $room_seat_user_lock_key = "room_seat_user_lock{$other_user_id}";
            $room_seat_up_user_lock_key = "room_seat_up_user_lock{$other_user_id}";
        }

        $room_seat_lock = tryLock($room_seat_lock_key, 1000);
        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000);

        $current_user = $this->currentUser(true);
        $other_user = $this->otherUser(true);

        $room_seat = \RoomSeats::findFirstById($room_seat_id);

        if (!$room_seat) {
            unlock($room_seat_lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位不存在');
        }

        if ($hot_cache->get($room_seat_up_user_lock_key)) {
            info($room_seat_up_user_lock_key, "用户已被抱上麦");
            unlock($room_seat_lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '用户已被抱上麦');
        }

        // 抱用户上麦
        list($error_code, $error_reason) = $room_seat->up($current_user, $other_user);

        if (ERROR_CODE_SUCCESS == $error_code && $other_user_id) {
            //标记用户被抱上麦
            $hot_cache->setex($room_seat_up_user_lock_key, 3, $room_seat_id);
        }

        unlock($room_seat_lock);
        unlock($room_seat_user_lock);

        return $this->renderJSON($error_code, $error_reason, $room_seat->toSimpleJson());
    }

    function downAction()
    {
        $room_seat_id = $this->params('id', 0);

        if (!$room_seat_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $current_user_id = $this->currentUserId();
        $other_user_id = $this->otherUserId();

        $room_seat_lock_key = "room_seat_lock{$room_seat_id}";
        $room_seat_user_lock_key = "room_seat_user_lock{$current_user_id}";

        if ($other_user_id) {
            $room_seat_user_lock_key = "room_seat_user_lock{$other_user_id}";
        }

        $room_seat_lock = tryLock($room_seat_lock_key, 1000);
        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000);

        $current_user = $this->currentUser(true);
        $other_user = $this->otherUser(true);
        $room_seat = \RoomSeats::findFirstById($room_seat_id);

        if (!$room_seat) {
            unlock($room_seat_lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位不存在');
        }

        if ($other_user && !$this->currentUser()->isRoomHost($room_seat->room)) {
            unlock($room_seat_lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->down($current_user, $other_user);

        unlock($room_seat_lock);
        unlock($room_seat_user_lock);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 封麦
    function closeAction()
    {
        $room_seat_id = $this->params('id', 0);

        if (!$room_seat_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $current_user_id = $this->currentUserId();
        $other_user_id = $this->otherUserId();

        $room_seat_user_lock_key = "room_seat_user_lock{$current_user_id}";

        if ($other_user_id) {
            $room_seat_user_lock_key = "room_seat_user_lock{$other_user_id}";
        }

        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000);
        $lock = tryLock("room_seat_lock{$room_seat_id}", 1000);

        $current_user = $this->currentUser();
        $room_seat = \RoomSeats::findFirstById($room_seat_id);

        if (!$room_seat) {
            unlock($lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位不存在');
        }

        if (!$current_user->isRoomHost($room_seat->room)) {
            unlock($lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->close();

        unlock($lock);
        unlock($room_seat_user_lock);
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

    //确认上麦
    function confirmUpAction()
    {
        $room_seat_id = $this->params('id', 0);

        if (!$room_seat_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $hot_cache = \RoomSeats::getHotWriteCache();
        $room_seat_up_user_lock_key = "room_seat_up_user_lock{$this->currentUserId()}";
        $hot_cache->del($room_seat_up_user_lock_key);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //取消上麦
    function cancelUpAction()
    {
        $room_seat_id = $this->params('id', 0);

        if (!$room_seat_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $current_user_id = $this->currentUserId();

        $hot_cache = \RoomSeats::getHotWriteCache();
        $room_seat_up_user_lock_key = "room_seat_up_user_lock{$current_user_id}";
        $hot_cache->del($room_seat_up_user_lock_key);

        $room_seat_lock_key = "room_seat_lock{$room_seat_id}";
        $room_seat_user_lock_key = "room_seat_user_lock{$current_user_id}";

        $room_seat_lock = tryLock($room_seat_lock_key, 1000);
        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000);

        $current_user = $this->currentUser(true);
        $other_user = $this->otherUser(true);
        $room_seat = \RoomSeats::findFirstById($room_seat_id);

        if (!$room_seat) {
            unlock($room_seat_lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位不存在');
        }

        if ($other_user && !$this->currentUser()->isRoomHost($room_seat->room)) {
            unlock($room_seat_lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->down($current_user, $other_user);

        unlock($room_seat_lock);
        unlock($room_seat_user_lock);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }
}