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
        $user_id = $this->params('id');
        $albums = \Albums::findPagination(['conditions' => 'user_id =' . $user_id, 'order' => 'id desc'], $page, $per_page);

        $this->view->albums = $albums;
    }
}