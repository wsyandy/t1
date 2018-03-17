<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/17
 * Time: 上午9:58
 */
namespace admin;

class HotRoomHistoriesController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('hot_room_history');
        $cond['order'] = 'id desc';
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 30);
        $hot_room_histories = \HotRoomHistories::findPagination($cond, $page, $per_page);
        $this->view->hot_room_histories = $hot_room_histories;
    }

    function editAction()
    {
        $id = $this->params('id');
        $hot_room_history = \HotRoomHistories::findFirstById($id);
        $this->view->hot_room_history = $hot_room_history;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $hot_room_history = \HotRoomHistories::findFirstById($id);

        if (!$hot_room_history) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        if (STATUS_PROGRESS != $hot_room_history->status) {
            return $this->renderJSON(ERROR_CODE_FAIL, '只允许修改申请中的申请');
        }

        $this->assign($hot_room_history, 'hot_room_history');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $hot_room_history);

        if ($hot_room_history->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['hot_room_history' => $hot_room_history->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }
}