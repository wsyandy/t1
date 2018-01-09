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
        $user_id = 75;
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

        $room_seat = RoomSeats::findFirstById(89);
        $room_seat->down($user);
    }
}