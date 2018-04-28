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

        $user_db = \Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_diamond';
        $incr_num = $user_db->get($cache_key);
        // 系统支出
        $cache_decr_key = 'draw_history_total_amount_decr_diamond';
        $decr_num = $user_db->get($cache_decr_key);

        $cache_decr_gold_key = 'draw_history_total_amount_decr_gold';
        $decr_gold_num = $user_db->get($cache_decr_gold_key);

        $this->view->total_incr_num = $incr_num;
        $this->view->total_decr_num = $decr_num;
        $this->view->total_decr_gold_num = $decr_gold_num;

        $draw_histories = \DrawHistories::findPagination($conds, $page);
        $this->view->draw_histories = $draw_histories;
    }

}