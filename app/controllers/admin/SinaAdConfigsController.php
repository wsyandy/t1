<?php

namespace admin;


class SinaAdConfigsController extends BaseController
{

    function indexAction()
    {
        $conds = $this->getConditions('sina_ad_config');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $sina_ad_configs = \SinaAdConfigs::findPagination($conds, $page);
        $this->view->sina_ad_configs = $sina_ad_configs;
    }

    function newAction()
    {
        $sina_ad_config = new \SinaAdConfigs();
        $this->view->sina_ad_config = $sina_ad_config;
    }

    function createAction()
    {
        $sina_ad_config = new \SinaAdConfigs();
        $this->assign($sina_ad_config, 'sina_ad_config');
        $old_config = \SinaAdConfigs::findFirstByGroupId($sina_ad_config->group_id);
        if ($old_config) {
            $this->renderJSON(ERROR_CODE_FAIL, '已存在广告组' . $sina_ad_config->group_id);
            return;
        }

        $sina_ad_config->operator_id = $this->currentOperator()->id;
        $sina_ad_config->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $sina_ad_config);

        $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['sina_ad_config' => $sina_ad_config->toJson()]);
    }

    function editAction()
    {
        $id = $this->params('id');
        $sina_ad_config = \SinaAdConfigs::findFirstById($id);
        $this->view->sina_ad_config = $sina_ad_config;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $sina_ad_config = \SinaAdConfigs::findFirstById($id);
        $this->assign($sina_ad_config, 'sina_ad_config');

        $sina_ad_config->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $sina_ad_config);
        $sina_ad_config->save();
        $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['sina_ad_config' => $sina_ad_config->toJson()]);
    }

}