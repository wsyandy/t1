<?php

namespace admin;

class PartnerAccountsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $partner_accounts = \PartnerAccounts::findPagination(['order' => 'id desc'], $page, 60);

        $root = $this->getRoot();
        $this->view->root = $root;
        $this->view->partner_accounts = $partner_accounts;
    }

    function editAction()
    {
        $partner_account = \PartnerAccounts::findFirstById($this->params('id'));
        $this->view->partner_account = $partner_account;
    }

    function updateAction()
    {
        $partner_account = \PartnerAccounts::findFirstById($this->params('id'));
        $old_password = $partner_account->password;
        $this->assign($partner_account, 'partner_account');

        if (isBlank($partner_account->password)) {
            $partner_account->password = $old_password;
        } else {
            $partner_account->password = md5($partner_account->password);
        }
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $partner_account);
        if ($partner_account->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功', ['partner_account' => $partner_account->toSimpleJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }

    function newAction()
    {
        $partner_account = new \PartnerAccounts();
        $this->view->partner_account = $partner_account;

    }

    function createAction()
    {
        $partner_account = new \PartnerAccounts();
        $this->assign($partner_account, 'partner_account');
        //去重
        $res = \PartnerAccounts::findFirstByUsername($partner_account->username);

        if (isBlank($res)) {
            $partner_account->password = md5($partner_account->password);
            $partner_account->save();
            \OperatingRecords::logAfterCreate($this->currentOperator(), $partner_account);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '新增成功', ['partner_account' => $partner_account->toSimpleJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '该用户名已经存在');
        }

    }

    function configsAction()
    {
        $partner_account_product_channels = \PartnerAccountProductChannels::findByPartnerAccountId($this->params('id'));
        $this->view->partner_account_product_channels = $partner_account_product_channels;
        $this->view->partner_account_id = $this->params('id');
    }

    function newConfigsAction()
    {
        $partner_account = \PartnerAccounts::findFirstById($this->params('id'));
        $this->view->partner_account = $partner_account;

        $all_product_channels = \ProductChannels::find(array('order' => ' id desc'));
        $this->view->product_channels = $all_product_channels;
        $all_partners = \Partners::find(array('order' => ' id desc'));
        $this->view->partners = $all_partners;
    }

    function updateConfigsAction()
    {
        $opts = $this->params('partner_account');
        if (!fetch($opts, 'partner_id') || !fetch($opts, 'product_channel_id')) {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置不完整');
        }
        $partner_account_product_channel = \PartnerAccountProductChannels::findFirst(['conditions' =>
            'partner_id=:partner_id: and product_channel_id=:product_channel_id: and partner_account_id=:partner_account_id:',
            'bind' => ['partner_id' => fetch($opts, 'partner_id'), 'product_channel_id' => fetch($opts, 'product_channel_id'),
                'partner_account_id' => fetch($opts, 'id')
            ]
        ]);

        if($partner_account_product_channel){
            return $this->renderJSON(ERROR_CODE_FAIL, '已存在');
        }

        $partner_account_product_channel = new \PartnerAccountProductChannels();
        $partner_account_product_channel->partner_id = fetch($opts, 'partner_id');
        $partner_account_product_channel->product_channel_id = fetch($opts, 'product_channel_id');
        $partner_account_product_channel->partner_account_id = fetch($opts, 'id');
        $partner_account_product_channel->save();

        $this->renderJSON(ERROR_CODE_SUCCESS, '', ['redirect_url' => '/admin/partner_accounts/configs/' . $partner_account_product_channel->partner_account_id]);
    }

}