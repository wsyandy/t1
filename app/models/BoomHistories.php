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

    static $TYPE = [1 => '礼物', 2 => '钻石', 3 => '金币'];
    static $start_value = 500;
    static $total_value = 1000; // 爆礼物总值
    static $boom_SVGA = 'http://test.momoyuedu.cn/m/images/boom_animation_1.svga';

    public function getGiftName()
    {
        if ($this->target_id == 0) {
            return null;
        }
        $gifts = Gifts::findFirstById($this->target_id);
        return $gifts->name;
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