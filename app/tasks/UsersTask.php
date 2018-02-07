<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 04/01/2018
 * Time: 16:40
 */
class UsersTask extends \Phalcon\Cli\Task
{

    function findByIdAction($params)
    {
        echoLine(Users::findFirstById($params[0]));
    }

    function addAuthUserAction()
    {
        $hot_db = \Users::getHotWriteCache();
        $offset = 0;
        while (true) {
            $user_ids = $hot_db->zrange('yuanfen_ids', $offset, $offset + 99);
            if (count($user_ids) <= 0) {
                break;
            }
            foreach ($user_ids as $user_id) {
                $hot_db->zadd("wait_auth_users", time(), $user_id);
            }
            $offset += 100;
        }
    }

    function exportAuthedUsersAction()
    {
        \Users::exportAuthedUser();
    }

    function importAuthedUsersAction()
    {
        \Users::importAuthedUser();
    }

}

