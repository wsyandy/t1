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
    function weekRankActivityAction()
    {
        $this->view->title = "周榜专属奖励";
    }

    function weekRankActivity1Action()
    {
        $stat_at = strtotime("last monday", time()) - 86400 * 7;
        $end_at = $stat_at + 86400 * 6;

        $start = date("Ymd", $stat_at);
        $end = date("Ymd", $end_at);

        $opts = ['start' => $start, 'end' => $end];


        $wealth_users = \Users::findFieldRankList('week', 'wealth', 1, 3, $opts);

        $charm_users = \Users::findFieldRankList('week', 'charm', 1, 3, $opts);

        if (isDevelopmentEnv()) {
            $gift_1 = \Gifts::findFirstById(61);
            $gift_2 = \Gifts::findFirstById(49);
            $gift_3 = \Gifts::findFirstById(52);
        } else {
            $gift_1 = \Gifts::findFirstById(1);
            $gift_2 = \Gifts::findFirstById(2);
            $gift_3 = \Gifts::findFirstById(3);
        }


        $this->view->wealth_users = $wealth_users;
        $this->view->charm_users = $charm_users;

        $this->view->gift_1 = $gift_1;
        $this->view->gift_2 = $gift_2;
        $this->view->gift_3 = $gift_3;

        $this->view->start = date("Y.m.d", $stat_at);
        $this->view->end = date("Y.m.d", $end_at);

        $this->view->give_time = date("Y年m月d日", $end_at + 86400 * 2);
        $this->view->official_id = 100101;
    }
}