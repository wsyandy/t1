<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/5/20
 * Time: 上午1:33
 */

namespace admin;

class CouplesController extends BaseController
{
    //cp统计
    function indexAction()
    {
        if($this->request->isAjax())
        {
            $page = $this->params('page');
            $per_page = 30;
            $couples = \Couples::findByUsersListForCp($page, $per_page);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['couples' => $couples]);
        }

    }
}