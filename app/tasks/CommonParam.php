<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 13:33
 */
trait CommonParam
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
            'verc' => '1',
            'ver' => '1.0',
            'ts' => time(),
            'net' => 'wifi',
        );
        return $body;
    }

    function fakeNickname()
    {
        return 'test';
    }

    function fakeSex()
    {
        return USER_SEX_MALE;
    }

    function fakeProvinceId()
    {
        return 1;
    }

    function fakeCityId()
    {
        return 1;
    }

    function fakeAvatar()
    {
        return 'test.jpg';
    }

    function updateUserInfo($user)
    {
        $columns = ['nickname', 'sex', 'province_id', 'city_id', 'avatar'];
        foreach ($columns as $column) {
            if (isBlank($user->$column)) {
                $method = \Phalcon\Text::camelize("fake_" . $column);
                $user->$column = $this->$method();
            }
            $user->update();
        }

        return $user;
    }
}