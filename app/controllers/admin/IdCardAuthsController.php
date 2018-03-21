<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/19
 * Time: 下午8:00
 */

namespace admin;

class IdCardAuthsController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('id_card_auth');

        $page = $this->params('page');
        $per_page = 30;

        $cond['order'] = 'auth_status desc';
        $id_card_auths = \IdCardAuths::findPagination($cond, $page, $per_page);

        $this->view->id_card_auths = $id_card_auths;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function editAction()
    {
        $id = $this->params('id');
        $id_card_auth = \IdCardAuths::findFirstById($id);
        $this->view->id_card_auth = $id_card_auth;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $id_card_auth = \IdCardAuths::findFirstById($id);

        $this->assign($id_card_auth, 'id_card_auth');

        if ($id_card_auth->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '');
    }
}