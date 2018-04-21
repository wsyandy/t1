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
        $union_id = $this->params('union_id');
        $auth_status = $this->params('auth_status');

        $cond = ['conditions' => 'auth_status = :auth_status: and status = :status: and type = :type:',
            'bind' => ['auth_status' => $auth_status, 'status' => STATUS_ON, 'type' => UNION_TYPE_PUBLIC]];

        if ($union_id) {
            $cond = ['conditions' => 'id = ' . $union_id];
        }

        $unions = \Unions::findPagination($cond, $page, $per_page);

        $this->view->unions = $unions;
        $this->view->auth_status = $auth_status;
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

    function usersAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('page', 20);
        $id = $this->params('id');
        $users = \Users::findPagination(['conditions' => 'union_id = ' . $id], $page, $per_page);
        $this->view->users = $users;
        $this->view->id = $id;
    }

    function addUserAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);

        if ($this->request->isPost()) {

            if (!$union) {
                return $this->renderJSON(ERROR_CODE_FAIL, "公会不存在");
            }

            $user_id = $this->params('user_id');

            $user = \Users::findFirstById($user_id);

            if (!$user) {
                return $this->renderJSON(ERROR_CODE_FAIL, "用户不存在");
            }

            if ($user->union_id) {
                if (UNION_TYPE_PRIVATE == $user->union_type) {
                    return $this->renderJSON(ERROR_CODE_FAIL, "用户已加入其它家族");
                } else {
                    return $this->renderJSON(ERROR_CODE_FAIL, "用户已加入其它公会");
                }
            }

            $db = \Users::getUserDb();
            $key = $union->generateUsersKey();

            if ($db->zscore($key, $user->id)) {
                return $this->renderJSON(ERROR_CODE_FAIL, "该用户已经加入您的家族");
            }

            $db->zadd($key, time(), $user_id);

            $user->union_id = $union->id;
            $user->union_type = $union->type;
            $user->update();

            \UnionHistories::createRecord($user_id, $union->id);

            return $this->renderJSON(ERROR_CODE_SUCCESS, "", ['error_url' => '/admin/unions/users/' . $id]);
        }

        $this->view->union = $union;
        $this->view->id = $id;
    }

    function deleteUserAction()
    {
        $user_id = $this->params('user_id');

        $user = \Users::findFirstById($user_id);

        $union = \Unions::findFirstById($user->union_id);

        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, "用户不存在");
        }

        if (isDevelopmentEnv()) {
            $opts = ['exit' => 'exit'];
            $union->exitUnion($user, $opts);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '线上禁止此操作');
        }


        return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
    }

    function familyAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $user_id = $this->params('user_id');
        $id = $this->params('id');
        $uid = $this->params('uid');
        $status = $this->params('status', STATUS_ON);

        $cond = [];
        $cond['order'] = "id desc";
        $cond['conditions'] = " type = " . UNION_TYPE_PRIVATE;

        if ($id) {
            $cond['conditions'] .= " and id = " . $id;
        }

        if ($uid) {
            $cond['conditions'] .= " and uid = " . $uid;
        }

        if ($user_id) {
            $user = \Users::findFirstById($user_id);
            $union_id = $user->union_id;
            if (!$id && $union_id) {
                $cond['conditions'] .= " and (id = $union_id  or  user_id = $user_id) ";
            } else {
                $cond['conditions'] .= " and user_id = $user_id ";
            }
        }

        if (!$id && !$user_id && !$uid) {
            $cond['conditions'] .= " and status = " . $status;
        }

        debug($cond);

        $unions = \Unions::findPagination($cond, $page, $per_page);

        $this->view->unions = $unions;
    }

    function settledAmountAction()
    {
        $id = $this->params('union_id');
        $union = \Unions::findFirstById($id);

        if ($this->request->isPost()) {
            $amount = $this->params('amount');
            $union->amount = $amount;

            if ($union->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '添加失败');
        }

        $this->view->union = $union;
        $this->view->amount = $union->amount;
        $this->view->union_id = $id;
    }

    function dayRankListAction()
    {
        $start_at = $this->params('start_at', date('Y-m-d', beginOfDay()));

        $product_channel_id = $this->params('product_channel_id');

        $page = $this->params('page', 1);

        $per_page = $this->params('per_page', 20);

        $opts = ['product_channel_id' => $product_channel_id, 'date' => date("Ymd", strtotime($start_at))];

        $unions = \Unions::findFameValueRankList('day', $page, $per_page, $opts);

        $this->view->unions = $unions;

        $this->view->start_at = $start_at;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channel_id = intval($product_channel_id);

    }

    function weekRankListAction()
    {
        $start_at = $this->params('start_at', date('Y-m-d', beginOfWeek()));

        $start = date("Ymd", strtotime($start_at));

        $end = date("Ymd", strtotime($start) + 6 * 86400);

        $product_channel_id = $this->params('product_channel_id');

        $opts = ['product_channel_id' => $product_channel_id, 'start' => $start, 'end' => $end];

        $page = $this->params('page', 1);

        $per_page = $this->params('per_page', 20);

        $unions = \Unions::findFameValueRankList('week', $page, $per_page, $opts);

        $this->view->unions = $unions;

        $this->view->start_at = $start_at;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channel_id = intval($product_channel_id);

    }

    function totalRankListAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $unions = \Unions::findPagination(['order' => 'fame_value desc'], $page, $per_page);

        $this->view->unions = $unions;
    }
}