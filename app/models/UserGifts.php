<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 上午10:48
 */
class UserGifts extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Gifts
     */
    private $_gift;

    static $STATUS = [STATUS_ON => '使用中', STATUS_OFF => '未使用'];

    function afterUpdate()
    {
        if ($this->hasChanged('status') && STATUS_ON == $this->status && GIFT_TYPE_CAR == $this->gift_type) {

            $user_gifts = UserGifts::find(['conditions' => 'status = :status: and user_id = :user_id: and id != :id: and gift_type = :gift_type:',
                'bind' => ['status' => STATUS_ON, 'id' => $this->id, 'user_id' => $this->user_id, 'gift_type' => GIFT_TYPE_CAR]
            ]);

            if (count($user_gifts) > 0) {
                foreach ($user_gifts as $user_gift) {

                    debug($user_gift->id, $user_gift->user->sid);

                    $user_gift->status = STATUS_OFF;
                    $user_gift->update();
                }
            }

        }
    }

    function isExpired()
    {
        return $this->expire_at <= time();
    }

    static function updateGiftNum($gift_order_id)
    {
        if (is_numeric($gift_order_id)) {
            $gift_order = \GiftOrders::findFirstById($gift_order_id);
        } else {
            $gift_order = $gift_order_id;
        }

        if (isBlank($gift_order) || !$gift_order->isSuccess()) {
            return false;
        }

        $lock_key = "user_gift_lock_" . $gift_order->user_id . '_' . $gift_order->gift_id;
        $lock = tryLock($lock_key);

        $user_gift = \UserGifts::findFirstOrNew(['user_id' => $gift_order->user_id, 'gift_id' => $gift_order->gift_id]);
        $gift = $gift_order->gift;

        $gift_amount = $gift->amount;
        $gift_num = $gift_order->gift_num;

        $user_gift->gift_id = $gift->id;
        $user_gift->name = $gift->name;
        $user_gift->amount = $gift_amount;
        $user_gift->num = $gift_num + intval($user_gift->num);
        $user_gift->total_amount = $gift_amount * $gift_num + intval($user_gift->total_amount);
        $user_gift->pay_type = $gift->pay_type;
        $user_gift->gift_type = $gift->type;
        $user_gift->save();

        $user_gift->statSilentUserSendGiftNum($gift_order);

        unlock($lock);
        return $user_gift;
    }

    static function updateGiftExpireAt($gift_order_id, $opts = [])
    {
        $content = fetch($opts, 'content');
        $expire_day = fetch($opts, 'expire_day', 0);

        if (is_numeric($gift_order_id)) {
            $gift_order = \GiftOrders::findFirstById($gift_order_id);
        } else {
            $gift_order = $gift_order_id;
        }

        if (isBlank($gift_order) || !$gift_order->isSuccess()) {
            return false;
        }

        $lock_key = "user_gift_lock_" . $gift_order->user_id . '_' . $gift_order->gift_id;
        $lock = tryLock($lock_key);

        $exist_user_gift = $gift_order->user->getUserCarGift();
        $user_gift = \UserGifts::findFirstOrNew(['user_id' => $gift_order->user_id, 'gift_id' => $gift_order->gift_id]);
        $gift = $gift_order->gift;

        $gift_amount = $gift->amount;
        $gift_num = $gift_order->gift_num;

        $user_gift->gift_id = $gift->id;
        $user_gift->name = $gift->name;
        $user_gift->amount = $gift_amount;
        $user_gift->num = $gift_num;
        $user_gift->total_amount = $gift_amount * $gift_num + intval($user_gift->total_amount);
        $user_gift->pay_type = $gift->pay_type;
        $user_gift->gift_type = $gift->type;

        if (!$exist_user_gift) {
            $user_gift->status = STATUS_ON;
        }

        if (!$expire_day) {
            $expire_day = $gift->expire_day ;
        }

        if (isDevelopmentEnv()) {
            if ($user_gift->expire_at > time()) {
                $user_gift->expire_at += $expire_day * 60 * 2;
            } else {
                $user_gift->expire_at = time() + $expire_day * 60 * 2;
            }
        } else {
            if ($user_gift->expire_at > time()) {
                $user_gift->expire_at += $expire_day * 86400;
            } else {
                $user_gift->expire_at = time() + $expire_day * 86400;
            }
        }

        $user_gift->save();
        $user_gift->statSilentUserSendGiftNum($gift_order);

        if ($gift_order->sender_id != $gift_order->user_id) {
            if (!$content) {
                $content = $gift_order->sender->nickname . "送您了一个炫酷的" . $gift_order->name . "座驾给你快去车库查看,君临各大房间吧~ ";
            }
            Chats::sendTextSystemMessage($gift_order->user_id, $content);
        }

        unlock($lock);
        return $user_gift;
    }


    static function findListByUserId($user_id, $page, $per_page)
    {
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];
        return \UserGifts::findPagination($conditions, $page, $per_page);
    }

    function toJson()
    {
        $gift = $this->gift;

        return [
            'gift_id' => $gift->id,
            'name' => $gift->name,
            'amount' => $this->amount,
            'pay_type' => $this->pay_type,
            'image_url' => $gift->image_url,
            'image_small_url' => $gift->image_small_url,
            'image_big_url' => $gift->image_big_url,
            'dynamic_image_url' => $gift->dynamic_image_url,
            'num' => $this->num,
            'pay_type_text' => $this->getPayTypeText(),
            'gift_type_text' => $this->getGiftTypeText(),
            'expire_day' => $this->expire_day,
            'svga_image_name' => $gift->svga_image_name,
            'render_type' => $gift->render_type,
            'svga_image_url' => $gift->svga_image_url,
            'status' => $this->status,
            'is_expired' => $gift->isExpired()
        ];
    }

    function toSimpleJson()
    {
        return [
            'image_url' => $this->gift_image_url,
            'image_small_url' => $this->gift_image_small_url,
            'image_big_url' => $this->gift_image_big_url,
            'dynamic_image_url' => $this->gift_dynamic_image_url,
            'svga_image_name' => $this->gift_svga_image_name,
            'render_type' => $this->gift_render_type,
            'svga_image_url' => $this->gift_svga_image_url,
            'show_rank' => $this->gift_show_rank,
            'status' => $this->status,
            'expire_time' => $this->gift->expire_time,
            'gift_type' => $this->gift_type,
            'notice_content' => $this->notice_content
        ];
    }

    function getNoticeContent()
    {
        $text_content = $this->gift->text_content;

        if (isBlank($text_content)) {
            return "<p style='font-size: 14px;text-align: center' ><span style = 'color: yellow' >" . $this->user_nickname .
                "</span><span style = 'color: white' >" . "骑着" . "</span ><b style = 'color: white' >" . $this->gift_name .
                "</b ><span style = 'color: white' > 进来了</span ></p >";
        }

        $user_name = "<p style='font-size: 14px;text-align: center' ><span style = 'color: yellow' >" . $this->user_nickname . "</span><span style = 'color: white' >";

        $gift_name = "</b ><span style = 'color: white' >" . $this->gift_name . "</b ><span style = 'color: white' >";

        $data = str_replace(['%user_name%', '%gift_name%'], [$user_name, $gift_name,], $text_content);

        return $data . "</span ></p >";
    }

    function expireDay()
    {
        $expire_at = $this->expire_at;
        $expire_time = $expire_at - time();

        if ($expire_time < 1) {
            return 0;
        }

        $time = 86400;

        if (isDevelopmentEnv()) {
            $time = 60 * 2;
        }

        $day = ceil($expire_time / $time);

        return $day;
    }

    //统计沉默用户送礼物个数
    function statSilentUserSendGiftNum($gift_order)
    {
        $sender = $gift_order->sender;

        if ($sender->isActive()) {
            return;
        }

        $current_room = $sender->current_room;
        $hot_cache = self::getHotWriteCache();

        if ($current_room) {

            $amount_day_key = $current_room->getStatGiftAmountKey();
            $amount_hour_key = $current_room->getStatGiftAmountKey(false);
            $user_num_day_key = $current_room->getStatGiftUserNumKey();
            $user_num_hour_key = $current_room->getStatGiftUserNumKey(false);
            $send_gift_rooms_key = date("Ymd") . "_user_send_gift_rooms_user_id_" . $sender->id;

            $hot_cache->incrby($amount_day_key, $gift_order->amount);
            $hot_cache->incrby($amount_hour_key, $gift_order->amount);
            $hot_cache->zadd($user_num_day_key, time(), $sender->id);
            $hot_cache->zadd($user_num_hour_key, time(), $sender->id);
            $hot_cache->zadd($send_gift_rooms_key, time(), $current_room->id);

            $day_expire = endOfDay() + 60 - time();
            $hour_expire = endOfHour() + 60 - time();

            $hot_cache->expire($amount_day_key, $day_expire);
            $hot_cache->expire($amount_hour_key, $hour_expire);
            $hot_cache->expire($user_num_day_key, $day_expire);
            $hot_cache->expire($user_num_hour_key, $hour_expire);
            $hot_cache->expire($send_gift_rooms_key, $day_expire);
        }
    }

    function getGiftTypeText()
    {
        return fetch(Gifts::$TYPE, $this->gift_type);
    }

    function getPayTypeText()
    {
        return fetch(Gifts::$PAY_TYPE, $this->pay_type);
    }

    static function searchCarGifts($user_id)
    {
        $conds = [
            'conditions' => 'user_id = :user_id: and gift_type = :gift_type: and expire_at > :expire_at:',
            'bind' => ['user_id' => $user_id, 'gift_type' => GIFT_TYPE_CAR, 'expire_at' => time()],
            'order' => 'amount desc'
        ];

        if (isDevelopmentEnv()) {

            $conds = [
                'conditions' => 'user_id = :user_id: and gift_type = :gift_type:',
                'bind' => ['user_id' => $user_id, 'gift_type' => GIFT_TYPE_CAR],
                'order' => 'amount desc'
            ];

        }

        $user_gifts = UserGifts::find($conds);

        $res = [];

        foreach ($user_gifts as $user_gift) {
            $res[] = $user_gift->toJson();
        }

        return $res;
    }

    static function searchCommonGifts($user_id)
    {
        $conds = [
            'conditions' => 'user_id = :user_id: and gift_type = :gift_type:',
            'bind' => ['user_id' => $user_id, 'gift_type' => GIFT_TYPE_COMMON],
            'order' => 'amount desc'
        ];

        $user_gifts = UserGifts::find($conds);

        $res = [];

        foreach ($user_gifts as $user_gift) {
            $res[] = $user_gift->toJson();
        }

        return $res;
    }
}