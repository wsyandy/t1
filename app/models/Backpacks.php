<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:46
 */
class Backpacks extends BaseModel
{

    static $DIAMOND_IMG = '/m/images/ico.png'; // 钻石图片

    static $GOLD_IMG = '/m/images/gold.png'; // 金币图片

    static $boom_SVGA = 'http://test.momoyuedu.cn/m/images/boom_animation_1.svga';

    static $total_value = 50000; // 爆礼物总值

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
            'conditions' => 'user_id = :user_id: and number > :number:',
            'bind' => [
                'user_id' => $user->id,
                'number' => 0
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
     * Task任务
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

            if ($noun >= $line) {
                $backpack->pushClientAboutBoom($total, $noun, $value['id']);
            }
        }
    }


    /**
     * 爆礼物推送
     * @param $total_value
     * @param $cur_value
     * @param $room_id
     */
    public function pushClientAboutBoom($total_value, $cur_value, $room_id)
    {
        $body = array(
            'action' => 'blasting_gift',
            'blasting_gift' => [
                'expire_at' => self::getExpireAt($room_id),
                'url' => 'url://m/backpacks',
                'svga_image_url' => self::getSvgaImageUrl(),
                'total_value' => (int)$total_value,
                'current_value' => (int)$cur_value
            ]
        );

        if (isDevelopmentEnv() && $room_id == 137039) {
            $body['room_id'] = 137039;
            Chats::sendSystemMessage(41792, CHAT_CONTENT_TYPE_TEXT, json_encode($body));
        }
        $room = Rooms::findFirstById($room_id);
        $room->push($body);
    }


    /**
     * 数据写入背包
     * @param $user_id
     * @param $target_id 当类型为钻石或金币时，值为0
     * @param $number
     * @param int $type
     * @param int $status
     * @return bool
     */
    static public function createTarget($user_id, $target_id, $number, $type, $status = STATUS_ON)
    {
        $backpack = new \Backpacks();

        // 礼物类型
        if ($type == BACKPACK_GIFT_TYPE) {

            $conditions = [
                'conditions' => 'user_id = :user_id: and target_id = :target_id:',
                'bind' => [
                    'user_id' => $user_id,
                    'target_id' => $target_id
                ]
            ];

            // 已经爆过的礼物更新数量
            if (Backpacks::count($conditions) >= 1) {
                $item = Backpacks::findByConditions([
                    'user_id' => $user_id,
                    'target_id' => $target_id
                ]);
                $item = $item->toJson('backpack');
                $id = $item['backpack'][0]['id'];
                $backpack->id = $id;
                $backpack->increase('number', $number);
                return true;
            }
        }

        // 新增礼物进背包
        $backpack->user_id = $user_id;
        $backpack->target_id = $target_id;
        $backpack->number = $number;
        $backpack->type = $type;
        $backpack->status = $status;
        $backpack->created_at = time();
        $backpack->updated_at = time();
        $backpack->save();
        return true;
    }


    /**
     * 爆钻石或金币
     * @param int $type
     * @return array
     */
    static public function getBoomDiamondOrGold($type)
    {

        if ($type == BACKPACK_DIAMOND_TYPE) {

            $name = '钻石';
            $image = \Backpacks::getDiamondImage();
            $number = mt_rand(10, 1000);
        } else {

            $name = '金币';
            $image = \Backpacks::getGoldImage();
            $number = mt_rand(500, 2000);
        }

        // 嵌套array 返回接口固定结构数据
        $target = array(
            [
                'id' => 0,
                'name' => $name,
                'image_url' => $image,
                'number' => $number
            ]
        );
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
        return self::$DIAMOND_IMG;
    }


    /**
     * @return string
     */
    static function getGoldImage()
    {
        return self::$GOLD_IMG;
    }


    /**
     * @return string
     */
    static function getSvgaImageUrl()
    {
        return self::$boom_SVGA;
    }


    /**
     * @return string
     */
    static function getTotalBoomValue()
    {
        return self::$total_value;
    }


    /**
     * @param $room_id
     * @return false|int
     */
    static function getExpireAt($room_id)
    {
        $cache = self::getHotWriteCache();
        $cache_room_name = self::getBoomRoomCacheName($room_id);
        $time = $cache->get($cache_room_name);

        if (empty($time)) {
            return 0;
        }

        $time = strtotime('+3 minutes', $time);
        return $time;
    }


    /**
     * @param $room_id
     * @return string
     */
    static function getBoomRoomCacheName($room_id)
    {
        return 'boom_target_room:'.$room_id;
    }


    function isGift()
    {
        return BACKPACK_GIFT_TYPE == $this->type;
    }

    function sendGift($user, $user_id, $gift_num, $opt = [])
    {
        $receiver_ids = explode(',', $user_id);

        if (!$this->isGift()) {
            return [ERROR_CODE_FAIL, '赠送失败', null];
        }

        $gift = $this->getGift();

        if (!$gift) {
            return [ERROR_CODE_FAIL, '赠送失败', null];
        }

        // 赠送的数量
        $send_number = count($receiver_ids) * $gift_num;

        if ($this->number < $send_number) {
            return [ERROR_CODE_FAIL, '赠送失败', null];
        }

        // 背包减去数量
        $this->number = $this->number - $send_number;
        $give_result = $this->save();

        $notify_type = fetch($opt, 'notify_type');

        \GiftOrders::asyncCreateGiftOrder($user->id, $receiver_ids, $gift->id);

        if ($give_result) {

            $notify_data = \ImNotify::generateNotifyData(
                'gifts',
                'give',
                $notify_type,
                [
                    'gift' => $gift,
                    'gift_num' => $gift_num,
                    'sender' => $user,
                    'user_id' => $receiver_ids[0]
                ]
            );

            $gift_amount = count($receiver_ids) * $gift->amount;
            $res = array_merge($notify_data, ['diamond' => $user->diamond, 'gold' => $user->gold, 'total_amount' => $gift_amount, 'pay_type' => $gift->pay_type]);

            $error_reason = "赠送成功";

            return [ERROR_CODE_SUCCESS, $error_reason, $res];
        } else {
            return [ERROR_CODE_FAIL, '赠送失败', null];
        }
    }
}