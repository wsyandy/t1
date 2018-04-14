<?php
/**
 * Created by PhpStorm.
 * User: administrator
 * Date: 2018/3/28
 * Time: 下午4:02
 */

namespace m;

class HiCoinHistoriesController extends BaseController
{

    function exchangeAction()
    {
        $products = \Products::findHiCoinDiamondListByUser($this->currentUser());
        $this->view->products = $products;
        $this->view->hi_coin_diamond_rate = HI_COIN_DIAMOND_RATE;
        $this->view->user = $this->currentUser();
        $this->view->title = 'Hi币兑钻';
    }

    function createAction()
    {
        if ($this->request->isAjax()) {

            $product_id = $this->params('product_id');
            $hi_coins = intval($this->params('hi_coins'));

            if ($hi_coins < 30) {
                return $this->renderJSON(ERROR_CODE_FAIL, '至少30Hi币才能兑换钻石');
            }

            info('product_id', $product_id, 'hi_coins', $hi_coins);
            $product = \Products::findFirstById($product_id);

            $gold = 0;
            $amount = \HiCoinHistories::rateOfHiCoinToCny() * $hi_coins;
            $diamond = HI_COIN_DIAMOND_RATE * $hi_coins;

            if ($product) {
                $amount = $product->amount;
                $hi_coins = \HiCoinHistories::rateOfCnyToHiCoin() * $amount;
                $gold = $product->gold;
                $diamond = $product->diamond;
            }

            $user = $this->currentUser();

            //所有以扣除人民币为准
            if ($user->getWithdrawAmount() < $amount) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您的Hi币不足！');
            }

            if ($hi_coins < 0) {
                return $this->renderJSON(ERROR_CODE_FAIL, 'Hi币不能为0！');
            }

            $opts = ['product_id' => $product_id, 'hi_coins' => $hi_coins, 'gold' => $gold, 'diamond' => $diamond];
            info('user_id', $user->id, $opts);

            $hi_coin_history = \HiCoinHistories::hiCoinExchangeDiamondHiCoinHistory($user->id, $opts);

            info('hi_coin_history', $hi_coin_history->id);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '兑换成功！', ['hi_coins' => $user->getHiCoinText()]);
        }

    }


}