<?php

namespace admin;

class AwardHistoriesController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('award_history');
        $stat_at = $this->params('stat_at', date('Y-m'));
        $start_at = beginOfMonth(strtotime($stat_at));
        $end_at = endOfMonth(strtotime($stat_at));

        if ($conds) {
            $conds['conditions'] .= ' and created_at>=:start_at: and created_at <=:end_at:';
            $conds['bind'] = array_merge($conds['bind'], ['start_at' => $start_at, 'end_at' => $end_at]);
        } else {
            $conds['conditions'] = 'created_at>=:start_at: and created_at <=:end_at:';
            $conds['bind'] = ['start_at' => $start_at, 'end_at' => $end_at];
        }


        info('查询条件', $conds);
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $award_histories = \AwardHistories::findPagination($conds, $page, $per_page);

        $this->view->award_histories = $award_histories;
        $this->view->stat_at = $stat_at;
    }

    function sendSystemMessageAction()
    {
        $user_id = $this->params('user_id');
        $id = $this->params('id');

        if ($this->request->isPost()) {
            $auth_status = $this->params('auth_status');
            $award_history = \AwardHistories::findFirstById($id);
            $award_history->auth_status = $auth_status;
            $award_history->update();

        }

        $this->view->user_id = $user_id;
        $this->view->id = $id;
    }

}