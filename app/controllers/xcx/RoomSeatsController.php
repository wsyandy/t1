<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/2
 * Time: 下午7:53
 */

namespace xcx;


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

        // 报用户上麦
        if ($other_user_id) {
            $room_seat_user_lock_key = "room_seat_user_lock{$other_user_id}";
            $room_seat_up_user_lock_key = "room_seat_up_user_lock{$other_user_id}";
        }

        $room_seat_lock = tryLock($room_seat_lock_key, 1000, true);
        if(!$room_seat_lock){
            return $this->renderJSON(ERROR_CODE_FAIL, '您的手速太慢');
        }

        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000, true);
        if(!$room_seat_user_lock){
            unlock($room_seat_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '您的手速太慢');
        }

        $current_user = $this->currentUser(true);
        $other_user = $this->otherUser(true);

        if ($hot_cache->get($room_seat_up_user_lock_key)) {
            unlock($room_seat_lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '用户已被抱上麦');
        }

        $room_seat = \RoomSeats::findFirstById($room_seat_id);

        if (!$room_seat) {
            unlock($room_seat_lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位参数非法');
        }

        // 抱用户上麦
        list($error_code, $error_reason) = $room_seat->up($current_user, $other_user);
        if (ERROR_CODE_SUCCESS == $error_code && $other_user_id) {
            //标记用户被抱上麦
            $hot_cache->setex($room_seat_up_user_lock_key, 3, $room_seat_id);
        }

        $room = $room_seat->room;
        $room->updateLastAt($current_user);

        unlock($room_seat_lock);
        unlock($room_seat_user_lock);

        info($this->currentUser()->sid, $error_code, $error_reason);
        return $this->renderJSON($error_code, $error_reason, $room_seat->toSimpleJson());
    }

    function downAction()
    {

        $room_seat_id = $this->params('id', 0);
        if (!$room_seat_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room_seat = \RoomSeats::findFirstById($room_seat_id);
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位参数非法');
        }

        $room = $room_seat->room;
        if ($this->otherUserId() && !$this->currentUser()->canManagerRoom($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $current_user_id = $this->currentUserId();
        $other_user_id = $this->otherUserId();

        $room_seat_lock_key = "room_seat_lock{$room_seat_id}";
        $room_seat_user_lock_key = "room_seat_user_lock{$current_user_id}";
        if ($other_user_id) {
            $room_seat_user_lock_key = "room_seat_user_lock{$other_user_id}";
        }

        $room_seat_lock = tryLock($room_seat_lock_key, 1000, true);
        if(!$room_seat_lock){
            return $this->renderJSON(ERROR_CODE_FAIL, '网络慢请重试');
        }

        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000, true);
        if(!$room_seat_user_lock){
            unlock($room_seat_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '网络慢请重试');
        }

        $current_user = $this->currentUser(true);
        $other_user = $this->otherUser(true);
        $room_seat->down($current_user, $other_user);

        $room = $room_seat->room;
        $room->updateLastAt($current_user);

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
        if(!$room_seat_user_lock){
            return $this->renderJSON(ERROR_CODE_FAIL, '网络慢请重试');
        }

        $lock = tryLock("room_seat_lock{$room_seat_id}", 1000);
        if(!$lock){
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '网络慢请重试');
        }

        $current_user = $this->currentUser();
        $room_seat = \RoomSeats::findFirstById($room_seat_id);
        if (!$room_seat) {
            unlock($lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '麦位参数非法');
        }

        if (!$current_user->canManagerRoom($room_seat->room)) {
            unlock($lock);
            unlock($room_seat_user_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->close();

        $room = $room_seat->room;
        $room->updateLastAt($current_user);

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

        if (!$this->currentUser()->canManagerRoom($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->open();
        $room = $room_seat->room;
        $room->updateLastAt($this->currentUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 禁麦
    function closeMicrophoneAction()
    {

        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->canManagerRoom($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->microphone = false;
        $room_seat->save();

        $room = $room_seat->room;
        $room->updateLastAt($this->currentUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    // 取消禁麦
    function openMicrophoneAction()
    {

        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));
        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->canManagerRoom($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->microphone = true;
        $room_seat->save();

        $room = $room_seat->room;
        $room->updateLastAt($this->currentUser());

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

        $room_seat_lock = tryLock($room_seat_lock_key, 1000, true);
        if(!$room_seat_lock){
            return $this->renderJSON(ERROR_CODE_FAIL, '网络慢请重试');
        }

        $room_seat_user_lock = tryLock($room_seat_user_lock_key, 1000, true);
        if(!$room_seat_user_lock){
            unlock($room_seat_lock);
            return $this->renderJSON(ERROR_CODE_FAIL, '网络慢请重试');
        }

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


    function openMusicPermissionAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->can_play_music = true;
        $room_seat->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());

    }

    function closeMusicPermissionAction()
    {
        $room_seat = \RoomSeats::findFirstById($this->params('id', 0));

        if (!$room_seat) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room_seat->room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room_seat->can_play_music = false;
        $room_seat->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toSimpleJson());
    }

    function detailAction()
    {
        $id = intval($this->params('id'));
        $room_seat = \RoomSeats::findFirstById($id);
        if (!$room_seat) {
            return renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room_seat->toJson());

    }
}