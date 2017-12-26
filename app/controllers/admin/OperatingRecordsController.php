<?php

namespace admin;

class OperatingRecordsController extends BaseController
{

    function indexAction()
    {
        $conds = $this->getConditions('operating_record');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $per_page = $this->params('per_page', 60);
        $operating_records = \OperatingRecords::findPagination($conds, $page, $per_page);
        $this->view->operating_records = $operating_records;
        $this->view->operators = \Operators::find(['order' => 'id desc']);
        $this->view->table_names = \OperatingRecords::getTableNames();
    }

}