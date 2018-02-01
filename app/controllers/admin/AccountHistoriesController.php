<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 15:22
 */

namespace admin;

class AccountHistoriesController extends BaseController
{
    function indexAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $account_histories = \AccountHistories::findAccountList($user_id, $page, $per_page);
        $this->view->account_histories = $account_histories;
        $this->view->user_id = $user_id;
    }

    function giveDiamondAction()
    {
        $user_id = $this->params('user_id');
        if ($this->request->isPost()) {
            $amount = intval($this->params('diamond'));
            $operator = $this->currentOperator();
            $remark = $operator->role_text .'：'. $operator->username . '赠送' . $amount . '钻石';
            $opts = array('remark' => $remark);
            if ($amount > 0) {
                \AccountHistories::changeBalance($user_id, ACCOUNT_TYPE_GIVE, $amount, $opts);
            }
            $this->response->redirect('/admin/account_histories?user_id=' . $user_id);
        }
        $this->view->user_id = $user_id;
    }
}