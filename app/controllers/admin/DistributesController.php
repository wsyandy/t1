<?php

namespace admin;

class DistributesController extends BaseController
{
    function indexAction()
    {
        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $stat_at = strtotime($stat_at);
        $stat_at = beginOfDay($stat_at);
        $datas = [];
        $stat_db = \Stats::getStatDb();

        $loop_num = 7;
        for ($i = 0; $i < $loop_num; $i++) {

            $start_at = beginOfDay($stat_at - $i * 24 * 3600);
            $end_at = endOfDay($stat_at - $i * 24 * 3600);

            //分享次数
            $distribute_num_key = \SmsDistributeHistories::generateDistributeNumKey($start_at);
            $share_num = $stat_db->get($distribute_num_key);
            $result['share_num'] = $share_num ? $share_num : 0;

            //分享人数
            $share_distribute_user_key = \SmsDistributeHistories::generateShareDistributeUserListKey($start_at);
            $share_distribute_user_num = $stat_db->zcard($share_distribute_user_key);
            $result['share_distribute_user_num'] = $share_distribute_user_num;

            //人均分享次数
            $per_capita_share_num = 0;
            if ($share_distribute_user_num) {
                $per_capita_share_num = round($share_num / $share_distribute_user_num);
            }
            $result['per_capita_share_num'] = $per_capita_share_num;

            //一、二级充值分成的钻石奖励
            $distribute_bonus_key = \SmsDistributeHistories::generateDistributeBonusKey($start_at);
            $distribute_bonus_datas = $stat_db->hgetall($distribute_bonus_key);

            $result['register_distribute_bonus'] = fetch($distribute_bonus_datas, 'register_distribute_bonus', 0);
            $result['first_distribute_bonus'] = fetch($distribute_bonus_datas, 'first_distribute_bonus', 0);
            $result['second_distribute_bonus'] = fetch($distribute_bonus_datas, 'second_distribute_bonus', 0);

            $result['distribute_total_amount'] = $result['register_distribute_bonus'] + $result['first_distribute_bonus'] + $result['second_distribute_bonus'];

            //充值人数
            $share_user_pay_num_key = \SmsDistributeHistories::generateShareDistributePayUserNumKey($start_at);
            $share_user_pay_num = $stat_db->zcard($share_user_pay_num_key);
            $result['share_user_pay_num'] = $share_user_pay_num ? $share_user_pay_num : 0;

            //充值金额
            $share_user_amount_key = \SmsDistributeHistories::generateShareDistributePayAmountKey($start_at);
            $share_user_amount = $stat_db->get($share_user_amount_key);
            $result['share_user_amount'] = $share_user_amount ? $share_user_amount : 0;

            //充值钻石数
            $share_user_diamond_key = \SmsDistributeHistories::generateShareDistributePayDiamondKey($start_at);
            $share_user_diamond = $stat_db->get($share_user_diamond_key);
            $result['share_user_diamond'] = $share_user_diamond ? $share_user_diamond : 0;

            //已邀请人数
            $invited_user_num = \SmsDistributeHistories::count(['conditions' => 'status=:status: and user_id is not null and created_at >=:start_at: and created_at <=:end_at:',
                'bind' => ['status' => AUTH_SUCCESS, 'start_at' => $start_at, 'end_at' => $end_at]
            ]);
            $result['invited_user_num'] = $invited_user_num;

            $datas[date('Y-m-d', $start_at)] = $result;
        }

        info($datas);
        $this->view->datas = $datas;
        $this->view->stat_at = date('Y-m-d', $stat_at);
    }
}