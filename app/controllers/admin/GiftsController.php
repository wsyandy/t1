<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 11:30
 */

namespace admin;

class GiftsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = 30;

        $cond = $this->getConditions('gift');
        $product_channel_id = $this->params('product_channel_id');
        if ($product_channel_id) {
            if (isset($cond['conditions'])) {
                $cond['conditions'] .= " and (product_channel_ids = '' or product_channel_ids is null or product_channel_ids like :product_channel_ids:)";
            } else {
                $cond['conditions'] = "  (product_channel_ids = '' or product_channel_ids is null or product_channel_ids like :product_channel_ids:)";
            }
            $cond['bind']['product_channel_ids'] = "%," . $product_channel_id . "%,";
        }

        $cond['order'] = 'status desc, rank asc';
        $gifts = \Gifts::findPagination($cond, $page, $per_page);
        $this->view->gifts = $gifts;
        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc', 'columns' => 'id,name']);
        $this->view->product_channel_id = $product_channel_id;

        $this->view->type = intval($this->params('gift[type_eq]'));
        $this->view->pay_type = $this->params('gift[pay_type_eq]');
        $this->view->id = $this->params('gift[id_eq]');
    }

    function newAction()
    {
        $gift = new \Gifts();
        $gift->status = STATUS_OFF;
        $this->view->gift = $gift;
    }

    function createAction()
    {
        if (\Gifts::hasUploadLock()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '有文件上传中,请稍后上传');
        }

        $gift = new \Gifts();
        $this->assign($gift, 'gift');
        if ($gift->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $gift);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('gift' => $gift->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function editAction()
    {
        $gift = \Gifts::findById($this->params('id'));
        $this->view->gift = $gift;
    }

    function updateAction()
    {
        if (\Gifts::hasUploadLock()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '有文件上传中,请稍后上传');
        }

        $gift = \Gifts::findById($this->params('id'));
        $this->assign($gift, 'gift');
        if($gift->status == STATUS_ON && !$gift->product_channel_ids){
            return $this->renderJSON(ERROR_CODE_FAIL, '先选择支持的产品渠道');
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $gift);
        if ($gift->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('gift' => $gift->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }

    function platformsAction()
    {
        $id = $this->params('id');
        $gift = \Gifts::findFirstById($id);
        $platforms = \Products::$PLATFORMS;
        $all_select_platforms = explode(',', $gift->platforms);
        $this->view->id = $id;
        $this->view->platforms = $platforms;
        $this->view->all_select_platforms = $all_select_platforms;
    }

    function updatePlatformsAction()
    {
        $id = $this->params('id');
        $gift = \Gifts::findFirstById($id);
        $platforms = $this->params('platforms', ['*']);
        if (in_array('*', $platforms)) {
            $platforms = ['*'];
        }

        $gift->platforms = implode(',', $platforms);
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $gift);
        if ($gift->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/gifts']);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }

    function productChannelIdsAction()
    {
        $id = $this->params('id');
        $gift = \Gifts::findFirstById($id);

        $product_channels = \ProductChannels::find(['id' => 'desc']);

        $select_product_channel_ids = [];
        $product_channel_ids = $gift->product_channel_ids;
        if (isPresent($product_channel_ids)) {
            $select_product_channel_ids = explode(',', $product_channel_ids);
            $select_product_channel_ids = array_filter($select_product_channel_ids);
        }

        $this->view->select_product_channel_ids = $select_product_channel_ids;

        $this->view->product_channels = $product_channels;
        $this->view->id = $id;
    }

    function updateProductChannelIdsAction()
    {
        $id = $this->params('id');
        $gift = \Gifts::findFirstById($id);
        if (isBlank($gift)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '礼物不存在');
        }

        $product_channel_ids = $this->params('product_channel_ids');
        if ($product_channel_ids) {
            $product_channel_ids = implode(',', $product_channel_ids);
            $gift->product_channel_ids = ',' . $product_channel_ids . ',';
        } else {
            $gift->product_channel_ids = '';
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $gift);
        if ($gift->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/gifts']);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }
}