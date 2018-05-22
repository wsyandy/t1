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

        foreach ($products as $product) {
            $data = json_decode($product->data, true);
            $product->tamp_gold_egg = $data['tamp_gold_egg'];
        }
        $this->view->products = $products;
        $this->view->product_channel_id = $product_channel_id;
        $this->view->product_group_id = $product_group_id;
    }

    function newAction()
    {
        $product = new \Products();
        $product_group_id = $this->params('product_group_id');
        $product->product_group_id = $product_group_id;
        $product->tamp_gold_egg = 0;
        $this->view->product = $product;
        $this->view->product_group_id = $product_group_id;
    }

    function createAction()
    {
        $product = new \Products();
        $this->assign($product, 'product');

        $product->data = json_encode(['tamp_gold_egg'=>$product->data]);

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

        $data = json_decode($product->data, true);
        $product->tamp_gold_egg = $data['tamp_gold_egg'];

        $this->view->product = $product;
        $this->view->product_group_id = $product->product_group_id;
    }

    function updateAction()
    {
        $product = \Products::findById($this->params("id"));
        $this->assign($product, 'product');

        $product->data = json_encode(['tamp_gold_egg'=>$product->tamp_gold_egg]);

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $product);
        if ($product->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('product' => $product->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }
}