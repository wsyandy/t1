<?php

namespace admin;

class ClientThemesController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('client_theme');
        $conds['order'] = 'status desc, id desc';

        $page = $this->params('page');
        $client_themes = \ClientThemes::findPagination($conds, $page);
        $this->view->client_themes = $client_themes;

        $this->view->product_channel_id = $conds['bind']['product_channel_id_eq'];
    }

    function newAction()
    {
        $client_theme = new \ClientThemes();
        $client_theme->product_channel_id = $this->params('product_channel_id');

        $this->view->client_theme = $client_theme;
    }

    function createAction()
    {
        $client_theme = new \ClientThemes();
        $this->assign($client_theme, 'client_theme');

        $platform = $this->params('client_theme[platform]');
        if ($platform == 'ios') {
            $client_theme->ios_version_code = $this->params('client_theme[soft_version_code]');
            $client_theme->android_version_code = -1;
        } elseif ($platform == 'android') {
            $client_theme->ios_version_code = -1;
            $client_theme->android_version_code = $this->params('client_theme[soft_version_code]');
        }
        if (!isBlank($client_theme->remark)) {
            $client_theme->save();
            \OperatingRecords::logAfterCreate($this->currentOperator(), $client_theme);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['client_theme' => $client_theme->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新简介不能为空');
        }
    }

    function editAction()
    {
        $client_theme = \ClientThemes::findFirstById($this->params('id'));
        $this->view->client_theme = $client_theme;
        $this->view->ios_version_code = $client_theme->ios_version_code;
        $this->view->android_version_code = $client_theme->android_version_code;
    }

    function updateAction()
    {
        $client_theme = \ClientThemes::findFirstById($this->params('id'));
        $this->assign($client_theme, 'client_theme');
        if (!isBlank($client_theme->remark)) {
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $client_theme);
            $client_theme->update();
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['client_theme' => $client_theme->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新简介不能为空');
        }
    }
}