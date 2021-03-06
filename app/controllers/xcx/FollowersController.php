<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:48
 */

namespace xcx;

class FollowersController extends BaseController
{
    //我关注的人
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);
        $per_page = 10;
        $users = $this->currentUser()->followList($page, $per_page);

        $opts = ['follow_num' => $this->currentUser()->followNum()];

        if (count($users)) {
            $res = $users->toJson('users', 'toRelationJson');
            $opts = array_merge($res, $opts);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $opts);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $opts);
    }

    //关注我的人
    function listAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        $users = $this->currentUser()->followedList($page, $per_page);

        if (count($users)) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toRelationJson'));
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //关注
    function createAction()
    {
        if ($this->currentUser()->id == $this->otherUser()->id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '不能关注自己哦');
        }

        $this->currentUser()->follow($this->otherUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '关注成功');
    }

    //取消关注
    function destroyAction()
    {
        $this->currentUser()->unFollow($this->otherUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '取消关注成功');
    }
}