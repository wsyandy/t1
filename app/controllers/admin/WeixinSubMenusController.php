<?php

namespace admin;

class WeixinSubMenusController extends BaseController
{

    public function indexAction()
    {

        $page = $this->params('page');
        $per_page = $this->params('per_page', 50);
        $weixin_menu_id = $this->params('id');

        $weixin_menu = \WeixinMenus::findFirstById($weixin_menu_id);
        $con = [
            'conditions' => 'weixin_menu_id = :weixin_menu_id:',
            'bind' => ['weixin_menu_id' => $weixin_menu_id],
            'order' => 'rank desc, id asc'
        ];
        $weixin_sub_menus = \WeixinSubMenus::findPagination($con, $page, $per_page);
        $this->view->weixin_sub_menus = $weixin_sub_menus;
        $this->view->weixin_menu_name = $weixin_menu->name;
        $this->view->weixin_menu_id = $weixin_menu->id;
    }

    public function newAction()
    {
        $weixin_sub_menu = new \WeixinSubMenus();
        $weixin_menu_id = $this->params('weixin_menu_id');
        $weixin_sub_menu->weixin_menu_id = $weixin_menu_id;
        $this->view->weixin_sub_menu = $weixin_sub_menu;
    }

    public function createAction()
    {
        $weixin_sub_menu = new \WeixinSubMenus();
        $this->assign($weixin_sub_menu, 'weixin_sub_menu');

        if ($weixin_sub_menu->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $weixin_sub_menu);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_sub_menu' => $weixin_sub_menu->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    public function editAction()
    {
        $weixin_sub_menu_id = $this->params('id');
        $weixin_sub_menu = \WeixinSubMenus::findFirstById($weixin_sub_menu_id);
        $this->view->weixin_sub_menu = $weixin_sub_menu;
    }

    public function updateAction()
    {
        $weixin_sub_menu_id = $this->params('id');
        $weixin_sub_menu = \WeixinSubMenus::findFirstById($weixin_sub_menu_id);
        $this->assign($weixin_sub_menu, 'weixin_sub_menu');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_sub_menu);

        if ($weixin_sub_menu->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_sub_menu' => $weixin_sub_menu->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    public function deleteAction()
    {
        $weixin_sub_menu_id = $this->params('id');
        $weixin_sub_menu = \WeixinSubMenus::findFirstById($weixin_sub_menu_id);
        \OperatingRecords::logBeforeDelete($this->currentOperator(), $weixin_sub_menu);

        if ($weixin_sub_menu->delete()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_sub_menu' => $weixin_sub_menu->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }
}