<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/27
 * Time: 下午8:23
 */

namespace m;

// 分销
class DistributeController extends BaseController
{

    function indexAction()
    {
        $user = $this->currentUser();
        $total_amount = \AccountHistories::sum(['conditions' => '(fee_type=:fee_type1: or fee_type=:fee_type2:) and user_id=:user_id:',
            'bind' => ['fee_type1' => ACCOUNT_TYPE_DISTRIBUTE_REGISTER, 'fee_type2' => ACCOUNT_TYPE_DISTRIBUTE_PAY, 'user_id' => $user->id],
            'column' => 'amount'
        ]);

        $user_num = \SmsDistributeHistories::count(['conditions' => 'status=:status: and share_user_id=:share_user_id:',
            'bind' => ['status' => AUTH_SUCCESS, 'share_user_id' => $user->id]
        ]);

        $this->view->title = '有奖邀请';
        $this->view->total_amount = $total_amount;
        $this->view->user_num = $user_num;
    }

    // 我的推广页
    function pageAction()
    {
        $user = $this->currentUser();
        $share_history = \ShareHistories::findFirst([
            'conditions' => 'user_id = :user_id: and share_source=:share_source:',
            'bind' => ['user_id' => $user->id, 'share_source' => 'distribute'],
            'order' => 'id desc']);
        if (!$share_history) {
            $opts = [
                'user_id' => $user->id,
                'product_channel_id' => $this->currentProductChannelId(),
                'share_source' => 'distribute'
            ];

            $share_history = \ShareHistories::createShareHistory($opts);
        }

        $share_url = $share_history->getShareUrl($this->getRoot(), $this->currentProductChannel()->code);

        $qrcode = generateQrcode($share_url);
        $product_channel_name = $this->currentProductChannel()->name;

        $this->view->title = '我的推广页';
        $this->view->qrcode = $qrcode;
        $this->view->product_channel_name = $product_channel_name;


    }

    function detailAction()
    {

    }

    function distributeRegisterBonusAction()
    {
        if ($this->request->isAjax()) {
            $cond['conditions'] = 'user_id=:user_id: and fee_type=:fee_type:';
            $cond['bind'] = ['user_id' => $this->currentUserId(), 'fee_type' => ACCOUNT_TYPE_DISTRIBUTE_REGISTER];
            $account_histories = \AccountHistories::find($cond);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',$account_histories->toJson('account_histories','toSimpleJson') );
        }

    }
    function distributePayBonusAction()
    {
        if ($this->request->isAjax()) {
            $cond['conditions'] = 'user_id=:user_id: and fee_type=:fee_type:';
            $cond['bind'] = ['user_id' => $this->currentUserId(), 'fee_type' => ACCOUNT_TYPE_DISTRIBUTE_PAY];
            $account_histories = \AccountHistories::find($cond);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',$account_histories->toJson('account_histories','toSimpleJson') );
        }

    }
}