<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/19
 * Time: 下午8:57
 */


class SwooleTask extends Phalcon\CLI\Task
{

    function startAction()
    {
        SwooleWebsocketSever::start();
    }

    function reloadAction()
    {
        SwooleWebsocketSever::reload();
    }

    function shutdownAction()
    {
        SwooleWebsocketSever::shutdown();
    }

}