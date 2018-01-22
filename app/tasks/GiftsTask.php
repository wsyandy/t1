<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 13:31
 */
require 'CommonParam.php';

class GiftsTask extends \Phalcon\Cli\Task
{
    use CommonParam;

    function testIndexAction()
    {
        $url = "http://www.chance_php.com/api/gifts";
        $body = $this->commonBody();

        $user = \Users::findLast();
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $user->version = 1.3;
        $user->update();
        $body = array_merge($body, array('sid' => $user->sid));

        $res = httpGet($url, $body);
        var_dump($res);
    }

    function testGiveGiftAction()
    {
        $url = "http://www.chance_php.com/api/gifts";

        $body = $this->commonBody();
        $user = \Users::findLast();
        $sender = \Users::findFirst();

        $gift = \Gifts::findLast();
        $gift_num = 3;
        $body = array_merge($body, array(
            'sid' => $sender->sid, 'user_id' => $user->id,
            'gift_id' => $gift->id, 'gift_num' => $gift_num)
        );
        $res = httpPost($url, $body);
        //echo json_encode($res, JSON_UNESCAPED_UNICODE);
        echo $res;
        //var_dump($res);
    }

    function testCanGiveGiftAction()
    {
        //$user = \Users::findLast();
        $sender = \Users::findFirst();
        $gift = \Gifts::findLast();
        var_dump($sender->canGiveGift($gift, 10));
    }
}