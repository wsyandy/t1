<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/30
 * Time: 下午4:01
 */
namespace admin;

class EmoticonImagesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = 30;
        $emoticon_images = \EmoticonImages::findPagination(['order' => 'status desc,id desc'], $page, $per_page);
        $this->view->emoticon_images = $emoticon_images;
    }

    function newAction()
    {
        $emoticon_image = new \EmoticonImages();
        $this->view->emoticon_image = $emoticon_image;
    }

    function createAction()
    {
        $emoticon_image = new \EmoticonImages();
        $this->assign($emoticon_image, 'emoticon_image');

        list($error_code, $error_reason) = $emoticon_image->checkFields();
        if ($error_code == ERROR_CODE_FAIL) {
            return $this->renderJSON($error_code, $error_reason);
        }

        if ($emoticon_image->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $emoticon_image);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('emoticon_image' => $emoticon_image->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '创建失败');
        }
    }

    function editAction()
    {
        $emoticon_image = \EmoticonImages::findById($this->params('id'));
        $this->view->emoticon_image = $emoticon_image;
    }

    function updateAction()
    {
        $emoticon_image = \EmoticonImages::findById($this->params('id'));
        $this->assign($emoticon_image, 'emoticon_image');

        list($error_code, $error_reason) = $emoticon_image->checkFields();
        if ($error_code == ERROR_CODE_FAIL) {
            return $this->renderJSON($error_code, $error_reason);
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $emoticon_image);
        if ($emoticon_image->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('emoticon_image' => $emoticon_image->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }

    function platformsAction()
    {
        $id = $this->params('id');
        $emoticon_image = \EmoticonImages::findFirstById($id);
        $platforms = \Products::$PLATFORMS;
        $all_select_platforms = explode(',', $emoticon_image->platforms);
        $this->view->id = $id;
        $this->view->platforms = $platforms;
        $this->view->all_select_platforms = $all_select_platforms;
    }

    function updatePlatformsAction()
    {
        $id = $this->params('id');
        $emoticon_image = \EmoticonImages::findFirstById($id);
        $platforms = $this->params('platforms', ['*']);
        if (in_array('*', $platforms)) {
            $platforms = ['*'];
        }

        $emoticon_image->platforms = implode(',', $platforms);
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $emoticon_image);
        if ($emoticon_image->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/emoticon_images']);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }

    function productChannelIdsAction()
    {
        $id = $this->params('id');
        $emoticon_image = \EmoticonImages::findFirstById($id);

        $product_channels = \ProductChannels::find(['id' => 'desc']);

        $select_product_channel_ids = [];
        $product_channel_ids = $emoticon_image->product_channel_ids;
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
        $emoticon_image = \EmoticonImages::findFirstById($id);
        if (isBlank($emoticon_image)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '表情不存在');
        }

        $product_channel_ids = $this->params('product_channel_ids');
        if ($product_channel_ids) {
            $product_channel_ids = implode(',', $product_channel_ids);
            $emoticon_image->product_channel_ids = ',' . $product_channel_ids . ',';
        } else {
            $emoticon_image->product_channel_ids = '';
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $emoticon_image);
        if ($emoticon_image->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['emoticon_image' => $emoticon_image->toJson]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }
}