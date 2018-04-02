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
        $hot_cache = \Users::getHotWriteCache();

        for ($find_at1 = $find_at; $find_at1 <= $stop_at; $find_at1 += 600) {
            $key = 'online_user_list_' . date('YmdHi', $find_at1);
            $portion_active_user_number = $hot_cache->zcard($key);
            $time = date('Y-m-d H:i:s',$find_at1);
            $active_user_number[$time] = $portion_active_user_number;
        }
        $this->view->start_at = $start_at;
        $this->view->active_user_number = json_encode($active_user_number,JSON_UNESCAPED_UNICODE);
    }

}
