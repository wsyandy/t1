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
}