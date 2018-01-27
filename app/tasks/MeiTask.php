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
        $server = new PushSever();
        echoLine($server->getConnectionNum());

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
}