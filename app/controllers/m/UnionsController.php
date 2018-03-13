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
        } else {
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
            $need_apply = $this->params('need_apply', 1);
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
        $order = 'created_at desc';
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
        //要做找到union处理
        $user = $this->currentUser();
        if ($union && $union->user_id == $user->id) {
            $is_president = 1;
        } else {
            $is_president = 0;
        }
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

    }

    //用户列表
    function usersAction()
    {
        if ($this->request->isAjax()) {

            $union_id = $this->params('union_id');

            $page = $this->params('page', 1);
            $per_page = $this->params('per_page', 10);

            $res = [];

            $union = \Unions::findFirstById($union_id);

            if ($union) {
                $users = $union->users($page, $per_page);

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

    }

    //更新家族资料
    function updateAction()
    {

    }

    //解散
    function dissolutionUnionAction()
    {

    }

    //申请加入工会
    function applyJoinUnionAction()
    {

    }

    //同意加入工会
    function agreeJoinUnionAction()
    {

    }

    //拒绝加入工会
    function refusedJoinUnionAction()
    {

    }

    //退出
    function exitUnionAction()
    {

    }

    //踢出工会
    function kickingAction()
    {

    }

    //申请上热门
    function applyGoHotAction()
    {

    }
}