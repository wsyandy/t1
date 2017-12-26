<?php

namespace admin;

class PartnersController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('partner');
        $cond['order'] = 'id desc';
        $page = $this->params('page');
        $partners = \Partners::findPagination($cond, $page);
        $this->view->partners = $partners;
        $this->view->select_partners = \Partners::find(['order' => 'id desc']);
    }

    function newAction()
    {
        $partner = new \Partners();
        $this->view->partner = $partner;
    }

    function createAction()
    {
        $partner = new \Partners();
        $this->assign($partner, 'partner');

        $existing_partner = \Partners::findFirstByFr($partner->fr);
        if ($existing_partner && $existing_partner->id != $partner->id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '来源FR已经存在');
        }

        if (isPresent($partner->password)) {
            $partner->password = md5($partner->password);
        }
        $partner->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $partner);

        $this->renderJSON(ERROR_CODE_SUCCESS, '', array('partner' => $partner->to_json));
    }

    function editAction()
    {
        $partner = \Partners::findFirstById($this->params('id'));
        $partner->password = '';
        $this->view->partner = $partner;
    }

    function updateAction()
    {
        $partner = \Partners::findFirstById($this->params('id'));
        $this->assign($partner, 'partner');

        $existing_partner = \Partners::findFirstByFr($partner->fr);
        if ($existing_partner && $existing_partner->id != $partner->id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '来源FR已经存在');
        }

        if (isBlank($partner->password)) {
            $partner->password = $partner->was('password');
        } else {
            $partner->password = md5($partner->password);
        }
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $partner);
        $partner->save();

        $this->renderJSON(ERROR_CODE_SUCCESS, '', array('partner' => $partner->to_json));
    }

    function exportAction()
    {
        $data_file = \Partners::exportPartners();

        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($data_file));
        header("Content-Disposition: attachment; filename=" . basename($data_file));
        echo file_get_contents($data_file);
    }

}