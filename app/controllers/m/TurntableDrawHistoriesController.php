<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/6/02
 * Time: 下午4:35
 */

namespace m;

class TurntableDrawHistoriesController extends BaseController
{
    function indexAction()
    {
        $cond = ['conditions' => 'type=:type: or type=:type2:',
            'bind' => ['type' => 'diamond', 'type2' => 'gift'],
            'order' => 'id desc'
        ];

        $draw_histories = \TurntableDrawHistories::findPagination($cond, 1, 20);
        $res = $draw_histories->toJson('turntable_draw_histories', 'toSimpleJson');

        $hot_cache = \TurntableDrawHistories::getHotWriteCache();
        $num = $hot_cache->zcard('turntable_draw_histories_1000');
        if ($num > 1000) {
            $hot_cache->zremrangebyrank('turntable_draw_histories_1000', 0, $num - 800);
        }

        $ids = $hot_cache->zrevrange('turntable_draw_histories_1000', 0, 10);
        if ($ids) {
            $qian_draw_histories = \TurntableDrawHistories::findByIds($ids);
        } else {
            $cond = ['conditions' => '(type=:type: or type=:type2:) and number >= 1000',
                'bind' => ['type' => 'diamond', 'type2' => 'gift'],
                'order' => 'id desc'
            ];
            $qian_draw_histories = \TurntableDrawHistories::findPagination($cond, 1, 10);
        }

        $qian_res = $qian_draw_histories->toJson('turntable_draw_histories', 'toSimpleJson');
        $res['turntable_draw_histories'] = array_merge($qian_res['turntable_draw_histories'], $res['turntable_draw_histories']);

        $num = $hot_cache->zcard('turntable_draw_histories_10000');
        if ($num > 1000) {
            $hot_cache->zremrangebyrank('turntable_draw_histories_10000', 0, $num - 800);
        }
        $ids = $hot_cache->zrevrange('turntable_draw_histories_10000', 0, 10);
        if ($ids) {
            $wan_draw_histories = \TurntableDrawHistories::findByIds($ids);
        } else {
            $cond = ['conditions' => '(type=:type: or type=:type2:) and number >= 10000',
                'bind' => ['type' => 'diamond', 'type2' => 'gift'],
                'order' => 'id desc'
            ];

            $wan_draw_histories = \TurntableDrawHistories::findPagination($cond, 1, 10);
        }

        $wan_res = $wan_draw_histories->toJson('turntable_draw_histories', 'toSimpleJson');
        $res['turntable_draw_histories'] = array_merge($wan_res['turntable_draw_histories'], $res['turntable_draw_histories']);

        $num = $hot_cache->zcard('turntable_draw_histories_100000');
        if ($num > 1000) {
            $hot_cache->zremrangebyrank('turntable_draw_histories_100000', 0, $num - 800);
        }
        $ids = $hot_cache->zrevrange('turntable_draw_histories_100000', 0, 10);
        if ($ids) {
            $wan10_draw_histories = \TurntableDrawHistories::findByIds($ids);
        } else {
            $cond = ['conditions' => 'type=:type: and number >= 30000',
                'bind' => ['type' => 'diamond'],
                'order' => 'id desc'
            ];

            $wan10_draw_histories = \TurntableDrawHistories::findPagination($cond, 1, 10);
        }

        $wan10_res = $wan10_draw_histories->toJson('turntable_draw_histories', 'toSimpleJson');
        $res['turntable_draw_histories'] = array_merge($wan10_res['turntable_draw_histories'], $res['turntable_draw_histories']);

        shuffle($res['turntable_draw_histories']);

        $this->view->turntable_draw_histories = json_encode($res['turntable_draw_histories'], JSON_UNESCAPED_UNICODE);
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

            $target = \AccountHistories::changeBalance($user, ACCOUNT_TYPE_DRAW_EXPENSES, $total_amount, $opts);
            if (!$target) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }

            $turntable_draw_histories = [];

            $hit_diamond_num = 0;
            for ($i = 1; $i <= $num; $i++) {

                if ($i >= mt_rand(8, 10) && $hit_diamond_num < 1) {
                    $turntable_draw_history = \TurntableDrawHistories::createHistory($this->currentUser(), ['pay_type' => 'diamond', 'pay_amount' => $amount, 'hit_diamond' => true]);
                } else {
                    $turntable_draw_history = \TurntableDrawHistories::createHistory($this->currentUser(), ['pay_type' => 'diamond', 'pay_amount' => $amount]);
                }

                if ($turntable_draw_history->type == 'diamond') {
                    $hit_diamond_num++;
                    array_unshift($turntable_draw_histories, $turntable_draw_history->toSimpleJson());
                } else {
                    $turntable_draw_histories[] = $turntable_draw_history->toSimpleJson();
                }
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['turntable_draw_histories' => $turntable_draw_histories]);
        }

    }

    // 我的奖品
    function listAction()
    {
        $user = $this->currentUser();
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        if ($this->request->isAjax()) {
            $turntable_draw_histories = \TurntableDrawHistories::findPagination(['conditions' => 'user_id=:user_id:',
                'bind' => ['user_id' => $user->id], 'order' => 'id desc'
            ], $page, $per_page);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $turntable_draw_histories->toJson('turntable_draw_histories', 'toSimpleJson'));
        }

        $turntable_draw_history = \TurntableDrawHistories::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user->id],
            'order' => 'id desc']);

        $this->view->total_gold = $turntable_draw_history->total_gold;
        $this->view->total_diamond = $turntable_draw_history->total_diamond;
        $this->view->car_gift_num = $turntable_draw_history->total_gift_num;;
    }
}