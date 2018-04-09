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
        $this->view->activity_prize_types = \Activities::$ACTIVITY_PRIZE_TYPE;
    }

    function indexAction()
    {
        $stat_at = $this->params('stat_at', date('Y-m-d'));

        $start_at = beginOfDay(strtotime($stat_at));
        $end_at = endOfDay(strtotime($stat_at));
        $activity_id = $this->params('activity_id');
        $prize_type = $this->params('prize_type');
        $activity = \Activities::findFirstById($activity_id);

        $cond = ['conditions' => 'activity_id = :activity_id: and created_at >= :start: and created_at <= :end:',
            'bind' => ['activity_id' => $activity->id, 'start' => $start_at, 'end' => $end_at],
            'order' => 'id desc'
        ];

        if ($prize_type) {
            $cond['conditions'] .= " and prize_type = :prize_type:";
            $cond['bind']['prize_type'] = $prize_type;
        }

        $page = $this->params('page');
        $activity_histories = \ActivityHistories::findPagination($cond, $page, 30);

        $this->view->activity_histories = $activity_histories;
        $this->view->stat_at = $stat_at;
        $this->view->activity_id = $activity_id;
        $this->view->prize_type = intval($prize_type);
        $this->view->activity_prize_types = \Activities::$ACTIVITY_PRIZE_TYPE;
        $this->view->total_num = $activity_histories->total_entries;
    }

    function editAction()
    {
        $activity_history = \ActivityHistories::findFirstById($this->params('id'));
        debug($activity_history);
        $this->view->activity_history = $activity_history;
    }

    function updateAction()
    {
        $activity_history = \ActivityHistories::findFirstById($this->params('id'));
        $this->assign($activity_history, 'activity_history');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $activity_history);
        if ($activity_history->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['activity_history' => $activity_history->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }
}