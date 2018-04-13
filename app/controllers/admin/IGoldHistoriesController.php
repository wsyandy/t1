<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 15:22
 */

namespace admin;

class IGoldHistoriesController extends BaseController
{
    function indexAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];

        $i_gold_histories = \IGoldHistories::findPagination($conditions, $page, $per_page);
        $this->view->i_gold_histories = $i_gold_histories;
        $this->view->user_id = $user_id;
    }

    function giveIGoldAction()
    {
        $user_id = $this->params('user_id');
        if ($this->request->isPost()) {

            if (!$this->currentOperator()->isSuperOperator()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
            }

            $user = \Users::findFirstById($user_id);

            $i_gold = intval($this->params('i_gold'));
            $opts = ['remark' => '系统赠送' . $i_gold . '金币', 'operator_id' => $this->currentOperator()->id];

            if ($i_gold > 10000 && isProduction()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '赠送数量超过限制');
            }

            if ($i_gold > 0) {
                \IGoldHistories::changeBalance($user_id, I_GOLD_HISTORY_FEE_TYPE_GIVE, $i_gold, $opts);
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/i_gold_histories?user_id=' . $user_id]);
        }
        $this->view->user_id = $user_id;
    }
}