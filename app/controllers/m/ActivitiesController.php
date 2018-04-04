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
        $code = $this->params('code');

        $activities = \Activities::findActivity(['product_channel_id' => $product_channel_id, 'platform' => $pf]);

        $this->view->code = $code;
        $this->view->activities = $activities;
        $this->view->title = "活动";
    }

    function weekRankActivityAction()
    {
        $id = $this->params('id');
        $activity = \Activities::findFirstById($id);

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
            $gifts = \Gifts::findByIds([33, 32, 11]);
        }

        $this->view->start_text = date("Y年m月d日H点", $start_at);
        $this->view->end_text = date("Y年m月d日H点", $end_at);

        $this->view->wealth_users = $wealth_users;
        $this->view->charm_users = $charm_users;

        $this->view->gifts = $gifts;

        $this->view->last_start_text = date("Y.m.d", $last_stat_at);
        $this->view->last_end_text = date("Y.m.d", $last_end_at);

        $this->view->give_time = date("Y年m月d日", $last_end_at + 86400 * 2);
        $this->view->official_id = 100101;
        $this->view->title = "周榜专属奖励";

        if ($id > 0) {
            $this->pick("m/activities/week_rank_activity{$id}");
        }
    }
}