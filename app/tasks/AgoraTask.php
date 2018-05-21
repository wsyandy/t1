<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/20
 * Time: 下午6:05
 */

class AgoraTask extends \Phalcon\Cli\Task
{

    //b044e3fb33ab4f129ad0cd9dfd7e9d65
    //9eeb8dc5543b4df7b1c832d841929f81

    //uid
    function userAction($params)
    {

        $params = [];

        $uid = 1200587;
        $user = Users::findFirstByUid($uid);
        echoLine($user);
        if (!$user) {
            echoLine('error ', $params);
            return;
        }

        $room = $user->current_room;
        if (!$room) {
            echoLine('error room', $params);
            return;
        }

        $product_channel = $user->product_channel;
        $channel_name = $room->channel_name;
        $key = $product_channel->getChannelKey($channel_name, $user->id);
        $app_id = $product_channel->getImAppId();

        //'Postman-Token' => '5590c096-35b2-4651-a840-7719f13dfcdf',
        $headers = array(
            'Cache-Control' => 'no-cache',
            'Authorization' => 'Basic YjA0NGUzZmIzM2FiNGYxMjlhZDBjZDlkZmQ3ZTlkNjU6OWVlYjhkYzU1NDNiNGRmN2IxYzgzMmQ4NDE5MjlmODE='
        );
        $url = "http://api.agora.io/dev/v1/channel/user/property/{$app_id}/{$user->id}/{$channel_name}";
        echoLine($url);
        $res = httpGet($url, [], $headers);

        echoLine($res);

    }

    // room_id
    function userListAction($params)
    {

        $params = [1027722];
        $room_id = $params[0];
        $room = Rooms::findFirstById($room_id);
        if (!$room) {
            echoLine('error room', $params);
            return;
        }

        $room->checkBroadcasters();
    }


}