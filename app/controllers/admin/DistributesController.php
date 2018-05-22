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
        //分享次数，人均分享次数，
        $loop_num = 7;
        for ($i = 0; $i < $loop_num; $i++) {
            $stat_at = beginOfDay($stat_at - $i * 24 * 3600);
            $key = \SmsDistributeHistories::generateDistributeNumKey($stat_at);
            $share_num = $stat_db->get($key);
//            if (!$share_num) {
//                return $this->renderJSON(ERROR_CODE_FAIL, '暂无数据');
//            }

            //邀请注册的钻石奖励
            $share_register_bonus = \AccountHistories::sum(['conditions' => 'fee_type=:fee_type:',
                'bind' => ['fee_type' => ACCOUNT_TYPE_DISTRIBUTE_REGISTER],
                'column' => 'amount'
            ]);

            //一、二级充值分成的钻石奖励
            $distribute_bonus_key = \SmsDistributeHistories::generateDistributeBonusKey($stat_at);
            $distribute_bonus_datas = $stat_db->hgetall($distribute_bonus_key);
            $first_distribute_bonus = fetch($distribute_bonus_datas, 'first_distribute_bonus');
            $second_distribute_bonus = fetch($distribute_bonus_datas, 'second_distribute_bonus');


            //已邀请人数
            $invited_user_num = \SmsDistributeHistories::count(['conditions' => 'status=:status: and user_id is not null',
                'bind' => ['status' => AUTH_SUCCESS]
            ]);
            $datas[$i]['invited_user_num'] = $invited_user_num;
            $datas[$i]['share_num'] = $share_num;
            $datas[$i]['share_register_bonus'] = $share_register_bonus;
            $datas[$i]['first_distribute_bonus'] = $first_distribute_bonus;
            $datas[$i]['second_distribute_bonus'] = $second_distribute_bonus;
        }
        $this->view->datas = $datas;
        $this->view->stat_at = date('Y-m-d', $stat_at);
    }
}