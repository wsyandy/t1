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

        $user = $this->currentUser();
        $cond = ['conditions' => 'user_id!=:user_id:',
            'bind' => ['user_id' => $user->id],
            'order' => 'id desc'
        ];

        $draw_histories = \DrawHistories::findPagination($cond, 1, 10);

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
            if ($user->diamond < $total_amount) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }

            $target = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DRAW_EXPENSES, $total_amount, $opts);
            if (!$target) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }

            $draw_histories = [];
            for ($i = 1; $i <= $num; $i++) {
                $draw_histories[] = \DrawHistories::createHistory($this->currentUser(),
                    ['pay_type' => 'diamond', 'pay_amount' => $amount]);
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

    }

    // 我的奖品
    function listAction()
    {
        $user = $this->currentUser();
        $draw_histories = \DrawHistories::find(['conditions' => 'user_id=:user_id:',
            'bind' => ['user_id' => $user->id]
        ]);
    }


}