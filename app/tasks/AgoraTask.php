<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/20
 * Time: 下午6:05
 */

class AgoraTask extends \Phalcon\Cli\Task
{

    //test
    //5b02b16dc32345e38e0d687f04a2629b
    //16cf000e1f004527b3a7c540c38bf856

    //b044e3fb33ab4f129ad0cd9dfd7e9d65
    //9eeb8dc5543b4df7b1c832d841929f81

    //uid
    function userAction($params)
    {

        $uid = $params[0];
        $user = Users::findFirstByUid($uid);
        echoLine($user);
        if (!$user) {
            echoLine('error ', $params);
            return;
        }

        $res = AgoraApi::userProfile($user);
        echoLine($res);
    }

    function checkHotRoomAction()
    {
        $hot_cache = Users::getHotWriteCache();
        $hot_total_room_list_key = Rooms::getTotalRoomListKey();
        $room_ids = $hot_cache->zrevrange($hot_total_room_list_key, 0, -1);
        echoLine('count', count($room_ids));

        $rooms = Rooms::findByIds($room_ids);
        foreach ($rooms as $room) {
            AgoraApi::checkBroadcasters($room);
        }

    }

    // uid
    function killAction($params)
    {

        $uid = $params[0];
        $user = Users::findFirstByUid($uid);
        if (!$user) {
            echoLine('error ', $params);
            return;
        }

        $room = $user->current_room;
        if (!$room) {
            echoLine('error room', $params);
            return;
        }

        AgoraApi::kickingRule($user, $room);

    }

}