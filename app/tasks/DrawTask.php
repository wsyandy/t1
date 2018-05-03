<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 15:17
 */
class DrawTask extends \Phalcon\Cli\Task
{

    function checkUserAction()
    {

        $draw_histories = DrawHistories::find(['conditions' => 'total_pay_amount>:pay_amount:', 'bind' => ['pay_amount' => 50000]]);
        $user_ids = [];
        foreach ($draw_histories as $draw_history) {
            $user_ids[] = $draw_history->user_id;
        }

        $user_ids = array_unique($user_ids);
        echoLine($user_ids);
    }

    function sendDrawHistoryMessageAction()
    {
        $cache_hit_10w_key = 'draw_history_hit_all_notice';
        $hot_cache = Users::getHotWriteCache();
        $draw_history_id = $hot_cache->get($cache_hit_10w_key);

        if (!$draw_history_id) {
            return;
        }

        $hot_cache->del($cache_hit_10w_key);

        $draw_history = DrawHistories::findFirstById($draw_history_id);

        $user = $draw_history->user;
        $product_channel = $draw_history->product_channel;

        $content = <<<EOF
哇哦！ {$user->nickname}刚刚砸出{$draw_history->number}钻大奖！还不快来砸金蛋，试试手气~;
EOF;

        if ($draw_history->gift_id) {
            $content = <<<EOF
哇哦！ {$user->nickname}刚刚砸出{$draw_history->gift->name}大奖！还不快来砸金蛋，试试手气~;
EOF;
        }

        $body = '';
        $platforms = ['ios', 'android'];

        if (isProduction()) {
            foreach ($platforms as $platform) {
                GeTuiMessages::globalPush($product_channel, $platform, $content, $body);
            }
        }


        $users = Users::find([
            'conditions' => 'product_channel_id = :product_channel_id: and register_at > 0 and user_type = :user_type: and last_at >= :last_at:',
            'bind' => ['product_channel_id' => $product_channel->id, 'user_type' => USER_TYPE_ACTIVE, 'last_at' => time() - 5 * 86400],
            'columns' => 'id'
        ]);

        info($draw_history_id, count($users));

        $delay = 1;
        $user_ids = [];
        $num = 0;

        foreach ($users as $user) {

            $num++;
            $user_ids[] = $user->id;

            if ($num >= 50) {
                Chats::delay($delay)->batchSendTextSystemMessage($user_ids, $content);
                $delay += 2;
                $user_ids = [];
                $num = 0;
            }
        }
    }

    function fixAction($params)
    {
        $min_id = $params[0];
        $max_id = $params[1];

        $draw_histories = DrawHistories::find(['conditions' => 'id>=:min_id: and id<=:max_id:',
            'bind' => ['min_id' => $min_id, 'max_id' => $max_id]]);
        foreach ($draw_histories as $draw_history) {
            
        }
    }

}