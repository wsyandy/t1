<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/7
 * Time: 10:30
 */
class MakiTask extends Phalcon\Cli\Task
{
    function indexAction()
    {
        echoLine('test task!');
    }
}