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

    //活动类型
    static $ACTIVITY_TYPE = ['gift_minutes_list' => '礼物分钟榜单', 'gift_charm_day_list' => '礼物魅力日榜单',
        'gift_charm_week_list' => '礼物魅力周榜单', 'gif_wealth_day_list' => '礼物财富日榜单', 'gift_wealth_week_list' => '礼物财富周榜单'];

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
//            if (isBlank($val)) {
//                return [ERROR_CODE_FAIL, $field . "不能为空"];
//            }

//            if ($this->hasChanged($field)) {
//                $obj = self::findFirst([
//                    'conditions' => "$field  = :$field: and type = :type:",
//                    'bind' => [$field => $val, 'type' => $this->type]
//                ]);
//
////                if (isPresent($obj)) {
////                    return [ERROR_CODE_FAIL, $field . "不能重复"];
////                }
//            }
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

        $activities = Activities::find($cond);

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
                    $url = 'url://m/draw_histories';
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
        $time = fetch($opts, 'time');

        $gift_id = $gift_order->gift_id;
        $cond = [
            'conditions' => 'type = :type: and gift_ids like :gift_ids: and status = :status: and start_at <= :start: and end_at >= :end:',
            'bind' => ['type' => ACTIVITY_TYPE_COMMON, 'gift_ids' => "%," . $gift_id . ",%", 'status' => STATUS_ON, 'start' => $time, 'end' => $time]
        ];

        $activities = Activities::find($cond);

        if (count($activities) > 0) {

            debug($cond, count($activities));

            foreach ($activities as $activity) {

                $key = $activity->getStatKey($gift_id);
                $opts['key'] = $key;

                debug($key, $activity->activity_type);

                if ($activity->isGiftMinuteList()) {
                    self::activityGiftListStat($gift_order, $opts);
                    continue;
                }

                if ($activity->isGiftCharmWeekList() || $activity->isGiftWealthWeekList()) {
                    self::giftWeekRankListStat($activity, $gift_order, $opts);
                    continue;
                }
            }

        } else {
            debug($gift_order->id, $cond, $opts);
        }
    }

    function isGiftCharmDayList()
    {
        return 'gift_charm_day_list' == $this->activity_type;
    }

    function isGiftCharmWeekList()
    {
        return 'gift_charm_week_list' == $this->activity_type;
    }

    function isGiftWealthDayList()
    {
        return 'gift_wealth_day_list' == $this->activity_type;
    }

    function isGiftWealthWeekList()
    {
        return 'gift_wealth_week_list' == $this->activity_type;
    }

    function isGiftMinuteList()
    {
        return 'gift_minutes_list' == $this->activity_type;
    }

    //礼物周榜活动
    static function giftWeekRankListStat($activity, $gift_order, $opts = [])
    {
        $key = fetch($opts, 'key');
        $gift_id = $gift_order->gift_id;

        $amount = $gift_order->amount;
        $user_id = $gift_order->user_id;
        $sender_id = $gift_order->sender_id;

        $db = Users::getUserDb();

        if ($activity->isGiftCharmWeekList()) {
            $db->zincrby($key, $amount, $user_id);
        } else {
            $db->zincrby($key, $amount, $sender_id);
        }
    }

    static function activityGiftListStat($gift_order, $opts)
    {
        $key = fetch($opts, 'key');
        $gift_id = $gift_order->gift_id;

        if ($gift_id) {
            $gift_num = $gift_order->gift_num;
            $sender_id = $gift_order->sender_id;
            $time = fetch($opts, 'time');
            $db = Users::getUserDb();
            $db->zincrby($key, $gift_num, $sender_id);
        }
    }

    function getStatKey($gift_id)
    {
        $key = '';

        if ($this->isGiftMinuteList()) {
            $key = "gift_minutes_list_activity_stat_gift_id_" . $gift_id . "_start_" . $this->start_at . "_end_" . $this->end_at;
        }

        if ($this->isGiftCharmDayList()) {
            $key = "gift_charm_day_list_activity_stat_gift_id_" . $gift_id . "_start_" . $this->start_at . "_end_" . $this->end_at;
        }

        if ($this->isGiftCharmWeekList()) {
            $start_at = date("Ymd", beginOfWeek($this->start_at));
            $end_at = date("Ymd", endOfWeek($this->start_at));
            $key = "gift_charm_week_list_activity_stat_gift_id_" . $gift_id . "_start_" . $start_at . "_end_" . $end_at;
        }

        if ($this->isGiftWealthDayList()) {
            $key = "gift_wealth_day_list_activity_stat_gift_id_" . $gift_id . "_start_" . $this->start_at . "_end_" . $this->end_at;
        }

        if ($this->isGiftWealthWeekList()) {
            $start_at = date("Ymd", beginOfWeek($this->start_at));
            $end_at = date("Ymd", endOfWeek($this->start_at));
            $key = "gift_wealth_week_list_activity_stat_gift_id_" . $gift_id . "_start_" . $start_at . "_end_" . $end_at;
        }

        return $key;
    }

    function getRanListUsers($gift_id, $num)
    {
        $key = $this->getStatKey($gift_id);
        $user_db = \Users::getUserDb();
        $datas = $user_db->zrevrange($key, 0, $num, 'withscores');
        $data = [];
        $user_ids = [];

        foreach ($datas as $user_id => $gift_num) {
            $data[$user_id] = $gift_num;
            $user_ids[] = $user_id;
        }

        $users = \Users::findByIds($user_ids);

        foreach ($users as $user) {
            $user->gift_num = $data[$user->id];
        }

        return $users;
    }

    function getGiftIdsArray()
    {
        $gift_ids = trim($this->gift_ids, ',');

        if (!$gift_ids) {
            return [];
        }

        $gift_ids = explode(",", $gift_ids);

        return $gift_ids;
    }

    static function getLastActivityRankListUsers($last_opts)
    {
        $last_gifts = fetch($last_opts, 'last_gifts');
        $last_activity = fetch($last_opts, 'last_activity');
        $last_activity_start = fetch($last_opts, 'last_activity_start');
        $last_activity_end = fetch($last_opts, 'last_activity_end');

        $last_activity_rank_list_users = [];
        foreach ($last_gifts as $last_gift) {

            if ($last_activity->id < 16) {
                $key = "week_charm_rank_list_gift_id_" . $last_gift->id . "_" . $last_activity_start . "_" . $last_activity_end;
            } else {
                $key = $last_activity->getStatKey($last_gift->id);
            }

            $users = \Users::findFieldRankListByKey($key, 'charm', 1, 1);

            if (isset($users[0])) {
                $last_activity_rank_list_users[] = $users[0]->toRankListJson();
            }
        }

        return $last_activity_rank_list_users;
    }

    static function getLastWeekCharmRankListUser($opts)
    {
        $last_week_charm_rank_list_key = Users::generateFieldRankListKey('week', 'charm', $opts);
        $users = Users::findFieldRankListByKey($last_week_charm_rank_list_key, 'charm', 1, 1);

        $last_week_charm_rank_list_user = [];

        if (isset($users[0])) {
            $last_week_charm_rank_list_user = $users[0]->toRankListJson();
        }

        return $last_week_charm_rank_list_user;

    }
}