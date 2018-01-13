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
        $id = 182;
        $user = \Users::findFirstById($id);
        if (!$user) {
            return false;
        }

        $new = 1;
        $page = 1;
        $per_page = 30;
        $users = $user->friendList($page, $per_page, $new);
        echoLine(count($users),$users->toJson('users', 'toRelationJson'));
    }

    function testTwoAction()
    {
        $id = 182;
        $user = \Users::findFirstById($id);
        if (!$user) {
            return false;
        }
        echoLine($user->toDetailJson());
    }
}
