<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 17/01/2018
 * Time: 17:02
 */

require 'CommonParam.php';

class ChatsTask extends \Phalcon\Cli\Task
{
    use CommonParam;

    function testCreateAction($params = array())
    {
        $user_id = fetch($params, 0, 2);
        $attrs = array(
            'sender_id' => SYSTEM_ID,
            'receiver_id' => $user_id,
            'content' => '测试',
            'content_type' => CHAT_CONTENT_TYPE_TEXT
        );
        $chat = \Chats::createChat($attrs);
        if ($chat) {
            var_dump($chat->toJson());
        } else {
            echo 'chat create error';
        }
    }

    function testAdminChatsAction($params = array())
    {
        $user_id = fetch($params, 0, 2);
        $user = \Users::findById($user_id);
        $page = 1;
        $per_page = 10;

        $chats = \Chats::findChatsList($user, $page, $per_page);
        var_dump($chats);
    }

    function testIndexAction($params = array())
    {
        $user_id = fetch($params, 0, 2);
        $user = \Users::findById($user_id);
        $host = fetch($params, 1, 'www.chance_php.com');
        $url = 'http://' . $host . '/api/chats';

        $param = array('sid' => $user->sid, 'user_id' => SYSTEM_ID);
        //var_dump($param);
        $body = array_merge($this->commonBody(), $param);
        //var_dump($body);
        //echo json_encode($body, JSON_UNESCAPED_UNICODE);
        //exit;
        $res = httpGet($url, $body);

        var_dump($res);
    }

    function testSendWelcomeAction($params = array())
    {
        $user_id = fetch($params, 0, 2);
        $res = \Chats::sendWelcomeMessage($user_id);
        if ($res) {
            var_dump($res);
        } else {
            echo "send welcome message fail";
        }
    }
}