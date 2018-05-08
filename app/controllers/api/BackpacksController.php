<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:43
 */
namespace api;

class BackpacksController extends BaseController
{
    public function indexAction()
    {
        $type = $this->params('type');
        $opt = [ 'type' => $type ];

        $list = \Backpacks::findListByUserId($this->currentUser(), $opt);
        $list = $list->toJson('backpacks', 'toSimpleJson');

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $list);
    }


    public function createAction()
    {

    }
}