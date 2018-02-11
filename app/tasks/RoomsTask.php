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

                if ($user->current_room_id != $room->id || !$user->isNormal() || $user->last_at < time() - 3600) {
                    info($user->id, $room->id, $user->current_room_id, $user->user_status, $user->last_at, time());

                    $unbind = true;

                    //用户在新的房间 不解绑
                    if ($user->current_room_id != $room->id && $user->current_room_id > 0) {
                        $unbind = false;
                    }

                    $room->exitRoom($user, $unbind);
                    $room->pushExitRoomMessage($user);
                }
            }
        }
    }

    function initRoomTypeAction()
    {
        $rooms = Rooms::findForeach();

        foreach ($rooms as $room) {
            $room->user_type = $room->user->user_type;
            $room->save();
        }
    }

    function initRoomRealNumAction()
    {
        $rooms = Rooms::findForeach();
        $hot_cache = Rooms::getHotWriteCache();

        foreach ($rooms as $room) {
            if ($room->user_num > 0) {
                $user_ids = $hot_cache->zrange($room->getUserListKey(), 0, -1, true);
                echoLine($room->id);
                $real_user_list_key = $room->getRealUserListKey();
                foreach ($user_ids as $user_id => $time) {
                    $user = Users::findFirstById($user_id);

                    if ($user->isSilent()) {
                        echoLine("silent user", $user_id);
                        continue;
                    }

                    $hot_cache->zadd($real_user_list_key, $time, $user_id);
                }
            }
        }
    }

    function initSilentRoomsAction()
    {
        $name_file = APP_ROOT . "doc/room_topic.xls";
        $names = readExcel($name_file);

        foreach ($names as $name) {
            $title = $name[0];
            $topic = $name[1];

            $room = Rooms::findFirstByName($title);

            if ($room) {
                continue;
            }

            $cond['conditions'] = '(current_room_id = 0 or current_room_id is null) and user_type = ' . USER_TYPE_SILENT;
            $user = Users::findFirst($cond);

            $room = Rooms::createRoom($user, $title);
            $room->topic = $topic;
            $room->status = STATUS_OFF;
            $room->save();
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

    //
    function generateStableRoomAction()
    {
        $per_page = 2;
        $last_room = Rooms::findLast();
        $last_room_id = $last_room->id;
        $total_page = ceil($last_room_id / $per_page);
        $page = mt_rand(1, $total_page);
        $rooms = Rooms::getOfflineSilentRooms($page, $per_page);

        echoLine(count($rooms));
        foreach ($rooms as $room) {
            $user = $room->user;

            if ($user->isInAnyRoom()) {
                info($user->id, $user->current_room_id, $room->id);
                continue;
            }

            Rooms::enterSilentRoom($room->id, $user->id);
            info($room->id);
        }
    }

    //唤醒离线沉默房间
    function wakeUpOfflineSilentRoomsAction()
    {
        $online_silent_room_num = Rooms::getOnlineSilentRoomNum();

        if ($online_silent_room_num >= 60) {
            info("online_silent_room_num", $online_silent_room_num);
            return;
        }

        $per_page = mt_rand(1, 5);
        $last_room = Rooms::findLast();
        $last_room_id = $last_room->id;
        $total_page = ceil($last_room_id / $per_page);
        $page = mt_rand(1, $total_page);
        $rooms = Rooms::getOfflineSilentRooms($page, $per_page);

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

        info($page, $per_page, $online_silent_room_num, count($rooms));
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
        $rooms = Rooms::find(['order' => 'last_at desc', 'limit' => 60]);

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

    function fixNormalRoomsAction()
    {
        $rooms = Rooms::find([
            'conditions' => 'user_type != :user_type: and theme_type = :theme_type:',
            'bind' => ['user_type' => USER_TYPE_SILENT, 'theme_type' => ROOM_THEME_TYPE_BROADCAST]
        ]);
        foreach ($rooms as $room) {
            echoLine($room->id);
            $room->theme_type = ROOM_THEME_TYPE_NORMAL;
            $room->audio_id = 0;
            $room->save();
        }

    }
}
