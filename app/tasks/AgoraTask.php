<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/20
 * Time: 下午6:05
 */

class AgoraTask extends \Phalcon\Cli\Task {

    //uid
    function userAction($params){

        $params = [];

        $uid =575899;
        $user = Users::findFirstByUid($uid);
        echoLine($user);
        if(!$user){
            echoLine('error ', $params);
            return;
        }

        $room = $user->current_room;
        if(!$room){
            echoLine('error room', $params);
            return;
        }

        $product_channel = $user->product_channel;
        $channel_name = $room->channel_name;
        $key = $product_channel->getChannelKey($channel_name, $user->id);
        $app_id = $product_channel->getImAppId();

        $url = "http://api.agora.io/dev/v1/channel/user/property/{$app_id}/{$user->id}/{$channel_name}";
        $res = httpGet($url);

        echoLine($res);

    }

}