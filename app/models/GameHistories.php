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

    function afterUpdate()
    {

        if ($this->hasChanged('status') && $this->status == GAME_STATUS_END) {
            $hot_cache = \Users::getHotWriteCache();
            $room_wait_key = "game_room_wait_" . $this->id;
            $room_enter_key = "game_room_enter_" . $this->id;
            $room_user_quit_key = "game_room_user_quit_" . $this->id;
            $hot_cache->del($room_wait_key);
            $hot_cache->del($room_enter_key);
            $hot_cache->del($room_user_quit_key);
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

    }

}