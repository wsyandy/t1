<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/13
 * Time: 下午2:49
 */
class MenTask extends \Phalcon\Cli\Task
{

    function testAction()
    {
        $user = Users::findFirstById(240);
        $user->version_code = 14;
        $user->save();
        echoLine($user->id);
    }
}
