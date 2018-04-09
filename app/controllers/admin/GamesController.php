<?php
namespace admin;

class GamesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 8);
        $cond = $this->getConditions('game');
        $cond['order'] = 'id asc';

        $games  = \Games::findPagination($cond, $page, $per_page);
        $this->view->games = $games;
    }

    function newAction()
    {
        $game = new \Games();
        $this->view->game = $game;
    }

    function createAction()
    {
        $game = new \Games();
        $this->assign($game, 'game');

        if ($game->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('game' => $game->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL);
        }

    }

    function editAction()
    {
        $game = \Games::findFirstById($this->params('id'));
        $this->view->game = $game;
    }

    function updateAction()
    {
        $game = \Games::findFirstById($this->params('id'));
        $this->assign($game, 'game');

        if ($game->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('game' => $game->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL);
        }
    }
}