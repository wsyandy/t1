<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/3
 * Time: 下午8:10
 */
class Activities extends BaseModel
{
    static $STATUS = [STATUS_ON => '上架', STATUS_OFF => '下架', STATUS_FORBIDDEN => '禁用'];
    static $files = ['image' => APP_NAME . '/activities/image/%s'];
    static $PLATFORMS = ['client_ios' => '客户端ios', 'client_android' => '客户端安卓', 'weixin_ios' => '微信ios',
        'weixin_android' => '微信安卓', 'touch_ios' => 'H5ios', 'touch_android' => 'H5安卓'];
    static $TYPE = [ACTIVITY_TYPE_COMMON => '普通活动', ACTIVITY_TYPE_ROOM => '房间活动'];

    //抽奖奖品类型
    static $ACTIVITY_PRIZE_TYPE = [1 => '10000金币', 2 => '5位数幸运号', 3 => '1000金币', 4 => '6位数幸运号', 5 => '100金币',
        6 => '小马驹座驾', 7 => '神秘礼物', 8 => '兰博基尼座驾'];

    function getImageUrl()
    {
        $image = $this->image;
        if (isBlank($image)) {
            return '';
        }

        return StoreFile::getUrl($this->image);
    }

    function getImageSmallUrl()
    {
        $image = $this->image;

        if (isBlank($image)) {
            return '';
        }
        return StoreFile::getUrl($image) . '@!small';
    }


    function getStartText()
    {
        $start_at = $this->start_at;
        if (isBlank($start_at)) {
            return '';
        }
        return date("m月d日G时", $start_at);
    }

    function getEndText()
    {
        $end_at = $this->end_at;
        if (isBlank($end_at)) {
            return '';
        }
        return date("m月d日G时", $end_at);
    }

    function mergeJson()
    {
        return [
            'image_small_url' => $this->image_small_url,
            'platform_num' => $this->platform_num,
            'product_channel_num' => $this->product_channel_num
        ];
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'image_small_url' => $this->image_small_url
        ];
    }

    //是否存在 code
    function checkFields()
    {
        $fields = ['code'];

        foreach ($fields as $field) {
            $val = $this->$field;
            if (isBlank($val)) {
                return [ERROR_CODE_FAIL, $field . "不能为空"];
            }

            if ($this->hasChanged($field)) {
                $obj = self::findFirst([
                    'conditions' => "$field  = :$field: and type = :type:",
                    'bind' => [$field => $val, 'type' => $this->type]
                ]);

                if (isPresent($obj)) {
                    return [ERROR_CODE_FAIL, $field . "不能重复"];
                }
            }
        }
        return [ERROR_CODE_SUCCESS, ''];
    }


    static function findActivities($opts)
    {
        $platform = fetch($opts, 'platform');
        $product_channel_id = fetch($opts, 'product_channel_id');
        $type = fetch($opts, 'type', ACTIVITY_TYPE_COMMON);
        $conditions = [];
        $bind = [];

        $conditions[] = "type = :type: ";
        $bind['type'] = $type;

        $conditions[] = " (platforms like :platform: or platforms like '*' or platforms = '') ";
        $bind['platform'] = "%" . $platform . "%";

        $conditions[] = " (product_channel_ids like :product_channel_id: or product_channel_ids = '' or product_channel_ids is null) ";
        $bind['product_channel_id'] = '%,' . $product_channel_id . ',%';

        $conditions[] = ' status != :status: ';
        $bind['status'] = STATUS_OFF;

        $cond['conditions'] = implode(' and ', $conditions);
        $cond['bind'] = $bind;
        $cond['order'] = 'rank desc, id desc';

        debug($cond);

        $activities = Activities::findForeach($cond);

        return $activities;
    }

    static function findRoomActivities($user, $opts)
    {
        if (!$user->canShowRoomActivity()) {
            return [];
        }

        $activities = self::findActivities($opts);
        $res = [];

        if ($activities) {

            foreach ($activities as $activity) {

                $url = 'url://m/activities/' . $activity->code . '?id=' . $activity->id;

                if ('gold_eggs_draw' == $activity->code) {
                    $url = 'url://m/draw_histories/draw';
                }

                $activity = $activity->toSimpleJson();
                $activity['url'] = $url;
                $res[] = $activity;
            }

        }

        return $res;
    }

    //添加抽奖活动
    static function addLuckyDrawActivity($user_id, $opts = [])
    {
        $activity_id = 3;

        $activity = Activities::findFirstById($activity_id);

        //2018-0407 17点结束
        if (time() >= $activity->end_at) {
            info($user_id, $opts);
            return;
        }

        $amount = fetch($opts, 'amount');
        $gift_order_id = fetch($opts, 'gift_order_id');
        $key = 'lucky_draw_num_activity_id_' . $activity_id; //记录每个用户可以抽多少次
        $day_user_key = 'obtain_lucky_draw_activity_id_' . $activity_id . '_user' . date("Y-m-d"); //记录每天获得抽奖的人数
        $day_num_key = 'obtain_lucky_draw_activity_id_' . $activity_id . '_num' . date("Y-m-d"); //记录每天获得抽奖的次数

        $num = 0;

        switch ($amount) {
            case $amount == 998:
                $num = 3;
                break;

            case $amount == 2888:
                $num = 10;
                break;
            case $amount == 5888:
                $num = 22;
                break;
        }

        if ($gift_order_id) {

            $gift_order = GiftOrders::findFirstById($gift_order_id);
            $gift_num = $gift_order->gift_num;
            $gift_id = $gift_order->gift_id;

            if (isDevelopmentEnv()) {
                switch ($gift_id) {
                    case $gift_id == 44:
                        $num = 1 * $gift_num;
                        break;
                    case $gift_id == 19:
                        $num = 3 * $gift_num;
                        break;
                    case $gift_id == 15:
                        $num = 10 * $gift_num;
                        break;
                }
            } else {
                switch ($gift_id) {
                    case $gift_id == 25:
                        $num = 1 * $gift_num;
                        break;
                    case $gift_id == 14:
                        $num = 3 * $gift_num;
                        break;
                    case $gift_id == 13:
                        $num = 10 * $gift_num;
                        break;
                }
            }
        }

        info($user_id, $opts, $num);

        if ($num > 0) {

            $content = "恭喜您获得{$num}次抽奖机会，点侧边栏-活动-幸运大转盘即可抽奖，100%中奖赶紧去试试手气吧！";
            Chats::sendTextSystemMessage($user_id, $content);
            $db = Users::getUserDb();
            $db->zincrby($key, $num, $user_id);
            $db->zadd($day_user_key, time(), $user_id);
            $db->incrby($day_num_key, $num);
        }
    }

    function getObtainLuckyDrawActivityUser($day)
    {
        $db = Users::getUserDb();
        $obtain_day_user_key = 'obtain_lucky_draw_activity_id_' . $this->id . '_user' . $day; //记录每天获得抽奖的人数
        return $db->zcard($obtain_day_user_key);
    }


    function getObtainLuckyDrawActivityNum($day)
    {
        $db = Users::getUserDb();
        $obtain_day_num_key = 'obtain_lucky_draw_activity_id_' . $this->id . '_num' . $day; //记录每天获得抽奖的次数
        return intval($db->get($obtain_day_num_key));
    }

    function getLuckyDrawActivityUser($day)
    {
        $db = Users::getUserDb();
        $day_user_key = 'lucky_draw_activity_id_' . $this->id . '_user' . $day; //记录每天抽奖的人数
        return $db->zcard($day_user_key);
    }

    function getLuckyDrawActivityNum($day)
    {
        $db = Users::getUserDb();
        $day_num_key = 'lucky_draw_activity_id_' . $this->id . '_num' . $day; //记录每天抽奖的次数
        return intval($db->get($day_num_key));
    }

    function isForbidden()
    {
        return STATUS_FORBIDDEN == $this->status;
    }

    //已经结束
    function isOver()
    {
        if ($this->end_at && $this->end_at <= time()) {
            return true;
        }

        return false;
    }

    function productChannelNum()
    {
        $num = 0;
        if ($this->product_channel_ids) {
            $product_channel_ids = explode(',', $this->product_channel_ids);
            $product_channel_ids = array_filter(array_unique($product_channel_ids));
            $num = count($product_channel_ids);
        }

        return $num;
    }

    function platformNum()
    {
        $platforms = $this->platforms;
        $num = 'all';

        if ($platforms && '*' != $platforms) {
            $platforms = array_filter(explode(',', $platforms));
            $num = count($platforms);
        }

        return $num;
    }

    //礼物活动
    static function giftActivityStat($gift_order, $opts = [])
    {
        //礼物周榜活动
        self::giftWeekRankList($gift_order, $opts);
    }

    //礼物周榜活动
    static function giftWeekRankList($gift_order, $opts = [])
    {
        $gift_id = $gift_order->gift_id;

        debug($gift_id, $opts);

        $gift_ids = [59, 60, 61];
        if (isDevelopmentEnv()) {
            $gift_ids = [123, 124, 125];
        }

        if (in_array($gift_id, $gift_ids)) {

            $time = fetch($opts, 'time', time());
            $user_id = $gift_order->user_id;
            $amount = $gift_order->amount;
            $activity_start = '2018-04-23 18:00:00';

            if (isDevelopmentEnv()) {
                $activity_start = '2018-04-23 14:50:00';
            }

            if ($time < strtotime($activity_start) || $time > strtotime('2018-04-29 23:59:59')) {
                info("game_over", $gift_id, $user_id, $amount, $opts);
                return;
            }

            info($gift_id, $user_id, $amount, $opts);

            $start = fetch($opts, 'start', date("Ymd", beginOfWeek($time)));
            $end = fetch($opts, 'end', date("Ymd", endOfWeek($time)));
            $key = "week_charm_rank_list_gift_id_{$gift_id}_" . $start . "_" . $end;
            $user_db = Users::getUserDb();
            $user_db->zincrby($key, $amount, $user_id);
        }
    }

    //小黄瓜活动统计
    static function activityStat($gift_order, $opts)
    {
        $start = strtotime('2018-04-21 21:10:00');
        $end = strtotime('2018-04-21 21:20:00');
        $time = fetch($opts, 'time', time());
        $gift_id = 26;

        if (isDevelopmentEnv()) {
            $start = strtotime('2018-04-21 18:00:00');
            $end = strtotime('2018-04-21 19:25:59');
            $gift_id = 87;
        }

        if ($time >= $start && $time <= $end && $gift_id == $gift_order->gift_id) {
            $gift_num = $gift_order->gift_num;
            $sender_id = $gift_order->sender_id;
            $time = fetch($opts, 'time');
            $db = Users::getUserDb();
            $key = "give_diamond_by_cucumber_activity_gift_id_" . $gift_id . "start_" . $start . "_end_" . $end;
            $db->zincrby($key, $gift_num, $sender_id);
            info($start, $end, $key, $gift_num, $sender_id, $time);
        }
    }
}