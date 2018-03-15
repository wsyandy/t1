<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/3/10
 * Time: 下午9:06
 */

namespace m;

class UnionsController extends BaseController
{
    //首页
    function indexAction()
    {
        $this->view->title = "家族";
        $user = $this->currentUser();
        $union = $user->union;
        if (isBlank($union)) {
            $this->view->union = 0;
            $this->view->avatar_url = '';
        } else {
            $this->view->avatar_url = $union->avatar_url;
            $this->view->union = $union;
        }

        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
    }

    function AddUnionAction()
    {
        $this->view->title = "创建家族";
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
    }

    //创建家族
    function createAction()
    {
        if ($this->request->isAjax()) {

            $file = $this->file('avatar_file');
            $name = $this->params('name');
            $notice = $this->params('notice');
            $need_apply = $this->params('need_apply');
            $opts = ['avatar_file' => $file, 'name' => $name, 'notice' => $notice, 'need_apply' => $need_apply];

            $user = $this->currentUser();

            list($error_code, $error_reason) = \Unions::createPrivateUnion($user, $opts);

            return $this->renderJSON($error_code, $error_reason);
        }
    }

    function recommendAction()
    {
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
        $this->view->title = "推荐家族";
    }

    //搜索
    function searchAction()
    {
        $recommend = $this->params('recommend', 0);
        $id = $this->params('search_value', 0);
        $type = $this->params('type', 0);
        $order = $this->params('order', null);
//        if (preg_match('/^\d+$/', $search_value)) {
//            $id = $search_value;
//        } else {
//            $id = 0;
//        }

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

//        $opts = ['type' => $type, 'recommend' => $recommend, 'name' => $search_value, 'id' => $id, 'order' => $order];
        $opts = ['type' => $type, 'recommend' => $recommend, 'id' => $id, 'order' => $order];
        $user = $this->currentUser();

        $unions = \Unions::search($user, $page, $per_page, $opts);

        $res = [];
        if (count($unions)) {
            $res = $unions->toJson('unions', 'toSimpleJson');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    //我的家族
    function myUnionAction()
    {
        $union_id = $this->params('union_id');
        $union = \Unions::findFirstById($union_id);
        $user = $this->currentUser();
        if ($union && $union->user_id == $user->id) {
            $is_president = 1;
        } else {
            $is_president = 0;
        }
        $this->view->user = $user;
        $this->view->is_president = $is_president;
        $this->view->union = $union;
        $this->view->title = "我的家族";
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
    }

    //其他家族
    function otherUnionAction()
    {

    }

    //家族排行榜
    function rankAction()
    {
        $this->view->title = "家族排行";
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
    }

    //用户列表
    function usersAction()
    {
        if ($this->request->isAjax()) {

            $union_id = $this->params('union_id');

            $res = [];

            $union = \Unions::findFirstById($union_id);

            if ($union) {
                $page = $this->params('page', 1);
                $per_page = $this->params('per_page', 10);
                $order = $this->params('order', null);

                $opts = ['order' => $order];

                $users = $union->users($page, $per_page, $opts);

                if (count($users)) {
                    $res = $users->toJson('users', 'toUnionJson');
                }
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
        }
    }

    //新用户
    function newUsersAction()
    {
        $this->view->title = "新的成员";
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');

    }

    function applicationListAction()
    {
        $user_id = $this->currentUserId();
        $union = \Unions::findFirstByUserId($user_id);

        $res = [];
        if (isPresent($union)) {
            $page = $this->params('page');
            $per_page = $this->params('per_page');
            $users = $union->newUsers($page, $per_page);
            if (count($users)) {
                $res = $users->toJson('users', 'toUnionJson');
            }
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }

    //申请详情
    function applicationDetailAction()
    {
        $user = \Users::findFirstById($this->params('user_id'));
        $union = $this->currentUser()->union;
        $user->application_status = $union->applicationStatus($user->id);
        $this->view->user = $user;
        $this->view->title = "申请详情";
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
    }

    function editAction()
    {
        $user = $this->currentUser();
        $union = $user->union;
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
        $this->view->union = $union;
        $this->view->title = "修改资料";
    }

    //更新家族资料
    function updateAction()
    {
        $avatar_file = $this->file('avatar_file');

        $name = $this->params('name');
        $notice = $this->params('notice');
        $need_apply = $this->params('need_apply', 1);
        $opts = ['name' => $name, 'notice' => $notice, 'need_apply' => $need_apply];

        $user = $this->currentUser();
        $union = $user->union;
        if (!$user->isUnionHost($union)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您没有权限');
        }

        if ($avatar_file) {
            $union->updateAvatar($avatar_file);
        }

        $union->updateProfile($opts);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }

    //解散
    function dissolutionUnionAction()
    {
        $user = $this->currentUser();
        $union = $user->union;
        list($error_code, $error_reason) = $union->dissolutionUnion($user);
        return $this->renderJSON($error_code, $error_reason);
    }

    //申请加入工会
    function applyJoinUnionAction()
    {
        $user = $this->currentUser();
        $union_id = $this->params('union_id');
        $union = \Unions::findFirstById($union_id);
        list($error_code, $error_reason) = $union->applyJoinUnion($user);
        return $this->renderJSON($error_code, $error_reason, '');
    }

    //处理申请
    function handelApplicationAction()
    {
        $user = \Users::findFirstById($this->params('user_id'));
        $status = $this->params('status');
        debug($status);
        $current_user = $this->currentUser();
        $union = $current_user->union;
        if ($status == 1) {

            list($error_code, $error_reason) = $union->agreeJoinUnion($current_user, $user);

        } else if ($status == -1) {

            list($error_code, $error_reason) = $union->refuseJoinUnion($current_user, $user);
        }
        return $this->renderJSON($error_code, $error_reason);
    }

    //退出
    function exitUnionAction()
    {
        if ($this->request->isAjax()) {

            $user = $this->currentUser();
            $union = \Unions::findFirstById($this->params('union_id'));

            $opts = ['exit' => "exit"];

            list($error_code, $error_reason) = $union->exitUnion($user, $opts);
            return $this->renderJSON($error_code, $error_reason);
        }
    }

    //踢出工会
    function kickingAction()
    {
        if ($this->request->isAjax()) {

            $current_user = $this->currentUser();
            $union = $current_user->union;

            $user = \Users::findFirstById($this->params('user_id'));

            $opts = ['kicking' => "kicking"];

            list($error_code, $error_reason) = $union->exitUnion($user, $opts, $current_user);
            return $this->renderJSON($error_code, $error_reason);
        }
    }

    //申请上热门
    function applyGoHotAction()
    {

    }
}