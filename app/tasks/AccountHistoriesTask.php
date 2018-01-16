<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 15:17
 */

class AccountHistoriesTask extends \Phalcon\Cli\Task
{
    function testChangeBalanceAction()
    {
        $user = \Users::findLast();
        $amount = 100;
        $opts = array('remark' => '系统赠送100钻石');
        $result = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_GIVE, $amount, $opts);
        var_dump($result);
    }

    function testBuyGiftAction()
    {
        $user = \Users::findById(2);
        //$gift = \Gifts::findLast();
        //$gift_num = 1000;
        //$amount = $gift->amount * $gift_num;
        $result = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_BUY_GIFT, 1);
        var_dump($result);
    }

    function testDiamondAction()
    {
        $user = \Users::findById(2);
        echo $user->diamond . PHP_EOL;

        $user->diamond = 0;
        $user->update();
    }
}