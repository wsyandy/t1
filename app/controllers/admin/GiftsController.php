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
        $page = 1;
        $per_page = 100;
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
        $gift = new \Gifts();
        $this->assign($gift, 'gift');
        if ($gift->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('gift' => $gift->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '创建失败');
        }
    }

    function editAction()
    {
        $gift = \Gifts::findById($this->params('id'));
        $this->view->gift = $gift;
    }

    function updateAction()
    {
        $gift = \Gifts::findById($this->params('id'));
        $this->assign($gift, 'gift');
        if ($gift->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('gift' => $gift->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }

}