<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/26
 * Time: 上午11:46
 */

namespace api;


class MusicsController extends BaseController
{
    function indexAction()
    {
        $hot = $this->params('hot');
        $search_name = $this->params('search_name');
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $cond = [];

        if ($hot) {
            $cond['conditions'] = 'hot = :hot:';
            $cond['bind']['hot'] = intval($hot);
        } elseif ($search_name) {

            $name = $search_name;
            $singer_name = $search_name;

            $cond['conditions'] = 'name like :name: or singer_name like :singer_name:';
            $cond['bind'] = ['name' => '%' . $name . "%", 'singer_name' => "%" . $singer_name . "%"];
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $cond['order'] = 'rank desc,id desc';

        debug("music_search", $cond, $this->params());
        $musics = \Musics::findPagination($cond, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $musics->toJson('musics', 'toSimpleJson'));
    }

    function downAction()
    {
        $id = $this->params('id');

        $music = \Musics::findFirstById($id);

        if (!$music) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $music->down($this->currentUser()->id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function deleteAction()
    {
        $id = $this->params('id');

        $music = \Musics::findFirstById($id);

        if (!$music) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $music->remove($this->currentUser()->id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}