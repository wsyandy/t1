<?php


namespace pt;

class BaseController extends \ApplicationController
{
    /**
     * @var \PartnerAccounts
     */
    private $_current_partner_account;

    function currentPartnerAccount()
    {
        $partner_account_id = $this->session->get('partner_account_id');
        $partner_account_md5 = $this->session->get('partner_account_md5');
        if (!$partner_account_id || !$partner_account_md5) {
            return null;
        }

        if ($this->_current_partner_account) {
            return $this->_current_partner_account;
        }

        $this->_current_partner_account = \PartnerAccounts::auth($partner_account_id, $partner_account_md5);
        return $this->_current_partner_account;
    }

    function beforeAction($dispatcher)
    {

        $this->view->is_development = isDevelopmentEnv();

        $controller_name = \Phalcon\Text::uncamelize($dispatcher->getControllerName());
        $action_name = \Phalcon\Text::uncamelize($dispatcher->getActionName());

        if (isProduction() && $this->request->isGet() && !$this->request->isAjax() && !$this->isHttps()) {

            $url = $this->getFullUrl();
            $url = preg_replace('/^http:/', 'https:', $url);

            $this->response->redirect($url);
            return false;
        }

        if (!$this->currentPartnerAccount()) {
            if ($this->request->isAjax()) {
                $this->renderJSON(ERROR_CODE_FAIL, '请重新登录', array('redirect_url' => '/partner'));
            } else {
                $this->response->redirect('/partner');
            }
            $this->view->disable();
            return false;
        }

        if ($this->currentPartnerAccount()->status != OPERATOR_STATUS_NORMAL) {
            $this->renderJSON(ERROR_CODE_FAIL, '账户禁用');
            return false;
        }

        $this->request->current_partner_account = $this->currentPartnerAccount();
        $this->request->current_username = $this->currentPartnerAccount()->username;

    }
}