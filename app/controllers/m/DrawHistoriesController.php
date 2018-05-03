<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/28
 * Time: 下午4:35
 */

namespace m;


class DrawHistoriesController extends BaseController
{

    function indexAction()
    {

        $cond = ['conditions' => 'type=:type:',
            'bind' => ['type' => 'diamond'],
            'order' => 'id desc'
        ];

        $draw_histories = \DrawHistories::findPagination($cond, 1, 20);
        $res = $draw_histories->toJson('draw_histories', 'toSimpleJson');

        $cond = ['conditions' => 'type=:type: and number >= 1000',
            'bind' => ['type' => 'diamond'],
            'order' => 'id desc'
        ];

        $qian_draw_histories = \DrawHistories::findPagination($cond, 1, 15);
        $qian_res = $qian_draw_histories->toJson('draw_histories', 'toSimpleJson');
        $res['draw_histories'] = array_merge($qian_res['draw_histories'], $res['draw_histories']);

        $cond = ['conditions' => 'type=:type: and number >= 10000',
            'bind' => ['type' => 'diamond'],
            'order' => 'id desc'
        ];

        $wan_draw_histories = \DrawHistories::findPagination($cond, 1, 15);
        $wan_res = $wan_draw_histories->toJson('draw_histories', 'toSimpleJson');
        $res['draw_histories'] = array_merge($wan_res['draw_histories'], $res['draw_histories']);


        $cond = ['conditions' => 'type=:type: and number >= 100000',
            'bind' => ['type' => 'diamond'],
            'order' => 'id desc'
        ];

        $wan10_draw_histories = \DrawHistories::findPagination($cond, 1, 10);
        $wan10_res = $wan10_draw_histories->toJson('draw_histories', 'toSimpleJson');
        $res['draw_histories'] = array_merge($wan10_res['draw_histories'], $res['draw_histories']);

        shuffle($res['draw_histories']);

        $this->view->draw_histories = json_encode($res['draw_histories'], JSON_UNESCAPED_UNICODE);
    }

    // 砸蛋抽奖
    function drawAction()
    {

        if ($this->request->isAjax()) {

            $num = $this->params('num', 1);
            $amount = 10;
            $total_amount = $amount * $num;

            $remark = '抽奖消费' . $total_amount . '钻石';
            $opts['remark'] = $remark;
            $user = $this->currentUser();

            debug($user->id, $user->diamond);
            if ($user->diamond < $total_amount) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }

            $target = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DRAW_EXPENSES, $total_amount, $opts);
            if (!$target) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }

            $draw_histories = [];

            $hit_diamond_num = 0;
            for ($i = 1; $i <= $num; $i++) {

                if ($i >= mt_rand(8, 10) && $hit_diamond_num < 1) {
                    $draw_history = \DrawHistories::createHistory($this->currentUser(), ['pay_type' => 'diamond', 'pay_amount' => $amount, 'hit_diamond' => true]);
                } else {
                    $draw_history = \DrawHistories::createHistory($this->currentUser(), ['pay_type' => 'diamond', 'pay_amount' => $amount]);
                }

                if ($draw_history->type == 'diamond') {
                    $hit_diamond_num++;
                    array_unshift($draw_histories, $draw_history->toSimpleJson());
                } else {
                    $draw_histories[] = $draw_history->toSimpleJson();
                }
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['draw_histories' => $draw_histories]);
        }

    }

    // 我的奖品
    function listAction()
    {
        $user = $this->currentUser();

        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        if ($this->request->isAjax()) {

            $draw_histories = \DrawHistories::findPagination(['conditions' => 'user_id=:user_id:',
                'bind' => ['user_id' => $user->id], 'order' => 'id desc'
            ], $page, $per_page);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $draw_histories->toJson('draw_histories', 'toSimpleJson'));
        }

        $diamond_draw_history = \DrawHistories::findFirst([
            'conditions' => 'user_id = :user_id: and type=:type:',
            'bind' => ['user_id' => $user->id, 'type' => 'diamond'],
            'order' => 'id desc']);

        $gold_draw_history = \DrawHistories::findFirst([
            'conditions' => 'user_id = :user_id: and type=:type:',
            'bind' => ['user_id' => $user->id, 'type' => 'gold'],
            'order' => 'id desc']);

        $car_gift_num = \DrawHistories::count([
            'conditions' => 'gift_type = :gift_type: and user_id = :user_id:',
            'bind' => ['gift_type' => GIFT_TYPE_CAR, 'user_id' => $user->id],
        ]);

        $diamond_total_number = $diamond_draw_history ? $diamond_draw_history->total_number : 0;
        $gold_total_number = $gold_draw_history ? $gold_draw_history->total_number : 0;

        $this->view->gold_total_number = $gold_total_number;
        $this->view->diamond_total_number = $diamond_total_number;
        $this->view->car_gift_num = $car_gift_num;
    }


}