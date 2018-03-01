<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/28
 * Time: 下午3:30
 */
namespace web;
class MusicsController extends BaseController
{
    function indexAction()
    {
    }


    function listAction()
    {
        if ($this->request->isAjax()) {
            $user_id = $this->currentUserId();
            $page = $this->params('page');
            $per_page = $this->params('per_page');
            $music_list = \Musics::findByUserId($page, $per_page, $user_id);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $music_list->toJson('musics', 'toDetailJson'));
        }
    }


    function uploadAction()
    {
        $user = $this->currentUser();

        $this->view->user = $user;

        $types = \Musics::$TYPE;

        $this->view->types = $types;
    }


    function uploadMusicAction()
    {
        $user = $this->currentUser();
        $user_id = $user->id;

        $type = $this->params('type');
        $singer_name = $this->params('singer_name');
        $name = $this->params('name');

        $opts = ['user_id' => $user_id, 'type' => $type, 'singer_name' => $singer_name, 'name' => $name];
        list($error_code, $error_reason, $music) = \Musics::upload($_FILES, $opts);

        if ($error_code == ERROR_CODE_SUCCESS) {
            $music->updateFile($this->file('file'));
        }
        return $this->renderJSON($error_code, $error_reason);

    }
}