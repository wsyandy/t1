<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/5
 * Time: 下午4:18
 */

namespace admin;

class MusicsController extends BaseController
{
    function indexAction()
    {
        $page = 1;
        $per_page = 100;
        $cond = $this->getConditions('musics');
        $cond['order'] = 'id desc';
        $musics = \Musics::findPagination($cond, $page, $per_page);
        $this->view->musics = $musics;
    }

    function newAction()
    {
        $music = new \Musics();
        $this->view->music = $music;
    }

    function createAction()
    {
        $music = new \Musics();
        $this->assign($music, 'music');
        debug($_FILES);
        list($error_code, $error_reason) = $music->checkField($_FILES);
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }
        $music->updateFile($music,$_FILES);
        if(!$music->checkFileMd5($_FILES))
        {
            return $this->renderJSON(ERROR_CODE_FAIL, '不能重复上传文件');
        }
        if(!$music->checkRank())
        {
            return $this->renderJSON(ERROR_CODE_FAIL, '排序不能重复');
        }
        if ($music->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $music);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('music' => $music->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '创建失败');
        }
    }

    function editAction()
    {
        $music = \Musics::findById($this->params('id'));
        $this->view->music = $music;
    }

    function updateAction()
    {

        debug($_FILES, $this->file('music[file]'));
        $music = \Musics::findById($this->params('id'));
        $this->assign($music, 'music');
        list($error_code, $error_reason) = $music->checkField($_FILES);
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }
        list($error_code, $error_reason) = $music->updateFileMd5($music,$_FILES);
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $music);
        if ($music->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('music' => $music->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }
}