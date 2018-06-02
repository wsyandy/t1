<?php

namespace m;

class AwardHistoriesController extends BaseController
{
    function indexAction()
    {
        $user = $this->currentUser();
        $product_channel = $this->currentProductChannel();
        $award_history_id = $this->params('award_history_id');
        $award_history = \AwardHistories::findFirstById($award_history_id);
        if (!$award_history) {
            return $this->response->redirect('app://back');
        }

        info($award_history->toSimpleJson());

        $this->view->award_history_json = json_encode($award_history->toSimpleJson(), JSON_UNESCAPED_UNICODE);
        $this->view->user = $user;
        $this->view->product_channel_name = $product_channel->name;
    }

    function getAwardsAction()
    {
        $user = $this->currentUser();
        $id = $this->params('award_history_id');
        $cache = \AwardHistories::getHotWriteCache();
        $lock_key = 'get_award_lock_' . $id;
        if (!$cache->set($lock_key, $id, ['NX', 'EX' => 3])) {
            $this->renderJSON(ERROR_CODE_FAIL, '请勿频繁操作！');
        }

        $award_history = \AwardHistories::findFirstById($id);
        if ($award_history->status == STATUS_OFF) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您已经领取过了哦，快去您的账户中请查收！');
        }

        if ($award_history->user_id != $user->id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '这不是您的奖励哦！');
        }

        //用户获取系统扶持奖励
        $result = $award_history->getAwards($user);
        if ($result) {
            $award_history->status = STATUS_OFF;
            if ($award_history->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '您的奖励将自动发放至您的账户中请查收！');
            }
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
    }
}