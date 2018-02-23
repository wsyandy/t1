<?php

namespace admin;

class ThirdAuthsController extends BaseController
{

    function indexAction()
    {
        $page = $this->params('page');
        $conditions = $this->getConditions('third_auth');
        $conditions['order'] = 'id desc';
        $third_auths = \ThirdAuths::findPagination($conditions, $page, 20);
        $this->view->third_auths = $third_auths;
    }

    function deleteAction()
    {
        $third_auth = \ThirdAuths::findFirstById($this->params('id'));
        if (isBlank($third_auth)) {
            $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
        if ($third_auth->delete()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '操纵成功');
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

}