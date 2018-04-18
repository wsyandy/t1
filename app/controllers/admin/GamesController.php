<?php
namespace admin;

class GamesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 8);
        $cond = $this->getConditions('game');
        $cond['order'] = 'rank desc, id desc';

        $games  = \Games::findPagination($cond, $page, $per_page);
        $this->view->games = $games;
    }

    function newAction()
    {
        $game = new \Games();
        $game->status = STATUS_OFF;
        $this->view->game = $game;
        $this->view->clazz_names = \Games::getGatewayClasses();
    }

    function createAction()
    {
        $game = new \Games();
        $this->assign($game, 'game');
        if ($game->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['game' => $game->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL);
        }

    }

    function editAction()
    {
        $game = \Games::findFirstById($this->params('id'));
        $this->view->game = $game;
        $this->view->clazz_names = \Games::getGatewayClasses();
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