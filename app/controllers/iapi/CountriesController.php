<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/7
 * Time: 下午3:33
 */

namespace iapi;

class CountriesController extends BaseController
{
    function indexAction()
    {
        $cond = [
            'conditions' => 'status = :status:',
            'bind' => ['status' => STATUS_ON],
            'order' => 'rank desc'
        ];

        $countries = \Countries::findPagination($cond, 1, 100);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $countries->toJson('countries', 'toSimpleJson'));
    }
}