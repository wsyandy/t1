<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/29
 * Time: 上午1:05
 */

namespace admin;


class DrawHistoriesController extends BaseController
{

    function indexAction()
    {
        $conds = $this->getConditions('draw_history');
        $conds['order'] = 'id desc';
        $page = $this->params('page');


        $user_db = \Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_diamond';
        $incr_num = $user_db->get($cache_key);
        // 系统支出
        $cache_decr_key = 'draw_history_total_amount_decr_diamond';
        $decr_num = $user_db->get($cache_decr_key);
        $cache_gift_decr_key = 'draw_history_total_amount_decr_gift';
        $gift_decr_num = $user_db->get($cache_gift_decr_key);
        $cache_decr_gold_key = 'draw_history_total_amount_decr_gold';
        $decr_gold_num = $user_db->get($cache_decr_gold_key);

        $pool_rate = sprintf("%0.3f", ($incr_num - $decr_num) / $incr_num);

        $this->view->pool_rate = $pool_rate;
        $this->view->total_incr_num = $incr_num;
        $this->view->total_decr_num = $decr_num;
        $this->view->total_decr_gift_num = $gift_decr_num;
        $this->view->total_decr_gold_num = $decr_gold_num;

        $draw_histories = \DrawHistories::findPagination($conds, $page);
        $this->view->draw_histories = $draw_histories;
    }

    function dayStatAction()
    {

        $user_db = \Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_diamond';
        $incr_num = $user_db->get($cache_key);
        // 系统支出
        $cache_decr_key = 'draw_history_total_amount_decr_diamond';
        $decr_num = $user_db->get($cache_decr_key);
        $cache_gift_decr_key = 'draw_history_total_amount_decr_gift';
        $gift_decr_num = $user_db->get($cache_gift_decr_key);
        $cache_decr_gold_key = 'draw_history_total_amount_decr_gold';
        $decr_gold_num = $user_db->get($cache_decr_gold_key);

        $pool_rate = sprintf("%0.3f", ($incr_num - $decr_num) / $incr_num);

        $this->view->pool_rate = $pool_rate;
        $this->view->total_incr_num = $incr_num;
        $this->view->total_decr_num = $decr_num;
        $this->view->total_decr_gift_num = $gift_decr_num;
        $this->view->total_decr_gold_num = $decr_gold_num;


        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $stat_at = strtotime($stat_at);
        $stat_at = beginOfDay($stat_at);

        $cache_key = 'draw_histories_day_stat_' . $stat_at;
        $hot_cache = \DrawHistories::getHotWriteCache();

        $stats = $hot_cache->get($cache_key);
        if ($stats) {
            $stats = json_decode($stats, true);
            if ($hot_cache->get($cache_key . '_run')) {
                $this->view->stats = $stats;
                $this->view->stat_at = date('Y-m-d', $stat_at);
                return;
            }

            $loop_num = 0;
            if (date('Y-m-d', $stat_at) == date("Y-m-d")) {
                $loop_num = 1;
            }
        } else {
            $loop_num = 7;
            $stats = [];
        }

        $hot_cache->setex($cache_key . '_run', 300, 1);

        for ($i = 0; $i < $loop_num; $i++) {

            $start_at = beginOfDay($stat_at - $i * 24 * 3600);
            $end_at = endOfDay($stat_at - $i * 24 * 3600);

            $result = [];
            $total_pay_amount = \DrawHistories::sum([
                'conditions' => 'pay_type = :pay_type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['pay_type' => 'diamond', 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'pay_amount'
            ]);
            $result['total_pay_amount'] = intval($total_pay_amount);

            $total_diamond = \DrawHistories::sum([
                'conditions' => 'type = :type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['type' => 'diamond', 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'number'
            ]);
            $result['total_diamond'] = $total_diamond;

            $total_gold = \DrawHistories::sum([
                'conditions' => 'type = :type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['type' => 'gold', 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'number'
            ]);
            $result['total_gold'] = $total_gold;

            $total_gift_num = \DrawHistories::sum([
                'conditions' => 'type = :type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['type' => 'gift', 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'gift_num'
            ]);
            $result['total_gift_num'] = $total_gift_num;

            $total_hit_num = \DrawHistories::count([
                'conditions' => 'created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['start_at' => $start_at, 'end_at' => $end_at]
            ]);
            $result['total_hit_num'] = $total_hit_num;

            $histories = \DrawHistories::find([
                'conditions' => 'created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['start_at' => $start_at, 'end_at' => $end_at],
                'columns' => 'distinct user_id'
            ]);
            $total_hit_user_num = count($histories);

            $result['total_hit_user_num'] = $total_hit_user_num;

            $avg_hit_num = 0;
            if ($total_hit_user_num) {
                $avg_hit_num = sprintf("%0.2f", $total_hit_num / $total_hit_user_num);
            }
            $result['avg_hit_num'] = $avg_hit_num;

            $avg_hit_diamond = 0;
            if ($total_hit_user_num) {
                $avg_hit_diamond = sprintf("%0.2f", $total_diamond / $total_hit_user_num);
            }
            $result['avg_hit_diamond'] = $avg_hit_diamond;

            $stats[date("Ymd", $start_at)] = $result;
        }

        $hot_cache->setex($cache_key, 7 * 3600 * 24, json_encode($stats, JSON_UNESCAPED_UNICODE));

        $this->view->stats = $stats;
        $this->view->stat_at = date('Y-m-d', $stat_at);
    }

    function hourStatAction()
    {

        $user_db = \Users::getUserDb();
        // 系统总收入
        $cache_key = 'draw_history_total_amount_incr_diamond';
        $incr_num = $user_db->get($cache_key);
        // 系统支出
        $cache_decr_key = 'draw_history_total_amount_decr_diamond';
        $decr_num = $user_db->get($cache_decr_key);
        $cache_gift_decr_key = 'draw_history_total_amount_decr_gift';
        $gift_decr_num = $user_db->get($cache_gift_decr_key);
        $cache_decr_gold_key = 'draw_history_total_amount_decr_gold';
        $decr_gold_num = $user_db->get($cache_decr_gold_key);

        $pool_rate = sprintf("%0.3f", ($incr_num - $decr_num) / $incr_num);

        $this->view->pool_rate = $pool_rate;
        $this->view->total_incr_num = $incr_num;
        $this->view->total_decr_num = $decr_num;
        $this->view->total_decr_gift_num = $gift_decr_num;
        $this->view->total_decr_gold_num = $decr_gold_num;


        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $stat_at = strtotime($stat_at);
        $stat_at = beginOfDay($stat_at);

        $cache_key = 'draw_histories_hour_stat_' . $stat_at;
        $hot_cache = \DrawHistories::getHotWriteCache();

        $stats = $hot_cache->get($cache_key);
        if ($stats) {
            $stats = json_decode($stats, true);
            if ($hot_cache->get($cache_key . '_run')) {
                $this->view->stats = $stats;
                $this->view->stat_at = date('Y-m-d', $stat_at);
                return;
            }

        } else {
            $stats = [];
        }

        $hour = 23;
        if (date("Y-m-d", $stat_at) == date('Y-m-d')) {
            $hour = date('H');
        }

        $hot_cache->setex($cache_key . '_run', 300, 1);

        $first = 0;
        for ($i = $hour; $i >= 0; $i--) {

            $start_at = $stat_at + $i * 3600;
            $end_at = $stat_at + ($i + 1) * 3600;

            if (isset($stats[date("YmdH", $start_at)])) {
                $first++;
                if ($first > 1) {
                    continue;
                }
            }

            $result = [];
            $total_pay_amount = \DrawHistories::sum([
                'conditions' => 'pay_type = :pay_type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['pay_type' => 'diamond', 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'pay_amount'
            ]);
            $result['total_pay_amount'] = intval($total_pay_amount);

            $total_diamond = \DrawHistories::sum([
                'conditions' => 'type = :type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['type' => 'diamond', 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'number'
            ]);
            $result['total_diamond'] = $total_diamond;

            $total_gold = \DrawHistories::sum([
                'conditions' => 'type = :type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['type' => 'gold', 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'number'
            ]);
            $result['total_gold'] = $total_gold;

            $total_gift_num = \DrawHistories::sum([
                'conditions' => 'type = :type: and created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['type' => 'gift', 'start_at' => $start_at, 'end_at' => $end_at],
                'column' => 'gift_num'
            ]);
            $result['total_gift_num'] = $total_gift_num;

            $total_hit_num = \DrawHistories::count([
                'conditions' => 'created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['start_at' => $start_at, 'end_at' => $end_at]
            ]);
            $result['total_hit_num'] = $total_hit_num;

            $histories = \DrawHistories::find([
                'conditions' => 'created_at>=:start_at: and created_at<=:end_at:',
                'bind' => ['start_at' => $start_at, 'end_at' => $end_at],
                'columns' => 'distinct user_id'
            ]);
            $total_hit_user_num = count($histories);

            $result['total_hit_user_num'] = $total_hit_user_num;

            $avg_hit_num = 0;
            if ($total_hit_user_num) {
                $avg_hit_num = sprintf("%0.2f", $total_hit_num / $total_hit_user_num);
            }
            $result['avg_hit_num'] = $avg_hit_num;

            $avg_hit_diamond = 0;
            if ($total_hit_user_num) {
                $avg_hit_diamond = sprintf("%0.2f", $total_diamond / $total_hit_user_num);
            }
            $result['avg_hit_diamond'] = $avg_hit_diamond;

            $stats[date("YmdH", $start_at)] = $result;
        }

        $hot_cache->setex($cache_key, 600, json_encode($stats, JSON_UNESCAPED_UNICODE));

        $this->view->stats = $stats;
        $this->view->stat_at = date('Y-m-d', $stat_at);
    }

    //砸金蛋屏蔽用户列表
    function blockUsersListAction()
    {
        $block_users_list = \DrawHistories::findBlockUsersList(1, 100);
        $this->view->block_users_list = $block_users_list;
    }

    //删除屏蔽用户
    function deleteBlockUserAction()
    {
        $id = $this->params('id');
        \DrawHistories::deleteBlockUser($id);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/draw_histories/block_users_list']);
    }


}