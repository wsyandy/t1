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

        $activities = \Activities::findActivity(['product_channel_id' => $product_channel_id, 'platform' => $platform]);

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

            $last_stat_at = strtotime("last monday", $start_at);
            $last_end_at = $last_stat_at + 86400 * 6;

            $lats_start = date("Ymd", $last_stat_at);
            $last_end = date("Ymd", $last_end_at);

            $opts = ['start' => $lats_start, 'end' => $last_end];
            $wealth_users = \Users::findFieldRankList('week', 'wealth', 1, 3, $opts);
            $charm_users = \Users::findFieldRankList('week', 'charm', 1, 3, $opts);

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

        $charm_rank_list = $db->zrevrange($charm_key, 0, 19, 'withscores');
        $wealth_rank_list = $db->zrevrange($wealth_key, 0, 19, 'withscores');

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
        $this->view->lucky_draw_num = $this->currentUser()->getLuckyDrawNum($activity_id);
        $this->view->activity_id = $activity_id;
        $this->view->sid = $this->currentUser()->sid;
    }

    //抽奖
    function luckyDrawAction()
    {
        if ($this->request->isAjax()) {

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
                    $type = $new_types[array_rand([1, 3, 5])];
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
}