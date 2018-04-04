<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/4
 * Time: 下午7:47
 */

namespace admin;

class CountriesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $countries = \Countries::findPagination(['order' => 'id asc'], $page, 20);

        $this->view->countries = $countries;
    }

    function editAction()
    {
        $id = $this->params('id');
        $country = \Countries::findFirstById($id);
        $this->view->country = $country;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $country = \Countries::findFirstById($id);

        $this->assign($country, 'country');
        $country->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['country' => $country->toJson()]);
    }
}