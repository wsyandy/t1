<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/13
 * Time: 上午11:07
 */

namespace admin;

class UnionHistoriesController extends BaseController
{
    function basicAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $type = $this->params('type');
        $user_id = $this->params('user_id');
        $cond = ['conditions' => 'user_id = :user_id: and union_type = :union_type:',
            'bind' => ['user_id' => $user_id, 'union_type' => $type]];

        $union_histories = \UnionHistories::findPagination($cond, $page, $per_page);
        $this->view->union_histories = $union_histories;
    }
}