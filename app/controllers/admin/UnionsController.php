<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/13
 * Time: 上午11:07
 */

namespace admin;

class UnionsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $unions = \Unions::findPagination([], $page, $per_page);

        $this->view->unions = $unions;
    }

    function editAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);
        $this->view->union = $union;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);
        $this->assign($union, 'union');

        if ($union->update()) {
            return renderJSON(ERROR_CODE_SUCCESS, '');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '');
    }

    function deleteAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);

        if ($union->delete()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '');
    }
}