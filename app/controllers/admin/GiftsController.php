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
        $gifts = \Gifts::findPagination(['order' => 'rank asc'], $page, $per_page);
        $this->view->gifts = $gifts;
    }

    function newAction()
    {
        $gift = new \Gifts();
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
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $gift);
        if ($gift->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('gift' => $gift->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }

}