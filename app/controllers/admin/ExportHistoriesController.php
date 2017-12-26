<?php

namespace admin;

class ExportHistoriesController extends BaseController
{

    function indexAction()
    {
        $conds = $this->getConditions('export_history');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $export_histories = \ExportHistories::findPagination($conds, $page, $per_page);
        $this->view->export_histories = $export_histories;
        $this->view->operators = \Operators::find(['order' => 'id desc']);
        $this->view->table_names = \OperatingRecords::getTableNames();
    }

    function downloadAction()
    {
        $id = $this->params('id');

        if ($this->request->isPost()) {
            $export_history = \ExportHistories::findFirstById($id);
            $file_url = '';
            if ($export_history->file && $export_history->operator_id == $this->currentOperator()->id) {
                $file_url = $export_history->file_url;
                $export_history->download_num += 1;
                $export_history->save();
            }

            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['redirect_url' => $file_url]);
        }

        $this->view->id = $id;
    }
}