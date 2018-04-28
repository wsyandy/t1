<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/28
 * Time: 下午4:29
 */
class DrawHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    static $TYPE = ['gold' => '金币', 'diamond' => '钻石', 'gift' => '礼物'];

    static function getData()
    {
        $data = [];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 100000, 'rate' => 0.1];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 10000, 'rate' => 0.5];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 1000, 'rate' => 1];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 500, 'rate' => 2];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 100, 'rate' => 5];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 30, 'rate' => 15];
        $data[] = ['type' => 'diamond', 'name' => '钻石', 'number' => 10, 'rate' => 15];
        $data[] = ['type' => 'gold', 'name' => '金币', 'number' => 1000, 'rate' => 30];
        $data[] = ['type' => 'gold', 'name' => '金币', 'number' => 100, 'rate' => 43.4];

        return $data;
    }

    // 计算奖品
    static function calculatePrize($user)
    {
        $random = mt_rand(1, 1000);
        $data = self::getData();
        foreach ($data as $datum) {
            if (fetch($datum, 'rate') * 10 > $random) {
                return $datum;
            }
        }

        return ['type' => 'gold', 'name' => '金币', 'number' => 100, 'rate' => 43.4];
    }

    static function createHistory($user, $opts = [])
    {

        $result = self::calculatePrize($user);

        $draw_history = new DrawHistories();
        $draw_history->user_id = $user->id;
        $draw_history->product_channel_id = $user->product_channel_id;
        $draw_history->type = fetch($result, 'type');
        $draw_history->number = fetch($result, 'number');
        $draw_history->type = fetch($result, 'type');
        $draw_history->save();

        info($draw_history);

        return $draw_history;
    }

}