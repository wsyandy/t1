<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/28
 * Time: 下午4:35
 */

namespace m;


class RotaryDrawHistoriesController extends BaseController
{

    function indexAction()
    {

        $user = $this->currentUser();
        $cond = ['conditions' => 'user_id!=:user_id:',
            'bind' => ['user_id' => $user->id],
            'order' => 'id desc'
        ];

        $rotary_draw_histories = \RotaryDrawHistories::findPagination($cond, 1, 10);

    }

    // 转盘抽奖
    function rotaryAction()
    {
        if ($this->request->isAjax()) {

            $amount = 10;
            $remark = '抽奖消费' . $amount . '钻石';
            $opts['remark'] = $remark;
            $user = $this->currentUser();
            if ($user->diamond < $amount) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }

            $target = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DRAW_EXPENSES, $amount, $opts);
            if (!$target) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }

            $rotary_draw_history = \RotaryDrawHistories::createHistory($this->currentUser(), []);


            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

    }

    // 我的奖品
    function listAction()
    {
        $user = $this->currentUser();
        $rotary_draw_histories = \RotaryDrawHistories::find(['conditions' => 'user_id=:user_id:',
            'bind' => ['user_id' => $user->id]
        ]);
    }


}