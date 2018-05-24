<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/16
 * Time: 10:19
 */

namespace admin;

class BoomConfigsController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('boom_configs');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $boom_configs = \BoomConfigs::findPagination($conds, $page, $per_page);

        $this->view->boom_configs = $boom_configs;
    }

    function newAction()
    {
        $boom_config = new \BoomConfigs();
        $this->view->boom_config = $boom_config;
    }

    function createAction()
    {
        $boom_config = new \BoomConfigs();
        $this->assign($boom_config, 'boom_config');

        if ($boom_config->save()) {

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('boom_config' => $boom_config->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '创建失败');
        }
    }

    function editAction()
    {
        $boom_config = \BoomConfigs::findById($this->params('id'));
        $this->view->boom_config = $boom_config;
    }

    function updateAction()
    {
        $boom_config = \BoomConfigs::findById($this->params('id'));
        $this->assign($boom_config, 'boom_config');

        if ($boom_config->update()) {
            info("数据",$boom_config->toJson());
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('boom_config' => $boom_config->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }
}