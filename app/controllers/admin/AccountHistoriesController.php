<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 15:22
 */

namespace admin;

class AccountHistoriesController extends BaseController
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

        $account_histories = \AccountHistories::findPagination($conditions, $page, $per_page);
        $this->view->account_histories = $account_histories;
        $this->view->user_id = $user_id;
    }

    function giveDiamondAction()
    {
        $user_id = $this->params('user_id');
        $content = $this->params('content');
        $remark = $this->params('remark');

        if ($this->request->isPost()) {

            if (!$this->currentOperator()->canGiveDiamond()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
            }

            $user = \Users::findFirstById($user_id);
            if ($content) {
                \Chats::sendTextSystemMessage($user, $content);
            }

            $amount = intval($this->params('diamond'));

            if (!$remark) {
                $remark = '系统赠送' . $amount . '钻石';
            }

            $opts = ['remark' => $remark, 'mobile' => $user->mobile, 'operator_id' => $this->currentOperator()->id];

            if ($amount > 500000 && isProduction()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '赠送数量超过限制');
            }

            if ($amount > 0) {
                \AccountHistories::changeBalance($user, ACCOUNT_TYPE_GIVE, $amount, $opts);
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/account_histories?user_id=' . $user_id]);
        }
        $this->view->user_id = $user_id;
    }
}