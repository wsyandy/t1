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


    function pushRechargeFailedAction()
    {
        
        $conditions = [
            'conditions' => 'status = :status: and created_at >= :created_at: and created_at < :end_at:',
            'bind' => ['status' => ORDER_STATUS_WAIT, 'created_at' => beginOfHour() - 3600, 'end_at' => beginOfHour()],
            'columns' => 'distinct user_id'
        ];

        $orders = Orders::find($conditions);

        $user_ids = []; // 计数器

        foreach ($orders as $order) {

            if (isset($user_ids[$order->user_id])) {
                $user_ids[$order->user_id] += 1;
            } else {
                $user_ids[$order->user_id] = 1;
            }
        }

        if (empty($user_ids)) return;

        $content = '尊敬用户：您好！请问您是否在支付的时候遇到了问题？如有疑问请联系官方客服中心400-018-7755解决。';

        $push_data = [
            'title' => '系统充值通知',
            'body' => $content
        ];

        // 次数大于2的user_id
        foreach ($user_ids as $user_id => $num) {

            if ($num >= 2) {

                info($user_id, $num);

                // 需要推送消息的
                Chats::sendSystemMessage($user_id, CHAT_CONTENT_TYPE_TEXT, $content);

                // 个推
                $user = Users::findFirstById($user_id);
                Pushers::push($user->getPushContext(), $user->getPushReceiverContext(), $push_data);
            }
        }
    }
}