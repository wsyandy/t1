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

        $albums = \Albums::findPagination(['order' => 'id desc'], $page, $per_page);

        $this->view->albums = $albums;
    }
}