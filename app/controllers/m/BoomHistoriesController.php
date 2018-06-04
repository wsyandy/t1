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

        if (!$room_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误', ['boom_histories' => '']);
        }

        $room = \Rooms::findFirstById($room_id);
        $res = \BoomHistories::getPrize($user, $room);
        list($code, $reason, $boom_history) = $res;

        $json = [];
        $is_car = 0;
        if ($boom_history) {
            $json = $boom_history->toSimpleJson();

            if ($boom_history->isCar()) {
                $is_car = 1;
            }
        } else {
            return $this->renderJSON($code, $reason, ['boom_histories' => '']);
        }

        return $this->renderJSON($code, $reason, ['boom_histories' => [$json], 'is_car' => $is_car]);
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