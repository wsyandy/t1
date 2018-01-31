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
        $page = 1;
        $per_page = 100;
        $emoticon_images = \EmoticonImages::findPagination(['order' => 'id desc'], $page, $per_page);
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
        if ($emoticon_image->isRepeating()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '排序或code错误');
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
        if ($emoticon_image->isRepeating()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '排序或code错误');
        }
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $emoticon_image);
        if ($emoticon_image->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('emoticon_image' => $emoticon_image->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }
}