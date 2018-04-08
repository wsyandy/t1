<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/21
 * Time: 下午2:58
 */

namespace admin;

class GoldHistoriesController extends BaseController
{
    function indexAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        debug($user_id, $page, $per_page);

        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];

        $gold_histories = \GoldHistories::findPagination($conditions, $page, $per_page);
        $this->view->gold_histories = $gold_histories;
        $this->view->user_id = $user_id;
    }

    function giveGoldAction()
    {
        $user_id = $this->params('user_id');

        if ($this->request->isPost()) {

            if (!$this->currentOperator()->isSuperOperator()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
            }

            $user = \Users::findFirstById($user_id);

            $amount = intval($this->params('gold'));
            $opts = ['remark' => '系统赠送' . $amount . '钻石', 'operator_id' => $this->currentOperator()->id];

            if ($amount > 10000 && isProduction()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '赠送数量超过限制');
            }

            if ($amount > 0) {
                \GoldHistories::changeBalance($user_id, GOLD_TYPE_GIVE, $amount, $opts);
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/gold_histories?user_id=' . $user_id]);
        }

        $this->view->user_id = $user_id;
    }
}