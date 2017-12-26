<?php

namespace admin;

class WapVisitsController extends BaseController
{

    #列表数据
    public function indexAction()
    {
        $visit_at = $this->params('visit_at', date('Y-m-d'));
        $page = $this->params('page');
        $per_page = $this->params('per_page', 20);

        $begin_at = strtotime($visit_at);

        $cond = [
            'conditions' => 'visit_at = :begin_at:',
            'bind' => ['begin_at' => $begin_at],
            'order' => "visit_num desc",
        ];

        $sems = \WapVisits::find(['columns' => 'distinct sem']);
        $sem = $this->params('sem');
        if ($sem) {
            $cond['conditions'] .= " and sem = :sem:";
            $cond['bind']['sem'] = $sem;
        }

        $wap_visits = \WapVisits::findPagination($cond, $page, $per_page);
        $this->view->wap_visits = $wap_visits;
        $this->view->visit_at = $visit_at;
        $this->view->sems = $sems;
        $this->view->sem = $sem;
    }

}