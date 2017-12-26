<?php

namespace admin;

class WordVisitHistoriesController extends BaseController
{
    public function indexAction()
    {
        $page = $this->params('page');
        $word_visit_id = $this->params('word_visit_id');
        $cond = [
            'conditions' => 'word_visit_id = :word_visit_id:',
            'bind' => ['word_visit_id' => $word_visit_id],
            'order' => 'down_num desc'
        ];

        $word_visit_histories = \WordVisitHistories::findPagination($cond, $page);
        $this->view->word_visit_histories = $word_visit_histories;
    }
}