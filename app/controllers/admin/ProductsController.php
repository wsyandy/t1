<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 22:23
 */

namespace admin;

class ProductsController extends BaseController
{
    function indexAction()
    {
        $product_group_id = $this->params('product_group_id');
        $product_channel_id = $this->params('product_channel_id');
        $products = \Products::findByProductGroupId($product_group_id);
        $this->view->products = $products;
        $this->view->product_channel_id = $product_channel_id;
        $this->view->product_group_id = $product_group_id;
    }

    function newAction()
    {
        $product = new \Products();
        $product_group_id = $this->params('product_group_id');
        $product->product_group_id = $product_group_id;
        $this->view->product = $product;
        $this->view->product_group_id = $product_group_id;
    }

    function createAction()
    {
        $product = new \Products();
        $this->assign($product, 'product');
        $draw_num = $this->params('product[draw_num]');

        if ($draw_num) {
            $product->data = json_encode(['draw_num' => $draw_num], JSON_UNESCAPED_UNICODE);
        }

        if ($product->create()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $product);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('product' => $product->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function editAction()
    {
        $product = \Products::findById($this->params('id'));
        $this->view->product = $product;
        $this->view->product_group_id = $product->product_group_id;
    }

    function updateAction()
    {
        $product = \Products::findById($this->params("id"));
        $this->assign($product, 'product');
        $draw_num = $this->params('product[draw_num]', 0);
        $data = [];

        if ($product->data) {
            $data = json_decode($product->data, true);
        }

        $data['draw_num'] = $draw_num;
        $product->data = json_encode($data, JSON_UNESCAPED_UNICODE);

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $product);
        if ($product->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('product' => $product->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }
}