<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 15:17
 */

class DrawTask extends \Phalcon\Cli\Task
{

    function checkUserAction(){

        $draw_histories = DrawHistories::find(['conditions' => 'total_pay_amount>:pay_amount:', 'bind' => ['pay_amount' => 50000]]);
        $user_ids = [];
        foreach($draw_histories as $draw_history){
            $user_ids[] = $draw_history->user_id;
        }

        $user_ids = array_unique($user_ids);
        echoLine($user_ids);
    }

}