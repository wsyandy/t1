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
    function testBlockAction()
    {
        $db = DrawHistories::getHotWriteCache();
        $block_user = 'draw_histories_block_user_ids';
        $db->zadd($block_user, time(),147);
        $db->zadd($block_user, time(),258);
        info($db->zrange($block_user,0,-1));
    }

    function testAction()
    {
        $db = DrawHistories::getHotWriteCache();
        $block_user = 'draw_histories_block_user_ids';
        $db->zrem("draw_histories_block_user_ids", 147);

    }
}