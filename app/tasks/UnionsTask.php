<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/15
 * Time: 下午5:42
 */

class UnionsTask extends \Phalcon\Cli\Task
{
    //推荐家族
    function recommendAction()
    {
        $db = Users::getUserDb();
        $key = "total_union_fame_value_day_" . date("Ymd", strtotime('-1 day'));

        $unions = Unions::findBy(['recommend' => STATUS_ON]);

        foreach ($unions as $union) {
            $union->recommend = STATUS_OFF;
            $union->update();
        }

        $union_ids = $db->zrevrange($key, 0, 4);
        $unions = Unions::findByIds($union_ids);

        foreach ($unions as $union) {
            $union->recommend = STATUS_ON;
            $union->update();
        }

    }

    function initGiftOrdersAction()
    {
        $gift_orders = GiftOrders::findForeach();

        foreach ($gift_orders as $gift_order) {
            $gift_order->sendder_union_id = 8;
            $gift_order->receiver_union_id = 8;
            $gift_order->update();
        }
    }

    function initUnionsAction()
    {
        while (true) {
            $union = new Unions();
            $union->status = STATUS_OFF;
            $union->user_id = 0;
            $union->name = '';
            $union->product_channel_id = 0;
            $union->save();

            if ($union->id >= 1000) {
                echoLine($union->id);
                break;
            }
        }
    }

    function fixUnionIdAction()
    {
        $users = Users::find(['conditions' => 'union_id > 0']);

        foreach ($users as $user) {
            $user->uninon_id = 0;
            $user->uninon_type = 0;
            $user->update();
        }

        $orders = Orders::find(['conditions' => 'union_id > 0']);

        foreach ($orders as $order) {
            $order->union_id = 0;
            $order->union_type = 0;
            $order->update();
        }

        $account_histories = AccountHistories::find(['conditions' => 'union_id > 0']);

        foreach ($account_histories as $account_history) {
            $account_history->union_id = 0;
            $account_history->union_type = 0;
            $account_history->update();
        }

        $gift_orders = GiftOrders::find(['conditions' => 'sender_union_id > 0 or receiver_union_id > 0']);

        foreach ($gift_orders as $gift_order) {
            $gift_order->sender_union_id = 0;
            $gift_order->sender_union_type = 0;
            $gift_order->receiver_union_id = 0;
            $gift_order->receiver_union_type = 0;
            $gift_order->update();
        }

        $rooms = Rooms::find(['conditions' => 'union_id > 0']);

        foreach ($rooms as $room) {
            $room->union_id = 0;
            $room->union_type = 0;
            $room->update();
        }

        $withdraw_histories = WithdrawHistories::find(['conditions' => 'union_id > 0']);;

        foreach ($withdraw_histories as $withdraw_history) {
            $withdraw_history->union_id = 0;
            $withdraw_history->union_type = 0;
            $withdraw_history->update();
        }

        $union_histories = UnionHistories::find(['conditions' => 'union_id > 0']);;

        foreach ($union_histories as $union_history) {
            $union_history->union_id = 0;
            $union_history->union_type = 0;
            $union_history->update();
        }

        $unions = Unions::findForeach();

        foreach ($unions as $union) {
            $union->delete();
        }
    }
}