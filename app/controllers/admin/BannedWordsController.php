<?php

namespace admin;

class BannedWordsController extends BaseController
{

    function indexAction()
    {

        $conds = $this->getConditions('banned_word');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $banned_words = \BannedWords::findPagination($conds, $page);
        $this->view->banned_words = $banned_words;
    }

    function newAction()
    {
        $banned_word = new \BannedWords();
        $this->view->banned_word = $banned_word;
    }

    function createAction()
    {
        $banned_word = new \BannedWords();
        $this->assign($banned_word, 'banned_word');

        $redis = \BannedWords::getHotWriteCache();
        $key = \BannedWords::getBannedWordsListSignKey();

        $banned_word->save();

        $redis->hset($key, $banned_word->id, $banned_word->word);

        $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['banned_word' => $banned_word->toJson()]);
    }

    function editAction()
    {
        $banned_word = \BannedWords::findFirstById($this->params('id'));
        $this->view->banned_word = $banned_word;
    }

    function updateAction()
    {

        $banned_word = \BannedWords::findFirstById($this->params('id'));
        $this->assign($banned_word, 'banned_word');

        $redis = \BannedWords::getHotWriteCache();
        $key = \BannedWords::getBannedWordsListSignKey();
        $redis->hset($key, $banned_word->id, $banned_word->word);

        $banned_word->save();

        $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['banned_word' => $banned_word->toJson()]);
    }

    function deleteAction()
    {
        $banned_word = \BannedWords::findFirstById($this->params('id'));

        $redis = \BannedWords::getHotWriteCache();
        $key = \BannedWords::getBannedWordsListSignKey();
        $redis->hdel($key, $banned_word->id);

        $banned_word->delete();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
    }

}