<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/6
 * Time: 上午9:32
 */

namespace api;

class AudioChaptersController extends BaseController
{
    function indexAction()
    {
        $audio_id = intval($this->params('audio_id', 0));

        if (!$audio_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $page = $this->params('page');
        $per_page = $this->params('per_page', 5);

        $audio_chapters = \AudioChapters::search($audio_id, $page, $per_page);
        $json = $audio_chapters->toJson('audio_chapters', 'toSimpleJson');

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $json);
    }
}


