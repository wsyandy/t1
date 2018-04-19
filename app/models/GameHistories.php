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
            $room_wait_key = "game_room_wait_" . $this->room_id;
            $room_enter_key = "game_room_enter_" . $this->room_id;
            $hot_cache->del($room_wait_key);
            $hot_cache->del($room_enter_key);
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

}