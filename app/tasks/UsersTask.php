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

    /**
     * 导入用户
     */
    function importUserAction($opts = array())
    {
        $filename = fetch($opts, 0, 'user_detail.log');
        $path = APP_ROOT . 'log/' . $filename;
        $from_dev = false;
        if (preg_match('/^dev_/', $filename)) {
            $from_dev = true;
        }

        echoLine($path, $from_dev);

        $yuanfen = new \Yuanfen($path, $from_dev);
        $yuanfen->parseFile();
    }

    function silentUserAction()
    {
        $user_id = 2;
        while (true) {
            $user = \Users::findById($user_id);
            if (isBlank($user)) {
                break;
            }
            if ($user && $user->isSilent() && isBlank($user->avatar)) {
                \Yuanfen::addSilentUser($user);
            }
            $user_id += 1;
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

