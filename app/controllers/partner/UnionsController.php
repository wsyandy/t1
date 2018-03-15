<?php

namespace partner;

class UnionsController extends BaseController
{
    function indexAction()
    {
        $union = $this->currentUser()->union;

        if (!$union) {
            list($error_code, $error_reason, $union) = \Unions::createPublicUnion($this->currentUser());

            if (ERROR_CODE_SUCCESS != $error_code) {
                echo "登录失败";
                return false;
            }
        }

        if ($union->needUpdateProfile()) {
            $forward = [
                "namespace" => "partner",
                "controller" => "unions",
                "action" => "update",
                "params" => $this->params()
            ];
            $this->dispatcher->forward($forward);
        }
    }

    function updateAction()
    {
        if ($this->request->isAjax()) {
            $name = $this->params('name');
            $id_name = $this->params('id_name');
            $id_no = $this->params('id_no');
            $alipay_account = $this->params('alipay_account');

            if (isBlank($name) || isBlank($id_name) || isBlank($id_no) || isBlank($alipay_account)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            if (!checkIdCard($id_no)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '身份证号码错误');
            }

            $params = ['name' => $name, 'id_name' => $id_name, 'id_no' => $id_no, 'alipay_account' => $alipay_account];

            $this->currentUser()->union->updateProfile($params);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/partner/unions']);
        }
    }

    public function logoutAction()
    {
        $this->session->set("user_id", null);
        $this->response->redirect("/partner/home");
    }

    function usersAction()
    {

    }

    function roomsAction()
    {

    }

    function incomeDetailsAction()
    {

    }

    function withdrawHistoriesAction()
    {

    }
}