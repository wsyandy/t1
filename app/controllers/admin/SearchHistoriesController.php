<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/5/6
 * Time: 下午10:35
 */

namespace admin;

class SearchHistoriesController extends BaseController
{
    function indexAction()
    {
        $cond = [
            'conditions' => 'type = room',
            'order' => 'num desc'
        ];

        $page = $this->params('page');

        $search_histories = \SearchHistories::findPagination($cond, $page, 30);

        $this->view->search_histories = $search_histories;
    }
}