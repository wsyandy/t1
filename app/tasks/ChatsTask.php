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

    function testCreateAction()
    {
        $attrs = array(
            'sender_id' => SYSTEM_ID,
            'receiver_id' => 2,
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

    function testAdminChatsAction()
    {
        $user = \Users::findById(2);
        $page = 1;
        $per_page = 10;

        $chats = \Chats::findChatsList($user, $page, $per_page);
        var_dump($chats);
    }

    function testIndexAction()
    {
        $user = \Users::findById(2);
        $url = 'http://www.chance_php.com/api/chats';

        $param = array('sid' => $user->sid, 'user_id' => SYSTEM_ID);
        //var_dump($param);
        $body = array_merge($this->commonBody(), $param);
        //var_dump($body);
        //echo json_encode($body, JSON_UNESCAPED_UNICODE);
        //exit;
        $res = httpGet($url, $body);

        var_dump($res);
    }

    function testSendWelcomeAction()
    {
        \Chats::sendWelcomeMessage(2);
    }
}