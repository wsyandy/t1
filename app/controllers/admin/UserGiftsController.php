<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 20:51
 */

namespace admin;

class UserGiftsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $user_gifts = \UserGifts::findListByUserId($this->params('user_id'), $page, $per_page);
        $this->view->user_gifts = $user_gifts;
    }
}