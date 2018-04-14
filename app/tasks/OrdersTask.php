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

    function mobileRechargeAction()
    {
        $orders = Orders::findBy(['partner_id' => 14, 'status' => ORDER_STATUS_SUCCESS]);

        $amounts = [];

        foreach ($orders as $order) {

            $user = $order->user;

            if ($user->isCompanyUser()) {
                echoLine($user->id);
                continue;
            }

            $device = $user->device;
            $model = $device->model;

            if (isset($amounts[$model])) {
                $amounts[$model] += $order->amount;
            } else {
                $amounts[$model] = $order->amount;
            }
        }


        arsort($amounts);

        $f = fopen(APP_ROOT . "public/mobile_type_amount.txt", 'w');

        foreach ($amounts as $type => $amount) {
            $text = "手机型号:" . $type . "充值总额:" . $amount;
            echoLine($text);
            fwrite($f, $text . "\r\n");
        }

        fclose($f);
    }
}