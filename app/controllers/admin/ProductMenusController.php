<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/24
 * Time: 上午9:48
 */
namespace admin;

class ProductMenusController extends BaseController
{
    function indexAction()
    {
        $product_channel_id = $this->params('product_channel_id');
        if (isBlank($product_channel_id)) {
            return;
        }

        $cond = $this->getConditions('product_menu');

        if (isset($cond['conditions'])) {
            $cond['conditions'] .= " and product_channel_id = " . $product_channel_id;
        } else {
            $cond['conditions'] = " product_channel_id = " . $product_channel_id;
        }

        $cond['order'] = 'rank desc,id desc';

        $page = $this->params('page');
        $product_menus = \ProductMenus::findPagination($cond, $page);
        $this->view->product_channel_id = $product_channel_id;
        $this->view->product_menus = $product_menus;
    }

    function newAction()
    {
        $product_menu = new \ProductMenus();
        $product_menu->status = STATUS_ON;
        $product_menu->product_channel_id = $this->params('product_channel_id');

        $room_categories = \RoomCategories::find(['conditions' => 'parent_id is null or parent_id = 0', 'order' => 'rank desc,id desc']);

        $this->view->room_categories = $room_categories;
        $this->view->product_menu = $product_menu;
    }

    function createAction()
    {
        $product_menu = new \ProductMenus();

        $this->assign($product_menu, 'product_menu');

        list($error_code, $error_reason) = $product_menu->checkFields();
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        if ($product_menu->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $product_menu);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['product_menu' => $product_menu->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    function editAction()
    {
        $product_menu = \ProductMenus::findFirstById($this->params('id'));

        $room_categories = \RoomCategories::find(['conditions' => 'parent_id is null or parent_id = 0', 'order' => 'rank desc,id desc']);

        $this->view->room_categories = $room_categories;
        $this->view->product_menu = $product_menu;
    }

    function updateAction()
    {
        $product_menu = \ProductMenus::findFirstById($this->params('id'));
        $this->assign($product_menu, 'product_menu');

        list($error_code, $error_reason) = $product_menu->checkFields();
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON($error_code, $error_reason);
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $product_menu);
        if ($product_menu->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['product_menu' => $product_menu->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }
}