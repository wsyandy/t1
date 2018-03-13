<?php

namespace admin;

class GiftResourcesController extends BaseController
{

    function indexAction()
    {
       $cond = [];
       $page = $this->params('page');
       $per_page = $this->params('per_page');

       $gift_resources = \GiftResources::findPagination($cond, $page, $per_page);
       $this->view->gift_resources = $gift_resources;
    }

}