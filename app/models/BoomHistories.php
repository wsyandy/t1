<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/9
 * Time: 19:45
 */

class BoomHistories extends BaseModel
{

    /**
     * @type Users
     */
    private $_user;

    static $TYPE = [BOOM_HISTORY_GIFT_TYPE => '礼物', BOOM_HISTORY_DIAMOND_TYPE => '钻石', BOOM_HISTORY_GOLD_TYPE => '金币'];

    public function getGiftName()
    {
        if ($this->target_id == 0) {
            return null;
        }

        $gifts = Gifts::findFirstById($this->target_id);
        return $gifts->name;
    }

    //引爆者礼物
    static function randomBoomUserGiftId()
    {
        $rate = mt_rand(1, 100);

        switch ($rate) {
            case $rate > 0 && $rate <= 35:
                $gift_id = 16;
                break;
            case $rate > 35 && $rate <= 70:
                $gift_id = 15;
                break;
            case $rate > 70 && $rate <= 90:
                $gift_id = 33;
                break;
            case $rate > 90 && $rate <= 100:
                $gift_id = 87;
                break;
        }

        return $gift_id;
    }

    //贡献这礼物 rank 用户贡献的排名
    static function randomContributionUserGiftIdByRank($rank)
    {
        $gift_ids = [15, 16, 33, 87];

        $can_get_gift = false;

        switch ($rank) {
            case 1:
                $rate = mt_rand(1, 100);

                if ($rate > 0 && $rate <= 50) {
                    $can_get_gift = true;
                }
                break;
            case 2;
                $rate = mt_rand(1, 100);

                if ($rate > 0 && $rate <= 30) {
                    $can_get_gift = true;
                }
                break;
            case 3:
                $rate = mt_rand(1, 100);

                if ($rate > 0 && $rate <= 10) {
                    $can_get_gift = true;
                }
                break;
        }

        if ($can_get_gift) {
            $gift_id = $gift_ids[array_rand($gift_ids)];
            return $gift_id;
        }

        return null;
    }

    //$num 爆礼物次数
    static function randomBoomGiftIdByBoomNum($room, $num, $rate = 0)
    {
        if ($rate) {

            $random = mt_rand(1, 100);

            if ($random > $rate) {
                return null;
            }
        }

        if ($num > 3) {
            $num = 3;
        }

        $datas = [
            1 => [
                1 => ['type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 20, 'target_id' => 66, 'number' => 1],
                2 => ['type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 50],
                3 => ['type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 10, 'number' => 100],
            ],
            2 => [
                1 => ['type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 20, 'target_id' => 94, 'number' => 1],
                2 => ['type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 100],
                3 => ['type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 10, 'number' => 200],
            ],
            3 => [
                1 => ['type' => BOOM_HISTORY_GIFT_TYPE, 'total_number' => 20, 'target_id' => 47, 'number' => 1],
                2 => ['type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 20, 'number' => 188],
                3 => ['type' => BOOM_HISTORY_DIAMOND_TYPE, 'total_number' => 10, 'number' => 300],
            ]
        ];


        $data = fetch($datas, $num);

        $gift_ids = fetch($data, $num);

        $gift_id = $gift_ids[array_rand($gift_ids)];
        return $gift_id;
    }

    static function randomDiamond($total_value)
    {
        $total_amount = intval($total_value * 0.05);
        $gift_ids = [64 => 1, 65 => 5, 66 => 50, 47 => 188, 92 => 20, 93 => 10, 94 => 100];
        $gift_amount = array_count_values($gift_ids);
        $total_amount = $total_amount - $gift_amount;
        $amount = intval($total_amount / 100);
        return $amount;
    }

    /**
     * 爆礼物id 倒叙日志排行
     * @param $user_id
     * @param int $per_page
     * @return PaginationModel
     */
    static public function historiesTopList($user_id = null, $per_page = 10)
    {
        $conditions = [
            'order' => 'id desc',
        ];

        if (isPresent($user_id)) {
            $conditions = [
                'conditions' => ' user_id = :user_id:',
                'bind' => ['user_id' => $user_id],
                'order' => 'id desc'
            ];
        }

        $list = BoomHistories::findPagination($conditions, 1, $per_page);
        return $list;
    }


    /**
     * @return array
     */
    public function toSimpleJson()
    {

        if ($this->type != BOOM_HISTORY_GIFT_TYPE) {

            if ($this->type == BOOM_HISTORY_DIAMOND_TYPE) {
                $name = '钻石';
                $image_url = Backpacks::getDiamondImage();
            } else {
                $name = '金币';
                $image_url = Backpacks::getGoldImage();
            }

        } else {

            $target = Gifts::findFirstById($this->target_id);
            $image_url = $target->image_url;
            $name = $target->name;

        }

        return ['user' => $this->user->nickname, 'name' => $name, 'number' => $this->number, 'image_url' => $image_url];
    }

    static public function createBoomHistory($user, $opts)
    {
        $target_id = fetch($opts, 'target_id');
        $number = fetch($opts, 'number');
        $type = fetch($opts, 'type');
        $room_id = fetch($opts, 'room_id');

        $boom_history = new BoomHistories();

        if (isBlank($type) || isBlank($number)) {
            return [ERROR_CODE_FAIL, '参数错误', null];
        }

        if (isBlank($target_id) && $type == BOOM_HISTORY_GIFT_TYPE) {
            return [ERROR_CODE_FAIL, '参数错误', null];
        }

        $boom_history->user_id = $user->id;
        $boom_history->target_id = $target_id;
        $boom_history->type = $type;
        $boom_history->number = $number;
        $boom_history->room_id = $room_id;

        if ($boom_history->save()) {

            if ($type == BOOM_HISTORY_GIFT_TYPE) {

                if (isBlank($target_id)) {
                    return [ERROR_CODE_FAIL, '', null];
                }

                if (!Backpacks::createBackpack($user, ['target_id' => $target_id, 'number' => $number, 'type' => BACKPACK_GIFT_TYPE])) {
                    return [ERROR_CODE_FAIL, '加入背包失败', null];
                }

            } elseif ($type == BOOM_HISTORY_DIAMOND_TYPE) {

                $opts['remark'] = '爆礼物获得' . $number . '钻石';
                \AccountHistories::changeBalance($user, ACCOUNT_TYPE_IN_BOOM, $number, $opts);

            } elseif ($type == BOOM_HISTORY_GOLD_TYPE) {

                $opts['remark'] = '爆礼物获得' . $number . '金币';
                \GoldHistories::changeBalance($user, GOLD_TYPE_IN_BOOM, $number, $opts);

            }

            return [ERROR_CODE_SUCCESS, '', $boom_history];
        }
    }

    static public function getBoomDiamondOrGold($type, $number)
    {

        if ($type == BOOM_HISTORY_DIAMOND_TYPE) {
            $name = '钻石';
            $image = \Backpacks::getDiamondImage();
        } else {
            $name = '金币';
            $image = \Backpacks::getGoldImage();
        }

        // 嵌套array 返回接口固定结构数据
        $target = [

            [
                'name' => $name,
                'image_url' => $image,
                'number' => $number
            ]
        ];

        return $target;
    }
}