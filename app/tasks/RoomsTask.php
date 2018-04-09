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

            //$key = $room->getRealUserListKey();
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
                        $room->pushExitRoomMessage($user, $current_room_seat_id);
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
            $total_num = 20;
            $least_num = 8;
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

        info($hot_room_ids);

        arsort($hot_room_ids);

        $has_amount_room_ids = [];
        $no_amount_room_ids = [];

        $top_room_ids = [];
        $green_room_ids = [];
        $novice_room_ids = [];

        foreach ($hot_room_ids as $room_id => $income) {

            $room = Rooms::findFirstById($room_id);

            //置顶房间
            if ($room->isTop()) {
                $top_room_ids[] = $room->id;
                continue;
            }

            //绿色房间
            if ($room->isGreenRoom()) {
                $green_room_ids[] = $room->id;
                continue;
            }

            //新手房间
            if ($room->isNoviceRoom()) {
                $novice_room_ids[] = $room->id;
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

        $time = time();

        foreach ($top_room_ids as $top_room_id) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $top_room_id);
            $hot_cache->zadd($green_room_list_key, $time, $top_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $top_room_id);
        }

        foreach ($has_amount_room_ids as $has_amount_room_id) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $has_amount_room_id);
            $hot_cache->zadd($green_room_list_key, $time, $has_amount_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $has_amount_room_id);
        }

        foreach ($no_amount_room_ids as $no_amount_room_id => $user_num) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $no_amount_room_id);
            $hot_cache->zadd($green_room_list_key, $time, $no_amount_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $no_amount_room_id);
        }

        $time = time() + 2000;

        if (count($novice_room_ids) > 0) {
            $time -= 10;

            foreach ($novice_room_ids as $novice_room_id) {
                $hot_cache->zadd($novice_room_list_key, $time, $novice_room_id);
                $hot_cache->zadd($green_room_list_key, $time, $novice_room_id);
            }
        }

        $time = time() + 1000;

        if (count($green_room_ids) > 0) {

            $time -= 10;

            foreach ($green_room_ids as $green_room_id) {
                $hot_cache->zadd($green_room_list_key, $time, $green_room_id);
            }
        }

        info($hot_cache->zrevrange($hot_room_list_key, 0, -1, true));

        unlock($lock);
    }

    //热门房间排序
    function hotRoomRankAction()
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $novice_room_list_key = Rooms::generateNoviceHotRoomListKey();
        $green_room_list_key = Rooms::generateGreenHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();

        $lock = tryLock($hot_room_list_key, 1000);

        $hot_room_ids = $hot_cache->zrange($hot_room_list_key, 0, -1);
        $total_room_ids = [];

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

        arsort($total_room_ids);

        $has_amount_room_ids = [];
        $no_amount_room_ids = [];
        $top_room_ids = [];
        $green_room_ids = [];
        $novice_room_ids = [];


        foreach ($total_room_ids as $room_id => $income) {

            $room = Rooms::findFirstById($room_id);

            if ($room->isTop()) {
                $top_room_ids[] = $room->id;
                continue;
            }

            //绿色房间
            if ($room->isGreenRoom()) {
                $green_room_ids[] = $room->id;
                continue;
            }

            //新手房间
            if ($room->isNoviceRoom()) {
                $novice_room_ids[] = $room->id;
                continue;
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
            $hot_cache->zadd($green_room_list_key, $time, $top_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $top_room_id);
        }

        foreach ($has_amount_room_ids as $has_amount_room_id) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $has_amount_room_id);
            $hot_cache->zadd($green_room_list_key, $time, $has_amount_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $has_amount_room_id);
        }

        foreach ($no_amount_room_ids as $no_amount_room_id => $income) {
            $time -= 100;
            $hot_cache->zadd($hot_room_list_key, $time, $no_amount_room_id);
            $hot_cache->zadd($green_room_list_key, $time, $no_amount_room_id);
            $hot_cache->zadd($novice_room_list_key, $time, $no_amount_room_id);

        }

        $time = time() + 2000;

        if (count($novice_room_ids) > 0) {
            $time -= 10;

            foreach ($novice_room_ids as $novice_room_id) {
                $hot_cache->zadd($novice_room_list_key, $time, $novice_room_id);
                $hot_cache->zadd($green_room_list_key, $time, $novice_room_id);
            }
        }

        $time = time() + 1000;

        if (count($green_room_ids) > 0) {

            $time -= 10;

            foreach ($green_room_ids as $green_room_id) {
                $hot_cache->zadd($green_room_list_key, $time, $green_room_id);
            }
        }

        info($hot_cache->zrevrange($hot_room_list_key, 0, -1, true));

        unlock($lock);
    }
}
