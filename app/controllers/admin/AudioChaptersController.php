<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/5
 * Time: 下午5:44
 */
namespace admin;
class AudioChaptersController extends BaseController
{
    function indexAction()
    {
        $audio_id = $this->params('audio_id');
        $audio_chapters = \AudioChapters::findByAudioId($audio_id);
        $this->view->audio_chapters = $audio_chapters;
        $this->view->audio_id = $audio_id;
    }

    function newAction()
    {
        $audio_chapter = new \AudioChapters();
        $this->view->audio_chapter = $audio_chapter;
        $this->view->audio_id = $this->params('audio_id');
    }

    function createAction()
    {
        $audio_chapter = new \AudioChapters();
        $this->assign($audio_chapter, 'audio_chapter');
        if (!$audio_chapter->check()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '排名不能重复');
        }
        if ($audio_chapter->create()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $audio_chapter);
            return $this->renderJSON(
                ERROR_CODE_SUCCESS, '',
                array('audio_chapter' => $audio_chapter->toJson())
            );
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function editAction()
    {
        $audio_chapter = \AudioChapters::findFirstById($this->params('id'));
        $this->view->audio_chapter = $audio_chapter;
        $this->view->audio_id = $audio_chapter->audio_id;
    }

    function updateAction()
    {
        $audio_chapter = \AudioChapters::findFirstById($this->params('id'));
        $this->assign($audio_chapter, 'audio_chapter');
        if (!$audio_chapter->check()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '排名不能重复');
        }
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $audio_chapter);
        if ($audio_chapter->update()) {
            return $this->renderJSON(
                ERROR_CODE_SUCCESS, '',
                array('audio_chapter' => $audio_chapter->toJson())
            );
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }
}
