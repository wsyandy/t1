<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/21
 * Time: 下午2:58
 */

namespace admin;

class GoldHistoriesController extends BaseController
{
    function indexAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        debug($user_id,$page,$per_page);

        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];

        $gold_histories = \GoldHistories::findPagination($conditions, $page, $per_page);
        $this->view->gold_histories = $gold_histories;
        $this->view->user_id = $user_id;
    }
}