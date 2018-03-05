<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/28
 * Time: 下午3:30
 */
namespace web;
class UsersController extends BaseController
{
    function indexAction()
    {
        $user = $this->currentUser();

        $this->view->user = $user;
    }
}