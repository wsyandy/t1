<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:47
 */

namespace api;

class BlacksController extends BaseController
{
    //黑名单列表
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');


    }

    //拉黑
    function createAction()
    {

    }

    //取消拉黑
    function destroyAction()
    {

    }
}