<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/19
 * Time: 下午8:57
 */


class WebsocketTask extends Phalcon\CLI\Task
{

    function startAction()
    {
        WebsocketSever::start();
    }

    function reloadAction()
    {
        WebsocketSever::reload();
    }

    function shutdownAction()
    {
        WebsocketSever::shutdown();
    }

}