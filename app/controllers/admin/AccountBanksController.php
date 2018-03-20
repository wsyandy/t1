<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/20
 * Time: 上午11:34
 */
namespace admin;

class AccountBanksController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('account_bank');
        $conds['order'] = 'rank desc,id asc';
        $page = $this->params('page');
        $account_banks = \AccountBanks::findPagination($conds, $page);
        $this->view->account_banks = $account_banks;

    }

    function newAction()
    {
        $account_bank = new \AccountBanks();
        $this->view->account_bank = $account_bank;
    }

    function createAction()
    {
        $account_bank = new \AccountBanks();
        $this->assign($account_bank, 'account_bank');
        $account_bank->name = \AccountBanks::$BANK_CODE[$account_bank->code];
        $account_bank->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $account_bank);
        $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['account_bank' => $account_bank->toSimpleJson()]);
    }

    function editAction()
    {
        $account_bank = \AccountBanks::findFirstById($this->params('id'));
        $this->view->account_bank = $account_bank;
    }

    function updateAction()
    {
        $account_bank = \AccountBanks::findFirstById($this->params('id'));
        $this->assign($account_bank, 'account_bank');
        $account_bank->name = \AccountBanks::$BANK_CODE[$account_bank->code];
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $account_bank);
        $account_bank->save();
        $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['account_bank' => $account_bank->toSimpleJson()]);
    }
}