<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/29
 * Time: 上午1:05
 */

namespace admin;


class DrawHistoriesController extends BaseController
{

    function indexAction()
    {
        $conds = $this->getConditions('draw_history');
        $conds['order'] = 'id desc';
        $page = $this->params('page');

        $draw_histories = \DrawHistories::findPagination($conds, $page);
        $this->view->draw_histories = $draw_histories;
    }

}