<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午3:21
 */

trait UserEnumerations
{
    static $UPDATE_FIELDS = [
        'nickname' => '昵称',
        'avatar' => '头像',
        'ip' => 'ip',
        'sex' => '性别',
        'province_id' => '省份',
        'city_id' => '城市',
        'province_name' => '省份',
        'city_name' => '城市',
        'monologue' => '个人签名'
    ];
}
