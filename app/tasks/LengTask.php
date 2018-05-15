<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/15
 * Time: 下午3:36
 */
class LengTask extends \Phalcon\Cli\Task
{
    function testAddAction()
    {
        $db = \Users::getUserDb();
        $lucky_user_key = \WishHistories::generateLuckyUserList(4);
        $db->zadd($lucky_user_key, time(), 647);
        $db->zadd($lucky_user_key, time(), 657);
    }
}