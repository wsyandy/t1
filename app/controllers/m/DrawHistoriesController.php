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

        $res = $draw_histories->toJson('draw_histories', 'toSimpleJson');

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

            for ($i = 1; $i <= $num; $i++) {
                $draw_history = \DrawHistories::createHistory($this->currentUser(), ['pay_type' => 'diamond', 'pay_amount' => $amount]);
                $draw_histories[] = $draw_history->toSimpleJson();
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
    }


}