<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/26
 * Time: 下午5:58
 */

namespace admin;

class HiCoinHistoriesController extends BaseController
{
    function basicAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $hi_coin_histories = \HiCoinHistories::findPagination(['conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id], 'order' => 'id desc'], $page, $per_page);

        $this->view->hi_coin_histories = $hi_coin_histories;
        $this->view->user_id = $user_id;
    }

    function createHiCoinsAction()
    {
        $user_id = $this->params('user_id');

        if ($this->request->isPost()) {

            if (!$this->currentOperator()->canGiveHiCoins()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
            }

            $user = \Users::findFirstById($user_id);

            if (!$user) {
                return $this->renderJSON(ERROR_CODE_FAIL, '失败');
            }

            $hi_coins = intval($this->params('hi_coin_history[hi_coins]'));

            $opts = ['remark' => '房间流水奖励' . $hi_coins . '钻石', 'hi_coins' => $hi_coins, 'operator_id' => $this->currentOperator()->id];

            if ($hi_coins > 0) {
                $hi_coin_history = \HiCoinHistories::createHistory($user->id, $opts);
                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['hi_coin_history' => $hi_coin_history->toJson()]);
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
        $this->view->hi_coin_history = new \HiCoinHistories();
        $this->view->user_id = $user_id;
    }
}