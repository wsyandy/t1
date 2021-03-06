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

    static function getHeaders()
    {
        $headers = [
            'Cache-Control' => 'no-cache',
            'Authorization' => 'Basic NWIwMmIxNmRjMzIzNDVlMzhlMGQ2ODdmMDRhMjYyOWI6MTZjZjAwMGUxZjAwNDUyN2IzYTdjNTQwYzM4YmY4NTY='
        ];

        if (isProduction()) {
            $headers = [
                'Cache-Control' => 'no-cache',
                'Authorization' => 'Basic YjA0NGUzZmIzM2FiNGYxMjlhZDBjZDlkZmQ3ZTlkNjU6OWVlYjhkYzU1NDNiNGRmN2IxYzgzMmQ4NDE5MjlmODE='];
        }

        return $headers;
    }


    static function hostIn($user)
    {

        $room = $user->current_room;
        if (!$room) {
            return null;
        }

        $product_channel = $user->product_channel;
        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();

        $url = "http://api.agora.io/dev/v1/channel/business/hostin/{$app_id}/{$user->id}/{$channel_name}";

        try {
            $res = httpGet($url, [], self::getHeaders());
            info('user', $user->id, $res->raw_body);

            return json_decode($res->raw_body, true);

        } catch (\Exception $e) {
            warn('Exce', $e->getMessage());
        }

        return null;
    }

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

        try {

            $res = httpGet($url, [], self::getHeaders());

            info('user', $user->id, $res->raw_body);

            return json_decode($res->raw_body, true);

        } catch (\Exception $e) {
            warn('Exce', $e->getMessage());
        }

        return null;
    }

    static function checkBroadcasters($room)
    {

        $product_channel = $room->product_channel;
        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();

        $url = "http://api.agora.io/dev/v1/channel/user/{$app_id}/{$channel_name}";

        try {

            $res = httpGet($url, [], self::getHeaders());
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
            if (!$broadcaster_ids) {
                info('no broadcasters', $res_body);
                return;
            }

            $room_seats = RoomSeats::findPagination(['conditions' => 'room_id=:room_id:',
                'bind' => ['room_id' => $room->id], 'order' => 'rank asc'], 1, 8, 8);

            $user_ids = [];
            foreach ($room_seats as $room_seat) {
                if ($room_seat->user_id < 1) {
                    continue;
                }

                $user_ids[] = $room_seat->user_id;
            }

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

        } catch (\Exception $e) {
            warn('Exce', $e->getMessage());
        }

        return null;
    }

    static function checkBroadcaster($room, $user_id)
    {

        $user = Users::findFirstById($user_id);

        $product_channel = $room->product_channel;
        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();

        $url = "http://api.agora.io/dev/v1/channel/user/property/{$app_id}/{$user_id}/{$channel_name}";
        try {

            $res = httpGet($url, [], self::getHeaders());
            $res_body = $res->raw_body;
            $res_body = json_decode($res_body, true);
            if (fetch($res_body, 'success') !== true) {
                info('Exce', $url, $res_body);
                return null;
            }

            $data = fetch($res_body, 'data');
            $in_channel = fetch($data, 'in_channel', false);
            $role = fetch($data, 'role', 0);
            if ($in_channel === false) {
                info('离开频道', $room->id, $user_id, $data);
                return [];
            }

            if ($role == 2) {
                $hot_cache = Rooms::getHotWriteCache();
                $cache_key = 'room_kicking_rule_' . $user_id;
                $num = $hot_cache->incr($cache_key);
                $hot_cache->expire($cache_key, 3600);

                info('异常在房间', $room->id, $user->id, 'device', $user->device_id, "ip_city", $user->ip_city_id, "geo_city", $user->geo_city_id);

                if ($num > 3) {
                    info('异常在房间并封号', $room->id, $user->id, 'device', $user->device_id, "ip_city", $user->ip_city_id, "geo_city", $user->geo_city_id);
                    self::kickingRule($user, $room, 60);
//                    $device = $user->device;
//                    $device->status = DEVICE_STATUS_BLOCK;
//                    $device->update();
                } else {
                    self::kickingRule($user, $room, 1);
                }
            }

        } catch (\Exception $e) {
            warn('Exce', $e->getMessage());
        }

        return null;
    }

    static function kickingRule($user, $room, $time = 5)
    {

        $product_channel = $room->product_channel;
        if (!$product_channel) {
            info('Exce', $user->id, $room->id, $room->product_channel_id);
            return;
        }

        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();

        $url = "https://api.agora.io/dev/v1/kicking-rule/";
        $body = [
            'appid' => $app_id,
            'cname' => $channel_name,
            'uid' => $user->id,
            'time' => $time // 分钟
        ];

        for ($i = 0; $i < 3; $i++) {
            try {
                $res = httpPost($url, $body, self::getHeaders());
                info('踢出房间', 'user', $user->id, 'room', $room->id, $res->raw_body);
                break;
            } catch (\Exception $e) {
                warn('Exce', $body, $e->getMessage());
            }
        }
    }

    static function exitChannel($user, $room)
    {

        $product_channel = $room->product_channel;
        if (!$product_channel) {
            info('Exce', $user->id, $room->id, $room->product_channel_id);
            return true;
        }

        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();

        $url = "http://api.agora.io/dev/v1/channel/user/property/{$app_id}/{$user->id}/{$channel_name}";

        try {

            $res = httpGet($url, [], self::getHeaders());
            $res_body = $res->raw_body;
            $res_body = json_decode($res_body, true);
            if (fetch($res_body, 'success') !== true) {
                info('Exce', $url, $res_body);
                return false;
            }

            $data = fetch($res_body, 'data');
            $in_channel = fetch($data, 'in_channel', false);
            if ($in_channel === false) {
                info('已离开频道', 'user', $user->id, 'room', $room->id, $data);
                return true;
            }

            self::kickingRule($user, $room, 1);

            return true;

        } catch (\Exception $e) {
            warn('Exce', $e->getMessage());
        }

        return false;
    }

    static function inChannel($user, $room)
    {

        $product_channel = $room->product_channel;
        if (!$product_channel) {
            return [false, 0];
        }

        $channel_name = $room->channel_name;
        $app_id = $product_channel->getImAppId();
        $user_id = $user->id;

        $url = "http://api.agora.io/dev/v1/channel/user/property/{$app_id}/{$user->id}/{$channel_name}";

        try {

            $res = httpGet($url, [], self::getHeaders());
            $res_body = $res->raw_body;
            $res_body = json_decode($res_body, true);
            if (fetch($res_body, 'success') !== true) {
                info('Exce', $url, $res_body);
                return [true, 0];
            }

            $data = fetch($res_body, 'data');
            $in_channel = fetch($data, 'in_channel', false);
            $role = fetch($data, 'role', 0);

            $user_role = USER_ROLE_NO;
            if ($role == 2) {
                $user_role = USER_ROLE_BROADCASTER;
            }
            if ($role == 3) {
                $user_role = USER_ROLE_AUDIENCE;
            }

            if ($in_channel === false) {
                info('已离开频道', $room->id, $user_id, $data);
                return [false, $user_role];
            }

            return [true, $user_role];
        } catch (\Exception $e) {
            warn('Exce', $e->getMessage());
        }

        return [true, 0];
    }

}