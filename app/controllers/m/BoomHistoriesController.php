<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:38
 */

namespace m;

class BoomHistoriesController extends BaseController
{

    static $boom_type = [BOOM_HISTORY_GIFT_TYPE, BOOM_HISTORY_DIAMOND_TYPE, BOOM_HISTORY_GOLD_TYPE];

    function indexAction()
    {
        $sid = $this->params('sid');
        $code = $this->params('code');

        $this->view->title = '爆礼物';
        $this->view->sid = $sid;
        $this->view->code = $code;
    }

    function prizeAction()
    {
        $user = $this->currentUser();
        $room_id = $user->current_room_id;
        // 前三排行
        $boom_histories = \BoomHistories::findHistoriesByUser($user, 3);
        $boom_histories = $boom_histories->toJson('boom_histories', 'toSimpleJson');

        if (!$room_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误', $boom_histories);
        }

        $room = \Rooms::findFirstById($room_id);
        $res = \BoomHistories::getPrize($user, $room);
        list($code, $reason, $boom_history) = $res;
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['boom_histories' => [$boom_history->toSimpleJson()]]);
    }

    /**
     * @desc 历史记录
     * @return bool
     */
    function historyAction()
    {
        $room = $this->currentUser()->current_room;

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['boom_histories' => '']);
        }
        $boom_histories = \BoomHistories::findHistoriesByRoom($room);
        $boom_histories = $boom_histories->toJson('boom_histories', 'toSimpleJson');
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $boom_histories);
    }
}