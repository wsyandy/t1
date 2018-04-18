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

    static $STATUS = [0 => '等待', 1 => '游戏中', 2 => '结束'];

}