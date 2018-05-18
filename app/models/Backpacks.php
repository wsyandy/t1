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


    /**
     * 背包礼物列表
     * @param $user
     * @param $opt
     * @return PaginationModel
     */
    static public function findListByUserId($user, $opt)
    {

        $page = 1;
        $per_page = 100;

        $conditions = [
            'conditions' => 'user_id = :user_id: and number > :number:',
            'bind' => [
                'user_id' => $user->id,
                'number' => 0
            ],
            'order' => 'id desc'
        ];

        $type = fetch($opt, 'type');
        if ($type) {
            $conditions['conditions'] .= ' and type = :type:';
            $conditions['bind']['type'] = $type;
        }

        $list = \Backpacks::findPagination($conditions, $page, $per_page);
        return $list;
    }


    /**
     * @param $user_id
     * @param $target_id
     * @param $number
     * @param $type
     * @return array
     */
    static public function doCreate($user_id, $target_id, $number, $type)
    {
        $prize = array(
            'target_id' => $target_id,
            'type' => $type,
            'number' => $number
        );

        $boom_histories = new BoomHistories();
        $boom_histories->createBoomHistories($user_id, $target_id, $type, $number);

        if ($type == BACKPACK_GIFT_TYPE) {

            if (empty($target_id))
                return [ERROR_CODE_FAIL, '', null];


            if (!self::createTarget($user_id, $target_id, $number, BACKPACK_GIFT_TYPE))
                return [ERROR_CODE_FAIL, '加入背包失败', null];

        } elseif ($type == BACKPACK_DIAMOND_TYPE) {

            $opts['remark'] = '爆礼物获得' . $number . '钻石';
            \AccountHistories::changeBalance($user_id, ACCOUNT_TYPE_IN_BOOM, $number, $opts);

        } elseif ($type == BACKPACK_GOLD_TYPE) {

            $opts['remark'] = '爆礼物获得' . $number . '金币';
            \GoldHistories::changeBalance($user_id, GOLD_TYPE_IN_BOOM, $number, $opts);

        }

        return [ERROR_CODE_SUCCESS, '', $prize];
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
     * 记录爆礼物开始时间
     * @param $room_id
     * @return string
     */
    static function generateBoomRoomSignKey($room_id)
    {
        return 'boom_target_room_' . $room_id;
    }


    /**
     * 记录爆礼物抽中的奖品
     * @param $user_id
     * @param $room_id
     * @return string
     */
    static public function generateBoomUserSignKey($user_id, $room_id)
    {
        return 'boom_target_room_' . $room_id . '_user_' . $user_id;
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

        if ($this->save()) {

            $notify_type = fetch($opt, 'notify_type');

            \GiftOrders::asyncCreateGiftOrder($user->id, $receiver_ids, $gift->id);

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