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
            $music_list = \Musics::searchMusic($page, $per_page, $user_id);
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

        $type = $this->params('type');
        $singer_name = $this->params('singer_name');
        $name = $this->params('name');

        $opts = ['user_id' => $user->id, 'type' => $type, 'singer_name' => $singer_name, 'name' => $name];
        list($error_code, $error_reason, $music) = \Musics::upload($_FILES, $opts);

        $url = '';
        if ($error_code == ERROR_CODE_SUCCESS) {
            $music->down($user->id);
            if (!$music->file) {
                $music->updateFile($this->file('music[file]'));
            }
            $url = '/web/users';
        }
        return $this->renderJSON($error_code, $error_reason, ['error_url' => $url]);
    }

    function deleteAction()
    {
        if ($this->request->isPost()) {
            $user_id = $this->currentUserId();
            $delete_list = $this->params('delete_list', []);
            if (isBlank($delete_list)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您未选择文件');
            }
            debug($delete_list);
            \Musics::deleteMusic($user_id, $delete_list);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
        }
        return $this->renderJSON(ERROR_CODE_FAIL, '');
    }
}