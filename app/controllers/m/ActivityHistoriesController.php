<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/30
 * Time: 上午11:46
 */

namespace m;
class ActivityHistoriesController extends BaseController
{
    function indexAction()
    {
        $activity_id = $this->params('activity_id');

        if (!$activity_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        if ($this->request->isAJax()) {
            $page = $this->params('page');
            $per_page = $this->params('per_page');
            $activity_id = $this->params('activity_id');

            $cond = [
                'conditions' => 'user_id = :user_id: and activity_id = :activity_id:',
                'bind' => ['user_id' => $this->currentUser()->id, 'activity_id' => $activity_id],
                'order' => 'id desc'
            ];
            $activity_histories = \ActivityHistories::findPagination($cond, $page, $per_page);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',
                $activity_histories->toJson('activity_histories', 'toSimpleJson'));
        }

        $this->view->title = "中奖记录";
        $this->view->sid = $this->currentUser()->sid;
        $this->view->activity_id = $activity_id;
    }
}