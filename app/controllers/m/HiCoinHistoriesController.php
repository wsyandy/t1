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
        if ($this->request->isPost()) {

            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        $products = \Products::findDiamondListByUser($this->currentUser(), '', PRODUCT_GROUP_FEE_TYPE_HI_COINS);

        $this->view->products = $products;
        $this->view->hi_coin_diamond_rate = HI_COIN_DIAMOND_RATE;
        $this->view->user = $this->currentUser();
        $this->view->title = 'Hi币兑钻';

    }

    function createAction()
    {
        if ($this->request->isAjax()) {

            $product_id = $this->params('product_id');
            $hi_coins = $this->params('hi_coins');

            info('product_id', $product_id, 'hi_coins', $hi_coins);
            $product = \Products::findFirstById($product_id);

            if ($hi_coins) {
                $diamond = HI_COIN_DIAMOND_RATE * $hi_coins;
            }

            $gold = '';
            if ($product) {
                $hi_coins = $product->hi_coins;
                $gold = $product->gold;
                $diamond = $product->diamond;
            }

            $user = $this->currentUser();
            if ($user->hi_coins < intval(strval($hi_coins))) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您的Hi币不足！');
            }

            $opts = ['product_id' => $product_id, 'hi_coins' => $hi_coins, 'gold' => $gold, 'diamond' => $diamond];
            info('user_id', $user->id, $opts);

            $hi_coin_history = \HiCoinHistories::hiCoinExchangeDiamondHiCoinHistory($user->id, $opts);

            info('hi_coin_history', $hi_coin_history->id);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '兑换成功！');
        }

    }


}