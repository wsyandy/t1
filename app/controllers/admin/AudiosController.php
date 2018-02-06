<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/5
 * Time: 下午4:18
 */
namespace admin;

class AudiosController extends BaseController
{
    function indexAction()
    {
        $page = 1;
        $per_page = 100;
        $audios = \Audios::findPagination(['order' => 'id desc'], $page, $per_page);
        $this->view->audios = $audios;
    }

    function newAction()
    {
        $audio = new \Audios();
        $this->view->audio = $audio;
    }

    function createAction()
    {
        $audio = new \Audios();
        $this->assign($audio, 'audio');

        if ($audio->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $audio);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('audio' => $audio->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '创建失败');
        }
    }

    function editAction()
    {
        $audio = \Audios::findById($this->params('id'));
        $this->view->audio = $audio;
    }

    function updateAction()
    {
        $audio = \Audios::findById($this->params('id'));
        $this->assign($audio, 'audio');

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $audio);
        if ($audio->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('audio' => $audio->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }
}