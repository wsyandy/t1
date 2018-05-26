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
     * 数据写入背包
     * @param $user_id
     * @param $target_id 当类型为钻石或金币时，值为0
     * @param $number
     * @param int $type
     * @param int $status
     * @return bool
     */
    static function createBackpack($user, $opts = [])
    {
        $type = fetch($opts, 'type');
        $number = fetch($opts, 'number');
        $target_id = fetch($opts, 'target_id');
        $user_id = $user->id;

        if (!$type || !$number || !$target_id) {
            return false;
        }

        $conditions = [
            'conditions' => 'user_id = :user_id: and target_id = :target_id: and type = :type:',
            'bind' => [
                'user_id' => $user_id,
                'target_id' => $target_id,
                'type' => $type
            ]
        ];

        $backpack = Backpacks::findFirst($conditions);

        if ($backpack) {

            $backpack->number += $number;

            if ($backpack->update()) {
                return true;
            }

            return false;
        }

        $backpack = new \Backpacks();

        // 新增礼物进背包
        $backpack->user_id = $user_id;
        $backpack->target_id = $target_id;
        $backpack->number = $number;
        $backpack->type = $type;
        $backpack->status = STATUS_ON;
        $backpack->save();

        if ($backpack->save()) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function toSimpleJson()
    {
        if ($this->type == BACKPACK_GIFT_TYPE) {
            // 礼物背包
            $gift = $this->getGift();

            return [
                'id' => $this->id,
                'number' => $this->number,
                'image_url' => $gift->getImageUrl(),
                'name' => $gift->name,
                'svga_image_name' => $gift->getSvgaImageName(),
                'render_type' => $gift->render_type,
                'svga_image_url' => $gift->getImageSmallUrl(),
                'expire_day' => $gift->expire_day,
                'show_rank' => $gift->show_rank
            ];
        }

        return [
            'id' => $this->id,
            'number' => $this->number
        ];
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