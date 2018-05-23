<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/18
 * Time: 下午10:08
 */
class GameHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;
    /**
     * @type Rooms
     */
    private $_room;
    /**
     * @type Games
     */
    private $_game;

    static $STATUS = [GAME_STATUS_WAIT => '等待', GAME_STATUS_PLAYING => '游戏中', GAME_STATUS_END => '结束'];

    function afterCreate()
    {
        if ($this->status == GAME_STATUS_WAIT && $this->game->url == 'https://gtest.yueyuewo.cn') {
            //保存一个游戏用户队列
            $this->saveUserList($this->user_id);

            \GameHistories::delay(10 * 60)->asyncCloseGame($this->id);
        }
    }

    static function generateEnterGameUserList($room_id)
    {
        return 'enter_game_user_list' . $room_id;
    }

    function afterUpdate()
    {

        if ($this->hasChanged('status') && $this->status == GAME_STATUS_END) {
            $hot_cache = \Users::getHotWriteCache();
            $room_wait_key = "game_room_wait_" . $this->id;
            $room_enter_key = "game_room_enter_" . $this->id;
            $room_user_quit_key = "game_room_user_quit_" . $this->id;
            $game_user_list_key = \GameHistories::generateEnterGameUserList($this->room_id);
            $hot_cache->del($room_wait_key);
            $hot_cache->del($room_enter_key);
            $hot_cache->del($room_user_quit_key);
            $hot_cache->del($game_user_list_key);

            if ($this->game->url == 'https://gtest.yueyuewo.cn') {
                $user = \Users::findFirstById($this->user_id);
                $body = ['action' => 'game_notice', 'type' => 'over', 'content' => "游戏结束",];
                \Games::sendGameMessage($user, $body);
            }


        }
    }

    function canEnter()
    {
        if ($this->status == GAME_STATUS_PLAYING && (time() - $this->enter_at > 10)) {
            return false;
        }

        if ($this->status == GAME_STATUS_END) {
            return false;
        }

        return true;
    }

    static function asyncCloseGame($id)
    {
        $game_history = self::findFirstById($id);
        if ($game_history->status != GAME_STATUS_END) {
            $game_history->status = GAME_STATUS_END;
            $game_history->save();
        }
    }

    static function createGameHistory($current_user, $game_id)
    {
        //新游戏这边暂时无入场费逻辑，暂时写死为免费游戏
        $start_data = ['pay_type' => 'free', 'amount' => 0];
        $game_history = new \GameHistories();
        $game_history->game_id = $game_id;
        $game_history->user_id = $current_user->id;
        $game_history->room_id = $current_user->room_id;
        $game_history->status = GAME_STATUS_WAIT;
        $game_history->start_data = json_encode($start_data, JSON_UNESCAPED_UNICODE);
        $game_history->save();

        return $game_history;
    }


    function generateGameUrl($current_user, $room)
    {
        $is_host = $current_user->isRoomHost($room);
        $site = $current_user->current_room_seat_id;
        $owner = 1;
        if ($is_host) {
            $site = 1;
            $owner = 0;
        }

        if ($this->status == GAME_STATUS_PLAYING) {
            $site = 0;
        }
        $game_code = strtolower($this->game->clazz);
        $client_url = $this->game->url . '?sid=' . $current_user->sid . '&code=' . $current_user->product_channel->code .
            '&name=' . $this->game->name . '&username=' . $current_user->nickname . '&room_id=' . $room->id . '&game_code=' . $game_code .
            '&avater_url=' . $current_user->avatar_url . '&user_num_limit=8&site=' . $site . '&owner=' . $owner . '&game_history_id=' . $this->id;

        info('拼接跳转到游戏的链接', $client_url);

        return $client_url;
    }

    function saveUserList($user_id)
    {
        $cache = \GameHistories::getHotWriteCache();
        $game_user_list_key = \GameHistories::generateEnterGameUserList($this->room_id);
        $cache->zadd($game_user_list_key, time(), $user_id);

    }

    function delUserList($user_id)
    {
        $cache = \GameHistories::getHotWriteCache();
        $game_user_list_key = \GameHistories::generateEnterGameUserList($this->room_id);
        $cache->zrem($game_user_list_key, $user_id);

    }
}