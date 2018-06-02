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

        $cond = ['conditions' => 'type=:type: or type=:type2:',
            'bind' => ['type' => 'diamond', 'type2' => 'gift'],
            'order' => 'id desc'
        ];

        $draw_histories = \DrawHistories::findPagination($cond, 1, 15);
        $res = $draw_histories->toJson('draw_histories', 'toSimpleJson');


        $hot_cache = \DrawHistories::getHotWriteCache();
        $num = $hot_cache->zcard('draw_histories_1000');
        if ($num > 1000) {
            $hot_cache->zremrangebyrank('draw_histories_1000', 0, $num - 800);
        }

        $ids = $hot_cache->zrevrange('draw_histories_1000', 0, 10);
        if ($ids) {
            $qian_draw_histories = \DrawHistories::findByIds($ids);
        } else {
            $cond = ['conditions' => '(type=:type: or type=:type2:) and number >= 1000',
                'bind' => ['type' => 'diamond', 'type2' => 'gift'],
                'order' => 'id desc'
            ];
            $qian_draw_histories = \DrawHistories::findPagination($cond, 1, 10);
        }

        $qian_res = $qian_draw_histories->toJson('draw_histories', 'toSimpleJson');
        $res['draw_histories'] = array_merge($qian_res['draw_histories'], $res['draw_histories']);


        $num = $hot_cache->zcard('draw_histories_10000');
        if ($num > 1000) {
            $hot_cache->zremrangebyrank('draw_histories_10000', 0, $num - 800);
        }
        $ids = $hot_cache->zrevrange('draw_histories_10000', 0, 10);
        if ($ids) {
            $wan_draw_histories = \DrawHistories::findByIds($ids);
        } else {
            $cond = ['conditions' => '(type=:type: or type=:type2:) and number >= 10000',
                'bind' => ['type' => 'diamond', 'type2' => 'gift'],
                'order' => 'id desc'
            ];

            $wan_draw_histories = \DrawHistories::findPagination($cond, 1, 10);
        }

        $wan_res = $wan_draw_histories->toJson('draw_histories', 'toSimpleJson');
        $res['draw_histories'] = array_merge($wan_res['draw_histories'], $res['draw_histories']);

        $num = $hot_cache->zcard('draw_histories_100000');
        if ($num > 1000) {
            $hot_cache->zremrangebyrank('draw_histories_100000', 0, $num - 800);
        }
        $ids = $hot_cache->zrevrange('draw_histories_100000', 0, 10);
        if ($ids) {
            $wan10_draw_histories = \DrawHistories::findByIds($ids);
        } else {
            $cond = ['conditions' => 'type=:type: and number >= 30000',
                'bind' => ['type' => 'diamond'],
                'order' => 'id desc'
            ];

            $wan10_draw_histories = \DrawHistories::findPagination($cond, 1, 10);
        }

        $wan10_res = $wan10_draw_histories->toJson('draw_histories', 'toSimpleJson');
        $res['draw_histories'] = array_merge($wan10_res['draw_histories'], $res['draw_histories']);

        shuffle($res['draw_histories']);

        $this->view->draw_histories = json_encode($res['draw_histories'], JSON_UNESCAPED_UNICODE);
    }

    // 砸蛋抽奖
    function drawAction()
    {

        if (!$this->request->isAjax()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }

        $user = $this->currentUser();
        $num = $this->params('num', 1);
        $loop_num = $num;
        $amount = 10;

        $draw_num = $user->getDrawNum();
        if ($draw_num > $num) {
            $remain_draw_num = $draw_num - $num;
            $num = 0;
        } else {
            $remain_draw_num = 0;
            $num = $num - $draw_num;
        }

        if ($remain_draw_num) {
            $user->setDrawNum($remain_draw_num);
        }


        $total_amount = $amount * $num;
        $remark = '抽奖消费' . $total_amount . '钻石';
        $opts['remark'] = $remark;

        if ($user->diamond < $total_amount) {
            return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
        }

        if ($total_amount) {
            $target = \AccountHistories::changeBalance($user, ACCOUNT_TYPE_DRAW_EXPENSES, $total_amount, $opts);
            if (!$target) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }
        }

        $draw_histories = [];

        $hit_diamond_num = 0;
        for ($i = 1; $i <= $loop_num; $i++) {

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

        $draw_history = \DrawHistories::findFirst([
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user->id],
            'order' => 'id desc']);

        $this->view->total_gold = $draw_history->total_gold;
        $this->view->total_diamond = $draw_history->total_diamond;
        $this->view->car_gift_num = $draw_history->total_gift_num;;
    }


}