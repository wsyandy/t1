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
        $sid = $this->params('sid');
        $code = $this->params('code');

        if (isBlank($union)) {
            $this->view->union = 0;
            $this->view->avatar_small_url = '';
        } else {
            if ($union->type == UNION_TYPE_PRIVATE) {
                $this->view->avatar_small_url = $union->avatar_small_url;
            } else {
                $this->view->avatar_small_url = $union->user->avatar_small_url;
            }
            $this->view->union = $union;
        }

        $this->view->sid = $sid;
        $this->view->code = $code;
        $this->view->current_user = $this->currentUser();
    }

    function addUnionAction()
    {
        $union = $this->currentUser()->union;
        $sid = $this->params('sid');
        $code = $this->params('code');

        if ($union) {
            return $this->response->redirect("/m/unions/my_union?union_id={$union->id}&sid={$sid}&code={$code}");
        }

        $this->view->title = "创建家族";
        $this->view->sid = $sid;
        $this->view->code = $code;
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

            $url = '';

            if ($error_code == ERROR_CODE_SUCCESS) {
                $sid = $this->params('sid');
                $code = $this->params('code');
                $url = "/m/unions/index?sid={$sid}&code={$code}";
            }

            return $this->renderJSON($error_code, $error_reason, ['error_url' => $url]);
        }
    }

    function recommendAction()
    {
        $union = $this->currentUser()->union;
        if (isPresent($union)) {
            $this->view->show_tip = 1;
        } else {
            $this->view->show_tip = 0;
        }
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
        $this->view->title = "推荐家族";
    }

    //搜索
    function searchAction()
    {
        $recommend = $this->params('recommend', 0);
        $id = $this->params('search_value', 0);
        $order = $this->params('order', null);

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

        $opts = ['type' => UNION_TYPE_PRIVATE, 'recommend' => $recommend, 'id' => $id, 'order' => $order];
        $user = $this->currentUser();

        if ($recommend) {
            $unions = \Unions::recommend(1, 5);
        } else {
            $unions = \Unions::search($user, $page, $per_page, $opts);
        }

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
        $sid = $this->params('sid');
        $code = $this->params('code');
        $click_from = $this->params('click_from');

        $union = \Unions::findFirstById($union_id);
        $president = $union->user;

        if ($union->status != STATUS_ON || $union->type != UNION_TYPE_PRIVATE ||
            ($union->id != $this->currentUser()->union_id && 'my_union' == $click_from)
        ) {
            return $this->response->redirect("/m/unions?sid=$sid&code=$code");
        }

        $user = $this->currentUser();
        if ($union && $union->user_id == $user->id) {
            $is_president = 1;
        } else {
            $is_president = 0;
        }
        $this->view->president = $president;
        $this->view->user = $user;
        $this->view->is_president = $is_president;
        $this->view->union = $union;
        if ($union && $union->id == $user->union_id) {
            $this->view->title = "我的家族";
        } else {
            $this->view->title = $union->name;
        }
        $this->view->sid = $sid;
        $this->view->code = $code;
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

    function fameValueRankListAction()
    {
        $list_type = $this->params('list_type');
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

        if ($list_type != 'day' && $list_type != 'week') {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $unions = \Unions::findFameValueRankList($list_type, $page, $per_page);

        $unions_json = $unions->toJson('unions', 'toSimpleJson');

        $union = $this->currentUser()->union;

        if (isPresent($union)) {
            $unions_json['my_rank'] = $union->unionFameRank($list_type);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $unions_json);
    }

    //用户列表
    function usersAction()
    {
        if ($this->request->isAjax()) {

            $union_id = $this->params('union_id');

            $union = \Unions::findFirstById($union_id);

            $res = ['users' => []];

            if ($union) {
                $page = $this->params('page', 1);
                $per_page = $this->params('per_page', 10);
                $order = $this->params('order', null);
                $filter_id = $this->params('filter_id', null);

                $opts = ['order' => $order, 'filter_id' => $filter_id];

                $users = $union->users($page, $per_page, $opts);

                if (count($users)) {
                    $res = $users->toJson('users', 'toUnionJson');
                }
            }

            $res['user_num'] = $union->userNum();

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
        }
    }

    //新用户
    function newUsersAction()
    {
        $current_user = $this->currentUser();
        $union = $current_user->union;
        $code = $this->params('code');
        $sid = $this->params('sid');

        if (!$current_user->isUnionHost($union)) {
            return $this->response->redirect("/m/unions?sid=$sid&code=$code");
        }
        $this->view->title = "家族申请";
        $this->view->sid = $sid;
        $this->view->code = $code;
        $union->clearNewApplyNum();
    }

    function applicationListAction()
    {
        $union = $this->currentUser()->union;

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
        $current_user = $this->currentUser();
        $union = $current_user->union;
        $code = $this->params('code');
        $sid = $this->params('sid');

        if (!$current_user->isUnionHost($union)) {
            return $this->response->redirect("/m/unions?sid=$sid&code=$code");
        }

        $user = \Users::findFirstById($this->params('user_id'));
        $user->apply_status = $union->applicationStatus($user->id);
        $this->view->user = $user;
        $this->view->title = "申请详情";
        $this->view->sid = $sid;
        $this->view->code = $code;
    }

    function editAction()
    {
        $current_user = $this->currentUser();
        $union = $current_user->union;
        $code = $this->params('code');
        $sid = $this->params('sid');

        if (!$current_user->isUnionHost($union)) {
            return $this->response->redirect("/m/unions?sid=$sid&code=$code");
        }

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

        if ($name != $union->name) {

            $exist_union = \Unions::findFirstByName($name);

            if ($exist_union) {
                return $this->renderJSON(ERROR_CODE_FAIL, '家族名称已存在');
            }
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

    //申请加入公会
    function applyJoinUnionAction()
    {
        $user = $this->currentUser();
        $union_id = $this->params('union_id');
        $union = \Unions::findFirstById($union_id);
        list($error_code, $error_reason) = $union->applyJoinUnion($user);
        $url = '';
        if ($error_code == ERROR_CODE_SUCCESS && $union->applicationStatus($user->id) == 1) {
            $sid = $this->params('sid');
            $code = $this->params('code');
            $url = "/m/unions/my_union?sid=${sid}&code=${code}&union_id=${union_id}";
        }

        return $this->renderJSON($error_code, $error_reason, ['error_url' => $url]);
    }

    //处理申请
    function handelApplicationAction()
    {
        $user = \Users::findFirstById($this->params('user_id'));
        $status = $this->params('status');
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

            list($error_code, $error_reason) = $union->applyExitUnion($user);
            return $this->renderJSON($error_code, $error_reason);
        }
    }

    function confirmApplyExitAction()
    {
        $current_user = $this->currentUser();
        $user_id = $this->params('user_id');

        $union = $current_user->union;
        $user = \Users::findFirstById($user_id);
        list($error_code, $error_reason) = $union->confirmExitUnion($user);
        return $this->renderJSON($error_code, $error_reason);

    }

    function applyExitAction()
    {
        $current_user = $this->currentUser();

        $user_id = $this->params('user_id');
        $user = \Users::findFirstById($user_id);
        $union = \Unions::findFirstById($user->union_id);
        $code = $this->params('code');
        $sid = $this->params('sid');

        if (!$union) {
            return $this->response->redirect("/m/unions?sid=$sid&code=$code");
        }

        $this->view->user_id = $user_id;
        $this->view->user = $current_user;
        $this->view->title = "家族申请";
    }

    function clearNewUsersAction()
    {
        if ($this->request->isAjax()) {
            $current_user = $this->currentUser();
            $union = $current_user->union;
            list($error_code, $error_reason) = $union->clearNewUsers($current_user);
            return $this->renderJSON($error_code, $error_reason);
        }
    }

    //踢出公会
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
        $current_user = $this->currentUser();
        $union = $current_user->union;
        $code = $this->params('code');
        $sid = $this->params('sid');

        if (!$current_user->isUnionHost($union)) {
            return $this->response->redirect("/m/unions?sid=$sid&code=$code");
        }

        $time = time();
        $days = [];
        $hours = [];
        for ($i = 1; $i < 8; $i++) {
            $day = beginOfDay($time + $i * 60 * 60 * 24);
            $days[date("m月d日", $day)] = $day;
        }

        for ($j = 0; $j < 24; $j = $j + 2) {
            $k = $j + 2;
            $key = "$j:00-$k:00";
            $hours[$key] = $j;
        }


        $this->view->days = $days;
        $this->view->hours = $hours;
        $this->view->user = $current_user;
        $this->view->union = $union;
        $this->view->title = "申请上热门";
        $this->view->sid = $sid;
        $this->view->code = $code;
    }

    function hotRoomHistoryAction()
    {
        if ($this->request->isAjax()) {
            $applicant = $this->currentUser();

            $user_id = $this->params('user_id');
            $day = $this->params('day');
            $hour = $this->params('hour');
            $start_at = $day + $hour * 60 * 60;
            $introduce = $this->params('introduce');

            $opts = ['user_id' => $user_id, 'start_at' => $start_at, 'introduce' => $introduce];

            list($error_code, $error_reason) = \HotRoomHistories::createHistories($opts, $applicant);
            return $this->renderJSON($error_code, $error_reason);
        }
    }

    function agreementAction()
    {
        $this->view->product_channel = $this->currentProductChannel();
    }


    function isNeedPasswordAction()
    {
        $room_id = $this->params('room_id');
        $room = \Rooms::findFirstById($room_id);
        $current_user_id = $this->currentUserId();
        $current_room_id = $this->currentUser()->current_room_id;
        if ($room->lock && $room->user_id != $current_user_id && $current_room_id != $room->id) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '需要密码');
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function checkPasswordAction()
    {
        $password = $this->params('password');
        debug($password);
        $room_id = $this->params('room_id');
        $room = \Rooms::findFirstById($room_id);
        $user_id = $this->currentUserId();
        if (!$room->lock) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        } else if ($room->password == $password) {
            $room->addFilterUser($user_id);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '密码错误');
    }
}