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
        $room_id = intval($this->params('room_id', 0));
        if (!$room_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }
        $room = \Rooms::findFirstById($room_id);
        if (!$room || $room->theme_type != ROOM_THEME_TYPE_BROADCAST) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        if (!$room->audio_id) {
            debug($room_id);
            return $this->renderJSON(ERROR_CODE_FAIL, '此房间未配置音频');
        }

        $rank = intval($this->params('rank', 0));


        $audio_chapter = \AudioChapters::search($room->audio_id, $rank);

        if (!$audio_chapter) {
            return $this->renderJSON(ERROR_CODE_FAIL, '此房间未配置音频章节');
        }


        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['audio_chapter' => $audio_chapter->toSimpleJson()]);
    }
}


