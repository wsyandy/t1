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

        $cond = ['conditions' => 'status = :status: and last_at<:last_at:',
            'bind' => ['status' => STATUS_ON, 'last_at' => time()]];

        $rooms = Rooms::findForeach($cond);
        $hot_cache = Rooms::getHotWriteCache();

        foreach ($rooms as $room) {

            if ($room->isIosAuthRoom()) {
                continue;
            }

            $key = $room->getUserListKey();
            $user_ids = $hot_cache->zrange($key, 0, -1);
            if (count($user_ids) < 1) {
                $room->status = STATUS_OFF;
                $room->save();
                info('no user', $room->id, 'online_status_text', $room->online_status_text, date('c', $room->last_at));
                continue;
            }

            $users = Users::findByIds($user_ids);
            foreach ($users as $user) {

                if ((time() - $room->last_at) > 3600 * 12) {
                    $room->exitRoom($user, true);
                    info("room_last_at", $room->id, 'user', $user->id, 'last_at', date("YmdH", $room->last_at));
                    continue;
                }

                if ($user->isSilent()) {
                    continue;
                }

                // 用户已不再房间里或者状态不正常
                if ($user->current_room_id != $room->id || !$user->isNormal() || (time() - $user->last_at) > 3600 * 12) {

                    $current_room_id = $user->current_room_id;
                    $current_room_seat_id = $user->current_room_seat_id;
                    info('fix room', $room->id, 'user', $user->id, 'current_room_id', $user->current_room_id, $current_room_seat_id, 'last_at', date("YmdH", $user->last_at));

                    $room->exitRoom($user, true);
                    if ($current_room_id == $room->id) {
                        ////$room->pushExitRoomMessage($user, $current_room_seat_id);
                    }
                }


                //检测麦位状态
                $room_seats = RoomSeats::findByUserId($user->id);
                foreach ($room_seats as $room_seat) {
                    // 房间和麦位匹配
                    if ($room_seat->room_id == $user->current_room_id && $room_seat->id == $user->current_room_seat_id) {
                        continue;
                    }

                    $room_seat->user_id = 0;
                    $room_seat->save();
                    info('fix room_seat', $room_seat->id, 'user', $user->id, $user->current_room_seat_id);
                }


                if ($user->current_room_seat_id) {
                    $current_room_seat = $user->current_room_seat;
                    if ($current_room_seat->user_id != $user->id) {
                        info('fix current_room_seat', $current_room_seat->id, 'user', $user->id, $user->current_room_seat_id);
                        $user->current_room_seat_id = 0;
                        $user->save();
                    }
                }
            }
        }
    }

    function checkAbnormalExitRoomAction()
    {

        $target_ids = Rooms::getAbnormalExitRoomList();
        $total = count($target_ids);
        if ($total < 1) {
            info("no users", $target_ids);
            return;
        }

        info($total);

        foreach ($target_ids as $target_id) {

            list($room_id, $user_id) = explode("_", $target_id);
            if (!$room_id || !$user_id) {
                continue;
            }

            $user = Users::findFirstById($user_id);
            $room = Rooms::findFirstById($room_id);

            $current_room_id = $user->current_room_id;
            $current_room_seat_id = $user->current_room_seat_id;
            $need_push = false;

            if ($current_room_id != $room->id || !$user->isNormal()) {
                Rooms::delAbnormalExitRoomUserId($room_id, $user_id);
                $room->exitRoom($user, true);
                $need_push = true;
                info('room_is_change', $room->id, 'user', $user->id, 'current_room_id', $current_room_id, $current_room_seat_id, 'last_at', date("YmdH", $user->last_at));
            } else {

                $time = time() - 15 * 60;

                if ($user->last_at <= $time) {

                    $user_fd = $user->getUserFd();
                    if ($user_fd) {
                        info($user->id, 'user_fd', $user_fd, 'room_id', $room->id, 'current_room_id', $current_room_id, 'last_at', date("YmdH", $user->last_at));
                        continue;
                    }

                    info('fix room', $room->id, 'user', $user->id, 'current_room_id', $current_room_id, $current_room_seat_id, 'last_at', date("YmdHis", $user->last_at));

                    $need_push = true;
                    Rooms::delAbnormalExitRoomUserId($room_id, $user_id);
                    $room->exitRoom($user, true);
                }
            }

            if ($current_room_id == $room->id && $need_push) {
                ////$room->pushExitRoomMessage($user, $current_room_seat_id);
            }

            //检测麦位状态
            $room_seats = RoomSeats::findByUserId($user->id);

            foreach ($room_seats as $room_seat) {
                // 房间和麦位匹配
                if ($room_seat->room_id == $user->current_room_id && $room_seat->id == $user->current_room_seat_id) {
                    continue;
                }

                $room_seat->user_id = 0;
                $room_seat->save();
                info('fix room_seat', $room_seat->id, 'user', $user->id, $user->current_room_seat_id);
            }


            if ($user->current_room_seat_id) {
                $current_room_seat = $user->current_room_seat;
                if ($current_room_seat->user_id != $user->id) {
                    info('fix current_room_seat', $current_room_seat->id, 'user', $user->id, $user->current_room_seat_id);
                    $user->current_room_seat_id = 0;
                    $user->save();
                }
            }
        }
    }

    function checkExceExitRoomsAction()
    {

        $hot_cache = Rooms::getHotReadCache();
        $end_at = time() - 60;
        $target_ids = $hot_cache->zrangebyscore(Rooms::generateAbnormalExitRoomListKey(), $end_at - 36000, $end_at, array('limit' => array(0, 10000)));
        $total = count($target_ids);
        if ($total < 1) {
            info("no users", $target_ids);
            return;
        }

        info('count', $total);

        foreach ($target_ids as $target_id) {

            list($room_id, $user_id) = explode("_", $target_id);
            if (!$room_id || !$user_id) {
                info('no ', $room_id, $user_id);
                Rooms::delAbnormalExitRoomUserId($room_id, $user_id);
                continue;
            }

            $user = Users::findFirstById($user_id);
            $room = Rooms::findFirstById($room_id);
            if(!$user || !$room){
                info('no ', $room_id, $user_id);
                Rooms::delAbnormalExitRoomUserId($room_id, $user_id);
                continue;
            }

            $current_room_id = $user->current_room_id;
            $current_room_seat_id = $user->current_room_seat_id;

            // 不在房间 或 在其他房间
            if (!$current_room_id || $current_room_id != $room->id) {
                // 声网检测
                if (!AgoraApi::exitChannel($user, $room)) {
                    info('在其他房间 退出声网失败 room_is_change', $room->id, 'user', $user->id, 'current_room_id', $current_room_id, $current_room_seat_id, 'last_at', date("YmdHis", $user->last_at));
                    continue;
                }

                Rooms::delAbnormalExitRoomUserId($room_id, $user_id);
                $room->exitRoom($user);
                info('在其他房间 room_is_change', $room->id, 'user', $user->id, 'current_room_id', $current_room_id, $current_room_seat_id, 'last_at', date("YmdHis", $user->last_at));
                continue;
            }

            // 在原来房间

            list($in_channel, $user_role) = AgoraApi::inChannel($user, $room);

            // 不在频道
            if (!$in_channel) {
                info('不在频道 退出房间', $room->id, 'user', $user->id, 'current_room_id', $current_room_id, $current_room_seat_id, 'last_at', date("YmdHis", $user->last_at));
                Rooms::delAbnormalExitRoomUserId($room_id, $user_id);
                $room->exitRoom($user);
                continue;
            }

            // 在频道，角色错误
            if ($in_channel && $user_role == USER_ROLE_BROADCASTER && $user->id != $room->user_id && $current_room_seat_id < 1) {
                AgoraApi::kickingRule($user, $room, 1);
                Rooms::delAbnormalExitRoomUserId($room_id, $user_id);
                $room->exitRoom($user);
                info('角色错误 退出房间', $room->id, 'user', $user->id, 'current_room_id', $current_room_id, $current_room_seat_id, 'last_at', date("YmdHis", $user->last_at));
                continue;
            }

            $user_fd = $user->getUserFd();
            if (!$user_fd) {

                info('fd未连接 退出房间', $room->id, 'user', $user->id, 'current_room_id', $current_room_id, $current_room_seat_id, 'last_at', date("YmdHis", $user->last_at));

                Rooms::delAbnormalExitRoomUserId($room_id, $user_id);
                $room->exitRoom($user);
                AgoraApi::kickingRule($user, $room, 1);
                continue;
            }

            info('fd已连接', $user->id, 'user_fd', $user_fd, 'room_id', $room->id, 'current_room_id', $current_room_id, 'last_at', date("YmdHis", $user->last_at));
            Rooms::delAbnormalExitRoomUserId($room_id, $user_id);

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

            if ($online_silent_room->isIosAuthRoom()) {
                continue;
            }

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

        echoLine('room_num', $room_num);

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

            if ($room->isIosAuthRoom()) {
                continue;
            }

            $user = $room->user;

            if (!$user) {
                continue;
            }

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

            if ($online_silent_room->isIosAuthRoom()) {
                continue;
            }

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
            'bind' => ['online_status' => STATUS_ON, 'user_type' => USER_TYPE_SILENT, 'status' => STATUS_ON, 'user_type1' => USER_TYPE_ACTIVE],
            'order' => 'last_at desc', 'limit' => 60];

        $rooms = Rooms::find($cond);

        foreach ($rooms as $room) {

            if ($room->isIosAuthRoom()) {
                continue;
            }

            Rooms::autoActiveRoom($room->id);
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
        $db = Users::getUserDb();
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
        $hot_shield_room_list_key = Rooms::generateShieldHotRoomListKey();
        $novice_room_list_key = Rooms::generateNoviceHotRoomListKey();
        $green_room_list_key = Rooms::generateGreenHotRoomListKey();

        $hot_cache = Users::getHotWriteCache();
        $last = time() - 5 * 60;
        $manual_hot_room_num = 5;
        $total_num = 10;
        $least_num = 3;
        $least_user_num = 1;

        if (isProduction()) {
            $manual_hot_room_num = 10;
            $total_num = 30;
            $least_num = 9;
            $least_user_num = 3;
            $last = time() - 10 * 60;
        }

        $cond = [
            'conditions' => 'hot = :hot: and status = :status: and last_at >= :last_at:',
            'bind' => ['hot' => STATUS_ON, 'status' => STATUS_ON, 'last_at' => $last],
            'order' => 'last_at desc',
        ];

        //总的热门房间
        $total_room_ids = [];
        $shield_room_ids = [];

        //固定活跃房间
        $manual_hot_rooms = Rooms::find($cond);

        info(count($manual_hot_rooms));

        foreach ($manual_hot_rooms as $manual_hot_room) {

            if (!$manual_hot_room->canToHot($least_user_num)) {
                continue;
            }

            $total_room_ids[] = $manual_hot_room->id;

            //选10个手动房间
            if (count($total_room_ids) >= $manual_hot_room_num) {
                info($total_room_ids, count($total_room_ids), $manual_hot_room_num);
                break;
            }
        }

        $start = time() - 61 * 60;
        $end = time() - 60;

        $cond = [
            'conditions' => 'room_id > 0 and created_at >= :start: and created_at <= :end:',
            'bind' => ['start' => $start, 'end' => $end],
            'columns' => 'distinct room_id'];

        $gift_orders = GiftOrders::find($cond);

        $has_income_room_ids = [];

        info($total_room_ids, count($total_room_ids));

        foreach ($gift_orders as $gift_order) {

            $room = Rooms::findFirstById($gift_order->room_id);

            if (!$room) {
                info($gift_order->room_id);
                continue;
            }

            if (!$room->canToHot($least_user_num)) {
                continue;
            }

            if (in_array($room->id, $total_room_ids)) {
                continue;
            }

            if ($room->isHot()) {
                continue;
            }

            $cond = [
                'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end: and pay_type = :pay_type:',
                'bind' => ['start' => $start, 'end' => $end, 'room_id' => $room->id, 'pay_type' => GIFT_PAY_TYPE_DIAMOND],
                'column' => 'amount'
            ];

            $income = GiftOrders::sum($cond);

            $has_income_room_ids[$room->id] = $income;
        }

        info($has_income_room_ids);

        arsort($has_income_room_ids);

        info($has_income_room_ids);

        foreach ($has_income_room_ids as $has_income_room_id => $income_value) {

            $total_room_ids[] = $has_income_room_id;

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

                $hot_cache = Users::getHotWriteCache();
                $user_num_room_ids = $hot_cache->zrevrange(Rooms::getTotalRoomUserNumListKey(), 0, -1, true);
                echoLine($user_num_room_ids);
                $num = 1;

                foreach ($user_num_room_ids as $user_num_room_id => $user_room_num) {

                    if ($user_room_num < $least_user_num) {
                        break;
                    }

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

                    if (!$user_num_room->canToHot($least_user_num)) {
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

            if (in_array($room_id, $has_income_room_ids)) {
                $income = $has_income_room_ids[$room_id];
            } else {
                $cond = [
                    'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end: and pay_type = :pay_type:',
                    'bind' => ['start' => $start, 'end' => $end, 'room_id' => $room_id, 'pay_type' => GIFT_PAY_TYPE_DIAMOND],
                    'column' => 'amount'
                ];

                $income = GiftOrders::sum($cond);
            }

            info($income);

            $hot_room_ids[$room_id] = $income;
        }

        $has_amount_room_ids = [];
        $no_amount_room_ids = [];

        $top_room_ids = [];
        $green_room_ids = [];
        $novice_room_ids = [];

        info($hot_room_ids);
        uksort($hot_room_ids, function ($a, $b) use ($hot_room_ids) {

            if ($hot_room_ids[$a] == $hot_room_ids[$b]) {
                $rooma = Rooms::findFirstById($a);
                $roomb = Rooms::findFirstById($b);
                $rooma_user_num = $rooma->getUserNum();
                $roomb_user_num = $roomb->getUserNum();

                if ($rooma_user_num == $roomb_user_num) {
                    return 0;
                }

                if ($rooma_user_num > $roomb_user_num) {
                    return -1;
                }

                return 1;
            }

            if ($hot_room_ids[$a] > $hot_room_ids[$b]) {
                return -1;
            }

            return 1;
        });

        info($hot_room_ids);
        foreach ($hot_room_ids as $room_id => $income) {

            $room = Rooms::findFirstById($room_id);

            //绿色房间
            if ($room->isGreenRoom()) {
                $green_room_ids[] = $room->id;
            }

            //新手房间
            if ($room->isNoviceRoom()) {
                $novice_room_ids[] = $room->id;
            }

            if ($room->isShieldRoom()) {
                $shield_room_ids[] = $room->id;
            }

            //置顶房间
            if ($room->isTop()) {
                $top_room_ids[] = $room->id;
                continue;
            }

            if ($income > 0) {
                $has_amount_room_ids[] = $room_id;
            } else {
                $no_amount_room_ids[$room_id] = $room->getUserNum();
            }
        }

        arsort($no_amount_room_ids);

        $lock = tryLock($hot_room_list_key, 1000);

        $hot_cache->zclear($hot_room_list_key);
        $hot_cache->zclear($novice_room_list_key);
        $hot_cache->zclear($green_room_list_key);
        $hot_cache->zclear($hot_shield_room_list_key);

        $time = time();

        foreach ($top_room_ids as $top_room_id) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $top_room_id);

            if (!in_array($top_room_id, $shield_room_ids)) {
                $hot_cache->zadd($hot_shield_room_list_key, $time, $top_room_id);
            }

            $hot_cache->zadd($green_room_list_key, $time, $top_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $top_room_id);
        }

        foreach ($has_amount_room_ids as $has_amount_room_id) {
            $time -= 100;

            $hot_cache->zadd($hot_room_list_key, $time, $has_amount_room_id);

            if (!in_array($has_amount_room_id, $shield_room_ids)) {
                $hot_cache->zadd($hot_shield_room_list_key, $time, $has_amount_room_id);
            }

            $hot_cache->zadd($green_room_list_key, $time, $has_amount_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $has_amount_room_id);
        }

        foreach ($no_amount_room_ids as $no_amount_room_id => $user_num) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $no_amount_room_id);

            if (!in_array($no_amount_room_id, $shield_room_ids)) {
                $hot_cache->zadd($hot_shield_room_list_key, $time, $no_amount_room_id);
            }

            $hot_cache->zadd($green_room_list_key, $time, $no_amount_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $no_amount_room_id);
        }

        $time = time() + 2000;

        if (count($novice_room_ids) > 0) {

            foreach ($novice_room_ids as $novice_room_id) {
                $time -= 10;
                $hot_cache->zadd($novice_room_list_key, $time, $novice_room_id);
                $hot_cache->zadd($green_room_list_key, $time, $novice_room_id);
            }
        }

        $time = time() + 1000;

        if (count($green_room_ids) > 0) {

            foreach ($green_room_ids as $green_room_id) {
                $time -= 10;
                $hot_cache->zadd($green_room_list_key, $time, $green_room_id);
            }
        }

        info($hot_cache->zrevrange($hot_room_list_key, 0, -1, true));
        info($hot_cache->zrevrange($novice_room_list_key, 0, -1, true));
        info($hot_cache->zrevrange($green_room_list_key, 0, -1, true));
        info($shield_room_ids, $hot_cache->zrevrange($hot_shield_room_list_key, 0, -1, true));

        unlock($lock);
    }

    //热门房间排序
    function hotRoomRankAction()
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $hot_shield_room_list_key = Rooms::generateShieldHotRoomListKey();
        $novice_room_list_key = Rooms::generateNoviceHotRoomListKey();
        $green_room_list_key = Rooms::generateGreenHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();

        $lock = tryLock($hot_room_list_key, 1000);

        $hot_room_ids = $hot_cache->zrange($hot_room_list_key, 0, -1);
        $total_room_ids = [];
        $shield_room_ids = [];
        $start = time() - 61 * 60;
        $end = time() - 60;
        $least_user_num = 2;

        if (isDevelopmentEnv()) {
            $least_user_num = 1;
        }

        foreach ($hot_room_ids as $hot_room_id) {

            $hot_room = Rooms::findFirstById($hot_room_id);

            if (!$hot_room->canToHot($least_user_num)) {
                $hot_cache->zrem($hot_room_list_key, $hot_room_id);
                continue;
            }

            $cond = [
                'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end: and pay_type = :pay_type:',
                'bind' => ['start' => $start, 'end' => $end, 'room_id' => $hot_room_id, 'pay_type' => GIFT_PAY_TYPE_DIAMOND],
                'column' => 'amount'
            ];

            $income = GiftOrders::sum($cond);
            $total_room_ids[$hot_room_id] = $income;
        }

        info($total_room_ids);

        uksort($total_room_ids, function ($a, $b) use ($total_room_ids) {

            if ($total_room_ids[$a] == $total_room_ids[$b]) {
                $rooma = Rooms::findFirstById($a);
                $roomb = Rooms::findFirstById($b);
                $rooma_user_num = $rooma->getUserNum();
                $roomb_user_num = $roomb->getUserNum();

                if ($rooma_user_num == $roomb_user_num) {
                    return 0;
                }

                if ($rooma_user_num > $roomb_user_num) {
                    return -1;
                }

                return 1;
            }

            if ($total_room_ids[$a] > $total_room_ids[$b]) {
                return -1;
            }

            return 1;
        });

        info($total_room_ids);

        $has_amount_room_ids = [];
        $no_amount_room_ids = [];
        $top_room_ids = [];
        $green_room_ids = [];
        $novice_room_ids = [];


        foreach ($total_room_ids as $room_id => $income) {

            $room = Rooms::findFirstById($room_id);

            //绿色房间
            if ($room->isGreenRoom()) {
                $green_room_ids[] = $room->id;
            }

            //新手房间
            if ($room->isNoviceRoom()) {
                $novice_room_ids[] = $room->id;
            }

            if ($room->isTop()) {
                $top_room_ids[] = $room->id;
                continue;
            }

            if ($room->isShieldRoom()) {
                $shield_room_ids[] = $room->id;
            }

            if ($income > 0) {
                $has_amount_room_ids[] = $room_id;
            } else {
                $no_amount_room_ids[$room_id] = $room->getUserNum();
            }
        }

        arsort($no_amount_room_ids);

        $time = time();

        foreach ($top_room_ids as $top_room_id) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $top_room_id);

            if (!in_array($top_room_id, $shield_room_ids)) {
                $hot_cache->zadd($hot_shield_room_list_key, $time, $top_room_id);
            }

            $hot_cache->zadd($green_room_list_key, $time, $top_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $top_room_id);
        }

        foreach ($has_amount_room_ids as $has_amount_room_id) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $has_amount_room_id);

            if (!in_array($has_amount_room_id, $shield_room_ids)) {
                $hot_cache->zadd($hot_shield_room_list_key, $time, $has_amount_room_id);
            }

            $hot_cache->zadd($green_room_list_key, $time, $has_amount_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $has_amount_room_id);
        }

        foreach ($no_amount_room_ids as $no_amount_room_id => $income) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $no_amount_room_id);

            if (!in_array($no_amount_room_id, $shield_room_ids)) {
                $hot_cache->zadd($hot_shield_room_list_key, $time, $no_amount_room_id);
            }

            $hot_cache->zadd($green_room_list_key, $time, $no_amount_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $no_amount_room_id);

        }

        $time = time() + 2000;

        if (count($novice_room_ids) > 0) {

            foreach ($novice_room_ids as $novice_room_id) {
                $time -= 10;
                $hot_cache->zadd($novice_room_list_key, $time, $novice_room_id);
                $hot_cache->zadd($green_room_list_key, $time, $novice_room_id);
            }
        }

        $time = time() + 1000;

        if (count($green_room_ids) > 0) {

            foreach ($green_room_ids as $green_room_id) {
                $time -= 10;
                $hot_cache->zadd($green_room_list_key, $time, $green_room_id);
            }
        }

        info($hot_cache->zrevrange($hot_room_list_key, 0, -1, true));
        info($hot_cache->zrevrange($novice_room_list_key, 0, -1, true));
        info($hot_cache->zrevrange($green_room_list_key, 0, -1, true));
        info($hot_cache->zrevrange($hot_shield_room_list_key, 0, -1, true));

        unlock($lock);
    }

    function initRoomCategoryAction()
    {
        $rooms = Rooms::find(['conditions' => 'last_at >= :last_at:', 'bind' => ['last_at' => time() - 3600], 'columns' => 'id']);
        echoLine(count($rooms));

        foreach ($rooms as $room) {
            Rooms::updateRoomTypes($room->id);
        }
    }

    //新热门逻辑
    function generateHotRoomsAction()
    {
        $room_ids = Rooms::getActiveRoomIdsByTime();
        $total_num = count($room_ids);
        if ($total_num < 1) {
            echoLine(date('c'), 'error no room');
            return;
        }

        Rooms::updateHotRoomList($room_ids);
    }

    function generateNewHotRoomRankAction()
    {
        $total_new_hot_room_list_key = Rooms::getTotalRoomListKey(); //新的用户总的队列
        $hot_cache = Users::getHotWriteCache();
        $room_ids = $hot_cache->zrange($total_new_hot_room_list_key, 0, -1);
        $total_num = count($room_ids);
        if ($total_num < 1) {
            echoLine(date('c'), 'error no room');
            return;
        }

        Rooms::updateHotRoomList($room_ids);
    }


    function boomTargetAction()
    {
        $line = BoomHistories::getBoomStartLine();
        $total = BoomHistories::getBoomTotalValue();

        $rooms = Rooms::dayStatRooms();
        $cache = Rooms::getHotWriteCache();

        foreach ($rooms as $room) {
            $cur_income_cache_name = Rooms::generateBoomCurIncomeKey($room->id);
            $cur_income = $cache->get($cur_income_cache_name);

            if ($cur_income >= $line) {
                $room->pushBoomIncomeMessage($total, $cur_income);
            }
        }
    }


    function disappearBoomGiftRocketAction()
    {
        $boom_list_key = 'boom_gifts_list';

        $cache = Rooms::getHotWriteCache();
        $total_room_ids = $cache->zrange($boom_list_key, 0, -1);
        $total = count($total_room_ids);
        $per_page = 100;
        $offset = 0;
        $total_page = ceil($total / $per_page);

        for ($page = 1; $page <= $total_page; $page++) {

            $room_ids = array_slice($total_room_ids, $offset, $per_page);
            $offset += $per_page;

            $rooms = Rooms::findByIds($room_ids);

            foreach ($rooms as $room) {

                $cur_income = $room->getCurrentBoomGiftValue();
                $boom_config = \BoomConfigs::getBoomConfigByCache($room->boom_config_id);
                $total_income = \BoomConfigs::getBoomTotalValue($boom_config);

                if (!$cur_income) {
                    $cache->zrem($boom_list_key, $room->id);
                    $room->pushBoomIncomeMessage($total_income, $cur_income, STATUS_OFF);
                }
            }
        }
    }

    function kickingAction()
    {
        $room = Rooms::findFirstById(1010149);

        $users = $room->findTotalRealUsers();

        foreach ($users as $user) {

            if (!$user->current_room_seat_id && !$user->isRoomHost($room)) {

                $room_seat_user_lock_key = "room_seat_user_lock{$user->id}";

                $room->kickingRoom($user, 30);
                ////$room->pushExitRoomMessage($user, $user->current_room_seat_id);

                unlock($room_seat_user_lock_key);
            }
        }

    }

    function kicking1Action()
    {
        $users = Users::findByIp('61.158.148.7');
        echoLine(count($users));
        $room = Rooms::findFirstById(1010149);

        $users = $room->findTotalRealUsers();

        foreach ($users as $user) {

            if ($user->current_room_seat_id && !$user->isRoomHost($room)) {

                $room_seat_user_lock_key = "room_seat_user_lock{$user->id}";

                $room->kickingRoom($user, 30);
                ////$room->pushExitRoomMessage($user, $user->current_room_seat_id);

                unlock($room_seat_user_lock_key);
            }
        }

    }
}
