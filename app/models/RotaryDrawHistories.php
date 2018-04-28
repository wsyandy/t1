<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/28
 * Time: 下午4:29
 */
class RotaryDrawHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    static $TYPE = ['gold' => '金币', 'diamond' => '钻石', 'gift' => '礼物'];

    static function getData()
    {
        $data = [];
        $data[] = ['type' => 'gold', 'name' => '金币', 'number' => 100, 'rate' => 43.4];
        $data[] = ['type' => 'gold', 'name' => '金币', 'number' => 1000, 'rate' => 30];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 10, 'rate' => 15];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 30, 'rate' => 15];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 100, 'rate' => 5];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 500, 'rate' => 2];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 1000, 'rate' => 1];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 10000, 'rate' => 0.5];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 100000, 'rate' => 0.1];

        return $data;
    }


}