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
        $rooms = Rooms::findForeach();
        $hot_cache = Rooms::getHotWriteCache();

        foreach ($rooms as $room) {
            $key = 'room_user_list_' . $room->id;
            $user_ids = $hot_cache->zrange($key, 0, -1);

            $users = Users::findByIds($user_ids);

            foreach ($users as $user) {

                if ($user->current_room_id != $room->id || !$user->isNormal()) {
                    info($user->id, $room->id, $user->current_room_id);
                    $room->exitRoom($user);
                }
            }
        }
    }

    //检查用户活跃的状态
    function checkUserStatusAction()
    {
        //在房间内用户一小时未活跃
        $users = Users::findForeach(['conditions' => 'current_room_id > 0 and last_at < ' . time() - 3600]);

        foreach ($users as $user) {
            $current_room = $user->current_room;
            if ($current_room) {
                $current_room->exitRoom($user);
            }
        }
    }
}
