<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/22
 * Time: 下午3:35
 */

class AgoraApi extends BaseModel
{
    static $_only_cache = true;

    static $headers = [
        'Cache-Control' => 'no-cache',
        'Authorization' => 'Basic YjA0NGUzZmIzM2FiNGYxMjlhZDBjZDlkZmQ3ZTlkNjU6OWVlYjhkYzU1NDNiNGRmN2IxYzgzMmQ4NDE5MjlmODE='];


    static function userProfile($user)
    {

        $room = $user->current_room;
        if (!$room) {
            return null;
        }

        $product_channel = $user->product_channel;
        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();

        $url = "http://api.agora.io/dev/v1/channel/user/property/{$app_id}/{$user->id}/{$channel_name}";
        $res = httpGet($url, [], self::$headers);

        info('user', $user->id, $res->raw_body);

        return json_decode($res->raw_body, true);
    }

    static function checkBroadcasters($room)
    {

        $product_channel = $room->product_channel;
        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();


        $url = "http://api.agora.io/dev/v1/channel/user/{$app_id}/{$channel_name}";

        $res = httpGet($url, [], self::$headers);
        $res_body = $res->raw_body;
        $res_body = json_decode($res_body, true);
        //info($room->id, $res_body);
        // {"success":true,"data":{"channel_exist":true,"mode":2,"broadcasters":[1124659,1126101,1128598,1179619,1273421,1312458,1485292],
        //"audience":[1368420],"audience_total":1},"request_id":"6187c03270c5f51ff5d5c619f9413067"}
        if (fetch($res_body, 'success') !== true) {
            info('Exce', $url, $res_body);
            return;
        }

        $data = fetch($res_body, 'data');
        $broadcaster_ids = fetch($data, 'broadcasters');

        $room_seats = RoomSeats::findPagination(['conditions' => 'room_id=:room_id:',
            'bind' => ['room_id' => $room->id], 'order' => 'rank asc'], 1, 8, 8);

        $user_ids = [];
        foreach ($room_seats as $room_seat) {
            if ($room_seat->user_id < 1) {
                continue;
            }

            $user_ids[] = $room_seat->user_id;
        }

        //info($room->id, 'broadcaster_ids', $broadcaster_ids, 'user_ids', $user_ids);

        $hot_cache = Rooms::getHotWriteCache();
        $user_list_key = $room->getUserListKey();

        foreach ($broadcaster_ids as $broadcaster_id) {

            if ($room->user_id == $broadcaster_id) {
                continue;
            }

            if (in_array($broadcaster_id, $user_ids)) {
                continue;
            }

            if ($hot_cache->zscore($user_list_key, $broadcaster_id)) {
                info('异常id 在房间', $room->id, 'broadcaster_id', $broadcaster_id);
            } else {
                info('异常id 不在房间', $room->id, 'broadcaster_id', $broadcaster_id);
            }

            self::checkBroadcaster($room, $broadcaster_id);
        }
    }

    static function checkBroadcaster($room, $user_id)
    {

        $user = Users::findFirstById($user_id);

        $product_channel = $room->product_channel;
        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();

        $url = "http://api.agora.io/dev/v1/channel/user/property/{$app_id}/{$user_id}/{$channel_name}";
        $res = httpGet($url, [], self::$headers);
        $res_body = $res->raw_body;
        $res_body = json_decode($res_body, true);
        if (fetch($res_body, 'success') !== true) {
            info('Exce', $url, $res_body);
            return;
        }

        $data = fetch($res_body, 'data');
        info('data', $room->id, 'user_id', $user_id, $data);
        $in_channel = fetch($data, 'in_channel', false);
        $role = fetch($data, 'role', 0);
        if ($in_channel === false) {
            info('离开频道', $room->id, $user_id);
            return;
        }

        if ($role == 2) {
            $hot_cache = Rooms::getHotWriteCache();
            $cache_key = 'room_kicking_rule_' . $user_id;
            $num = $hot_cache->incr($cache_key);
            $hot_cache->expire($cache_key, 3600);

            info('异常在房间', $room->id, $user->id, 'device', $user->device_id, "ip_city", $user->ip_city_id, "geo_city", $user->geo_city_id);

            if ($num >= 3) {
                info('异常在房间并封号', $room->id, $user->id, 'device', $user->device_id, "ip_city", $user->ip_city_id, "geo_city", $user->geo_city_id);
                self::kickingRule($user_id, $app_id, $channel_name, 60);
                $device = $user->device;
                $device->status = DEVICE_STATUS_BLOCK;
                $device->update();
            }
        }

    }

    static function kickingRule($user_id, $app_id, $channel_name, $time = 5)
    {

        $url = "https://api.agora.io/dev/v1/kicking-rule/";
        $body = [
            'appid' => $app_id,
            'cname' => $channel_name,
            'uid' => $user_id,
            'time' => $time // 分钟
        ];

        $res = httpPost($url, $body, self::$headers);

        info('踢出房间', 'user', $user_id, $res->raw_body);
    }


}