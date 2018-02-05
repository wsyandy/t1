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

    function testProfileAction()
    {
        $url = "http://www.chance_php.com/api/users/detail";
        $body = array_merge($this->commonBody(), array('sid' => '2s36fc9464a3b37466a88951d0318c90a3b6'));

        $res = httpPost($url, $body);
        var_dump($res);
    }

    function testUserGiftsAction()
    {
        $user = \Users::findById(2);
        if ($user) {
            echo count($user->user_gifts) . PHP_EOL;
        }

        $cond = array();
        $results = \UserGifts::find();

        echo count($results) . PHP_EOL;
        //echo json_encode($results, JSON_UNESCAPED_UNICODE);

        $user_gift = \UserGifts::findLast();
        echo json_encode($user_gift->toJson(), JSON_UNESCAPED_UNICODE);
    }

    function testUserGiftsIndexAction()
    {
        $url = "http://www.chance_php.com/api/user_gifts";
        $body = array_merge($this->commonBody(), array('sid' => '2s36fc9464a3b37466a88951d0318c90a3b6', 'page' => 2));

        $res = httpPost($url, $body);
        var_dump($res);
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
        foreach ($users as $user) {
            echoLine($user->id);
        }
    }

    function disAction()
    {
        $user = Users::findFirstById(8);
        $users = $user->nearby(1, 10);
        $user->calDistance($users);

        echoLine($users);

        foreach ($users as $user) {
            echoLine($user->id, $user->geo_hash, $user->distance);
        }

        echoLine('cc', $users->count());
    }

    //初始化头像
    function initAvatarAction()
    {
        $res = StoreFile::upload(APP_ROOT . "public/images/avatar.png",  APP_NAME . '/users/avatar/default_avatar.png');
        echoLine($res);

        echoLine(StoreFile::getUrl('chance/users/avatar/default_avatar.png '));
    }

    /**
     * 测试我的账户api
     */
    function accountAction()
    {
        $url = 'http://www.chance_php.com/api/users/account';
        $body = $this->commonBody();

        $user = \Users::findById(2);
        $body = array_merge($body, array('sid' => $user->sid));

        $res = httpGet($url, $body);
        var_dump($res);
    }

    /**
     * 测试产品api
     */
    function productsAction()
    {
        $url = 'http://www.chance_php.com/api/products';
        $body = $this->commonBody();

        $user = \Users::findById(2);
        $body = array_merge($body, array('sid' => $user->sid));

        $res = httpGet($url, $body);
        var_dump($res);
    }

    /**
     * 导入用户
     */
    function importUserAction()
    {
        $filename = APP_ROOT . 'log/user_detail.log';
        if (isDevelopmentEnv()) {
            $filename = APP_ROOT . 'log/dev_user_detail.log';
        }
        $yuanfen = new \Yuanfen($filename);
        $yuanfen->parseFile();
    }

    function silentUserAction()
    {
        $user_id = 2;
        while (true) {
            $user = \Users::findById($user_id);
            if (isBlank($user)) {
                break;
            }
            if ($user && $user->isNpc() && isBlank($user->avatar)) {
                \Yuanfen::addSilentUser($user);
            }
            $user_id += 1;
        }
    }
}

