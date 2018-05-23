<?php

namespace admin;


class PkHistoriesController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('pk_histories');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $pk_histories = \PkHistories::findPagination($conds, $page, $per_page);
        $this->view->pk_histories = $pk_histories;
    }



}