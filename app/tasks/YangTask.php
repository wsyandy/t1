<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/13
 * Time: 下午2:49
 */
class YangTask extends \Phalcon\Cli\Task
{

    function testAction()
    {

        if (!$user) {
            return false;
        }

        echoLine($user->toDetailJson());
    }

    function testTwoAction()
    {

        $id = 75;
        $user = \Users::findFirstById($id);
//        $users = $user->followedList(1, 5);

        if ($users) {
            echoLine($users->toJson('users', 'toRelationJson'));
        }
    }
}
