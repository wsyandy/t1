<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 21:06
 */
class MakiTask extends Phalcon\Cli\Task
{
    public function testxAction()
    {
        $url = 'http://test.momoyuedu.cn/api/backpacks';
        $res = httpGet($url);
        echoLine($res);
    }
}