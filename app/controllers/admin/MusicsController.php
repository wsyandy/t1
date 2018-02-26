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
        $audios = \Musics::findPagination($cond, $page, $per_page);
        $this->view->audios = $audios;
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
        $user = \Users::findFirstById($music->user_id);
        if (isBlank($user)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '用户不存在');
        }
        if ($_FILES['music']['size'] > 20000000) {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '上传文件大小不能超过20M');
        }
        $music->file_size = $_FILES['music']['size'];

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
        $music = \Musics::findById($this->params('id'));
        $this->assign($music, 'music');
        $user = \Users::findFirstById($music->user_id);
        if (isBlank($user)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '用户不存在');
        }
        if ($_FILES['music']['size'] > 20000000) {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '上传文件大小不能超过20M');
        }
        $music->file_size = $_FILES['music']['size'];

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $music);
        if ($music->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('music' => $music->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }
}