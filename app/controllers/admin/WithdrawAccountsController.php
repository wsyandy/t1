<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/5
 * Time: 上午10:51
 */

namespace admin;

class WithdrawAccountsController extends BaseController
{
    function indexAction()
    {
        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;
        $cond = $this->getConditions('withdraw_account');
        
        $user_id = $this->params('user_id');

        
        if ($user_id) {

            if (isset($cond['conditions'])) {
                $cond['conditions'] .= ' and user_id =:user_id:';
            } else {
                $cond['conditions'] = ' user_id =:user_id:';
            }

            $cond['bind']['user_id'] = $user_id;
        }


        debug($cond);

        $cond['order'] = 'id desc';

        $status = $this->params('withdraw_account[status_eq]');

        if ('' !== $status) {
            $status = intval($status);
        }

        $withdraw_accounts = \WithdrawAccounts::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->withdraw_accounts = $withdraw_accounts;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->user_name = $this->params('withdraw_account[user_name_eq]');
        $this->view->id = $this->params('withdraw_account[id_eq]');
        $this->view->status = $status;
        $this->view->user_id = $user_id;
    }

}