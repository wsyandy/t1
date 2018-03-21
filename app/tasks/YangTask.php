<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/13
 * Time: 下午2:49
 */
require 'CommonParam.php';

class YangTask extends \Phalcon\Cli\Task
{
    use CommonParam;

    function testAction($params)
    {
        $url = "http://chance.com/api/friends";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test2Action($params)
    {
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if (!$user) {
            echoLine("no user");
            return;
        }
        echoLine($user->toDetailJson());
    }

    function test3Action()
    {
        $url = "http://chance.com/api/chats";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid, 'user_id' => 2));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test4Action()
    {
        $url = "http://chance.com/api/emoticon_images";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function audioChaptersAction($params)
    {
        $room_id = $params[0];
        $rank = $params[1];
        $url = "http://chance.com/api/audio_chapters";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'room_id' => $room_id, 'rank' => $rank]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test5Action($params)
    {
        $url = "http://chance.com/api/room_themes";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test6Action()
    {
        $url = "http://chance.com/api/rooms/set_theme";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'id' => 15, 'room_theme_id' => '2']);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test7Action()
    {
        $url = "http://chance.com/api/rooms/close_theme";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'id' => 15]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test8Action()
    {
        $url = "http://chance.com/api/rooms/detail";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'id' => 15]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function openMusicPermissionAction($params)
    {
        $url = "http://chance.com/api/room_seats/open_music_permission";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        $user = \Users::findFirstById($id);
        if (!$user) {
            return echoLine("此用户不存在");
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $room = $user->room;
        if (!$room) {
            return echoLine("此用户的房间不存在");
        }
        $room_seat = RoomSeats::findFirstByRoomId($room->id);
        $body = array_merge($body, ['sid' => $user->sid, 'id' => $room_seat->id]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function closeMusicPermissionAction($params)
    {
        $url = "http://chance.com/api/room_seats/close_music_permission";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if (!$user) {
            return echoLine("此用户不存在");
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $room = $user->room;
        if (!$room) {
            return echoLine("此用户的房间不存在");
        }
        $room_seat = RoomSeats::findFirstByRoomId($room->id);
        $body = array_merge($body, ['sid' => $user->sid, 'id' => $room_seat->id]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function detailAction($params)
    {
        $url = "http://chance.com/api/shares/detail";
        $body = $this->commonBody();
        $id = $params[0];
        $share_source = $params[1];
        $user = \Users::findFirstById($id);
        if (!$user) {
            return echoLine("此用户不存在");
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $room = $user->room;
        if (!$room) {
            return echoLine("此用户的房间不存在");
        }
        $body = array_merge($body, ['sid' => $user->sid, 'code' => 'yw', 'room_id' => $room->id, 'share_source' => $share_source]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function resultAction($params)
    {
        $url = "http://chance.com/api/shares/result";
        $body = $this->commonBody();

        $id = $params[0];
        $history_id = $params[1];
        $status = $params[2];
        $type = $params[3];

        $user = \Users::findFirstById($id);
        if (!$user) {
            return echoLine("此用户不存在");
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }

        $body = array_merge($body, ['sid' => $user->sid, 'share_history_id' => $history_id, 'status' => $status, 'type' => $type]);
        $res = httpGet($url, $body);
        echoLine($res);
    }


    function bannersAction($params)
    {
        $url = "http://chance.com/api/banners/index";
        $body = $this->commonBody();

        $id = $params[0];

        $user = \Users::findFirstById($id);
        if (!$user) {
            echoLine("此用户不存在");
            return;
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }

        $body = array_merge($body, ['sid' => $user->sid, 'new' => 1, 'hot' => 1]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test9Action()
    {
        $time = time();
        $days = [];
        $hours = [8, 10, 12, 14, 16, 18];
        for ($i = 0; $i < 5; $i++) {
            $day = beginOfDay($time + $i * 60 * 60 * 24);
            $times = [];
            foreach ($hours as $hour) {
                $time_at = $day + $hour * 60 * 60;
                $times[date('H-i', $time_at)] = $time_at;
            }

            $days[date("m月d日", $day)] = $times;
        }
        var_dump($days);
//        echoLine("----------------");
//        $test2 = beginOfDay(time());
//        echoLine($test2 + 60 * 60 * 4);
//        $time3 = strtotime(date('Y-m-d 04:00', $time));
//        echoLine($time3);
//        echoLine(date('Y-m-d-h-i-sa', $time3));
//        echoLine("----------------");
//        $time4 = $time3 + 60 * 60 * 2 - 1;
//        echoLine($time4);
//        $time5 = strtotime(date('Y-m-d 05:59:59', $time));
//        echoLine($time5);
//        echoLine(date('Y-m-d-h-i-sa', $time5));
    }

    function test10Action()
    {
        $users = Users::find([
            'limit' => 30
        ]);
        $union = Unions::findFirstById(59);
        foreach ($users as $user) {
            $union->applyJoinUnion($user);
        }
    }

    function test12Action($params)
    {
        $db = Users::getUserDb();

        $command = $params[0];
        if ($command == 1) {
            echoLine("----" . $db->setex("yangxing", 60, 2));
        } else {
            echoLine("++++" . $db->ttl("yangxing"));
        }
    }


    function test13Action()
    {
        $url = "http://chance.com/api/users/is_sign_in";
        $body = $this->commonBody();
        $id = 93;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test14Action($params)
    {
        $url = "http://chance.com/api/users/sign_in";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid));
        $res = httpPost($url, $body);
        echoLine($res);
    }
}
