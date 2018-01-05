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
        $body = array_merge($body, array('sid' => $user->sid));

        $res = httpPost($url, $body);
        var_dump($res);
    }
}