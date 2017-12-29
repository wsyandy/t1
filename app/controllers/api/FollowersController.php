<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:48
 */

namespace api;

class FollowersController extends BaseController
{
    //我关注的人
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $users = $this->currentUser()->followList($page, $per_page);

        if ($users) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toRelationJson'));
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //关注我的人
    function listAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $users = $this->currentUser()->followedList($page, $per_page);

        if ($users) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toRelationJson'));
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //关注
    function createAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }
        $this->currentUser()->follow($this->otherUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '关注成功');
    }

    //取消关注
    function destroyAction()
    {
        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }
        $this->currentUser()->unFollow($this->otherUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '取消关注成功');
    }
}