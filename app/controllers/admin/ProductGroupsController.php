<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 21:33
 */

namespace admin;

class ProductGroupsController extends BaseController
{
    function indexAction()
    {
        $product_channel_id = $this->params('product_channel_id');
        $product_groups = \ProductGroups::findByProductChannelId($product_channel_id);
        $this->view->product_groups = $product_groups;
        $this->view->product_channel_id = $product_channel_id;
    }

    function newAction()
    {
        $product_group = new \ProductGroups();
        $this->view->product_group = $product_group;
        $this->view->product_channel_id = $this->params('product_channel_id');
    }

    function createAction()
    {
        $product_group = new \ProductGroups();
        $this->assign($product_group, 'product_group');
        if ($product_group->create()) {
            return $this->renderJSON(
                ERROR_CODE_SUCCESS, '',
                array('product_group' => $product_group->toJson())
            );
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function editAction()
    {
        $product_group = \ProductGroups::findById($this->params('id'));
        $this->view->product_group = $product_group;
        $this->view->product_channel_id = $product_group->product_channel_id;
    }

    function updateAction()
    {
        $product_group = \ProductGroups::findById($this->params('id'));
        $this->assign($product_group, 'product_group');
        if ($product_group->update()) {
            return $this->renderJSON(
                ERROR_CODE_SUCCESS, '',
                array('product_group' => $product_group->toJson())
            );
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }
}