<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/16
 * Time: 10:19
 */

namespace admin;

class BoomHistoriesController extends BaseController
{

    public function basicAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 20);

        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => [
                'user_id' => $user_id
            ],
            'order' => 'id desc'
        ];
        $boom_histories = \BoomHistories::findPagination($conditions, $page, $per_page);

        $this->view->boom_histories = $boom_histories;
        $this->view->user_id = $user_id;
    }

    function indexAction()
    {
        $conditions = ['order' => 'id desc'];

        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $boom_histories = \BoomHistories::findPagination($conditions, $page, $per_page);

        $this->view->boom_histories = $boom_histories;
    }
}