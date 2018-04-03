<?php

namespace admin;

class ActiveUsersController extends BaseController
{
    function dayRankListAction()
    {
        $start_at = $this->params('start_at', date('Y-m-d', beginOfDay()));
        $find_at = strtotime($start_at);
        $stop_at = endOfDay($find_at);
        $active_user_number = [];
        $stat_db = \Stats::getStatDb();

        for ($find_at_dot = $find_at; $find_at_dot <= $stop_at; $find_at_dot += 600) {
            $key = 'online_user_list_' . date('YmdHi', $find_at_dot);
            $portion_active_user_number = $stat_db->zcard($key);
            $time = date('Y-m-d H:i:s',$find_at_dot);
            $active_user_number[$time] = $portion_active_user_number;
        }
        $this->view->start_at = $start_at;
        $this->view->active_user_number = json_encode($active_user_number,JSON_UNESCAPED_UNICODE);
    }

}
