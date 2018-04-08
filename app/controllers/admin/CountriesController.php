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
        $id = $this->params('id');
        $chinese_name = $this->params('chinese_name');

        $cond = ['conditions' => 'id > 0', 'order' => 'id asc'];

        if ($id) {
            $cond['conditions'] .= " and id = :id:";
            $cond['bind']['id'] = $id;
        }

        if ($chinese_name) {
            $cond['conditions'] .= " and chinese_name like :chinese_name:";
            $cond['bind']['chinese_name'] = '%' . $chinese_name . '%';
        }

        $countries = \Countries::findPagination($cond, $page, 20);

        $this->view->countries = $countries;
        $this->view->id = $id;
        $this->view->chinese_name = $chinese_name;
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