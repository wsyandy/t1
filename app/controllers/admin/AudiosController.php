<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/5
 * Time: 下午4:18
 */
namespace admin;

class AudiosController extends BaseController
{
    function indexAction()
    {
        $page = 1;
        $per_page = 100;
        $cond = $this->getConditions('audio');
        $cond['order'] = 'id desc';
        $audios = \Audios::findPagination($cond, $page, $per_page);
        $this->view->audios = $audios;
    }

    function newAction()
    {
        $audio = new \Audios();
        $this->view->audio = $audio;
    }

    function createAction()
    {
        $audio = new \Audios();
        $this->assign($audio, 'audio');

        if ($audio->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $audio);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('audio' => $audio->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '创建失败');
        }
    }

    function editAction()
    {
        $audio = \Audios::findById($this->params('id'));
        $this->view->audio = $audio;
    }

    function updateAction()
    {
        $audio = \Audios::findById($this->params('id'));
        $this->assign($audio, 'audio');

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $audio);
        if ($audio->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('audio' => $audio->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }

    function roomConfigAction()
    {
        $audio_id = $this->params('audio_id');
        debug($audio_id);
//        $audio = \Audios::findFirstById($audio_id);
        if ($this->request->isPost()) {
            $room_id = $this->params('room_id', 0);
            if ($room_id == 0) {
                $rooms = \Rooms::find(
                    [
                        'conditions' => 'theme_type != :theme_type: and user_type = :user_type: and audio_id is not null',
                        'bind' => ['theme_type' => ROOM_THEME_TYPE_BROADCAST, 'user_type' => USER_TYPE_SILENT]
                    ]
                );
                if ($rooms->total_entries == 0) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '可配置音频的沉默用户房间已用尽');
                }
                $rand = mt_rand(0, $rooms->total_entries - 1);
                $room = $rooms[$rand];
            } else {
                $room = \Rooms::findFirstById($room_id);
            }
            if (isBlank($room) || !$room->canSetAudio()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            $room->theme_type = ROOM_THEME_TYPE_BROADCAST;
            $room->audio_id = $audio_id;
            $room_seats = \RoomSeats::findByRoomId($room->id);
            foreach ($room_seats as $room_seat) {
                $room_seat->microphone = false;
                \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room_seat);
                $room_seat->save();
            }

            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room);
            if ($room->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '配置成功');
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
            }
        }
        $this->view->audio_id = $audio_id;
    }
}