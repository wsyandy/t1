<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 03/01/2018
 * Time: 10:51
 */

class EmChatTask extends \Phalcon\Cli\Task
{

    function getUsername()
    {
        return "localtest";
    }

    function getFriendName()
    {
        return "test_friend";
    }

    function testGetTokenAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->token);
    }

    function testCreateUserAction($params = array())
    {
        $emchat = new \Emchat();
        $password = '12345';
        if (isBlank($params)) {
            $username = $this->getUsername();
        } else {
            $username = $params[0];
        }
        $result = $emchat->createUser($username, $password);
        var_dump($result);
    }

    function testCreateUsersAction()
    {
        $emchat = new \Emchat();
        $users_info = array(
            array('username' => 'localtest','password' => '12345'),
            array('username' => 'localtest1','password' => '12345')
        );
        var_dump($emchat->createUsers($users_info));
    }

    function testResetPasswordAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->resetPassword('68', 'a3f390d88e4c41f2747bfa2f1b5f87db', 'a3f390d88e4c41f2747bfa2f1b5f87db'));
    }

    function testGetUserAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->getUser(68));
        echo md5('68');
    }

    function testGetUsersAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->getUsers());
    }

    function testDeleteUserAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->deleteUser('localtest'));
    }

    function testIsOnlineAction()
    {
        $emchat = new \Emchat();
        $username = 'localtest';
        var_dump($emchat->isOnline($username));
    }

    function testOfflineMessagesAction()
    {
        $emchat = new \Emchat();
        $username = 'localtest';
        var_dump($emchat->getOfflineMessages($username));
    }

    function testDeactiveUserAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->deactiveUser($this->getusername()));
    }

    function testActiveUserAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->activeUser($this->getUsername()));
    }

    function testDisconnectUserAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->disconnectUser($this->getUsername()));
    }

    function testAddFriendAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->addFriend($this->getUsername(), $this->getFriendName()));
    }

    function testGetFriendsAction()
    {
        $emchat = new \Emchat();
        var_dump($emchat->getFriends($this->getUsername()));
    }

    function testDeleteFriendAction()
    {
        $emchat = new \Emchat();
        $username = $this->getUsername();
        $friend_name = $this->getFriendName();
        var_dump($emchat->deleteFriend($username, $friend_name));
    }

    function testGetBlacksAction()
    {
        $emchat = new \Emchat();
        $username = $this->getUsername();

        var_dump($emchat->getBlacks($username));
    }

    function testAddBlacksAction()
    {
        $emchat = new \Emchat();
        $result = $emchat->addBlacks($this->getUsername(), array($this->getFriendName()));
        var_dump($result);
    }

    function testDeleteBlackAction()
    {
        $emchat = new \Emchat();
        $result = $emchat->deleteBlack($this->getUsername(), $this->getFriendName());
        var_dump($result);
    }

    function testUploadFileAction()
    {
        $emchat = new \Emchat();
        //$file_path = APP_ROOT . 'public/images/payment_channel/weixin.png';
        $file_path = '/Users/maoluanjuan/Pictures/1d46fd431d3106a599cc.jpg';
        debug($file_path);
        var_dump($emchat->uploadFile($file_path));
    }

    function testSendTextAction()
    {
        $emchat = new \Emchat();
        $sender_name = $this->getUsername();
        $receiver_name = $this->getFriendName();

        $content = 'test send text';
        var_dump($emchat->sendText($sender_name, array($receiver_name), $content));
    }

    function testSendCmdAction()
    {
        $emchat = new \Emchat();
        $sender_name = $this->getUsername();
        $recever_name = $this->getFriendName();

        $action = 'action0';
        var_dump($emchat->sendCmd($sender_name, $recever_name, $action));
    }

    function testSendImageAction()
    {
        $emchat = new \Emchat();
        $sender_name = $this->getUsername();
        $receiver_name = $this->getFriendName();

        $file_path = '/Users/maoluanjuan/Pictures/1d46fd431d3106a599cc.jpg';
        $filename = 'test.jpg';
        var_dump($emchat->sendImage($file_path, $sender_name, $receiver_name, $filename));
    }

    function testSendAudioAction()
    {
        $emchat = new \Emchat();
        $sender_name = $this->getUsername();
        $receiver_name = $this->getFriendName();
        $file_path = '/Users/maoluanjuan/Pictures/talk.mp3';
        $filename = 'talk.mp3';
        $length = 30;
        var_dump($emchat->sendAudio($file_path, $sender_name, $receiver_name, $filename, $length));
    }

    function testSendVideoAction()
    {
        $emchat = new \Emchat();
        $sender_name = $this->getUsername();
        $receiver_name = $this->getFriendName();

        $video_file_path = '/Users/maoluanjuan/Pictures/fannao.mp4';
        $thumb_image_path = '/Users/maoluanjuan/Pictures/1d46fd431d3106a599cc.jpg';
        $filename = 'test.mp4';
        var_dump($emchat->sendVideo($video_file_path, $sender_name, $receiver_name, $filename, 10, $thumb_image_path));
    }

    function testSendFileAction()
    {
        $emchat = new \Emchat();
        $sender_name = $this->getUsername();
        $receiver_name = $this->getFriendName();

        $file_path = '/Users/maoluanjuan/Pictures/fannao.mp4';
        $filename = 't.mp4';
        $length = filesize($file_path);
        var_dump($emchat->sendFile($file_path, $sender_name, $receiver_name, $filename, $length));
    }

    function testCreateUserApiAction()
    {
        $url = 'http://www.chance_php.com/api/users/emchat';
        $body = array( 'debug' => 1, 'sid' => '');

        $res = httpPost($url, $body);
        var_dump($res);

    }

}