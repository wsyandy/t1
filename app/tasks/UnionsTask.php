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

    //房主奖励
    function unionHostIncomeStat()
    {

    }

    //认证主播奖励
    function authHostIncomeStatAction()
    {
        beginOfMonth();
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
    }
}