<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/26
 * Time: 下午5:58
 */

namespace admin;

class HiCoinHistories extends BaseController
{
    function basicAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $hi_coin_histories = \HiCoinHistories::findPagination(['conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id]], $page, $per_page);

        $this->view->hi_coin_histories = $hi_coin_histories;
    }
}