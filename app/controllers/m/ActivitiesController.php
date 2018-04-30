<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/30
 * Time: 上午11:46
 */

namespace m;
class ActivitiesController extends BaseController
{
    function indexAction()
    {
        $product_channel_id = $this->currentProductChannelId();
        $platform = $this->params('pf');
        $sid = $this->params('sid');
        $code = $this->params('code');

        $platform = 'client_' . $platform;

        $activities = \Activities::findActivities(['product_channel_id' => $product_channel_id, 'platform' => $platform]);

        foreach ($activities as $activity) {
            if ($activity->isGiftCharmWeekList()) {
                $file_name = 'gift_charm_week' . date("Ymd", $activity->start_at) . 'rank_activity';
                $file_path = APP_ROOT . 'app/views/m/activities/' . $file_name . '.volt';

                if (file_exists($file_path)) {
                    $activity->code = $file_name;
                }
            }
        }

        $this->view->sid = $sid;
        $this->view->code = $code;
        $this->view->activities = $activities;
        $this->view->title = "活动";
    }

    function weekRankActivityAction()
    {
        $id = $this->params('id');
        $activity = \Activities::findFirstById($id);
        if (isPresent($activity)) {
            $start_at = $activity->start_at;
            $end_at = $activity->end_at;

            //上周排行榜开始时间
            //$last_stat_at = strtotime("last monday", time() - 86400 * 6);
            //$last_end_at = $last_stat_at + 86400 * 6;

            $last_stat_at = strtotime("2018-04-02");
            $last_end_at = strtotime("2018-04-08");

            $lats_start = date("Ymd", $last_stat_at);
            $last_end = date("Ymd", $last_end_at);

            $product_channel_id = $this->currentProductChannelId();

            $wealth_users = \Users::findFieldRankList('week', 'wealth', 1, 3);
            $charm_users = \Users::findFieldRankList('week', 'charm', 1, 3);

            if (isDevelopmentEnv()) {
                $gifts = \Gifts::findByIds([61, 49, 52]);
            } else {
                $gifts = \Gifts::findByIds([33, 32, 31]);
            }

            $this->view->start_text = date("Y年m月d日H点", $start_at);
            $this->view->end_text = date("Y年m月d日H点", $end_at);

            $this->view->wealth_users = $wealth_users;
            $this->view->charm_users = $charm_users;

            $this->view->gifts = $gifts;

            $this->view->last_start_text = date("Y.m.d", $last_stat_at);
            $this->view->last_end_text = date("Y.m.d", $last_end_at);

            $this->view->give_time = date("Y年m月d日", $last_end_at + 86400 * 2);

            if ($id > 0) {
                $this->pick("m/activities/week_rank_activity{$id}");
            }
        }
        $this->view->official_id = 100101;

        $this->view->title = "周榜专属奖励";
    }

    //清明节活动
    function qingMingActivityAction()
    {
        $start = 20180405;
        if (isDevelopmentEnv()) {
            $start = 20180404;
        }

        $end = 20180407;

        $db = \Users::getUserDb();

        $charm_key = "qing_ming_activity_charm_list_" . $start . "_" . $end;
        $wealth_key = "qing_ming_activity_wealth_list_" . $start . "_" . $end;

        $charm_rank_list = $db->zrevrange($charm_key, 0, 2, 'withscores');
        $wealth_rank_list = $db->zrevrange($wealth_key, 0, 2, 'withscores');

        //魅力榜
        $charm_ids = [];
        $charm_values = [];

        foreach ($charm_rank_list as $user_id => $value) {
            $charm_ids[] = $user_id;
            $charm_values[$user_id] = $value;
        }

        $charm_users = \Users::findByIds($charm_ids);

        foreach ($charm_users as $user) {
            $user->value = valueToStr($charm_values[$user->id]);
        }

        //贡献榜
        $wealth_ids = [];
        $wealth_values = [];

        foreach ($wealth_rank_list as $user_id => $value) {
            $wealth_ids[] = $user_id;
            $wealth_values[$user_id] = $value;
        }

        $wealth_users = \Users::findByIds($wealth_ids);

        foreach ($wealth_users as $user) {
            $user->value = valueToStr($wealth_values[$user->id]);
        }


        $this->view->start_text = "2018年4月5日0时";
        $this->view->end_text = "2018年4月8日0时";

        $this->view->charm_users = $charm_users;
        $this->view->wealth_users = $wealth_users;


        $this->view->title = "清明节活动";
    }

    //抽奖活动
    function luckyDrawActivityAction()
    {
        $activity_id = $this->params('id');
        $activity = \Activities::findFirstById($activity_id);
        $this->view->lucky_draw_num = $this->currentUser()->getLuckyDrawNum($activity_id);
        $this->view->activity_id = $activity_id;
        $this->view->activity = $activity;
        $this->view->sid = $this->currentUser()->sid;
        $this->view->title = "转盘活动";
    }

    //抽奖
    function luckyDrawAction()
    {
        if ($this->request->isAjax()) {

            return $this->renderJSON(ERROR_CODE_FAIL, '活动已过期');

            $activity_id = $this->params('activity_id');

            if (!$activity_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            $activity = \Activities::findFirstById($activity_id);

            if (!$activity) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            if ($this->currentUser()->getLuckyDrawNum($activity_id) < 1) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您的抽奖次数已使用完');
            }

            $random = mt_rand(1, 100);
            $type = 5;

            switch ($random) {

                case 1 <= $random && $random <= 40: //40%
                    $type = 5;
                    break;
                case $random > 40 && $random <= 65: //25%
                    $type = 3;
                    break;
                case $random > 65 && $random <= 75: //10%
                    $type = 1;
                    break;
                case $random > 75 && $random <= 85: //10%
                    $type = 7;
                    break;
                case $random > 85 && $random <= 86: //1%
                    $type = 2;
                    break;
                case $random > 86 && $random <= 89: //3%
                    $type = 4;
                    break;
                case $random > 89 && $random <= 93: //4%
                    $type = 8;
                    break;
                case $random > 93 && $random <= 100: //7%
                    $type = 6;
                    break;
            }

            info($this->currentUser()->sid, $random, $type);

            //每天五位号，六位号，兰博基尼座驾，小马驹座驾各限定10份 神秘礼物限定100份,金币不限量
            if (in_array($type, [2, 4, 6, 7, 8])) {

                $cache = \Users::getHotReadCache();

                $key = "lucky_draw_prize_" . $type;
                //奖品加锁
                $lock = tryLock($key);
                $num = $cache->get($key);

                if ($num < 1) {
                    info('prize', $this->currentUser()->sid, $type);
                    $new_types = [1, 3, 5];
                    $type = $new_types[array_rand($new_types)];
                } else {
                    $cache->decr($key);
                }

                unlock($lock);
            }


            $res = \ActivityHistories::createHistory($activity_id, ['user_id' => $this->currentUser()->id, 'prize_type' => $type]);

            $key = 'lucky_draw_num_activity_id_' . $activity_id; //减去用户抽取次数
            $day_user_key = 'lucky_draw_activity_id_' . $activity_id . '_user' . date("Y-m-d"); //记录每天抽奖的人数
            $day_num_key = 'lucky_draw_activity_id_' . $activity_id . '_num' . date("Y-m-d"); //记录每天抽奖的次数

            $db = \Users::getUserDb();
            $lucky_draw_num = $db->zincrby($key, -1, $this->currentUser()->id);
            $db->zadd($day_user_key, time(), $this->currentUser()->id);
            $db->incrby($day_num_key, 1);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['lucky_draw_num' => $lucky_draw_num, 'type' => $type]);
        }
    }

    //冲榜抢热门
    function rankingToHotActivityAction()
    {
        $id = $this->params('id');
        $activity = \Activities::findFirstById($id);


        $start_at = $activity->start_at;

        $start = date("Ymd", $start_at);
        $end = date("Ymd", $start_at + 86400 * 6);

        $product_channel_id = $this->currentProductChannelId();

        $wealth_users = \Users::findFieldRankList('week', 'wealth', 1, 1);
        $charm_users = \Users::findFieldRankList('week', 'charm', 1, 1);

        $first_wealth_user = '';
        $first_charm_user = '';

        if (count($wealth_users)) {
            $first_wealth_user = $wealth_users[0];
        }

        if (count($charm_users)) {
            $first_charm_user = $charm_users[0];
        }

        $this->view->first_wealth_user = $first_wealth_user;
        $this->view->first_charm_user = $first_charm_user;
        $this->view->activity = $activity;
        $this->view->title = "活动奖励";
    }

    //梦幻周榜
    function dreamWeekRankActivityAction()
    {
        $this->view->title = "梦幻周榜";

        $opts = ['start' => '20180416', 'end' => '20180422'];

        $charm_users = \Users::findFieldRankList('week', 'charm', 1, 3, $opts);
        $wealth_users = \Users::findFieldRankList('week', 'wealth', 1, 3, $opts);

        $this->view->charm_users = $charm_users;
        $this->view->wealth_users = $wealth_users;
    }

    //房间流水活动
    function roomIncomeRankActivityAction()
    {
        $stat_at = '20180420';

        if (isDevelopmentEnv()) {
            $stat_at = '20180419';
        }

        $date = date('Ymd');

        $max = 9;

        if (intval($date) > intval($stat_at)) {
            $max = 2;
        }

        $key = "room_stats_income_day_" . $stat_at;

        $db = \Rooms::getRoomDb();

        $res = $db->zrevrange($key, 0, $max, 'withscores');

        $room_ids = [];
        $incomes = [];


        foreach ($res as $k => $value) {
            $room_ids[] = $k;
            $incomes[$k] = $value;
        }

        $rooms = \Rooms::findByIds($room_ids);

        if (count($rooms)) {

            foreach ($rooms as $index => $room) {

                if ($index > 0) {
                    $last_room = $rooms[$index - 1];
                    $last_room_income = $incomes[$last_room->id];
                    $room->missing_income = $last_room_income - $incomes[$room->id];
                }
            }
        }

        $this->view->rooms = $rooms;
        $this->view->max = $max;
        $this->view->title = "hi语音活动";
    }

    function roomIncomeRankActivity1Action()
    {
        $id = $this->params('id');
        $activity = \Activities::findFirstById($id);
        $start_at = $activity->start_at;
        $end_at = $activity->end_at;
        $time = time();

        //活动未开始
        $activity_state = 0;
        $max = 0;
        $rooms = null;

        if ($time >= $end_at) {
            //活动结束
            $activity_state = 2;
            $max = 2;
        } else if ($time >= $start_at) {
            //活动进行中
            $activity_state = 1;
            $max = 9;
        }

        if ($activity_state > 0) {
            $key = "room_stats_income_day_" . date('Ymd', $start_at);
            $db = \Rooms::getRoomDb();
            $res = $db->zrevrange($key, 0, $max, 'withscores');

            $room_ids = [];
            $incomes = [];

            foreach ($res as $k => $value) {
                $room_ids[] = $k;
                $incomes[$k] = $value;
            }
            $rooms = \Rooms::findByIds($room_ids);

            if (count($rooms)) {
                foreach ($rooms as $index => $room) {
                    if ($index > 0) {
                        $last_room = $rooms[$index - 1];
                        $last_room_income = $incomes[$last_room->id];
                        $room->missing_income = $last_room_income - $incomes[$room->id];
                    }
                }
            }
        }

        $this->view->activity_state = $activity_state;
        $this->view->rooms = $rooms;

        $this->view->start_time = date("Y/m/d H:i:s", $start_at);
        $this->view->end_time = date("Y/m/d H:i:s", $end_at);;
        $end_hour = intval(date("H", $end_at));
        $start_hour = intval(date("H", $start_at));
        $this->view->end = date("Y年m月d号{$end_hour}点", $end_at);
        $this->view->start = date("Y年m月d号{$start_hour}点", $start_at);
        $this->view->title = "疯狂送!送!送!";
    }

    //送钻石活动 道具 小黄瓜 废弃
    function giveDiamondByCucumberActivityAction()
    {
        $this->view->title = "送1000钻";

        $start = strtotime('2018-04-21 21:10:00');
        $end = strtotime('2018-04-21 21:20:00');
        $gift_id = 26;

        if (isDevelopmentEnv()) {
            $start = strtotime('2018-04-21 18:00:00');
            $end = strtotime('2018-04-21 19:25:59');
            $gift_id = 87;
        }

        $key = "give_diamond_by_cucumber_activity_gift_id_" . $gift_id . "start_" . $start . "_end_" . $end;
        $user_db = \Users::getUserDb();
        $datas = $user_db->zrevrange($key, 0, 9, 'withscores');
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

        $is_end = 1;

        if ($end > time()) {
            $is_end = 0;
        }

        $is_start = 0;

        if ($start < time()) {
            $is_start = 1;
        }

        $end_time = $start;

        if ($is_start) {
            $end_time = $end;
        }

        $this->view->end = $end;
        $this->view->end = $end;
        $this->view->start = $start;
        $this->view->users = $users;
        $this->view->is_end = $is_end;
        $this->view->is_start = $is_start;
        $this->view->end_time = date("Y/m/d H:i:s", $end_time);
    }

    //送肥皂，社会猫，肥皂礼物 废弃
    function giftWeekRankActivityAction()
    {
        if ($this->request->isAjax()) {

            $index = intval($this->params('index'));
            $start = "20180423";
            $end = "20180429";

            $opts = ['start' => $start, 'end' => $end];

            $gift_ids = [61, 60, 59];

            if (isDevelopmentEnv()) {
                $gift_ids = [123, 124, 125];
            }

            if ($index && $index <= 3) {
                $key = "week_charm_rank_list_gift_id_" . $gift_ids[$index - 1] . "_" . $start . "_" . $end;
            } else {
                $key = \Users::generateFieldRankListKey('week', 'charm', $opts);
            }

            debug($key);

            $charm_users = \Users::findFieldRankListByKey($key, 'charm', 1, 10);

            if (count($charm_users)) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '', $charm_users->toJson('users', 'toRankListJson'));
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '暂无数据');
            }
        }

        $start_time = "2018/4/23 18:00";

        if (isDevelopmentEnv()) {
            $start_time = "2018/4/23 14:50";
        }

        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');
        $this->view->start_time = $start_time;
        $this->view->end_time = "2018/4/29 23:59:59";

        $this->view->title = "社会我Hi音";
    }

    //限时送礼物活动
    function giftLatiaoActivityAction()
    {
        $this->view->title = "送1000钻";
        $id = $this->params('id');

        $activity = \Activities::findFirstById($id);

        if (!$activity) {
            echo "参数错误";
            return false;
        }

        $start = $activity->start_at;
        $end = $activity->end_at;

        $gift_id = trim($activity->gift_ids, ',');
        $gift = \Gifts::findFirstById(intval($gift_id));

        if (!$gift) {
            echo "参数错误$gift_id";
            return false;
        }

        $users = $activity->getRanListUsers($gift_id, 9);


        $end_time = $start < time() ? $end : $start;

        $this->view->end = $end;
        $this->view->start = $start;
        $this->view->gift = $gift;
        $this->view->users = $users;
        $this->view->activity_start_hour = date("H:i", $activity->start_at);
        $this->view->activity_end_hour = date("H:i", $activity->end_at);
        $this->view->is_end = $end > time() ? 0 : 1;
        $this->view->is_start = $start < time() ? 1 : 0;
        $this->view->end_time = date("Y/m/d H:i:s", $end_time);
    }

    //限时送礼物活动
    function giftLaosijiActivityAction()
    {
        $this->view->title = "送1000钻";
        $id = $this->params('id');

        $activity = \Activities::findFirstById($id);

        if (!$activity) {
            echo "参数错误";
            return false;
        }

        $start = $activity->start_at;
        $end = $activity->end_at;

        $gift_id = trim($activity->gift_ids, ',');
        $gift = \Gifts::findFirstById(intval($gift_id));

        if (!$gift) {
            echo "参数错误$gift_id";
            return false;
        }

        $users = $activity->getRanListUsers($gift_id, 9);

        $end_time = $start < time() ? $end : $start;

        $this->view->end = $end;
        $this->view->start = $start;
        $this->view->gift = $gift;
        $this->view->users = $users;
        $this->view->activity_start_hour = date("H:i", $activity->start_at);
        $this->view->activity_end_hour = date("H:i", $activity->end_at);
        $this->view->is_end = $end > time() ? 0 : 1;
        $this->view->is_start = $start < time() ? 1 : 0;
        $this->view->end_time = date("Y/m/d H:i:s", $end_time);
    }

    //礼物周榜活动
    function giftCharmWeek20180430rankActivityAction()
    {
        $id = $this->params('id');
        $activity = \Activities::findFirstById($id);

        if (!$activity) {
            echo "参数错误";
        }

        $last_activity_id = $activity->last_activity_id;

        $last_activity = \Activities::findFirstById($last_activity_id);

        if (!$last_activity) {
            echo "参数错误";
        }


        if ($this->request->isAjax()) {

            $gift_id = $this->params('gift_id');
            $activity_start = date("Ymd", beginOfWeek($activity->start_at));
            $activity_end = date("Ymd", endOfWeek($activity->start_at));
            $opts = ['start' => $activity_start, 'end' => $activity_end];

            if (!$gift_id) {
                $key = \Users::generateFieldRankListKey('week', 'charm', $opts);
            } else {
                $key = $activity->getStatKey($gift_id);
            }

            debug($key);

            $charm_users = \Users::findFieldRankListByKey($key, 'charm', 1, 10);

            if (count($charm_users)) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '', $charm_users->toJson('users', 'toRankListJson'));
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '暂无数据');
            }
        }

        $last_gift_ids_array = $last_activity->getGiftIdsArray();
        $gift_ids_array = $activity->getGiftIdsArray();

        $last_gifts = \Gifts::findByIds($last_gift_ids_array);
        $gifts = \Gifts::findByIds($gift_ids_array);

        $last_activity_start = date("Ymd", beginOfWeek($last_activity->start_at));
        $last_activity_end = date("Ymd", endOfWeek($last_activity->start_at));

        $opts = ['start' => $last_activity_start, 'end' => $last_activity_end];
        $last_activity_rank_list_users = [];

        foreach ($last_gifts as $last_gift) {

            if ($last_activity < 16) {
                $key = "week_charm_rank_list_gift_id_" . $last_gift->id . "_" . $last_activity_start . "_" . $last_activity_start;
            } else {
                $key = $last_activity->getStatKey($last_gift->id);
            }

            $users = \Users::findFieldRankListByKey($key, 'charm', 1, 1);

            if (isset($users[0])) {
                $last_activity_rank_list_users[] = $users[0]->toRankListJson();
            }

            debug($key);
        }

        $last_week_charm_rank_list_key = \Users::generateFieldRankListKey('week', 'charm', $opts);
        debug($last_activity_rank_list_users, $last_week_charm_rank_list_key);
        $users = \Users::findFieldRankListByKey($last_week_charm_rank_list_key, 'charm', 1, 1);

        $last_week_charm_rank_list_user = [];

        if (isset($users[0])) {
            $last_week_charm_rank_list_user = $users[0]->toRankListJson();
        }

        $this->view->last_week_charm_rank_list_user = $last_week_charm_rank_list_user;
        $this->view->last_activity_rank_list_users = $last_activity_rank_list_users;
        $this->view->last_gifts = $last_gifts;
        $this->view->gifts = $gifts;
        $this->view->start_time = date("Y/m/d H:i:s", $activity->start_at);
        $this->view->end_time = date("Y/m/d H:i:s", $activity->end_at);
    }
}