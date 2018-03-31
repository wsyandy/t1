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
}