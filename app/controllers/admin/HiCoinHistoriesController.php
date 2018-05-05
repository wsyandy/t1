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
            $remark = $this->params('hi_coin_history[remark]');
            $content = $this->params('hi_coin_history[content]');


            if (!$remark) {
                $remark = '房间流水奖励' . $hi_coins . 'hi币';
            }

            $opts = ['remark' => $remark, 'hi_coins' => $hi_coins, 'operator_id' => $this->currentOperator()->id];

            if ($hi_coins > 0) {
                $hi_coin_history = \HiCoinHistories::createHistory($user->id, $opts);

                if ($content) {
                    \Chats::sendTextSystemMessage($user->id, $content);
                }

                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['hi_coin_history' => $hi_coin_history->toJson()]);
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
        $this->view->hi_coin_history = new \HiCoinHistories();
        $this->view->user_id = $user_id;
    }

    function showAction()
    {
        $hi_coin_histories = \HiCoinHistories::findByIds($this->params('id'));
        $this->view->hi_coin_histories = $hi_coin_histories;
    }

    function ordersAction()
    {

        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;
        $cond = ['conditions' => "fee_type = " . HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND];

        $cond['order'] = 'id desc';

        $user_id = $this->params('user_id');

        if ($user_id) {
            $cond['conditions'] .= " and user_id = " . $user_id;
        }

        $hi_coin_histories = \HiCoinHistories::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->hi_coin_histories = $hi_coin_histories;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 20);

        $user_uid = $this->params('user_uid');
        $union_id = $this->params('union_id');
        $start_at = $this->params('start_at');
        $end_at = $this->params('end_at');
        $fee_type = $this->params('fee_type');

        if (endOfDay(strtotime($end_at)) - beginOfDay(strtotime($start_at)) > 30 * 86400) {
            echo "时间跨度不能超过一个月";
            return false;
        }

        $cond = [
            'conditions' => 'id > 0',
        ];

        $user = null;
        $union = null;

        if ($user_uid) {

            $user = \Users::findFirstByUid($user_uid);

            if ($user) {
                $cond['conditions'] .= " and user_id = :user_id:";
                $cond['bind']['user_id'] = $user->id;
            } else {
                echo "用户不存在";
                return false;
            }
        }

        if ($fee_type) {
            $cond['conditions'] .= " and fee_type = :fee_type:";
            $cond['bind']['fee_type'] = $fee_type;
        }

        if ($union_id) {

            $union = \Unions::findFirstById($union_id);

            if (!$union) {
                echo "家族不存在";
                return false;
            }

            $cond['conditions'] .= " and union_id = :union_id:";
            $cond['bind']['union_id'] = $union_id;
        }

        if ($start_at) {
            $cond['conditions'] .= " and created_at >= :start_at:";
            $cond['bind']['start_at'] = beginOfDay(strtotime($start_at));
        }

        if ($end_at) {
            $cond['conditions'] .= " and created_at <= :end_at:";
            $cond['bind']['end_at'] = endOfDay(strtotime($end_at));
        }


        $hi_coin_histories = \HiCoinHistories::findPagination($cond, $page, $per_page);
        $total_hi_coins = '';

        if ($user || $union) {
            $cond['column'] = 'hi_coins';
            $total_hi_coins = \HiCoinHistories::sum($cond);
        }

        $this->view->hi_coin_histories = $hi_coin_histories;
        $this->view->start_at = $start_at;
        $this->view->end_at = $end_at;
        $this->view->user_uid = $user_uid;
        $this->view->union_id = $union_id;
        $this->view->fee_type = intval($fee_type);
        $this->view->total_hi_coins = $total_hi_coins;

    }
}