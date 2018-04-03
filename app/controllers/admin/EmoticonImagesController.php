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
}