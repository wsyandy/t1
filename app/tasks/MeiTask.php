<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/06/02
 * Time: 下午9:49
 */

class MeiTask extends \Phalcon\Cli\Task
{
    function testRemoteDelayAction()
    {
        $di = \Phalcon\Di::getDefault();
        $config = $di->get('config');
        echoLine($config->job_queue->remote_endpoint);

        Users::remoteDelay()->testRemoteDelay(['name' => 'user']);
    }

    function checkBoomHistoriesAction()
    {
        $cond = [
            'conditions' => 'created_at >= :start: and created_at <= :end: and boom_num = :boom_num:',
            'bind' => ['start' => beginOfDay(), 'end' => endOfDay(), 'boom_num' => 2],
            'columns' => 'distinct user_id'
        ];
        $boom_histories = BoomHistories::find($cond);
        echoLine(count($boom_histories));
    }
}