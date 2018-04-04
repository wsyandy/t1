<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/15
 * Time: 下午5:42
 */
class UnionsTask extends \Phalcon\Cli\Task
{
    //推荐家族task任务
    function recommendAction()
    {
        $db = Users::getUserDb();
        $key = "total_union_fame_value_day_" . date("Ymd", strtotime('-1 day'));

        $unions = Unions::findBy(['recommend' => STATUS_ON]);

        foreach ($unions as $union) {
            $union->recommend = STATUS_OFF;
            $union->update();
        }

        $union_recommend_key = "union_recommend_list";
        $db->zclear($union_recommend_key);

        $union_ids = $db->zrevrange($key, 0, 4, true);

        info($union_ids);

        foreach ($union_ids as $union_id => $value) {
            $db->zadd($union_recommend_key, $value, $union_id);
        }
    }

    function fixUnionRankListAction()
    {
        $unions = Unions::findForeach();
        $start = beginOfWeek();
        $end = endOfWeek();
        $db = Users::getUserDb();
        $start_at = date("Ymd", strtotime("last sunday next day", time()));
        $end_at = date("Ymd", strtotime("next monday", time()) - 1);
        $week_key = "total_union_fame_value_" . $start_at . "_" . $end_at;
        $union_ids = $db->zrange($week_key, 0, -1);
        $unions = Unions::findByIds($union_ids);

        foreach ($unions as $union) {
            if ($union->type == UNION_TYPE_PUBLIC) {
                $db->zrem($week_key, $union->id);
            }
        }

        $db->zclear($week_key);
        $db->zclear("total_union_fame_value_day_20180328");
        $db->zclear("total_union_fame_value_day_20180327");

        foreach ($unions as $union) {

            $gift_orders = GiftOrders::find(
                [
                    'conditions' => '(sender_union_id = :union_id: or receiver_union_id = :union_id1:) and created_at >= :start:' .
                        ' and created_at <= :end:',

                    'bind' => ['union_id' => $union->id, 'union_id1' => $union->id, 'start' => $start, 'end' => $end]
                ]
            );

            foreach ($gift_orders as $gift_order) {
                $day_key = "total_union_fame_value_day_" . date("Ymd", $gift_order->created_at);
                $db->zincrby($day_key, $gift_order->amount, $union->id);
                $db->zincrby($week_key, $gift_order->amount, $union->id);
            }
        }

        $union = Unions::findFirstById(1040);
        echoLine($union);
    }

    //认证主播奖励
    function authHostRewardAction()
    {
        $users = Users::find([
            'conditions' => 'id_card_auth = :id_card_auth:',
            'bind' => ['id_card_auth' => AUTH_SUCCESS]]);

        $current_day = intval(date('d'));
        $time = time() - $current_day * 86400 - 3600;
        $start = beginOfMonth($time);
        $end = endOfMonth($time);


        foreach ($users as $user) {

            $income = HiCoinHistories::sum(
                [
                    'conditions' => 'created_at >= :start: and created_at <= :end: and user_id = :user_id: and fee_type = :fee_type:',
                    'bind' => ['start' => $start, 'end' => $end, 'user_id' => $user->id, 'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT],
                    'column' => 'hi_coins'
                ]
            );

            $reward = 0;

            if ($income > 0) {

                switch ($income) {
                    case $income >= 1000 && $income <= 2000:
                        $reward = 100;
                        break;
                    case $income > 2000 && $income <= 5000:
                        $reward = 200;
                        break;
                    case $income > 5000 && $income <= 20000:
                        $reward = 500;
                        break;
                    case $income > 20000 && $income <= 50000:
                        $reward = 800;
                        break;
                    case $income > 50000:
                        $reward = 1000;
                        break;
                }

                if ($reward > 0) {


                    $remark = "主播奖励:" . $reward . "元";

                    HiCoinHistories::createHistory($user->id, ['fee_type' => HI_COIN_FEE_TYPE_HOST_REWARD, 'remark' => $remark,
                        'hi_coins' => $reward]);

                    Chats::sendTextSystemMessage($user->id, "恭喜您获得2018年3月份主持扶持奖励{$reward}元，小Hi已帮你存到Hi币收益，请注意查收！");
                    echoLine($user->id, $income, $reward);
                }
            }
        }
    }

    //家族长奖励
    function unionHostRewardAction()
    {
        $unions = Unions::find(
            [
                'conditions' => 'status = :status: and type = :type:',
                'bind' => ['status' => STATUS_ON, 'type' => UNION_TYPE_PRIVATE]
            ]);

        $current_day = intval(date('d'));
        $time = time() - $current_day * 86400 - 3600;
        $start = beginOfMonth($time);
        $end = endOfMonth($time);

        foreach ($unions as $union) {

            $income = HiCoinHistories::sum(
                [
                    'conditions' => 'created_at >= :start: and created_at <= :end: and union_id = :union_id: and union_type = :union_type:',
                    'bind' => ['start' => $start, 'end' => $end, 'union_id' => $union->id, 'union_type' => $union->type],
                    'column' => 'hi_coins'
                ]
            );

            $reward = 0;

            if ($income > 0) {

                switch ($income) {
                    case $income >= 10000 && $income <= 20000:
                        $reward = 600;
                        break;
                    case $income > 20000 && $income <= 50000:
                        $reward = 1600;
                        break;
                    case $income > 50000 && $income <= 200000:
                        $reward = 5000;
                        break;
                    case $income > 200000 && $income <= 500000:
                        $reward = 24000;
                        break;
                    case $income > 500000:
                        $reward = 70000;
                        break;
                }

                if ($reward > 0) {

                    $remark = "家族长奖励:" . $reward . "元";

                    HiCoinHistories::createHistory($union->user_id, ['fee_type' => HI_COIN_FEE_TYPE_UNION_HOST_REWARD, 'remark' => $remark,
                        'hi_coins' => $reward]);

                    Chats::sendTextSystemMessage($union->user_id, "恭喜您获得2018年3月份家族长扶持奖励{$reward}元，小Hi已帮你存到Hi币收益，请注意查收！");
                    echoLine($union->id, $income, $reward);
                }
            }
        }
    }

    function checkUserHiCoins()
    {
        $users = Users::find(['conditions' => 'hi_coins > 0']);

        foreach ($users as $user) {

            if ($user->isCompanyUser()) {
                continue;
            }

            $hi_coin_history = HiCoinHistories::findUserLast($user->id);
            $value = 0;

            if ($hi_coin_history) {
                $value = $hi_coin_history->balance;
            }

            $res = $user->hi_coins - $value;

            if (abs($res) > 0.001) {
                echoLine($user->id, "hi_coins", $user->hi_coins, 'value', $value);
            }
        }

        $union = Unions::findFirstById(1026);
        $users = Users::findBy(['union_id' => 1026]);

        foreach ($users as $user) {
            $union_history = UnionHistories::findFirstBy(['user_id' => $user->id, 'union_id' => $union->id]);

            if ($union_history) {
                echoLine($union_history->join_at_text);
            }
        }
    }

    //执行7天自动退出家族 task任务
    function confirmExitUnionAction()
    {

        $time = 7 * 24 * 60 * 60;

        //7天自动退出 测试环境30分钟
        if (isDevelopmentEnv()){
            $time =  30 * 60;
        }

        $start_at = time() - 60 * 7 - $time;
        $end_at = time() - $time;

        $union_histories = UnionHistories::find([
            'conditions' => 'status = :status: and apply_exit_at < :end_at: and apply_exit_at > :start_at:',
            'bind' => ['status' => STATUS_PROGRESS, 'start_at' => $start_at, 'end_at' => $end_at]
        ]);

        info('union_histories', $union_histories);
        foreach ($union_histories as $union_history) {

            $union = $union_history->union;
            $user = $union_history->user;
            $union->confirmExitUnion($user);

        }

    }
}