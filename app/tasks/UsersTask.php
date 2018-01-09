<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 04/01/2018
 * Time: 16:40
 */
class UsersTask extends \Phalcon\Cli\Task
{
    function commonBody()
    {
        $body = array(
            'debug' => 1,
            'code' => 'yuewan',
            'dno' => 'dnotest',
            'sid' => 'sidtest',
            'man' => 'apple',
            'mod' => 'iphone',
            'an' => '1.0',
            'h' => 'h',
            'fr' => 'local',
            'pf' => 'ios',
            'pf_ver' => '10.0.1',
            'verc' => '15',
            'ver' => '1.0',
            'ts' => time(),
            'net' => 'wifi',
        );
        return $body;
    }

    function testActiveAction()
    {
        $url = 'http://www.chance_php.com/api/devices/active';
        $body = array_merge($this->commonBody(), array(
            'ua' => 'ios',
            'ei' => '11111',
            'imei' => '1111',
            'if' => '1111',
            'idfa' => '1111',
        ));
        $res = httpPost($url, $body);
        var_dump($res);
    }

    function testRegisterAction()
    {
        $url = 'http://www.chance_php.com/api/users/register';
        $mobile = '13800000000';
        $auth_code = '1234';
        $password = 'test12';
        $sms_token = '';
        $body = array(
            'sms_token' => $sms_token,
            'auth_code' => $auth_code,
            'password' => $password, 'mobile' => $mobile);
        $body = array_merge($body, $this->commonBody());
        $res = httpPost($url, $body);
        var_dump($res);
    }

    function testCreateEmchatAction()
    {
        $url = 'http://www.chance_php.com/api/users/emchat';
        $body = array_merge($this->commonBody(), array('sid' => '1s2867faffa7acb625226c6eb1e2dca91b29'));

        $res = httpPost($url, $body);
        var_dump($res);
    }

    function freshAttrsAction()
    {
        $user = \Users::findById(1);
        $user->platform = 'ios';
        $user->save();
    }

    function geoAction()
    {

        $users = Users::findForeach();
        foreach ($users as $user) {
            if ($user->latitude < $user->longitude) {
                $geo_hash = new \geo\GeoHash();
                $hash = $geo_hash->encode($user->latitude / 10000, $user->longitude / 10000);
                info($user->id, $user->latitude, $user->longitude, $hash);
                if ($hash) {
                    $user->geo_hash = $hash;
                }
                $user->update();
            }
        }

        $user = Users::findFirstById(8);
        $users = $user->nearby(1, 10);
        foreach ($users as $user){
            echoLine($user->id);
        }
    }
}
