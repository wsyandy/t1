<?php

namespace admin;

class WeixinMenusController extends BaseController
{

    public function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 50);
        $weixin_menu_template_id = $this->params('weixin_menu_template_id');

        $weixin_menu_template = \WeixinMenuTemplates::findFirstById($weixin_menu_template_id);
        $con = [
            'conditions' => 'weixin_menu_template_id = :weixin_menu_template_id:',
            'bind' => ['weixin_menu_template_id' => $weixin_menu_template_id],
            'order' => 'rank desc, id asc'
        ];
        $weixin_menus = \WeixinMenus::findPagination($con, $page, $per_page);
        $this->view->weixin_menus = $weixin_menus;
        $this->view->weixin_menu_template_id = $weixin_menu_template->id;
        $this->view->weixin_menu_template_name = $weixin_menu_template->name;
    }

    public function newAction()
    {
        $weixin_menu = new \WeixinMenus();
        $weixin_menu_template_id = $this->params('weixin_menu_template_id');
        $weixin_menu->weixin_menu_template_id = $weixin_menu_template_id;
        $this->view->weixin_menu = $weixin_menu;
    }

    public function createAction()
    {
        $weixin_menu = new \WeixinMenus();
        $this->assign($weixin_menu, 'weixin_menu');

        if ($weixin_menu->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $weixin_menu);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_menu' => $weixin_menu->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    public function editAction()
    {
        $weixin_menu_id = $this->params('id');
        $weixin_menu = \WeixinMenus::findFirstById($weixin_menu_id);
        $this->view->weixin_menu = $weixin_menu;
    }

    public function updateAction()
    {
        $weixin_menu_id = $this->params('id');
        $weixin_menu = \WeixinMenus::findFirstById($weixin_menu_id);
        $this->assign($weixin_menu, 'weixin_menu');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_menu);
        if ($weixin_menu->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_menu' => $weixin_menu->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    public function deleteAction()
    {
        $weixin_menu_id = $this->params('id');
        $weixin_menu = \WeixinMenus::findFirstById($weixin_menu_id);
        \OperatingRecords::logBeforeDelete($this->currentOperator(), $weixin_menu);
        if ($weixin_menu->delete()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_menu' => $weixin_menu->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }
}