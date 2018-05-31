<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/16
 * Time: 上午11:32
 */
namespace admin;
class DrawConfigsController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('draw_configs');
        $conds['order'] = 'status desc,rank desc,id desc';
        $page = $this->params('page');
        $draw_configs = \DrawConfigs::findPagination($conds, $page);
        $this->view->draw_configs = $draw_configs;
    }

    function newAction()
    {
        $draw_config = new \DrawConfigs();
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channels = $product_channels;
        $this->view->draw_config = $draw_config;
    }

    function createAction()
    {
        $draw_config = new \DrawConfigs();
        $this->assign($draw_config, 'draw_config');
        $draw_config->operator_id = $this->currentOperator()->id;
        $draw_config->material_ids = trim(preg_replace('/，/', ',', $draw_config->material_ids), ',');
        $draw_config->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $draw_config);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['draw_config' => $draw_config->to_json]);
    }

    function editAction()
    {
        $id = $this->params('id');
        $draw_config = \DrawConfigs::findFirstById($id);
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->draw_config = $draw_config;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $draw_config = \DrawConfigs::findFirstById($id);
        $this->assign($draw_config, 'draw_config');
        $draw_config->operator_id = $this->currentOperator()->id;
        $draw_config->material_ids = trim(preg_replace('/，/', ',', $draw_config->material_ids), ',');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $draw_config);
        $draw_config->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['draw_config' => $draw_config->to_json]);
    }
}