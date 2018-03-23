<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/16
 * Time: 下午4:35
 */
class RoomsTask extends \Phalcon\Cli\Task
{
    //检查用户是否在房间
    function checkUserRoomAction()
    {
        $cond = ['conditions' => 'status = :status:', 'bind' => ['status' => STATUS_ON]];
        $rooms = Rooms::findForeach($cond);
        $hot_cache = Rooms::getHotWriteCache();

        foreach ($rooms as $room) {
            $key = $room->getRealUserListKey();
            $user_ids = $hot_cache->zrange($key, 0, -1);

            $users = Users::findByIds($user_ids);

            foreach ($users as $user) {

                if ($user->isSilent()) {
                    info("silent_user", $user->id);
                    continue;
                }

                if (($user->current_room_id != $room->id || !$user->isNormal() || $user->last_at < time() - 3600)
                    && STATUS_ON != $room->hot && !$user->isRoomHost($room) && $user->current_room_seat_id > 0) {

                    info($user->id, $room->id, $user->current_room_id, $user->user_status, $user->last_at, time());

                    $unbind = true;

                    //用户在新的房间 不解绑
                    if ($user->current_room_id != $room->id && $user->current_room_id > 0) {
                        $unbind = false;
                    }

                    $current_room_seat_id = $user->current_room_seat_id;
                    $room->exitRoom($user, $unbind);
                    $room->pushExitRoomMessage($user, $current_room_seat_id);
                }
            }
        }
    }

    //释放所有离线沉默房间
    function clearAllOfflineSilentRoomsAction()
    {
        $online_silent_rooms = Rooms::getOnlineSilentRooms();

        if (!$online_silent_rooms) {
            info("no rooms");
            return;
        }

        foreach ($online_silent_rooms as $online_silent_room) {

            $users = $online_silent_room->findSilentUsers();

            foreach ($users as $user) {
                $online_silent_room->exitSilentRoom($user);
            }
        }
    }

    //唤醒离线沉默房间
    function wakeUpOfflineSilentRoomsAction()
    {

        $room_num = Rooms::count(
            [
                'conditions' => 'status = :status: and online_status = :online_status:',
                'bind' => ['status' => STATUS_ON, 'online_status' => STATUS_ON]
            ]);

        if ($room_num >= 15) {
            info("room_num", $room_num);
            return;
        }

        $online_silent_room_num = Rooms::getOnlineSilentRoomNum();

        $num = 5;

        if (isDevelopmentEnv()) {
            $num = 30;
        }

        if ($online_silent_room_num >= $num) {
            info("online_silent_room_num", $online_silent_room_num);
            return;
        }

        $rooms = Rooms::getOfflineSilentRooms();

        foreach ($rooms as $room) {
            $user = $room->user;

            if ($user->isInAnyRoom()) {
                info($user->id, $user->current_room_id, $room->id);
                continue;
            }

            $delay_time = mt_rand(1, 60);
            Rooms::delay($delay_time)->enterSilentRoom($room->id, $user->id);
            info($room->id, $delay_time);
        }

        info($online_silent_room_num, count($rooms));
    }

    //释放离线沉默房间
    function clearOfflineSilentRoomsAction()
    {
        $online_silent_rooms = Rooms::getExpireOnlineSilentRooms();

        if (!$online_silent_rooms) {
            info("no rooms");
            return;
        }

        $hot_cache = Rooms::getHotWriteCache();

        foreach ($online_silent_rooms as $online_silent_room) {

            if ($online_silent_room->getUserNum() < 1) {
                info($online_silent_room->id);
                if ($online_silent_room->isOnline()) {
                    $online_silent_room->online_status = STATUS_OFF;
                    $online_silent_room->save();
                }

                $online_silent_room->rmOnlineSilentRoom();
                continue;
            }

            $real_user_num = $online_silent_room->getRealUserNum();

            if ($real_user_num > 0) {
                $expire_time = $online_silent_room->getExpireTime();
                info($online_silent_room->id, $real_user_num, date("Ymd H:i:s", $expire_time));
                //有真实用户,房间生命周期延长2分钟
                $delay_time = $expire_time + 120;
                $online_silent_room->updateOnlineSilentRoom($delay_time);
                continue;
            }

            $key = $online_silent_room->getUserListKey();
            $user_ids = $hot_cache->zrange($key, 0, -1);
            $users = Users::findByIds($user_ids);

            foreach ($users as $user) {
                $online_silent_room->exitSilentRoom($user);
            }
        }
    }

    //沉默用户活跃房间
    function activeSilentRoomAction()
    {
        $cond = ['conditions' => '(online_status = :online_status: and user_type = :user_type:) or
         (status = :status: and user_type = :user_type1:)',
            'bind' => ['status' => STATUS_ON, 'online_status' => STATUS_ON, 'user_type' => USER_TYPE_SILENT, 'user_type1' => USER_TYPE_ACTIVE],
            'order' => 'last_at desc', 'limit' => 60];

        $rooms = Rooms::find($cond);

        foreach ($rooms as $room) {
            Rooms::delay()->activeRoom($room->id);
        }
    }

    //压力测试
    function pressureTestAction($params)
    {
        $room_id = $params[0];
        $user_num = $params[1];

        debug($room_id, $user_num);
        $room = Rooms::findFirstById($room_id);

        if (!$room || $room->user_num >= 500) {
            return;
        }

        $users = $room->selectSilentUsers($user_num);

        foreach ($users as $user) {

            if ($user->isInAnyRoom()) {
                info("user_in_other_room", $user->id, $user->current_room_id, $room->id);
                continue;
            }

            $delay_time = mt_rand(1, 60);

            info($room->id, $user->id, $delay_time);
            Rooms::addWaitEnterSilentRoomList($user->id);
            Rooms::delay($delay_time)->enterSilentRoom($room->id, $user->id);
        }
    }

    //刷新管理员
    function freshManagersAction()
    {
        $db = Rooms::getRoomDb();
        $total_room_key = Rooms::generateTotalManagerKey();
        $keys = $db->zrange($total_room_key, 0, -1);

        info("total_room_key_num", count($keys));

        $room_ids = [];

        foreach ($keys as $key) {
            preg_match('/room_id(\d+)_user_id(\d+)/', $key, $matches);

            if (count($matches) < 3) {
                info("room_id not exit", $key);
                continue;
            }

            $room_id = $matches[1];

            if ($room_id) {
                $room_ids[] = $room_id;
            }
        }

        info($room_ids);

        if (count($room_ids) > 0) {
            $rooms = Rooms::findByIds($room_ids);

            foreach ($rooms as $room) {
                $room->freshManagerNum();
            }
        }
    }

    //自动上热门
    function roomAutoToHotAction()
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();
        $last = time() - 10 * 60;
        $manual_hot_room_num = 5;
        $total_num = 10;
        $least_num = 3;

        if (isProduction()) {
            $manual_hot_room_num = 10;
            $total_num = 20;
            $least_num = 5;
            $last = time() - 5 * 60;
        }

        $cond = [
            'conditions' => 'hot = :hot: and status = :status: and last_at >= :last_at:',
            'bind' => ['hot' => STATUS_ON, 'status' => STATUS_ON, 'last_at' => $last],
            'order' => 'last_at desc',
        ];

        //总的热门房间
        $total_room_ids = [];

        //固定活跃房间
        $manual_hot_rooms = Rooms::find($cond);

        info(count($manual_hot_rooms));

        foreach ($manual_hot_rooms as $manual_hot_room) {

            if (!$manual_hot_room->checkRoomSeat()) {
                info("room_seat_is_null", $manual_hot_room->id);
                continue;
            }

            if ($manual_hot_room->lock) {
                info("room_seat_is_lock", $manual_hot_room->id);
                continue;
            }

            $total_room_ids[] = $manual_hot_room->id;

            //选10个手动房间
            if (count($total_room_ids) >= $manual_hot_room_num) {
                info($total_room_ids, count($total_room_ids), $manual_hot_room_num);
                break;
            }
        }

        $start = time() - 11 * 60;
        $end = time() - 60;

        $cond = [
            'conditions' => 'room_id > 0 and created_at >= :start: and created_at <= :end:',
            'bind' => ['start' => $start, 'end' => $end],
            'columns' => 'distinct room_id'];

        $gift_orders = GiftOrders::find($cond);

        info($total_room_ids, count($total_room_ids));
        foreach ($gift_orders as $gift_order) {

            $room = Rooms::findFirstById($gift_order->room_id);

            if (!$room) {
                info($gift_order->room_id);
                continue;
            }

            if ($room->isForbiddenHot()) {
                info("isForbiddenHot", $room->id);
                continue;
            }

            if (!$room->checkRoomSeat()) {
                info("room_seat_is_null", $room->id);
                continue;
            }

            if ($room->lock) {
                info("room_seat_is_lock", $room->id);
                continue;
            }

            if (in_array($room->id, $total_room_ids)) {
                continue;
            }

            if ($room->isHot()) {
                continue;
            }

            $total_room_ids[] = $room->id;

            if (count($total_room_ids) >= $total_num) {
                info($total_room_ids, count($total_room_ids), $total_num);
                break;
            }
        }

        $total_room_num = count($total_room_ids);

        info($total_room_ids, $total_room_num);

        if ($total_room_num < $total_num) {

            $need_room_num = $total_num - $total_room_num;

            if ($hot_cache->zcard(Rooms::getTotalRoomUserNumListKey()) > 0) {

                $user_num_room_ids = $hot_cache->zrange(Rooms::getTotalRoomUserNumListKey(), 0, -1);

                $num = 1;

                foreach ($user_num_room_ids as $user_num_room_id) {

                    if ($num > $need_room_num) {
                        break;
                    }

                    $user_num_room = Rooms::findFirstById($user_num_room_id);

                    if (!$user_num_room) {
                        continue;
                    }

                    if ($user_num_room->isHot()) {
                        continue;
                    }

                    if ($user_num_room->isForbiddenHot()) {
                        info("isForbiddenHot", $user_num_room_id);
                        continue;
                    }

                    if (!$user_num_room->checkRoomSeat()) {
                        info("room_seat_is_null", $user_num_room->id);
                        continue;
                    }

                    if ($user_num_room->lock) {
                        info("room_seat_is_lock", $user_num_room->id);
                        continue;
                    }

                    if (!in_array($user_num_room_id, $total_room_ids)) {

                        $total_room_ids[] = $user_num_room_id;

                        $num++;

                        if (count($total_room_ids) >= $total_num) {
                            info($total_room_ids, count($total_room_ids), $total_num);
                            break;
                        }
                    }
                }
            }
        }

        $total_room_num = count($total_room_ids);

        info($total_room_ids, $total_room_num);

        if ($total_room_num < $least_num) {

            $broadcast_rooms = Rooms::find(
                [
                    'conditions' => "theme_type = :theme_type:",
                    'bind' => ['theme_type' => ROOM_THEME_TYPE_BROADCAST],
                    'limit' => $least_num - $total_room_num
                ]);

            foreach ($broadcast_rooms as $broadcast_room) {
                $broadcast_room->enterRoom($broadcast_room->user);
                $total_room_ids[] = $broadcast_room->id;
            }
        }

        $hot_room_ids = [];

        foreach ($total_room_ids as $room_id) {

            $cond = [
                'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end:',
                'bind' => ['start' => $start, 'end' => $end, 'room_id' => $room_id],
                'column' => 'amount'
            ];

            $income = GiftOrders::sum($cond);

            info($income);

            $hot_room_ids[$room_id] = $income;
        }

        info($hot_room_ids);

        $hot_cache->zclear($hot_room_list_key);

        arsort($hot_room_ids);

        $has_amount_room_ids = [];
        $no_amount_room_ids = [];

        foreach ($hot_room_ids as $room_id => $income) {

            if ($income > 0) {
                $has_amount_room_ids[] = $room_id;
            } else {
                $room = Rooms::findFirstById($room_id);
                $no_amount_room_ids[$room_id] = $room->user_num;
            }
        }

        arsort($no_amount_room_ids);

        $time = time();

        foreach ($has_amount_room_ids as $has_amount_room_id) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $has_amount_room_id);
        }

        foreach ($no_amount_room_ids as $no_amount_room_id => $user_num) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $no_amount_room_id);
        }

        info($hot_cache->zrevrange($hot_room_list_key, 0, -1, true));
    }

    //热门房间排序
    function hotRoomRankAction()
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();

        $hot_room_ids = $hot_cache->zrange($hot_room_list_key, 0, -1);
        $total_room_ids = [];

        $start = time() - 11 * 60;
        $end = time() - 60;

        foreach ($hot_room_ids as $hot_room_id) {

            $hot_room = Rooms::findFirstById($hot_room_id);

            if (!$hot_room->checkRoomSeat()) {
                info("room_seat_is_null", $hot_room->id);
                continue;
            }

            if ($hot_room->lock || $hot_room->isBlocked() || $hot_room->isForbiddenHot()) {
                info("lock", $hot_room->lock, "blocked", $hot_room->isBlocked(), "isForbiddenHot", $hot_room->isForbiddenHot());
                $hot_cache->zrem($hot_room_list_key, $hot_room_id);
                continue;
            }

            $cond = [
                'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end:',
                'bind' => ['start' => $start, 'end' => $end, 'room_id' => $hot_room_id],
                'column' => 'amount'
            ];

            $income = GiftOrders::sum($cond);
            $total_room_ids[$hot_room_id] = $income;
        }

        arsort($total_room_ids);

        $has_amount_room_ids = [];
        $no_amount_room_ids = [];

        foreach ($total_room_ids as $room_id => $income) {

            if ($income > 0) {
                $has_amount_room_ids[] = $room_id;
            } else {
                $room = Rooms::findFirstById($room_id);
                $no_amount_room_ids[$room_id] = $room->user_num;
            }
        }

        arsort($no_amount_room_ids);

        $time = time();

        foreach ($has_amount_room_ids as $has_amount_room_id) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $has_amount_room_id);
        }

        foreach ($no_amount_room_ids as $no_amount_room_id => $income) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $no_amount_room_id);
        }

        info($hot_cache->zrevrange($hot_room_list_key, 0, -1, true));
    }
}
