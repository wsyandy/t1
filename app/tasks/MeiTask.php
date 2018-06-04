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

    function testCreateFeedAction()
    {
        $user = Users::findFirstById(6);
        $content = <<<EOF
EOF;

        for ($i = 1; $i <= 10000; $i++) {
            echoLine("sss");
            Feeds::createdFeed($user, ['content' => $content, 'feed_topic_id' => 1]);
        }
    }

    function testGetTotalFeedsAction()
    {
        $feeds = Feeds::findTotalFeeds(1, 2);

        foreach ($feeds as $feed) {
            echoLine($feed);
        }
    }

    function clearGiftOrderAction()
    {

    }
}