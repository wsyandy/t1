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
        $key = "room_id1_user_id2";
        preg_match('/room_id(\d+)_user_id(\d+)/', $key, $matches);
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
        $intranet_ip = $receiver_user->getIntranetIp();
        $receiver_fd = $receiver_user->getUserFd();
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
        $intranet_ip = $user->getIntranetIp();
        $receiver_fd = $user->getUserFd();
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
        $intranet_ip = $user->getIntranetIp();
        $receiver_fd = $user->getUserFd();
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
        $intranet_ip = $user->getIntranetIp();
        $receiver_fd = $user->getUserFd();
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
        $room_names = [];
        $rooms = Rooms::findBy(['user_type' => USER_TYPE_SILENT]);

        foreach ($rooms as $room) {
            $topic = fetch($room_names, $room->name);

            if ($topic) {
                echoLine($topic);
                $room->topic = $topic;
                $room->save();
            }
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
            echoLine(date("Ymd H:i:s", $room->getExpireTime()), $room->id);
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

    function test82Action()
    {
        $monologues = file_get_contents(APP_ROOT . "doc/user_data/monolog_woman.txt");
        $monologues = explode(PHP_EOL, $monologues);

        $limit = count($monologues);

        //and monologue is not null
        $cond = [
            'conditions' => 'user_type = :user_type: and avatar_status = :avatar_status: and sex = :sex:',
            'bind' => ['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS, 'sex' => 1],
            //'limit' => $limit
        ];


        $users = Users::find($cond);
        echoLine(count($users));

        $i = 0;
        foreach ($users as $user) {
            echoLine($user->monologue, $user->id);
            $user->monologue = $monologues[$i];
//            $user->update();
            $i++;
        }

        $users = Users::findBy(['monologue' => '世界好宽，让孤单好满。']);
        echoLine(count($users));

    }

    function test83Action()
    {
        $array = [1, 2, 3, 4, 5];
        $array1 = [1, 2];
        $array2 = array_diff($array, $array1);
        print_r($array2[array_rand($array2)]);

        $user = Users::findFirstById(10123);
        echoLine($user);

        $users = Users::findBy(['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS]);

        foreach ($users as $user) {
            echoLine(date("Ymd", $user->birthday), $user->age);
        }


        $age = mt_rand(16, 25);
        $birthday = 2018 - $age;
        $month = mt_rand(1, 12);
        $day = mt_rand(1, 28);

        if ($day < 10) {
            $day = "0" . $day;
        }

        if ($month < 10) {
            $month = "0" . $month;
        }

        $new_birthday = $birthday . $month . $day;

        $user->birthday = strtotime($new_birthday);
        $user->update();
    }

    function test84Action()
    {
        $user = Users::findFirstById(100137);
        echoLine($user);
    }

    function test85Action()
    {

        while (true) {
            $user = new Users();
            $user->user_type = USER_TYPE_SILENT;
            $user->user_status = USER_STATUS_OFF;
            $user->sex = mt_rand(0, 1);
            $user->product_channel_id = 1;
            $user->save();

            if ($user->id >= 100000) {
                break;
            }
        }
    }

    function test86Action()
    {
        $str = "1";

        if (!preg_match('/^\d+\d$/', $str)) {
            debug("dddd");
        }

        $user = Users::findFirstById(10397);
        echoLine($user);
    }

    function test87Action()
    {
        $hot_cache = Rooms::getHotWriteCache();
        $token = '79ff4423baa4c9bfc04f7de917c65c9b1f6';
        if ($token) {
            debug("sss");
        }
        $hot_cache->set($token, 175);
        debug($hot_cache->get($token));
    }

    function test88Action()
    {
        $user = Users::findFirstById(100140);
        echoLine($user->online_token);

        $token = '79ff4423baa4c9bfc04f7de917c65c9b1f6';
        $room = Rooms::findRoomByOnlineToken($token);
        if ($room) {
            echoLine($room);
        }

        $room = Rooms::findFirstById(369);
        $hot_cache = Rooms::getHotWriteCache();
        $key = $room->getUserListKey();
        echoLine($hot_cache->zscore($key, 100168));

    }

    function test89Action()
    {
        $users = Users::findForeach(['conditions' => 'product_channel_id = 0 or product_channel_id is null']);

        foreach ($users as $user) {
            $user->product_channel_id = 1;
            $user->save();
        }
    }

    function test90Action()
    {
        $data = file_get_contents(APP_ROOT . "public/gdt2.log");
        $data = explode(PHP_EOL, $data);

        $res = [];
        $muids = [];

        foreach ($data as $item) {
            $log = json_decode($item, true);
            $muid = fetch($log, 'muid');
            $res[$muid] = $log;
            $muids[] = $muid;
        }

        $muids = array_unique($muids);
        echoLine(count($muids));
        $devices = Devices::find(['conditions' => 'created_at >= :begin: and created_at <= :end:', 'bind' => ['begin' => beginOfDay(), 'end' => endOfDay()]]);
        echoLine(count($devices));

        //echoLine($res);
        foreach ($devices as $device) {
            $muid = Partners::generateMuid(['imei' => $device->imei]);
            //echoLine($muid);
            if ($muid && in_array($muid, $muids)) {
                echoLine($device->fr, $device->id);
                $data = $res[$muid];
                echoLine($data, $muid);
                $data['act_time'] = time();
                Partners::notifyGdt($data);
            }
        }
    }

    function test91Action()
    {
        $gift = Gifts::findFirstById(1);
        $gift->dynamic_image = '';
        $gift->save();
    }

    function test92Action()
    {
        $user = Users::findFirstById(117);
        $user->sid = $user->generateSid('s');
        $user->save();
    }

    function test93Action()
    {
        $user_gifts = UserGifts::findByUserId(117);

        $user_gifts = UserGifts::findForeach();
        $amount = 0;

        foreach ($user_gifts as $gift) {
            if ($gift->total_amount != $gift->num * $gift->amount) {
                $gift->total_amount = $gift->num * $gift->amount;
                $gift->save();
                echoLine($gift->total_amount, $gift->id, $gift->num, $gift->amount);
            }
            $amount += $gift->total_amount;
        }

        echoLine($amount);
        echoLine($amount / 100);
    }

    //[PID 29705]54
    //[PID 29705]54
    //[PID 29705]["14901"]
    //[PID 29705]["14901"]
    //[PID 29705]["229"]
    function test94Action()
    {
        $hot_cache = Rooms::getHotReadCache();
        $user = Users::findFirstById(14901);
        $receiver = Users::findFirstById(6);
        $gift = Gifts::findFirstById(1);
        $gift_num = 3;
        $room = Rooms::findFirstById(229);
        echoLine($room->getDayGiftAmountBySilentUser(true));
        echoLine($room->getHourGiftAmountBySilentUser());
        echoLine($hot_cache->zrange($room->getStatGiftUserNumKey(), 0, -1));
        echoLine($hot_cache->zrange($room->getStatGiftUserNumKey(true), 0, -1));

        $key = "user_send_gift_rooms_user_id_14901";
        echoLine($hot_cache->zrange($key, 0, -1));

        $give_result = GiftOrders::giveTo($user->id, $receiver->id, $gift, $gift_num);
        if ($give_result) {
            $room->pushGiftMessage($user, $receiver, $gift, $gift_num);
        }


        $user = Users::findFirstById(101851);
        echoLine($user);

        $user = Users::findFirstById(10513);
        $room = $user->room;
        $room->enterRoom($user);

        $room = Rooms::findFirstById(176);
        $user = Users::findFirstById(39);
        $room->exitRoom($user);
        $room->pushExitRoomMessage($user);

        $ip = "192.168.64.96";
        $port = 9502;
        $client = new PushClient($ip, $port, 1);

        if (!$client->connect()) {
            info("Exce connect fail");
            return false;
        }

        $ip = PushSever::getIntranetIp();
        echoLine($ip);

        $users = Users::findBy(['user_type' => USER_TYPE_ACTIVE]);
        $hot_cache = Users::getHotWriteCache();

        foreach ($users as $user) {
            $online_token = $user->online_token;

            if ($online_token) {
                $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
                $user_ip = $hot_cache->get($fd_intranet_ip_key);
                echoLine($online_token);

                if ($user_ip && $user_ip != $ip) {
                    $hot_cache->set($fd_intranet_ip_key, $ip);
                    info("update user ip", $user_ip, $ip);
                }
            }
        }
    }

    function test95Action()
    {
        $user = Users::findFirstById(117);
        echoLine($user->getIntranetIp());
        echoLine($user->getUserFd());
        echoLine($user->online_token);

        $room = Rooms::findRoomByOnlineToken($user->online_token);
        $room_seat = RoomSeats::findRoomSeatByOnlineToken($user->online_token);
        echoLine($room);
        echoLine($room_seat);
    }

    function test96Action()
    {
        //http://www.woyaogexing.com/touxiang/index_42.html
        //src="http://img2.woyaogexing.com/2018/02/11/ecf67ec708f0c498!400x400_big.jpg"

        for ($i = 1; $i < 2; $i++) {

            $url = "http://www.woyaogexing.com/touxiang/index";

            if ($i < 2) {
                $url .= ".html";
            } else {
                $url .= "_{$i}.html";
            }

            $content = file_get_contents($url);
            preg_match_all('/src="(http.+\.jpg)/', $content, $matches);

            if (count($matches) < 2) {
                echoLine($i, $url);
                continue;
            }

            $images = $matches[1];
            print_r($images);
            $user = Users::findFirstById(1);

            foreach ($images as $image) {
                $image_url = APP_ROOT . 'temp/images/' . md5(uniqid(mt_rand())) . '.jpg';
                httpSave($image, $image_url);

                if (!file_exists($image_url)) {
                    continue;
                }

                $dest_filename = APP_NAME . '/albums/' . $user->id . '_' . date('YmdH') . uniqid() . '.jpg';
                $res = \StoreFile::upload($image_url, $dest_filename);

                if ($res) {
                    $album = new Albums();
                    $album->user_id = $user->id;
                    $album->image = $dest_filename;
                    $album->auth_status = AUTH_WAIT;
                    $album->save();
                }

                unlink($image_url);
            }
        }
    }

    function test97Action()
    {
        $albums = Albums::findForeach();
//
//        foreach ($albums as $album) {
//            if ($album->auth_status == 0) {
//                echoLine($album->id, $album->user_id);
//                $album->auth_status = AUTH_WAIT;
//                $album->update();
//            }
//        }
//
//        $hot_cahe = Albums::getHotWriteCache();
//        $hot_cahe->zclear('albums_auth_success_list_user_id1');
//        $albums = Albums::findBy(['auth_status' => AUTH_SUCCESS, 'user_id' => 1]);
//
//        foreach ($albums as $album) {
//            $hot_cahe->zadd('albums_auth_success_list_user_id1', time(), $album->id);
//        }

        $hot_cache = Albums::getHotWriteCache();
        //$album_ids = $hot_cahe->zcard('albums_auth_success_list_user_id1');
        $album_ids = $hot_cache->zrange("albums_auth_type_3_list_user_id_10", 0, -1);
        echoLine($album_ids);

    }

    function test98Action()
    {
        $hot_cache = \Albums::getHotWriteCache();
        $auth_types = [1, 2, 3];

        foreach ($auth_types as $auth_type) {
            $hot_cache->zclear("albums_auth_type_{$auth_type}_list_user_id_1");
        }
    }

    function test99Action()
    {
        $cond = ['conditions' => 'user_type = ' . USER_TYPE_SILENT];

        $hot_cache = Users::getHotWriteCache();
        $key = "silent_user_update_avatar_user_ids";

        $filter_user_ids = $hot_cache->zrange($key, 0, -1);

        if (count($filter_user_ids) > 0) {
            $cond['conditions'] .= " and id not in (" . implode(',', $filter_user_ids) . ")";
        }

        $user = Users::findFirst($cond);

        echoLine(count($filter_user_ids));
        echoLine($user);

        $hot_cache = Users::getHotWriteCache();
        $key = "silent_user_update_avatar_user_ids";


        $filter_user_ids = $hot_cache->zrange($key, 0, -1);

        echoLine(count($filter_user_ids));
        $cond = ['conditions' => 'avatar_status = ' . AUTH_SUCCESS . ' and user_type = ' . USER_TYPE_SILENT];

        //$cond['conditions'] .= " and sex = 0";

        if (count($filter_user_ids) > 0) {
            $cond['conditions'] .= " and id not in (" . implode(',', $filter_user_ids) . ")";
        }

        $users = Users::find($cond);
        echoLine(count($users));
    }
}