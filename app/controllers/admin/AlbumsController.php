<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/5
 * Time: 下午6:01
 */

namespace admin;

class AlbumsController extends BaseController
{
    function detailAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 30);
        $user_id = $this->params('user_id');
        $auth_status = $this->params('auth_status');
        $cond = ['conditions' => 'user_id =' . $user_id, 'order' => 'id asc'];

        if ($auth_status) {
            $cond['conditions'] .= " and auth_status = $auth_status";
        }

        $albums = \Albums::findPagination($cond, $page, $per_page);

        $this->view->albums = $albums;
        $this->view->user_id = $user_id;
    }


    function updateAction()
    {
        $album_id = $this->params('id');
        $auth_status = $this->params('auth_status');

        $album = \Albums::findFirstById($album_id);
        $album->auth_status = $auth_status;
        $album->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function batchUpdateAction()
    {
        $album_ids = $this->params('ids');
        $auth_status = $this->params('auth_status');

        foreach ($album_ids as $album_id) {
            $album = \Albums::findFirstById($album_id);
            $album->auth_status = $auth_status;
            $album->update();

        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}