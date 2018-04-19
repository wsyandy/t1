<?php

namespace admin;

class GameHistoriesController extends BaseController
{

    function indexAction()
    {
        $conds = $this->getConditions('game_history');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $game_histories = \GameHistories::findPagination($conds, $page, $per_page);
        $this->view->game_histories = $game_histories;
    }
}