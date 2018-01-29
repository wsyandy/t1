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

                if ($user->current_room_id != $room->id || !$user->isNormal() || $user->last_at < time() - 3600) {
                    info($user->id, $room->id, $user->current_room_id, $user->user_status, $user->last_at, time());
                    $room->exitRoom($user);
                }
            }
        }
    }
}
