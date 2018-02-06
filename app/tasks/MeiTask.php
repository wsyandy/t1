<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午9:49
 */

class MeiTask extends \Phalcon\Cli\Task
{
    function deviceInfoAction()
    {
        $device = Devices::findFirstById(1);
        echoLine($device);
    }


    function signAction()
    {
        $params = file_get_contents(APP_ROOT . "public/temp/test.txt");
        $params = json_decode($params, true);

        print_r($params);

        foreach ($params as $key => $val) {
            if ($key == 'h' || $key == '_url' || $key == 'file') {
                continue;
            }
            $data[] = $key . '=' . $val;
        }

        sort($data);
        print_r($data);
        $sign_str = implode('&', $data);
        echoLine($sign_str);
        $ckey = fetch($params, 'ckey');
        $sign = md5(md5($sign_str) . $ckey);
        echoLine($sign);
    }

    function redisAction()
    {
        $redis = Users::getHotWriteCache();
        $redis->set("test_1", 222);
    }

    function test1Action()
    {
        if ("000000") {
            echoLine(":sss");
        }
    }

    function citiesAction()
    {
    }

    function test2Action()
    {
        $k = '浙江';
        $province = Provinces::findFirstByName($k);

        $city_name = '丽水';
        $city = Cities::findFirstByName($city_name);
        echoLine($city);
        $user = Users::findFirstById(46);
        echoLine($user->city_id);
        $user->updateProfile(['province_name' => '浙江', 'city_name' => '丽水']);

        $opts = ['user_id' => '6'];
        $user_id = fetch($opts, 'user_id');

        $cond = [];

        if ($user_id) {
            $cond = ['conditions' => 'id = :user_id:', 'bind' => ['user_id' => $user_id]];
        }

        $users = Users::findPagination($cond, 1, 10);

        if (count($users) > 0) {
            echoLine($users->toJson('users', 'toBasicJson'));
        }
    }

    function test3Action()
    {
        $user = new Users();
        $user->birthday = strtotime("1991-09-27");

        debug($user->constellationText());
    }

    function test4Action()
    {
        $user_db = Users::getUserDb();
        $key = "add_friend_introduce_user_id1";
        $user_db->hset($key, 1, "你好");
        $user_db->hset($key, 2, "哈哈");

        debug($user_db->hgetall($key), $user_db->hget($key, 1), $user_db->hget($key, 2));

        $albums = Albums::findForeach();

        foreach ($albums as $album) {
            echoLine($album->user_id);
        }
    }

    function test5Action()
    {
        $user_db = Users::getUserDb();
        $follow_key = 'follow_list_user_id' . 44;
//        $followed_key = 'followed_list_user_id' . $other_user->id;
        echoLine($user_db->zrange($follow_key, 0, -1));
    }

    function test6Action()
    {
        $user = new Users();
        $user->save();
        $users = Users::findPagination([], 1, 20);
    }

    function test7Action()
    {
        $current_user_id = 75;
        $key = 'friend_total_list_user_id_' . $current_user_id;

        $user_db = Users::getUserDb();
        $user_ids = $user_db->zrange($key, 0, -1);
        $user_introduce_key = "add_friend_introduce_user_id" . $current_user_id;


        foreach ($user_ids as $user_id) {
            $other_user_introduce_key = "add_friend_introduce_user_id" . $user_id;
            $user_db->hset($user_introduce_key, $user_id, "你好");
            $user_db->hset($other_user_introduce_key, $current_user_id, "你好");
        }
    }

    function followAction()
    {
        $user_id = 83;
        $current_user = Users::findFirstById($user_id);

        if (!$current_user) {
            return;
        }

        $users = Users::find(['conditions' => 'id != ' . $user_id, 'limit' => 100]);

        foreach ($users as $user) {
            $current_user->follow($user);
        }
    }

    function addFriendsAction($params)
    {
        $user_id = 99;
        $current_user = Users::findFirstById($user_id);

        if (!$current_user) {
            return;
        }

        $users = Users::find(['conditions' => 'id != ' . $user_id, 'limit' => 100]);

        foreach ($users as $user) {
            $current_user->addFriend($user, ['self_introduce' => '你好']);
        }

    }

    function agreeAction()
    {
        $current_user_id = 99;

        $current_user = Users::findFirstById($current_user_id);

        if (!$current_user) {
            return;
        }

        $key = 'friend_total_list_user_id_' . $current_user_id;

        $user_db = Users::getUserDb();
        $user_ids = $user_db->zrange($key, 0, -1);

        foreach ($user_ids as $user_id) {
            $user = Users::findFirstById($user_id);

            $num = mt_rand(1, 100);

            if ($num <= 10) {
                $current_user->agreeAddFriend($user);
            }
        }
    }

    function getFriendListAction()
    {
        $user_id = 88;
        $current_user = Users::findFirstById($user_id);
        $users = $current_user->friendList(1, 100, 0);
        echoLine($users->toJson('users', 'toRelationJson'));
    }

    function getFollowListAction()
    {
        $user_id = 75;
        $current_user = Users::findFirstById($user_id);
        $users = $current_user->followList(1, 100, 1);
        echoLine($users->toJson('users', 'toRelationJson'));
    }

    function getRoomUsersAction()
    {
        $room = Rooms::findFirstById(5);
        echoLine($room);
        $key = 'room_user_list_' . 5;
        $user_db = Users::getUserDb();
        $user_ids = $user_db->zrange($key, 0, -1);
        echoLine($user_ids);
    }

    function test8Action()
    {
        $user_db = Users::getUserDb();
        $key = "set_type";
        $user_db->set($key, true);
        var_dump($user_db->get("sssss"));
    }

    function test9Action()
    {
        $room = Rooms::findFirstById(7);
        echoLine($room);

        $room_seat = RoomSeats::findFirstById(57);
        echoLine($room_seat);
        $rooms = Rooms::count();
        echoLine($rooms);
    }

    function test10Action()
    {
        $hot_cache = Users::getHotWriteCache();
        $key = "test_rank";

//        for ($i = 1; $i < 20; $i++) {
//            $hot_cache->zadd($key, $i, $i);
//        }

        $page = 2;
        $per_page = 5;
        $offset = ($page - 1) * $per_page;
        echoLine($hot_cache->zrevrange($key, $offset, $offset + $per_page - 1));
    }

    function roomUsersAction()
    {
        $rooms = Rooms::findForeach();

        foreach ($rooms as $room) {
            $hot_cache = Rooms::getHotWriteCache();
            $key = 'room_user_list_' . $room->id;
            $user_ids = $hot_cache->zrange($key, 0, -1);

            if (count($user_ids) > 0) {

                $users = Users::findByIds($user_ids);

                foreach ($users as $user) {
                    if ($user->current_room_id != $room->id) {
                        $room->exitRoom($user);
                    }
                }
            }
        }
    }

    function exitRoomAction()
    {
        $room = Rooms::findFirstById(5);
        $user = Users::findFirstById(37);

        $room->exitRoom($user);

        $user = Users::findFirstById(37);
        echoLine($user->user_role, $user->room_id);
    }

    function test11Action()
    {
        $users = Users::findForeach();

        foreach ($users as $user) {
            echoLine($user->geo_hash, $user->platform, $user->id, $user->latitude / 10000, $user->longitude / 10000);

            if ($user->latitude && $user->longitude) {
                $geo_hash = new \geo\GeoHash();
                $hash = $geo_hash->encode($user->latitude / 10000, $user->latitude / 10000);
                if ($hash) {
                    $user->geo_hash = $hash;
                }

                $user->update();
            }
        }
    }

    function test12Action()
    {
        $room_seats = RoomSeats::findForeach();

        foreach ($room_seats as $room_seat) {
            if ($room_seat->user) {
                if ($room_seat->room_id != $room_seat->user->current_room_id) {
                    $room_seat->down($room_seat->user);
                }
            }
        }
    }

    function test13Action()
    {
        $user = Users::findFirstById(73);
        echoLine($user->current_room_id, $user->current_room_seat_id, $user->room_id);

        $room_seat = RoomSeats::findFirstById(55);
        $room_seat->down($user);

        $room_user = Rooms::findFirstById(12);
        echoLine($room_user->user_id);
    }

    function test14Action()
    {
        $hot_cache = Users::getHotWriteCache();

        $key = "test_incrby1";

        $hot_cache->zincrby($key, -10, 3);

        echoLine($hot_cache->zscore($key, 3));
    }

    function test15Action()
    {
        $rooms = Rooms::findForeach();
        $hot_cache = Rooms::getHotWriteCache();
        $key = 'room_user_list_12';


        foreach ($rooms as $room) {

            if ($room->user->current_room_id == $room->id) {
                $key = 'room_user_list_' . $room->id;
                $hot_cache->zincrby($key, 86400 * 6, $room->user->id);
            }

        }
    }

    function test16Action()
    {
        $friend_list_key = 'friend_list_user_id_' . 88;
        $other_friend_list_key = 'friend_list_user_id_' . 111;
        $add_key = 'add_friend_list_user_id_' . 111;
        $added_key = 'added_friend_list_user_id_' . 88;

        $user_db = Users::getUserDb();

        echoLine($user_db->zscore($add_key, 88), $user_db->zrange($add_key, 0, -1));
        echoLine($user_db->zscore($added_key, 111), $user_db->zrange($added_key, 0, -1));
        echoLine($user_db->zrange($friend_list_key, 0, -1));
        echoLine($user_db->zrange($other_friend_list_key, 0, -1));
        if ($user_db->zscore($add_key, 111)) {
//            $user_db->zrem($add_key, 111);
//            $user_db->zadd($friend_list_key, time(), 111);
        }

        if ($user_db->zscore($added_key, 88)) {
//            $user_db->zrem($added_key, 88);
//            $user_db->zadd($other_friend_list_key, time(), 88);
        }
    }

    function test17Action()
    {
        $hot_cache = Users::getHotWriteCache();
        $key = "test_room_seat_down";

        if (!$hot_cache->set($key, 1, ['NX', 'PX' => 1000])) {
            echoLine("操作频繁");
        }

        $user = Users::findFirstById(137);
        echoLine($user);
    }

    function test18Action()
    {
        $user = Users::findFirstById(194);
        echoLine($user);

        $province = Provinces::findFirstByName("天津");
        echoLine($province);

        $cities = Cities::findFirstByProvinceId(3);

        foreach ($cities as $city) {
            echoLine($city);
        }
    }

    function test19Action()
    {
        $users = Users::findForeach();

        foreach ($users as $user) {

            echoLine($user->last_at_text, $user->id);
            $room_seats = RoomSeats::findBy(['user_id' => $user->id]);

            if (count($room_seats) > 1) {
                echoLine($user->last_at);
            }
        }

        $user = Users::findFirstById(90);
        echoLine($user);
        $hot_cache = Users::getHotWriteCache();
        $key = 'room_user_list_19';
        $user_ids = $hot_cache->zrange($key, 0, -1, 'withscores');

        echoLine($user_ids);
    }

    function test20Action()
    {
        $room_seats = RoomSeats::find(['conditions' => 'user_id > 0']);

        echoLine(count($room_seats));

        foreach ($room_seats as $room_seat) {
            $user = $room_seat->user;

            //一个小时不活跃踢出房间
            if ($user->last_at < time() - 3600) {
                echoLine($user->id, $room_seat->room->id);
                $room_seat->down($user);
                $room_seat->room->exitRoom($user);
            }
        }
    }

    function test21Action()
    {
        $rooms = Rooms::findForeach();
        $hot_cache = Users::getHotWriteCache();

        foreach ($rooms as $room) {
            $key = 'room_user_list_' . $room->id;
            $user_ids = $hot_cache->zrange($key, 0, -1);

            $users = Users::findByIds($user_ids);

            foreach ($users as $user) {

                if ($user->current_room_id != $room->id) {
                    echoLine($user->id, $room->id, $user->current_room_id);
                    $room->exitRoom($user);
                }
            }
        }
    }

    function userGiftsAction()
    {
        $user_gifts = UserGifts::findBy(['user_id' => 192]);

        foreach ($user_gifts as $user_gift) {
            echoLine($user_gift);
        }

        $gift_orders = GiftOrders::findBy(['user_id' => 192]);

        foreach ($gift_orders as $gift_order) {
            echoLine($gift_order);
        }
    }

    function paymentChannelAction()
    {
        $user = Users::findFirstById(196);
        $payment_channels = PaymentChannels::selectByUser($user);

        foreach ($payment_channels as $payment_channel) {
            echoLine("====", $payment_channel);
        }
    }

    function test22Action()
    {
        $user = Users::findFirstById(1);
        $user->mobile = '13800000000';
        $user->nickname = '系统小助手';
        $user->device_id = '';
        $user->device_no = '';
        $user->sid = '';
        $user->save();
    }

    function test23Action()
    {
        $swoole_server = PushSever::getServer();
        $swoole_server->send(1, "sss");


        $city = Cities::findFirstByName('盐城市');
        echoLine($city);


        $user = Users::findFirstById(258);
        $user->delete();
        echoLine($user->province_id, $user->city_id);

        $user->updateProfile(['province_name' => '河北', 'city_name' => '石家庄', 'product_channel_id' => 1]);
        $user->province_id = 1;
        $user->city_id = 1;
        echoLine($user->province_id, $user->city_id, $user->province->name, $user->city->name);
    }

    function test24Action()
    {

        $user = Users::findFirstById(5);
//        $user->delete();
        echoLine($user->province_id, $user->city_id, $user->province->name, $user->city->name);
        $user->updateProfile(['province_name' => '河北', 'city_name' => '石家庄']);
        echoLine($user->province_id, $user->city_id, $user->province_name, $user->city_name);

        $user = Users::findFirstById(52);
        echoLine($user->online_token);

        $key = 'room_user_list_8';
        $hot_cache = Rooms::getHotWriteCache();
        echoLine($hot_cache->zrange($key, 0, -1));

        $user = Users::findFirstById(117);
        echoLine($user->online_token);
    }

    function test25Action()
    {
        $receiver_id = 1 == 2 ? 1 : 2;
        debug($receiver_id);

        $voice_call_id = VoiceCalls::getVoiceCallIdByUserId(52);

        $key = "voice_calling_52";
        $hot_cache = Users::getHotReadCache();
        $hot_cache->del($key);

        $voice_call_id = VoiceCalls::getVoiceCallIdByUserId(52);
        echoLine($voice_call_id);

        $user = Users::findFirstById(117);

        if ($user->isCalling()) {
            echoLine("sssss");
        }

        $voice_call = VoiceCalls::getVoiceCallIdByUserId(117);
        echoLine($voice_call);
    }

    function test26Action()
    {
        $str = ["online_token" => "1414f68b05f7e14fee2f8216d2a492b242851", "action" => "ping", "timestamp" => "1516612042", "sign" => "sss"];
        print_r($str);

        unset($str['sign']);
        print_r($str);

//        $str = json_encode($str);
//        echoLine($str);
//
//        echoLine(md5($str));

    }

    function test27Action()
    {
        $params = ['action' => 'ping', 'online_token' => '17f2a022bb11ce07f77c52cded943be4c54',
            'sid' => '52s14a3974e9cadc7854b13dcb8cd653720a6', 'timestamp' => '1516633511'];

        ksort($params);

        print_r($params);
        $temp = [];

        foreach ($params as $k => $v) {
            $temp[] = $k . "=" . $v;
        }

        $str = implode("&", $temp);

        echoLine($str);
        echoLine(md5($str));
    }

    function test28Action()
    {
        echoLine(swoole_get_local_ip());
    }

    function test29Action()
    {
        $client = new \WebSocket\Client('ws://192.168.111.118:9508?user_id=1');

        $payload = array('room_id' => 1012,
            'token' => mt_rand(1, 100),
            'action' => 'ws/rooms/enter',
        );

        $data = json_encode($payload);

        $client->send($data);
        $client->close();
    }

    function test30Action()
    {
        $redis = new swoole_redis();

        $soft_versions = SoftVersions::findForeach();
        foreach ($soft_versions as $soft_version) {
            echoLine($soft_version->version_name);
        }
    }

    function test40Action()
    {
        $rooms = Rooms::findForeach();

        foreach ($rooms as $room) {
            if ($room->user_num > 0 && STATUS_OFF == $room->status) {
                $room->status = STATUS_ON;
                $room->save();
                echoLine($room);
            }
        }
    }

    function test41Action()
    {
        $user = Users::findFirstById(117);
        echoLine($user->current_room_id);
    }

    function test42Action()
    {
        $ip = PushSever::getIntranetIp();
        $client = new \WebSocket\Client("ws://{$ip}:9508");
        $payload = ['action' => 'push', 'message' => ['fd' => 1]];
        $data = json_encode($payload);
        $client->send($data);
    }

    function test43Action()
    {
        $connection_list = "websocket_connection_list";
        $hot_cache = PushSever::getHotReadCache();
        $local_ip = PushSever::getIntranetIp();
        $server = new PushSever();
        echoLine($server->getConnectionNum());
        $hot_cache = PushSever::getHotReadCache();
        $local_ip = PushSever::getIntranetIp();
        echoLine($local_ip, $connection_list);
        $hot_cache->zadd($connection_list, 1, $local_ip);

        $image = APP_ROOT . "public/images/avatar.png";
        StoreFile::upload($image, APP_NAME . '/users/avatar/default_avatar.png');
    }

    function test44Action()
    {
        $users = Users::findForeach();

        foreach ($users as $user) {
            if ($user->avatar) {
                $user->avatar_status = AUTH_SUCCESS;
                $user->update();
            }
        }
        $order = Orders::findFirstByOrderNo('5d6b1d8');
        echoLine($order);
        $payment = \Payments::findFirstByOrderId($order->id);
        echoLine($payment);

    }

    function test45Action()
    {
        $payments = Payments::findForeach();

        foreach ($payments as $payment) {
            echoLine($payment->paid_amount, $payment->user->platform, $payment->user->id);
        }
    }

    function test46Action()
    {
        $order = Orders::findFirstById(456);
        echoLine($order);

        $user = Users::findFirstById(117);
        echoLine($user->partner_id);
    }

    function test47Action()
    {
        $orders = Orders::findForeach();

        foreach ($orders as $order) {
            echoLine($order->product_channel_id);
        }
    }

    function test48Action()
    {
        $rooms = Rooms::findForeach();
        foreach ($rooms as $room) {
            if ($room->user_num > 0 && $room->status == STATUS_OFF) {
                echoLine($room);
            }
        }
    }

    function test49Action()
    {
        $user = Users::findFirstById(64);
        echoLine($user);

        $device = Devices::findFirstById(11);
        echoLine($device);

        $devices = Devices::findForeach();

        foreach ($devices as $device) {
            if ($device->imei) {
                echoLine($device->imei);
            }
        }

        $user = Users::findFirstById(19);
        echoLine($user);
    }

    function test50Action()
    {
        $ip = "192.168.64.96";
        $connection_list = "websocket_connection_list";
        $hot_cache = PushSever::getHotReadCache();

        $hot_cache->zincrby($connection_list, 1, $ip);
        echoLine($hot_cache->zscore($connection_list, $ip));

    }

    function test51Action()
    {
        $db = Users::getUserDb();
        $key = "test_zadd_incr";
        $db->zadd($key, 1000, 1);

        $num = $db->zcount($key, 1000, '+inf');
        echoLine($num);
    }

    function test52Action()
    {
        $key = "room_id1_user_id1";
        preg_match('/room_id(\d)_user_id(\d)/', $key, $matches);
        print_r($matches);
    }

    function test53Action()
    {
        $db = Users::getUserDb();
        $key = "test_zadd_incr";
        echoLine($db->zrangebyscore($key, '-inf', 100000));
    }

    function test54Action()
    {
        while (true) {
            $user = new Users();
            $user->user_type = USER_TYPE_SILENT;
            $user->user_status = USER_STATUS_OFF;
            $user->save();

            echoLine($user->id);
            if ($user->id >= 10000) {
                break;
            }
        }

        $users = Users::findForeach(['conditions' => 'user_type = ' . USER_TYPE_SILENT]);

        foreach ($users as $user) {
            echoLine($user->id);
            $user->user_status = USER_STATUS_OFF;
            $user->update();
        }

        $key = 'room_manager_list_id5';
        $db = Users::getUserDb();
        echoLine($db->zrange($key, 0, -1, true));
    }

    function test55Action()
    {
        $orders = GiftOrders::findBy(['user_id' => 10028]);
        echoLine(count($orders));

        foreach ($orders as $order) {
            $order->user_id = 66;
            $order->save();
        }

        $orders = UserGifts::findBy(['user_id' => 10028]);
        echoLine(count($orders));

        foreach ($orders as $order) {
            $order->user_id = 66;
            $order->save();
        }

        $user = Users::findFirstById(10028);
        $user->mobile = '1';
        $user->save();
    }

    function test56Action()
    {
        $key = 'room_manager_hset';
        $db = Users::getUserDb();
        $db->hset($key, 1, "你好");
        $db->hset($key, 2, "你好");
        $db->hset($key, 3, "你好");
        $db->hclear($key);
        debug($db->hgetall($key));

        $user = Users::findById(137);
        $user->user_status = USER_STATUS_OFF;
        $user->save();
    }


    function test57Action()
    {
        httpPost("127.0.0.1", ['a' => 1]);
        $push_server = new PushSever();
        $push_server->send("push", '192.168.0.104', ['fd' => 12]);

        $user = Users::findById(6);
        echoLine($user);
    }

    function test58Action()
    {
        $rooms = Rooms::findForeach();

        foreach ($rooms as $room) {
            $user = $room->user;
            if (!$user->room_id) {
                $user->room_id = $room->id;
                echoLine($user->id);
                $user->save();
            }
        }
    }

    function test59Action()
    {
        $receiver_user = Users::findById(52);
        $hot_cache = Users::getHotReadCache();
        $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $receiver_user->online_token;
        $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
        $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_user->id));
        $room = $receiver_user->current_room;

        $user = Users::findFirstById(6);
        $body = ['action' => 'enter_room', 'user_id' => $user->id, 'nickname' => $user->nickname, 'sex' => $user->sex,
            'avatar_url' => $user->avatar_url, 'avatar_small_url' => $user->avatar_small_url, 'channel_name' => $room->channel_name
        ];

        $payload = ['body' => $body, 'fd' => $receiver_fd];

        echoLine($intranet_ip, $receiver_fd, $payload);

        PushSever::send('push', $intranet_ip, 9508, $payload);
    }


    function test60Action()
    {
        $user = Users::findById(256);
        $hot_cache = Users::getHotReadCache();
        $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $user->online_token;
        $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
        $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . 52));
        $room = $user->current_room;
        $gift = Gifts::findFirstById(5);
        $data = $gift->toSimpleJson();
        $data['num'] = 10;
        $body = ['action' => 'send_gift', 'sender_room_seat_id' => 0, 'receiver_room_seat_id' => $user->current_room_seat_id,
            'sender_nickname' => "", 'receiver_nickname' => $user->nickname, 'notify_type' => 'bc',
            'sender_id' => 6, 'receiver_id' => 52, 'channel_name' => $room->channel_name,
            'gift' => $data
        ];

        $payload = ['body' => $body, 'fd' => $receiver_fd];

        echoLine($intranet_ip, $receiver_fd, $payload);

        $server = PushSever::send('push', $intranet_ip, 9508, $payload);
    }

    //上麦
    function test61Action()
    {
        $user = Users::findById(52);
        $hot_cache = Users::getHotReadCache();
        $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $user->online_token;
        $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
        $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . 52));
        $current_room = $user->current_room;
        $current_room_seat = $user->current_room_seat;
        $body = ['action' => 'up', 'channel_name' => $current_room->channel_name, 'room_seat' => $current_room_seat->toSimpleJson()];
        $payload = ['body' => $body, 'fd' => $receiver_fd];
        echoLine($intranet_ip, $receiver_fd, $payload);
        PushSever::send('push', $intranet_ip, 9508, $payload);
    }

    //下麦
    function test62Action()
    {
        $user = Users::findById(52);
        $hot_cache = Users::getHotReadCache();
        $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $user->online_token;
        $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
        $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . 52));
        $current_room = $user->current_room;
        $current_room_seat = $user->current_room_seat;
        $body = ['action' => 'down', 'channel_name' => $current_room->channel_name, 'room_seat' => $current_room_seat->toSimpleJson()];
        $payload = ['body' => $body, 'fd' => $receiver_fd];
        echoLine($intranet_ip, $receiver_fd, $payload);
        PushSever::send('push', $intranet_ip, 9508, $payload);
    }

    function test63Action()
    {
        $rooms = Rooms::findForeach();

        foreach ($rooms as $room) {
            if ($room->user_num < 1) {
                $room->status = STATUS_OFF;
                $room->save();
            }
        }
    }

    function test64Action()
    {
        $content = readExcel(APP_ROOT . "public/temp/room_topic.xls");
        print_r($content);
    }

    function test65Action()
    {
        $rooms = Rooms::findForeach();

        foreach ($rooms as $room) {
            $room->type = $room->user->user_type;
            $room->save();
        }
    }

    function test66Action()
    {
        $count = 0;

        for ($i = 1; $i <= 100; $i++) {
            $rand_num = mt_rand(1, 100);

            if ($rand_num < 50) {
                $count++;
            }
        }

        debug($count);
        $users = Users::count();
        echoLine($users);
    }

    function test67Action()
    {
        $db = Users::getHotWriteCache();
        $key = "test_zadd_incr";
        $db->zadd($key, 133, 1);
        echoLine($db->zrangebyscore($key, '-inf', 100000));
    }

    function test68Action()
    {
        $user = Users::findFirstById(10138);
        $room = $user->room;
        $room->enterRoom($user);

        $per_page = mt_rand(1, 5);
        $page = 3;
        $rooms = Rooms::getOfflineSilentRooms($page, $per_page);

        $cond['conditions'] = 'user_type = :user_type: and (online_status = :online_status: or online_status is null)';
        $cond['bind'] = ['user_type' => USER_TYPE_SILENT, 'online_status' => STATUS_OFF];
        $cond['order'] = 'id asc';
        $rooms = Rooms::findPagination($cond, $page, $per_page);

        foreach ($rooms as $room) {
            echoLine($room);
        }

        $rooms = Rooms::findBy(['user_type' => USER_TYPE_SILENT]);

        foreach ($rooms as $room) {
            if ($room->user_num < 1) {
                $room->status = STATUS_OFF;
                $room->save();
            }
        }
    }

    function test69Action()
    {
        $rooms = Rooms::findBy(['online_status' => STATUS_ON]);

        foreach ($rooms as $room) {
            //echoLine(date("Ymd H:i:s", $room->getExpireTime()));
            if ($room->user->current_room_id != $room->id) {
                echoLine($room->user->current_room_id, $room->id);
                $room->online_status = STATUS_OFF;
                $room->save();
            }
        }

        $room = Rooms::findFirstById(182);
        echoLine($room);
        echoLine($room->user->current_room_id);

        $rooms = Rooms::findBy(['user_id' => 11158]);

        foreach ($rooms as $room) {
            echoLine($room);
        }
    }

    function test70Action()
    {
        $contents = file_get_contents(APP_ROOT . "doc/top_messages.txt");
        $contents = explode(PHP_EOL, $contents);

        $array = "[";
        foreach ($contents as $content) {
            $array .= "'" . $content . "',";
        }

        $array .= "]";

        debug($array);
    }

    function test71Action()
    {
        $messages = Rooms::$TOP_TOPIC_MESSAGES;
        $content = $messages[array_rand($messages)];
        echoLine($content);

        $user = Users::findFirstById(6569);
        echoLine($user);

        $room = Rooms::findFirstById(137);
        $key = $room->getUserListKey();
        $hot_cache = Rooms::getHotReadCache();
        $user_ids = $hot_cache->zrange($key, 0, -1);

        $users = Users::findByIds($user_ids);

        foreach ($users as $user) {
            if ($user->diamond > 0) {
                echoLine($user->diamond, $user->id);
            }
        }

        $users = Users::findForeach(['conditions' => 'user_type = ' . USER_TYPE_SILENT]);

        foreach ($users as $user) {
            if ($user->diamond < 1) {
                $amount = mt_rand(5000, 10000);
                $opts = ['remark' => '系统赠送' . $amount . '钻石', 'mobile' => $user->mobile, 'operator_id' => 1];
                if ($amount > 0) {
                    \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_GIVE, $amount, $opts);
                }
            }
        }
    }

    function test72Action()
    {
        $room = Rooms::findFirstById(8);
        $key = $room->getUserListKey();
        $cache = Rooms::getHotWriteCache();
        echoLine(date("Ymd H:i:s", $cache->zscore($key, 13685)), $cache->zscore($key, 13685));

        echoLine(Users::findFirstById(13685));
    }

    function test73Action()
    {
        $rooms = Rooms::findForeach();
        $hot_cache = Rooms::getHotWriteCache();

        foreach ($rooms as $room) {
            $users = $room->findTotalUsers();
            $key = $room->getUserListKey();
            foreach ($users as $user) {
                if ($user->current_room_id != $room->id) {
                    //$hot_cache->zrem($key, $user->id);
                    echoLine($user->id, $room->id, $user->current_room_id);
                }
            }
        }
    }

    function test74Action()
    {
        $num = Rooms::getOnlineSilentRoomNum();
        echoLine($num);
    }

    function test75Action()
    {
        $rooms = ['房间名字' => '聊天话题', '同城交友' => '迅速认识同城的那个TA', '同龄交友' => '没有代沟，畅聊不停',
            '90后的我们' => '再不疯狂我们就老了', '00后的我们' => '我的世界我做主', '脱单联盟' => '脱单还是这里最靠谱',
            '命中注定爱上你' => '只看缘分，诚心交友', '游戏开黑' => '开黑~热血竞技尽享快感', '旅行青蛙' => '快来看看你的蛙儿子',
            '王者神队友' => '王者段位带你飞', '王者荣耀' => '农药的小伙伴们开黑吧', '荒野行动' => '今晚吃鸡，大吉大利',
            '谈婚论嫁' => '真的想好和TA过一辈子了吗', '故事与酒' => '有酒有故事', '情感天地' => '宣泄情绪，分享快乐和经验',
            '午夜话聊' => '想聊什么就可以', '娱乐八卦' => '最新最火爆的娱乐圈劲料', '时尚购物' => '潮流资讯分享', '型男搭配' => '做一位品质型男',
            '时尚腕表' => '热爱钟表文化', '穿衣打扮' => '能变美的穿搭小心机', '懒女人化妆' => '没有丑女人，只有懒女人', '口红爱好者' => 'N个试色，总有一款适合你',
            '美甲达人' => '都爱美甲 秀出个性', '童颜护肤' => '分享童颜美容护肤秘籍', '运动健身' => '分享健身成果，一起进步',
            '热辣火锅' => '一天不唰就难受', '长寿养生' => '吃出健康身体', '烘焙达人' => '各种烘焙教程、操作小细节',
            '美食美刻' => '共享生活中美好时食光', '游山玩水' => '旅行达人分享吃喝玩乐', '猫奴大大们' => '心甘情愿沦为猫奴',
            '铲屎官们' => '天天被狗溜的我们', '萌宠达人' => '一起交流分享养宠心得吧', '亲子育儿' => '专业的亲子育儿百科知识',
            '花花草草' => '养花弄草，养心怡情', '星星恋物语' => '最全星座、爱情、占卜分享', '搞笑联盟' => '博君一笑是我们的使命',
            '股市杂谈' => '分享炒股经验', '投资理财' => '理财牛人为你指点迷津', '职场天地' => '聊聊工作中遇到的那些事儿',
            '房产观澜' => '没房买房卖房的一起聊聊', '车友之家' => '全方位让你更懂车', '睡前故事' => '每天和你说晚安',
            '灵异事件' => '恐怖的让你瑟瑟发抖', '听歌电台' => '分享好听的歌', 'K歌大赏' => '好嗓子都来吼一吼',
            '跑调大王' => '我们都有勇气听', '粤语唱歌赛' => '只唱粤语歌', '小众民谣' => '有故事的民谣更有韵味',
            '煮酒论史' => '对历史感兴趣的进', '街舞联盟' => '跳起来一起high', 'i拍身边美' => '摄影达人分享技巧',
            '处对象不限' => '自信跳8', '分手挽留  表白' => '歌手跳8', '唱歌喊麦唠嗑' => '跳8 开始', '唠嗑 唱歌 ' => '安静听歌',
            '唱歌跑调大赛' => '唱歌最大', '处对象不限' => '带照 跳8', '唱歌唠嗑灵魂歌手' => '欢迎唱歌跳坑', '唱歌 聊天 ' => '唱歌跳 8',
            '唱歌 喜美女' => '下跳', '音乐 听歌 告白' => '唱歌跳8 告白跳7', '处对象不限' => '带照下跳', '御连睡不d' => '永恒音符sp',
            '安静的听歌' => '欢迎歌手回家', '分手挽留 +表白唱歌' => '禁要饭', '唱歌跑调大赛' => '可跑可不跑',
            '听歌连睡 告白' => '唱歌跳8麦', '荒野行动心态进' => '居居', '荒野行动带躺' => '看房主资料', '王者铂金五排' => 'm',
            '王者cp+' => '百钻买坑', 'QQ飞车处cp队友' => '招冠厅/ 主持', '飞车尬聊唠嗑听歌' => '安静听', '王者球球飞车处cp队友' => '百钻买坑',
            'QQ飞车处cp队友' => '骄傲一身', '球球+心态+' => '迷雾', '逃杀心态迷雾进' => '迷雾缺', '连睡不D.不限' => '', '听歌连睡 告白' => '',
            '连睡+' => '详情看头像', '连睡 单纯+' => '一直陪着你', '连睡喜东北' => '安慰 走心 ', '荒野二排限小学生' => '', '处王者cp' => '500钻冠厅',
            '王者cp不限' => '买坑50钻', 'QQ飞车王者cp不限' => '才艺买坑', 'QQ飞车处cp队友' => '招主持', '飞车匹配' => '飞车', '飞车vx排位' => '排位',
            '迷雾4级+心态' => '收跟班', '迷雾稳团进' => '稳团', '大龄叔御处连睡不d' => '素质享受', '温青叔 连睡' => '单纯', '荒野行动 吃鸡带躺' => '保护我方',
            '临时战队+心态' => '喜高手', '分手挽留表白唱歌大赛' => '百钻劈坑  禁要饭  墨迹踢', '读文连睡大龄叔御王者' => '钻p 刷1 买坑刷礼物 读文p',
            '飞车球球' => '刷礼物上坑', 'les处长麦要p' => '控感觉 拒小', '音乐 听歌睡觉觉' => '感情 我们都是认真的',
            '全买u处u连+不d' => '双费禁幸运 禁要饭 冠厅捕5折', '球球王者飞车处cp队友' => '招冠厅/主持+废人', '全麦u处U连+处关系' => '欢迎男神女神',
            '迷雾大逃杀心态+四级' => '欢迎帅哥美女', '飞车铂金道具排' => '交友Q群703657730带你hi', '荒野行动双排' => '高手',
            '迷雾心态拒废+' => '拒废+', '全麦U处U连+不D' => '禁要饭 钻p坑 墨迹t', '王者球球 cp对象不限' => '主持最大 钻p三费相同',
            '全麦u处u连+不d' => '钻p 禁要饭', '唱歌跑调大赛' => '自由麦收收谢谢', '分手挽留 表白唱歌' => '求关注 求礼物',
            '唱歌PK' => '欢迎麦手', '你喜欢民谣我喜欢你' => '谁是谁的谁', '唱歌、唠嗑' => '听歌睡觉觉', '荒野行动等人+' => '交友Q群703657730',
            '荒野行动4排' => '你的宝贝', '情感咨询+连麦' => '钻p 才艺最大', '分手挽留+表白唱歌' => '招主持 才艺优质+',
            '全麦U处U连+不D' => '禁要饭 墨迹t', '方言骂人大赛' => '喜欢新礼物', '听歌连睡 ' => '歌手跳', 'CF吃鸡' => '高手+',
            '原来是民谣啊' => '等待一个人', '处对象限女大龄' => '人在后台', 'les处关系p进' => '三麦上拒男', '处连睡  喜静限女' => '自己上麦',
            '连睡 限女 不D' => '进来叫我', 'les分手挽留' => '欢迎各位大大', '连睡+' => '欢迎老婆', '处连睡 限女' => '超喜欢你',
            '处对象限女大龄' => '优质跳8', '处对象限女' => '主持最大 钻p三费相同', '荒野行动限女' => '游戏中', '王者带cp限女' => '周冠200',
            '荒野行动自建 限女' => '缺2', '处对象限男' => '主持最大', '处对象限男' => '要坑200钻', '处连睡 限男' => '喜新礼物',
            '荒野行动带飞 限男' => '给力点', '荒野+穿越+王者 限男' => '拒废+', '王者铂金排位  限男' => '单排日志',
            '处对象限男 喜东北' => '带照上麦', '真心话大冒险' => '男女神', '猜拳真心话' => '你敢做我就敢说', '优质麦序' => '招主持',
            '爱在心男神女神' => '欢迎小可爱回家', '男神心中人' => '喜新礼物', 'MIL优质男神女神' => '欢迎回家', '唯爱女神' => '欢迎老公回家',
            '百钻男神女神' => '欢迎宝宝们回家', '摩登男神' => '女神新礼物会变美哦', '花花女神' => '欢迎小哥哥回家', 'SM优质男神' => '小姐姐到家喽', '
            江流女神' => '欧美头,钻p', '女神等你来' => '全场爆灯', '优质男女神聊天' => '关注房主/', '全民男神' => '我们的故事从爆音开始',
            '全民女神' => '我们的故事从十钻开始', '维C女神' => '最强维C厅', '迪奥女神' => '一起去做头发', '全麦聊天+处对象' => '188双费减半',
            '全麦处连+不D' => '双费禁幸运', '处关系' => '主持最大,才艺P坑', '处对象' => '搞对象排面足', '征婚交友' => '交友Q群703657730带你玩遍Hi',
            '连麦陪睡' => '看资料+', '处对象不限' => '10钻上麦处对象', '聊天' => '互粉聊天唠嗑', '老牛满天飞' => '100钻霸麦,老牛满天飞',
            '睡觉' => '安安静静睡觉你信吗', '连睡' => '我只听听不乱动', '连睡限东北' => '东北那旮旯子人呢', '处对象限女' => '上麦爆口活',
            '处对象限男' => '十钻上麦', '陪着我就好' => '禁幸运/招主持', '碎觉' => '安静', 'ls收闺女' => '看资料+',
            '吃鸡嘿嘿嘿' => '竖琴带走', '连睡  青受' => '永恒符号sp', '睡觉勿扰' => '洒脱高傲,随性也很浪荡', 'les' => '欢迎宝宝的到来',
            '荒野特训' => '欢迎加入吃鸡队伍', '荒野行动限女' => '好友一起玩', '荒野一区' => '加好友一起玩', '荒野2等2' => '钢枪',
            '荒野刚抢王' => '主城钢枪王', '荒野5排' => '猥琐打野流', '荒野5排钢枪' => '南郊钢枪', '荒野连麦等你' => '上麦喊我',
            '风里雨里荒野等你' => '三级头三级包', '终结一区' => '加好友一起玩', '终结二区' => '加好友一起玩', '终结三区' => '加好友一起玩',
            '终结一区4排' => '落地98K', '终结二区4排' => '落地三级头', '终结三区4排' => '落地三级甲+三级头', '终结一区钢枪' => '天降空调砸脸',
            '终结二区钢枪' => '见人就钢枪', '终结三区钢枪' => '无敌钢枪王', '终结一区5排' => '见人就是怼', '分手挽留' => '靠谱主持有责任心',
            '聊聊撩撩' => '关注我', '闲人唠嗑' => '感情,我们是认真的', '声优的陪伴' => '喜欢房主的点个关注', '摇出我撩天大旗' => '上麦挂喇叭',
            '连处才艺互动' => '大家每天开开心心', 'les情叔' => '为你哭的像条狗', '球球500稳+心态' => '来了都是大哥',
            '王者开黑' => '安静听音乐  好梦', '王者铂金五排来' => '', '王者-带妹子上分' => '', '聊天交友' => '', '声优女神' => '',
            '声优男神' => '', '古风声优' => '', '声优小哥哥' => '可爱5认真接活', '表白+挽留' => '主持最大 ', '唱歌房' => '主持最大,房主点关注',
            '金牌KTV' => '唱歌+清唱', '迷雾+球球' => '', '分手挽留+唱歌表白' => '分挽 独白 唱歌', '发泄泄愤房' => '100钻起,对房主泄愤',
            '声鉴' => '礼物声鉴', '退' => '.. 烦  ..', '球球稳团心疼+' => '梦里baby', '一无所有' => '搏一搏单车变摩托', '一个人的房间' => '',
            '临时战稳团500m' => '', '笔心男神好热好热' => '喜礼物', '才艺PK' => '不服来战', '憨包小窝' => '波波', '睡觉别烦' => '二麦对象坑',
            '暖心只宠自家小仙女' => '有没有小仙女宠我', '东北聊天扯犊子' => '不跳8必死', '单浪1200+心态' => '不作不会死',
            '聊天的进' => '久伴+人在喊我', '唠嗑荒野聊天刷粉' => '跳8刷粉', '单纯聊天' => '房主在呦~~', '撩妹套路分享' => '小礼物上麦分享撩妹套路',
            'gay 聊天处对象' => '上麦说话', '喜四川,聊天,睡觉' => '想睡觉,就上炕,感受快乐', '连麦陪睡+限女' => '黄金的上麦', '连麦陪睡+限男' => '拒绝小白',
            '处对象限女' => '上麦爆口活', '处对象限男' => '十钻上8麦', '荒野行动限女' => '带你吃鸡带你飞', '王者带妹子上分' => '', '声优女神' => '可爱5认真接活',
            '声优男神' => '10钻声鉴', '聊天交友-限女' => '1000钻交友我是认真的', '征婚交友' => '有意愿的上麦', '处女座的进' => '找对象',
            '连麦找对象' => '麦上的人聊聊天', '喜NBA的进' => '科比', '唱歌比赛' => '麦上的人PK', '无聊的进' => '可以上麦',
            '90后空巢老人' => '愿意上麦的等等', '想交朋友的进' => '', '骨傲天' => '', '故事会' => '讲出你的故事', '无聊睡觉' => '',
            '新人进房间互相交流' => '新人'];

        foreach ($rooms as $name => $topic) {

            $room = Rooms::findFirstByName($name);

            if ($room) {
                continue;
            }

            $cond['conditions'] = '(current_room_id = 0 or current_room_id is null) and user_type = ' . USER_TYPE_SILENT;
            $user = Users::findFirst($cond);

            $room = Rooms::createRoom($user, $name);
            $room->topic = $topic;
            $room->status = STATUS_OFF;
            $room->save();
        }
    }

    function test76Action()
    {
        $withdraw_histories = WithdrawHistories::findByUserId(11161);

        foreach ($withdraw_histories as $withdraw_history) {
            echoLine($withdraw_history);
        }

    }


    function test77Action()
    {
        $datas = [];
        $emotion_images = EmoticonImages::findForeach();
        foreach ($emotion_images as $emotion_image) {
            debug($emotion_image->toJson());
            $datas[] = $emotion_image->toJson();
        }

        file_put_contents(APP_ROOT . "public/emotion_images.json", json_encode($datas, JSON_UNESCAPED_UNICODE));
    }

    function test78Action()
    {
        $datas = file_get_contents(APP_ROOT . "public/emotion_images.json");

        $datas = json_decode($datas, true);

        foreach ($datas as $data) {
            $image_url = fetch($data, 'image_url');
            $dynamic_image_url = fetch($data, 'dynamic_image_url');
            $name = fetch($data, 'name');
            $rank = fetch($data, 'rank');
            $status = fetch($data, 'status');
            $code = fetch($data, 'code');
            $duration = fetch($data, 'duration');
            try {
                $source_image = APP_ROOT . "public/temp/" . uniqid() . ".png";
                httpSave($image_url, $source_image);
                $image = APP_NAME . "/emoticon_images/image/" . uniqid() . ".png";
                StoreFile::upload($source_image, $image);

                $source_image = APP_ROOT . "public/temp/" . uniqid() . ".gif";
                httpSave($dynamic_image_url, $source_image);
                $dynamic_image = APP_NAME . "/emoticon_images/dynamic_image/" . uniqid() . ".gif";
                StoreFile::upload($source_image, $dynamic_image);
            } catch (Exception $e) {
                debug($image_url, $dynamic_image_url, $e->getMessage());
            }

            $emotion_image = new EmoticonImages();
            $emotion_image->name = $name;
            $emotion_image->rank = $rank;
            $emotion_image->status = $status;
            $emotion_image->code = $code;
            $emotion_image->duration = $duration;
            $emotion_image->save();
        }
    }

    function test79Action()
    {
        $rooms = Rooms::findBy(['user_type' => USER_TYPE_SILENT]);

        foreach ($rooms as $room) {
            $cond['conditions'] = '(room_id = 0 or room_id is null) and user_type = ' . USER_TYPE_SILENT
                . " and avatar_status = " . AUTH_SUCCESS;
            $user = Users::findFirst($cond);
            if ($user) {
                $user->room_id = $room->id;
                $user->save();
                $room->user_id = $user->id;
                $room->save();
                echoLine($user->id);
            }
        }
    }

    function test80Action()
    {
        $rooms = Rooms::findForeach();
        foreach ($rooms as $room) {
            $total_users = $room->findTotalUsers();
            foreach ($total_users as $user) {
                if ($user->avatar_status != AUTH_SUCCESS) {
                    $room->exitRoom($user);
                }
            }
        }

        $user = Users::findFirstById(9043);
        echoLine($user);

        $cond['conditions'] = '(current_room_id = 0 or current_room_id is null) and user_type = ' . USER_TYPE_SILENT .
            " and avatar_status = " . AUTH_SUCCESS;
        $num = Users::count($cond);
        echoLine($num);

        $rooms = Rooms::getOnlineSilentRooms();

        foreach ($rooms as $room) {
            $user = $room->user;
            if ($user->isInRoom($room)) {
                echoLine("ss", $user->id, $room->id);
            }
        }
    }

    function test81Action()
    {
        $rooms = Rooms::findForeach();
        foreach ($rooms as $room) {
            $room->user_type = $room->user->user_type;
            $room->update();
        }
    }
}