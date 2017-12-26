<?php

namespace admin;

class CitiesController extends BaseController
{
    function indexAction()
    {
        $province_id = $this->params('province_id');
        $cities = \Cities::find(['conditions' => 'province_id=:province_id:',
            'bind' => ['province_id' => $province_id], 'order' => 'id asc']);

        $this->view->cities = $cities;
    }

}
