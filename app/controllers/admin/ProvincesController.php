<?php

namespace admin;

class ProvincesController extends BaseController
{
    function indexAction()
    {
        $provinces = \Provinces::find(['order' => 'id asc']);
        $this->view->provinces = $provinces;
    }

}
