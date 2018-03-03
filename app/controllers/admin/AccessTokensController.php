<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/27
 * Time: 下午3:34
 */

namespace admin;

class AccessTokensController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $access_tokens = \AccessTokens::findPagination(['order' => 'id desc'], $page, $per_page);
        $this->view->access_tokens = $access_tokens;
    }

    function editAction()
    {
        $id = $this->params('id');
        $access_token = \AccessTokens::findFirstById($id);
        $this->view->access_token = $access_token;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $access_token = \AccessTokens::findFirstById($id);

        $this->assign($access_token, 'access_token');

        if ($access_token->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
    }
}