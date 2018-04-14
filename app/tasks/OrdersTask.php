<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/31
 * Time: 下午5:54
 */
class OrdersTask extends \Phalcon\Cli\Task
{
    function incomeStatAction()
    {
        $orders = Orders::findForeach(['conditions' => 'status = :status:', 'bind' => ['status' => ORDER_STATUS_SUCCESS]]);

        $total_amount = 0;
        $auth_amount = 0;

        foreach ($orders as $order) {

            if ($order->user->isCompanyUser()) {
                continue;
            }

            $total_amount += $order->amount;

            if ($order->user->id_card_auth == AUTH_SUCCESS) {
                $auth_amount += $order->amount;
            }
        }

        $str = "总金额:{$total_amount}; 认证主播金额:$auth_amount; 占比:" . intval(($auth_amount / $total_amount) * 100) . "%";

        echoLine($str);
    }

    function idcardAuthUserIncomeAction()
    {

        $users = Users::find([
            'conditions' => 'id_card_auth = :id_card_auth: and organisation = 0',
            'bind' => ['id_card_auth' => AUTH_SUCCESS],
            'order' => 'id asc',
            'columns' => 'id,pay_amount'
        ]);

        $gain_user_num = 0;
        $loss_user_num = 0;

        $total_hi_coins = 0;
        $total_recharge_amount = 0;

        foreach ($users as $user) {

            $recharge_amount = $user->pay_amount;
            $total_recharge_amount += $recharge_amount;

            $hi_coins = HiCoinHistories::sum([
                'conditions' => 'user_id = :user_id: and hi_coins>0',
                'bind' => ['user_id' => $user->id],
                'column' => 'hi_coins'
            ]);

            $total_hi_coins += $hi_coins;
            $get_hi_coins = $hi_coins - $recharge_amount;
            if ($get_hi_coins > 0) {
                $gain_user_num++;
            } else {
                $loss_user_num++;
            }

            echoLine($user->id, '获利', $get_hi_coins, '充值人民币', $recharge_amount, '获得hi币', $hi_coins);
        }

        echoLine("盈利人数{$gain_user_num}, 亏损人数{$loss_user_num}", '系统收益', $total_recharge_amount - $total_hi_coins);

    }

}