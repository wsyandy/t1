<?php

namespace admin;

class UsersController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('user');
        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $cond['order'] = 'id desc';
        $users = \Users::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->users = $users;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function editAction()
    {
        $user = \Users::findFirstById($this->params('id'));
        $this->view->user = $user;
    }

    function updateAction()
    {
        $user = \Users::findFirstById($this->params('id'));
        $this->assign($user, 'user');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $user);
        $user->update();
        $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('user' => $user->toJson()));
    }

}