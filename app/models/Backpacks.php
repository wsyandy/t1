<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:46
 */
class Backpacks extends BaseModel
{

    static $DIAMONDIMG = '/m/images/ico.png'; // 钻石图片

    static $GOLDIMG = '/m/images/gold.png'; // 金币图片


    /**
     * 背包礼物列表
     * @param $user
     * @param $opt
     * @return PaginationModel
     */
    static public function findListByUserId($user, $opt)
    {
        // search for where
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => [
                'user_id' => $user->id
            ],
            'order' => 'id desc'
        ];

        if (!empty($opt)) {
            $conditions['conditions'] .= ' and type = :type:';
            $conditions['bind']['type'] = $opt['type'];
        }

        // no page
        $page = 1;
        $per_page = 100;

        $list = \Backpacks::findPagination($conditions, $page, $per_page);
        return $list;
    }


    /**
     * @desc 爆礼物房间流水值
     */
    static public function turnoverValue()
    {
        $line = 1000; // 初始值
        $total = 10000; // 流水上线
        $rooms = Rooms::dayStatRooms();
        $rooms = $rooms->toJson('rooms');

        $backpack = new Backpacks();

        foreach ($rooms['rooms'] as $value) {
            $room = Rooms::findFirstById($value['id']);
            $noun = $room->getDayIncome(date('Ymd'));

            $backpack->pushClientAboutBoom($total, $noun);
        }
    }


    public function pushClientAboutBoom($total_value, $cur_value, $room_id)
    {
        $body = array(
            'action' => 'blasting_gift',
            'blasting_gift' => [
                'expire_at' => time(),
                'url' => 'url://m/backpacks',
                'svga_image_url' => '',
                'total_value' => $total_value,
                'current_value' => $cur_value
            ]
        );

        $room = Rooms::findFirstById($room_id);
        $room->push($body);
    }


    /**
     * 数据写入背包
     * @param $user
     * @param $target 当类型为钻石或金币时，值为0
     * @param $number
     * @param int $type
     * @param int $status
     * @return bool
     */
    static public function createTarget($user, $target, $number, $type = BACKPACK_GIFT_TYPE, $status = STATUS_ON)
    {
        if (isDevelopmentEnv()) {
            $user = (object)['id' => 1];
        }

        $backpack = new \Backpacks();

        // 礼物类型
        if ($type == BACKPACK_GIFT_TYPE) {

            $conditions = [
                'conditions' => 'user_id = :user_id: and target_id = :target_id:',
                'bind' => [
                    'user_id' => $user->id,
                    'target_id' => $target
                ]
            ];

            // 已经爆过的礼物更新数量
            if (Backpacks::count($conditions) >= 1) {
                $item = Backpacks::findByConditions([
                    'user_id' => $user->id,
                    'target_id' => $target
                ]);
                $item = $item->toJson('backpack');
                $id = $item['backpack'][0]['id'];
                $backpack->id = $id;
                $backpack->increase('number', $number);
                return true;
            }
        }

        // 新增礼物进背包
        $backpack->user_id = $user->id;
        $backpack->target_id = $target;
        $backpack->number = $number;
        $backpack->type = $type;
        $backpack->status = $status;
        $backpack->created_at = time();
        $backpack->updated_at = time();
        $backpack->save();
        return true;
    }


    /**
     * 爆除礼物外的其他东西
     * @param int $type
     * @return array
     */
    static public function boomValue($type)
    {
        if ($type == BACKPACK_DIAMOND_TYPE) {

            $target[] = [
                'id' => 0,
                'name' => '钻石',
                'image_url' => \Backpacks::getDiamondImage(),
                'number' => mt_rand(10, 1000)
            ];
        } else {

            $target[] = [
                'id' => 0,
                'name' => '金币',
                'image_url' => \Backpacks::getGoldImage(),
                'number' => mt_rand(500, 2000)
            ];
        }
        return $target;
    }


    /**
     * @return array
     */
    public function toSimpleJson()
    {
        if ($this->type == BACKPACK_GIFT_TYPE) {
            // 礼物背包
            $gift = $this->getGift();

            return array(
                'id' => $this->id,
                'number' => $this->number,
                'image_url' => $gift->getImageUrl(),
                'name' => $gift->name,
                'svga_image_name' => $gift->getSvgaImageName(),
                'render_type' => $gift->render_type,
                'svga_image_url' => $gift->getImageSmallUrl(),
                'expire_day' => $gift->expire_day,
                'show_rank' => $gift->show_rank
            );
        }
        return array(
            'id' => $this->id,
            'number' => $this->number
        );

    }


    /**
     * 返回礼物对象
     * @return object
     */
    public function getGift()
    {
        $gift = Gifts::findFirstById($this->target_id);
        return $gift;
    }


    /**
     * @return string
     */
    static function getDiamondImage()
    {
        return self::$DIAMONDIMG;
    }


    /**
     * @return string
     */
    static function getGoldImage()
    {
        return self::$GOLDIMG;
    }
}