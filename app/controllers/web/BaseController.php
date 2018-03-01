<?php

namespace web;

class BaseController extends \ApplicationController
{

    /**
     * @var \Users
     */
    private $_current_user;

    /**
     * @return \Users
     */
    function currentUser()
    {
        if (!isset($this->_current_user)) {
            $user_id = $this->currentUserId();
            $this->_current_user = \Users::findFirstById($user_id);

//            if (!$this->_current_user && isDevelopmentEnv()) {
//                $this->_current_user = \Users::findLast();
//            }
        }

        return $this->_current_user;
    }

    function currentUserId()
    {
//        if (isDevelopmentEnv()) {
//            $user = \Users::findLast();
//            return $user->id;
//        }
        return $this->session->get('user_id');
    }

    function beforeAction($dispatcher)
    {

        $this->view->title = "";

        $current_user = $this->currentUser();

        if (isBlank($current_user)) {
            $this->response->redirect('/web/home/login');
            return;
        }
    }
}