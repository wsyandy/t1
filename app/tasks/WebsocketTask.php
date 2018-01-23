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
        $server = new PushSever();
        $server->start();
    }

    function reloadAction()
    {
        $server = new PushSever();
        $server->reload();
    }

    function shutdownAction()
    {
        $server = new PushSever();
        $server->shutdown();
    }

}