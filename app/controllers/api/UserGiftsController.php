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
        $conds = ['conditions' => 'user_id = ' . $user_id, 'order' => 'amount desc'];
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 20);

        $user_gifts = \UserGifts::findPagination($conds, $page, $per_page);

        $user = \Users::findFirstById($user_id);

        $total_gift_num = $user->getReceiveGiftNum();

        $opts = array_merge(['total_gift_num' => $total_gift_num], $user_gifts->toJson('user_gifts', 'toJson'));

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $opts);
    }
}