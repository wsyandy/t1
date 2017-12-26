<?php

namespace admin;

class WapVisitHistoriesController extends BaseController
{
    public function indexAction()
    {
        $page = $this->params('page');
        $wap_visit_id = $this->params('wap_visit_id');
        $cond = [
            'conditions' => 'wap_visit_id = :wap_visit_id:',
            'bind' => ['wap_visit_id' => $wap_visit_id],
            'order' => 'visit_num desc'
        ];

        $wap_visit_histories = \WapVisitHistories::findPagination($cond, $page);
        $this->view->wap_visit_histories = $wap_visit_histories;
    }
}