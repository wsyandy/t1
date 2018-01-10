<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 09/01/2018
 * Time: 22:35
 */

namespace api;

class UserGiftsController extends BaseController
{
    function indexAction()
    {
        $user_id = $this->params('user_id', $this->currentUserId());
        $conds = array('user_id' => $user_id, 'order' => 'amount desc');
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 20);

        $user_gifts = \UserGifts::findPagination($conds, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCCESS, '', $user_gifts->toJson('user_gifts', 'toJson'));

    }
}