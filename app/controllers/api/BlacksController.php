<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:47
 */

namespace api;

class BlacksController extends BaseController
{
    //黑名单列表
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $users = $this->currentUser()->blackList($page, $per_page);

        if ($users) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toRelationJson'));
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //拉黑
    function createAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $this->currentUser()->black($this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //取消拉黑
    function destroyAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        $this->currentUser()->unBlack($this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}