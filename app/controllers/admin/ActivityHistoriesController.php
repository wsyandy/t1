<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/5
 * Time: 下午8:11
 */

namespace admin;

class ActivityHistoriesController extends BaseController
{
    function basciAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $activity_histories = \ActivityHistories::findPagination(['conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id]], $page, 20);
        $this->view->activity_histories = $activity_histories;
    }
}