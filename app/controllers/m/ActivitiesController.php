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
        $pf = $this->params('pf');
        $sid = $this->params('sid');
        $code = $this->params('code');

        $activities = \Activities::findActivity(['product_channel_id' => $product_channel_id, 'platform' => $pf]);

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


    function qingMingActivityAction()
    {
        $start = 20180405;
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


        $this->view->start_text = "2018年04月07日00点";
        $this->view->end_text = "2018年04月08日00";

        $this->view->charm_users = $charm_users;
        $this->view->wealth_users = $wealth_users;


        $this->view->title = "清明节活动";
    }
}