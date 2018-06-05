<?php

namespace admin;

class UnionLevelConfigsController extends BaseController
{
    function indexAction()
    {

        $page = $this->params('page');
        $per_page = $this->params('per_page', 20);
        $cond = $this->getConditions('union_level_config');
        $cond['order'] = 'id asc';

        $union_level_configs = \UnionLevelConfigs::findPagination($cond, $page, $per_page);
        $this->view->union_level_configs = $union_level_configs;
    }

    function newAction()
    {
        $union_level_config = new \UnionLevelConfigs();
        $this->view->union_level_config = $union_level_config;

    }

    function createAction()
    {
        $union_level_config = new \UnionLevelConfigs();
        $this->assign($union_level_config, 'union_level_config');

        if ($union_level_config->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['union_level_config' => $union_level_config->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL);
        }

    }

    function editAction()
    {
        $union_level_config = \UnionLevelConfigs::findFirstById($this->params('id'));
        $this->view->union_level_config = $union_level_config;
    }

    function updateAction()
    {
        $union_level_config = \UnionLevelConfigs::findFirstById($this->params('id'));
        $this->assign($union_level_config, 'union_level_config');


        if ($union_level_config->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['union_level_config' => $union_level_config->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL);
        }
    }

}