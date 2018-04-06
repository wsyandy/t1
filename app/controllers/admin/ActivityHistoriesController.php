<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/5
 * Time: 下午8:11
 */

namespace admin;

class ActivityHistoriesController extends BaseController
{
    function basicAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $activity_histories = \ActivityHistories::findPagination(['conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id]], $page, 20);
        $this->view->activity_histories = $activity_histories;
    }

    function indexAction()
    {
        $stat_at = $this->params('stat_at', date('Y-m-d'));

        $start_at = beginOfDay($stat_at);
        $end_at = endOfDay($stat_at);

        $activity_id = $this->params('activity_id');
        $activity = \Activities::findFirstById($activity_id);
        $ond = ['conditions' => 'activity_id = :activity_id: and created_at >= :start: and created_at <= :end:',
            'bind' => ['activity_id' => $activity->id, 'start' => $start_at, 'end' => $end_at]];

        $page = $this->params('page');
        $activity_histories = \ActivityHistories::findPagination($ond, $page, 30);

        $this->view->activity_histories = $activity_histories;
        $this->view->stat_at = $stat_at;
        $this->view->activity_id = $activity_id;
    }
}