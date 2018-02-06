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
}
