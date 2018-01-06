<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/6
 * Time: 下午6:43
 */

namespace admin;

class ComplaintsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 20);
        $complaints = \Complaints::findPagination(['order' => 'id desc'], $page, $per_page);
        $this->view->complaints = $complaints;
    }
}