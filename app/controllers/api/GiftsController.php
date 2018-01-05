<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 下午5:04
 */

namespace api;

class GiftsController extends BaseController
{

    function indexAction()
    {
        $gifts = \Gifts::findValidList();
        $user_diamond_info = array('diamond' => intval($this->currentUser()->diamond));
        return $this->renderJSON(
            ERROR_CODE_SUCCESS, '',
            array_merge($user_diamond_info, $gifts->toJson('gifts', 'toSimpleJson'))
        );
    }
}