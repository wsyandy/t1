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
        if ($game_history->status == GAME_STATUS_WAIT) {
            $game_history->status = GAME_STATUS_END;
            $game_history->save();
        }
    }

}