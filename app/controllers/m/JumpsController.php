<?php

namespace m;

class JumpsController extends BaseController
{

    function notifyGameStatusAction()
    {
        $current_user = $this->currentUser();
        $raw_body = $this->params();

        info('当前用户ID', $current_user->id, '游戏推过来的数据', $raw_body);
        $type = fetch($raw_body, 'type');
        $room_id = fetch($raw_body, 'room_id');
        $game_history_id = fetch($raw_body, 'game_history_id');
        $room = \Rooms::findFirstById($room_id);
        $game_history = \GameHistories::findFirstById($game_history_id);

        if (!$room || !$game_history) {
            return false;
        }

        $is_host = $current_user->isRoomHost($room);

        switch ($type) {
            case 'wait':
                if ($game_history->status == GAME_STATUS_WAIT) {
                    $root = $this->getRoot();
                    $image_url = $root . 'images/go_game.png';
                    $body = ['action' => 'game_notice', 'type' => 'start', 'content' => $current_user->nickname . "发起了跳一跳游戏",
                        'image_url' => $image_url, 'client_url' => 'url://m/jumps/transfer_game_url?room_id=' . $room_id . '&game_history_id=' . $game_history_id];

                    $game_history->sendGameMessage($current_user, $body);
                }
                break;
            case 'start':
                if ($game_history->status == GAME_STATUS_WAIT) {
                    $game_history->status = GAME_STATUS_PLAYING;
                    $game_history->enter_at = time();
                    $game_history->update();
                }
                break;
            case 'over':
                //如果为房主，刷游戏记录，在afterUpdate中，将整个保存游戏用户队列删除
                info('游戏结束', $current_user->id, $game_history->status);
                if ($game_history->status != GAME_STATUS_END) {
                    $game_history->status = GAME_STATUS_END;
                    $game_history->update();
                }
        }
        return true;
    }

    function getGameClientUrlAction()
    {
        $current_user = $this->currentUser();

        $game_id = $this->params('game_id');
        $room_id = $this->params('room_id');

        $room = \Rooms::findFirstById($room_id);
        $game = \Games::findFirstById($game_id);
        if (!$room || !$game) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }
        $game_history = \GameHistories::createGameHistory($current_user, $game_id);
        $client_url = $game_history->generateGameUrl($current_user, $room);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['client_url' => $client_url]);

    }

    function transferGameUrlAction()
    {
        $user = $this->currentUser();
        $room_id = $this->params('room_id');
        $game_history_id = $this->params('game_history_id');
        $game_history = \GameHistories::findFirstById($game_history_id);

        $room = \Rooms::findFirstById($room_id);
        if (!$room || !$game_history) {
            info($room->id, $game_history->id);
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $client_url = $game_history->generateGameUrl($user, $room);
        info('跳转地址', $client_url);
        //当通过当前方法中转的用户保存在队列当中
        $game_history->saveUserList($user->id);

        $this->response->redirect($client_url);
    }


}