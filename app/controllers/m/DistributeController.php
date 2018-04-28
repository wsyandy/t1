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

    }

}