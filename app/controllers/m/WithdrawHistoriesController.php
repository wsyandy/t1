<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/6
 * Time: 上午11:56
 */
namespace m;

class WithdrawHistories extends BaseController
{
    function indexAction()
    {
        $user = $this->currentUser();
        $hi_coins = $user->hi_coins;
        $this->view->hi_coins = $hi_coins;
        $this->view->amount = $hi_coins / 10;
    }
}