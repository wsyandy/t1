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
        $time = beginOfMonth() - 3600;
        $start = beginOfMonth($time);
        $end = endOfMonth($time);

        echoLine($start, $end);

        $hi_coin_histories = HiCoinHistories::find(
            [
                'conditions' => 'created_at >= :start: and created_at <= :end: and fee_type = :fee_type:',
                'bind' => ['start' => $start, 'end' => $end, 'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT],
                'columns' => 'distinct user_id'
            ]);

        echoLine(count($hi_coin_histories));
        $rewards = [];
        $titles = ['uid', '昵称', 'hi币收益', '奖励钻石数额'];

        foreach ($hi_coin_histories as $hi_coin_history) {

            $user_id = $hi_coin_history->user_id;

            $user = Users::findById($user_id);

            if (!$user->isIdCardAuth()) {
                continue;
            }

            $id_card_auth = IdCardAuths::findFirstByUserId($user->id);

            if ($id_card_auth->auth_at > $end) {
                continue;
            }

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
                        $reward = 1200;
                        break;
                    case $income > 2000 && $income <= 5000:
                        $reward = 2400;
                        break;
                    case $income > 5000 && $income <= 20000:
                        $reward = 6000;
                        break;
                    case $income > 20000 && $income <= 50000:
                        $reward = 9600;
                        break;
                    case $income > 50000:
                        $reward = 12300;
                        break;
                }

                if ($reward > 0) {

                    $nickname = preg_replace_callback(
                        '/./u',
                        function ($match) {
                            return strlen($match[0]) >= 4 ? '*' : $match[0];
                        },
                        $user->nickname);

                    $rewards[$reward][] = [$user->uid, $nickname, $income, $reward];
                    $remark = "主播奖励:" . $reward . "钻石";
                    echoLine($user->id, $user->uid, $user->nickname, $income, $remark);
                    //HiCoinHistories::createHistory($user->id, ['fee_type' => HI_COIN_FEE_TYPE_HOST_REWARD, 'remark' => $remark,
                    // 'hi_coins' => $reward]);

                    // Chats::sendTextSystemMessage($user->id, "恭喜您获得2018年4月份主持扶持奖励{$reward}元，小Hi已帮你存到Hi币收益，请注意查收！");
                }
            }
        }

        foreach ($rewards as $reward => $data) {
            echoLine("奖励" . $reward . "钻的用户");
            $temp_file = 'reward_history_1_' . $reward . '.xls';
            $uri = writeExcel($titles, $data, $temp_file, true);
            echoLine(StoreFile::getUrl($uri), $uri);
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

        $time = beginOfMonth() - 3600;
        $start = beginOfMonth($time);
        $end = endOfMonth($time);

        foreach ($unions as $union) {

            if (1114 == $union->id) {
                continue;
            }

            $income = HiCoinHistories::sum(
                [
                    'conditions' => 'created_at >= :start: and created_at <= :end: and union_id = :union_id: and fee_type = :fee_type:',
                    'bind' => ['start' => $start, 'end' => $end, 'union_id' => $union->id, 'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT],
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

                    //echoLine($union->id, $income, $reward);

                    $remark = "家族长奖励:" . $reward . "元";

                    //HiCoinHistories::createHistory($union->user_id, ['fee_type' => HI_COIN_FEE_TYPE_UNION_HOST_REWARD, 'remark' => $remark,
                    //  'hi_coins' => $reward]);

                    //Chats::sendTextSystemMessage($union->user_id, "恭喜您获得2018年4月份家族长扶持奖励{$reward}元，小Hi已帮你存到Hi币收益，请注意查收！");
                    echoLine($union->id, $union->name, $income, $reward);
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
        if (isDevelopmentEnv()) {
            $time = 5 * 60;
        }

        $start_at = time() - 60 * 30 - $time;
        $end_at = time() - $time;

        $union_histories = \UnionHistories::find([
            'conditions' => 'status = :status: and (:exit_start_at: < apply_exit_at and apply_exit_at < :exit_end_at:)',
            'bind' => ['status' => STATUS_PROGRESS, 'exit_start_at' => $start_at, 'exit_end_at' => $end_at]
        ]);

        foreach ($union_histories as $union_history) {

            info($union_history->id, $union_history->status, $union_history->exit_start_at);
            $union = $union_history->union;
            $user = $union_history->user;
            $union->confirmExitUnion($user, 'auto');

        }

    }

    function testAction()
    {
        $union_histories = Unions::find(['columns' => 'user_id']);
        echoLine(count($union_histories));

        foreach ($union_histories as $union_history) {
            $user_id = $union_history->user_id;
            $exit_union_history = UnionHistories::findFirstBy(['status' => STATUS_OFF, 'user_id' => $user_id], 'id desc');
            $add_union_history = UnionHistories::findFirstBy(['status' => STATUS_ON, 'user_id' => $user_id], 'id desc');

            if ($add_union_history->id > $exit_union_history->id && $add_union_history->join_at <= $exit_union_history->exit_at) {
                $user = Users::findFirstById($user_id);

                $user->union_id = $add_union_history->union_id;
                $user->update();
                echoLine($user_id, $add_union_history->union_id, $exit_union_history->union_id);
            }
        }

        $time = 7 * 24 * 60 * 60;

        //7天自动退出 测试环境30分钟
        if (isDevelopmentEnv()) {
            $time = 5 * 60;
        }

        $start_at = time() - 60 * 30 - $time;
        $end_at = time() - $time;

        $union_histories = \UnionHistories::find([
            'conditions' => 'status = :status: and apply_exit_at < :exit_end_at:',
            'bind' => ['status' => STATUS_PROGRESS, 'exit_end_at' => $end_at]
        ]);

        echoLine(count($union_histories));
    }

    function incomeAction()
    {
        $cond = [
            'conditions' => 'union_id = :union_id: and created_at >= :start: and created_at <= :end: and fee_type = :fee_type:',
            'bind' => ['union_id' => 1068, 'start' => beginOfMonth(), 'end' => endOfMonth(), 'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT],
            'column' => 'hi_coins'
        ];

        $num = HiCoinHistories::sum($cond);

        echoLine($num);
    }

    function fixGiftOrderAction()
    {
        $gift_orders = GiftOrders::find([
            'conditions' => 'created_at >= :start: and created_at <= :end: and status = :status: and pay_type = :pay_type:',
            'bind' => [
                'start' => beginOfMonth(strtotime('2018-01-01')),
                'end' => endOfMonth(strtotime('2018-04-30')),
                'status' => GIFT_ORDER_STATUS_SUCCESS,
                'pay_type' => GIFT_PAY_TYPE_DIAMOND
            ],
            'columns' => 'id'
        ]);

        echoLine(count($gift_orders));

        $db = Users::getUserDb();

        foreach ($gift_orders as $gift_order) {

            $gift_order = GiftOrders::findFirstById($gift_order->id);
            $sender_union_id = $gift_order->sender_union_id;
            $receiver_union_id = $gift_order->receiver_union_id;
            $room_id = $gift_order->room_id;
            $room_union_id = $gift_order->room_union_id;
            $sender_id = $gift_order->sender_id;
            $user_id = $gift_order->user_id;
            $created_at = $gift_order->created_at;
            $month_start = date("Ymd", beginOfMonth($created_at));
            $month_end = date("Ymd", endOfMonth($created_at));
            $day = date("Ymd", $created_at);
            $amount = $gift_order->amount;

            if ($room_id && $room_union_id && GIFT_TYPE_COMMON == $gift_order->gift_type) {
                $total_key = 'union_room_total_amount_union_id_' . $room_union_id;
                $month_key = 'union_room_month_amount_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $room_union_id;
                $day_key = 'union_room_day_amount_' . $day . '_union_id_' . $room_union_id;

                echoLine("room_union_id", $total_key, $month_key, $day_key, $gift_order->room_id, $gift_order->room_union_id);

                $db->zincrby($total_key, $amount, $room_id);
                $db->zincrby($month_key, $amount, $room_id);
                $db->zincrby($day_key, $amount, $room_id);
            }

            if ($sender_union_id) {
                $total_key = 'union_user_total_wealth_rank_list_union_id_' . $sender_union_id;
                $month_key = 'union_user_month_wealth_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $sender_union_id;
                $day_key = 'union_user_day_wealth_rank_list_' . $day . '_union_id_' . $sender_union_id;
                echoLine('sender_union_id', $total_key, $month_key, $day_key);

                $db->zincrby($total_key, $amount, $sender_id);
                $db->zincrby($month_key, $amount, $sender_id);
                $db->zincrby($day_key, $amount, $sender_id);
            }

            if ($receiver_union_id) {
                $total_key = 'union_user_total_charm_rank_list_union_id_' . $receiver_union_id;
                $month_key = 'union_user_month_charm_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $receiver_union_id;
                $day_key = 'union_user_day_charm_rank_list_' . $day . '_union_id_' . $receiver_union_id;
                echoLine('receiver_union_id', $total_key, $month_key, $day_key);

                $db->zincrby($total_key, $amount, $user_id);
                $db->zincrby($month_key, $amount, $user_id);
                $db->zincrby($day_key, $amount, $user_id);
            }
        }
    }

    function fixHiCoinsAction()
    {
        $gift_orders = GiftOrders::find([
            'conditions' => 'status = :status:',
            'bind' => [
                'status' => GIFT_ORDER_STATUS_FREEZE
            ],
            'columns' => 'id'
        ]);

        $gift_order_ids = [];

        foreach ($gift_orders as $gift_order) {
            $gift_order_ids[] = $gift_order->id;
        }

        $hi_conin_histories = HiCoinHistories::find([
            'conditions' => 'created_at >= :start: and created_at <= :end: and union_id > 0 and fee_type = :fee_type:',
            'bind' => [
                'start' => beginOfMonth(strtotime('2018-01-01')),
                'end' => endOfMonth(strtotime('2018-04-30')),
                'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT
            ],
            'columns' => 'id'
        ]);
        $db = Users::getUserDb();

        foreach ($hi_conin_histories as $hi_conin_history) {

            $hi_conin_history = HiCoinHistories::findFirstById($hi_conin_history->id);

            if (in_array($hi_conin_history->gift_order_id, $gift_order_ids)) {
                continue;
            }

            $created_at = $hi_conin_history->created_at;
            $month_start = date("Ymd", beginOfMonth($created_at));
            $month_end = date("Ymd", endOfMonth($created_at));
            $day = date("Ymd", $created_at);
            $hi_coins = intval($hi_conin_history->hi_coins * 1000);
            $union_id = $hi_conin_history->union_id;
            $user_id = $hi_conin_history->user_id;

            $total_key = 'union_user_total_hi_coins_rank_list_union_id_' . $union_id;
            $month_key = 'union_user_month_hi_coins_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union_id;
            $day_key = 'union_user_day_hi_coins_rank_list_' . $day . '_union_id_' . $union_id;

            $db->zincrby($total_key, $hi_coins, $user_id);
            $db->zincrby($month_key, $hi_coins, $user_id);
            $db->zincrby($day_key, $hi_coins, $user_id);

            echoLine($hi_coins, $total_key, $month_key, $day_key, $hi_conin_history->id);

        }
        echoLine(count($hi_conin_histories));
    }

    function statAction()
    {
        $unions = Unions::find(['conditions' => 'status = :status:', 'bind' => ['status' => STATUS_ON]]);
        $stat_at = strtotime("-1 day");

        foreach ($unions as $union) {

            $union->statSender($stat_at);
            $union->statUser($stat_at);
            $union->statRoom($stat_at);
            $union->statUserHiCoins($stat_at);
        }
    }

    //每天刷新一次
    function updateUnionIntegralsAction()
    {
        $user_db = \Users::getUserDb();
        $room_db = \Rooms::getRoomDb();
        $month_start = date('Ymd', beginOfMonth());
        $month_end = date('Ymd', endOfMonth());
        $unions = \Unions::find(['conditions' => 'status=' . STATUS_ON]);

        foreach ($unions as $union) {
            $key = 'union_room_month_amount_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
            $total_host_broadcaster_time_key = 'union_room_month_time_integrals_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;

            //累计的之前的分数
            $union_month_integrals_key = 'union_room_month_integrals_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
            $month_integrals = $room_db->zscore($union_month_integrals_key, $union->id);

            $room_ids = $user_db->zrange($key, 0, -1);
            $rooms = \Rooms::findByIds($room_ids);

            info($key, $room_ids);
            $total_amount = 0;

            foreach ($rooms as $room) {
                $room->amount = $user_db->zscore($key, $room->id);
                $total_amount += $room->amount;

                $total_host_broadcaster_time = $room->getDayUserTime('host_broadcaster', date("Ymd", strtotime('-1 day')));
                if ($total_host_broadcaster_time >= 60 * 60 * 2) {
                    //房主在线时长积分
                    $room_db->zincrby($total_host_broadcaster_time_key, 1, $union->id);
                    //当月积分
                    $room_db->zincrby($union_month_integrals_key, 1, $union->id);
                }
            }

            $room_db->zadd($union_month_integrals_key, intval($total_amount / 10000), $union->id);
            $current_month_integrals = $room_db->zscore($union_month_integrals_key, $union->id);
            $union->total_integrals = $union->total_integrals - $month_integrals + $current_month_integrals;

            $union->update();
            info('家族总积分', $union->total_integrals, '今日新增', $current_month_integrals - $month_integrals, '房主在线时长积分', $room_db->zscore($total_host_broadcaster_time_key, $union->id));
        }
    }

    //每月刷新一次
    function updateUnionLevelAction()
    {
        $db = \Rooms::getRoomDb();
        $month_start = date('Ymd', beginOfMonth(time() - 86400));
        $month_end = date('Ymd', endOfMonth(time() - 86400));

        $grading_scores = [0, 150, 300, 500, 1000, 1500, 2000];     //保级积分

        $unions = \Unions::find(['conditions' => 'status=' . STATUS_ON]);

        foreach ($unions as $union) {
            $key = 'union_room_month_time_integrals_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
            $current_month_integrals = $db->zscore($key, $union->id);
            //先确保达到保级积分
            info('当月积分', $current_month_integrals, '当前等级', $union->union_level);
            if ($current_month_integrals >= $grading_scores[intval($union->union_level)]) {
                $union->union_level = $this->getUnionLevel($union->total_integrals);
                $union->update();
            } else {
                if (!$union->union_level) {
                    $union->union_level -= 1;
                    $union->update();
                }
            }
        }
    }

    function getUnionLevel($total_integrals)
    {
        switch (true) {
            case $total_integrals >= 200 && $total_integrals <= 500:
                $union_level = 1;
                break;
            case $total_integrals > 500 && $total_integrals <= 1000:
                $union_level = 2;
                break;
            case $total_integrals > 1000 && $total_integrals <= 2000:
                $union_level = 3;
                break;
            case $total_integrals > 2000 && $total_integrals <= 5000:
                $union_level = 4;
                break;
            case $total_integrals > 5000 && $total_integrals <= 10000:
                $union_level = 5;
                break;
            case $total_integrals > 10000:
                $union_level = 6;
                break;
            default:
                $union_level = 0;
                break;
        }

        return $union_level;

    }

    function repairUnionLevelAction($opts)
    {
        $user_db = \Users::getUserDb();
        $room_db = \Rooms::getRoomDb();
        $stat_at = strtotime($opts[0]);
        $month_start = date('Ymd', beginOfMonth($stat_at));
        $month_end = date('Ymd', endOfMonth($stat_at));
        $unions = \Unions::find(['conditions' => 'status=' . STATUS_ON]);
        $grading_scores = [0, 150, 300, 500, 1000, 1500, 2000];     //保级积分

        foreach ($unions as $union) {
            $key = 'union_room_month_amount_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
            $total_host_broadcaster_time_key = 'union_room_month_time_integrals_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;

            //累计的之前的分数
            $union_month_integrals_key = 'union_room_month_integrals_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
            $month_integrals = $room_db->zscore($union_month_integrals_key, $union->id);

            $room_ids = $user_db->zrange($key, 0, -1);
            $rooms = \Rooms::findByIds($room_ids);

            info($key, $room_ids);
            $total_amount = 0;

            foreach ($rooms as $room) {
                $room->amount = $user_db->zscore($key, $room->id);
                $total_amount += $room->amount;
                for ($date = $month_start; $date <= $month_end; $date += 86400) {
                    $total_host_broadcaster_time = $room->getDayUserTime('host_broadcaster', date("Ymd", $date));
                    if ($total_host_broadcaster_time >= 60 * 60 * 2) {
                        //房主在线时长积分
                        $room_db->zincrby($total_host_broadcaster_time_key, 1, $union->id);
                        //当月积分
                        $room_db->zincrby($union_month_integrals_key, 1, $union->id);
                    }
                }
            }

            $room_db->zadd($union_month_integrals_key, intval($total_amount / 10000), $union->id);
            $current_month_integrals = $room_db->zscore($union_month_integrals_key, $union->id);
            $union->total_integrals = $union->total_integrals - $month_integrals + $current_month_integrals;

            $union->update();
            info('家族总积分', $union->total_integrals, '今日新增', $current_month_integrals - $month_integrals, '房主在线时长积分', $room_db->zscore($total_host_broadcaster_time_key, $union->id));

            if ($current_month_integrals >= $grading_scores[intval($union->union_level)]) {
                $union->union_level = $this->getUnionLevel($union->total_integrals);
                $union->update();
            } else {
                if (!$union->union_level) {
                    $union->union_level -= 1;
                    $union->update();
                }
            }
        }
    }
}