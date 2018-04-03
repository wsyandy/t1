<?php

namespace admin;

class ActiveUsersController extends BaseController
{
    function dayRankListAction()
    {
        $start_at = $this->params('start_at', date('Y-m-d', beginOfDay()));
        $begin_at = beginOfDay(strtotime($start_at));
        $end_at = endOfDay($begin_at);
        $active_user_number = [];
        $stat_db = \Stats::getStatDb();

        for ($begin_at_dot = $begin_at; $begin_at_dot <= $end_at; $begin_at_dot += 600) {
            $key = 'online_user_list_' . date('YmdHi', $begin_at_dot);
            $portion_active_user_number = $stat_db->zcard($key);
            info($key, $portion_active_user_number);
            $time = date('Y-m-d H:i:s', $begin_at_dot);
            $active_user_number[$time] = intval($portion_active_user_number);
        }

        $this->view->start_at = $start_at;
        $this->view->active_user_number = json_encode($active_user_number, JSON_UNESCAPED_UNICODE);
    }

}
