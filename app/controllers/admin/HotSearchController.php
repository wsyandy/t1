<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/7
 * Time: 10:54
 */
namespace admin;

class HotSearchController extends BaseController
{

    private $hotSearch = 'hot_search_words';


    private function getRedis()
    {
        return \Gifts::getHotReadCache();
    }


    public function indexAction()
    {
        $redis = $this->getRedis();
        $list = $redis->zrange($this->hotSearch, 0, -1, 'WITHSCORES');

        $hot_search = array();
        foreach ($list as $item=>$value) {
            $hot_search[] = array(
                'word' => $item,
                'weight' => $value
            );
        }
        $this->view->hot_search = $hot_search;
    }


    public function newAction()
    {
        $this->view->hot_search = array();
    }


    public function createAction()
    {
        $word = $this->params('word');
        $weight = $this->params('weight');

        $redis = $this->getRedis();
        if ($redis->zadd($this->hotSearch, time(), $word)) {
            return $this->renderJSON(ERROR_CODE_SUCCESS);
        }
        return $this->renderJSON(ERROR_CODE_FAIL);
    }


    public function editAction()
    {
        $id = $this->params('id');
        $list = $this->getRedis()->zrangeByScore($this->hotSearch, $id, $id);
        $this->view->word = $list[0];
        $this->view->weight = $id;
        $this->view->hot_search = array();
    }


    public function updateAction()
    {

    }
}