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
        $auth_status = $this->params('auth_status');

        $cond = ['conditions' => 'auth_status = :auth_status: and status = :status: and type = :type:',
            'bind' => ['auth_status' => $auth_status, 'status' => STATUS_ON, 'type' => UNION_TYPE_PUBLIC]];

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

            $user->union_id = $union->id;
            $user->union_type = $union->type;
            $user->update();

            \UnionHistories::createRecord($user_id, $union->id);

            return $this->renderJSON(ERROR_CODE_SUCCESS, "", ['error_url' => '/admin/unions/users/' . $id]);
        }

        $this->view->union = $union;
        $this->view->id = $id;
    }
}