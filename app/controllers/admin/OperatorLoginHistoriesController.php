<?php

namespace admin;

class OperatorLoginHistoriesController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('operator_login_history');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $operator_login_histories = \OperatorLoginHistories::findPagination($conds, $page, $per_page);
        $this->view->operators = \Operators::find(['order' => 'id desc']);
        $this->view->operator_login_histories = $operator_login_histories;
    }
}