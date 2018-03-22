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

                if (($user->current_room_id != $room->id || !$user->isNormal() || $user->last_at < time() - 3600) && STATUS_ON != $room->hot) {
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
        $cond = [
            'conditions' => 'hot = :hot: and status = :status: and last_at >= :last_at:',
            'bind' => ['hot' => STATUS_ON, 'status' => STATUS_ON, 'last_at' => time() - 10 * 60],
            'order' => 'last_at desc',
            'limit' => 10
        ];

        //固定活跃房间
        $manual_hot_rooms = Rooms::find($cond);

        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();

        $start = time() - 11 * 60;
        $end = time() - 60;

        $cond = [
            'conditions' => 'room_id > 0 and created_at >= :start: and created_at <= :end:',
            'bind' => ['start' => $start, 'end' => $end],
            'columns' => 'distinct room_id'];

        $gift_orders = GiftOrders::find($cond);

        //有收益的房间
        $has_income_room_ids = [];

        //总的热门房间
        $total_room_ids = [];

        //手动上热门id
        $manual_hot_room_ids = [];

        foreach ($manual_hot_rooms as $manual_hot_room) {
            $manual_hot_room_ids[$manual_hot_room->id] = 0;
        }

        foreach ($gift_orders as $gift_order) {

            if (!$gift_order->room) {
                $gift_order->room_id;
                continue;
            }

            if ($gift_order->room->isForbiddenHot()) {
                info("isForbiddenHot", $gift_order->room_id);
                continue;
            }

            $room_id = $gift_order->room_id;

            $cond = [
                'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end:',
                'bind' => ['start' => $start, 'end' => $end, 'room_id' => $room_id],
                'column' => 'amount'
            ];

            $income = GiftOrders::sum($cond);

            if (in_array($room_id, $manual_hot_room_ids)) {
                $manual_hot_room_ids[$room_id] = $income;
                continue;
            }

            if ($income > 0) {
                $has_income_room_ids[$room_id] = $income;
            }
        }

        foreach ($manual_hot_room_ids as $manual_hot_room_id => $income) {
            $total_room_ids[$manual_hot_room_id] = $income;
        }

        foreach ($has_income_room_ids as $has_income_room_id => $income) {
            $total_room_ids[$has_income_room_id] = $income;
        }

        $total_room_num = count($total_room_ids);
        $need_room_num = 0;

        if ($total_room_num < 20) {

            $need_room_num = 20 - $total_room_num;

            if ($hot_cache->zcard(Rooms::getTotalRoomUserNumListKey()) > 0) {

                $user_num_room_ids = $hot_cache->zrange(Rooms::getTotalRoomUserNumListKey(), 0, -1);

                $num = 1;

                foreach ($user_num_room_ids as $user_num_room_id) {

                    if ($num > $need_room_num) {
                        break;
                    }

                    $user_num_room = Rooms::findFirstById($user_num_room_id);

                    if ($user_num_room->isForbiddenHot()) {
                        info("isForbiddenHot", $user_num_room_id);
                        continue;
                    }

                    if (!in_array($user_num_room_id, $total_room_ids)) {

                        $cond = [
                            'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end:',
                            'bind' => ['start' => $start, 'end' => $end, 'room_id' => $user_num_room_id],
                            'column' => 'amount'
                        ];

                        $income = GiftOrders::sum($cond);

                        $total_room_ids[$user_num_room_id] = $income;

                        $num++;
                    }
                }
            }
        }

        $hot_cache->zclear($hot_room_list_key);

        arsort($total_room_ids);

        foreach ($total_room_ids as $total_room_id => $income) {
            $hot_cache->zadd($hot_room_list_key, $income, $total_room_id);
        }

        info($hot_cache->zrevrange($hot_room_list_key, 0, -1, true));

        info($manual_hot_room_ids, $has_income_room_ids, $total_room_ids, $total_room_num, $need_room_num);
    }

    //热门房间排序
    function calculateRoomIncomeAction()
    {
        $hot_room_list_key = Rooms::generateHotRoomListKey();
        $hot_cache = Users::getHotWriteCache();

        $hot_room_ids = $hot_cache->zrange($hot_room_list_key, 0, -1);
        $total_room_ids = [];

        $start = time() - 11 * 60;
        $end = time() - 60;

        foreach ($hot_room_ids as $hot_room_id) {

            $hot_room = Rooms::findFirstById($hot_room_id);

            $cond = [
                'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end:',
                'bind' => ['start' => $start, 'end' => $end, 'room_id' => $hot_room_id],
                'column' => 'amount'
            ];

            //判断麦位上没有用户
            $room_seat = RoomSeats::findFirst(['conditions' => 'room_id = :room_id: and user_id > 0',
                'bind' => ['room_id' => $hot_room_id]]);

            if (!$room_seat || $hot_room->lock || $hot_room->isBlocked() || $hot_room->isForbiddenHot()) {
                info("room_seat_is_null", $hot_room_id, "lock", $hot_room->lock, "blocked", $hot_room->isBlocked(), "isForbiddenHot", $hot_room->isForbiddenHot());
                $hot_cache->zrem($hot_room_list_key, $hot_room_id);
                continue;
            }

            $income = GiftOrders::sum($cond);
            $total_room_ids[$hot_room_id] = $income;
        }

        arsort($total_room_ids);

        foreach ($total_room_ids as $total_room_id => $income) {
            $hot_cache->zadd($hot_room_list_key, $income, $total_room_id);
        }

        info($total_room_ids);
    }
}
