<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/8
 * Time: 下午8:23
 */
namespace m;
class RoomsController extends BaseController
{
    function matchingAction()
    {
        $code = $this->params('code');
        $sid = $this->params('sid');

        $this->view->code = $code;
        $this->view->sid = $sid;
        $this->view->user = $this->currentUser();
        $this->view->title = "匹配";
    }

    function findRoomAction()
    {
        $user = $this->currentUser();

        $cond = [
            'conditions' => 'online_status = ' . STATUS_ON . ' and status = ' . STATUS_ON . ' and product_channel_id = :product_channel_id: and lock = :lock:',
            'bind' => ['product_channel_id' => $user->product_channel_id, 'lock' => 'f'],
        ];

        if ($user->room_id) {
            $cond['conditions'] .= " and id != " . $user->room_id;
        }

        if ($user->current_room_id) {
            $cond['conditions'] .= " and id != " . $user->current_room_id;
        }

        $room = \Rooms::findFirst($cond);

        $url = "";

        if (isPresent($room)) {
            $url = "app://rooms/detail?id=" . $room->id;
        }
        debug($cond);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => $url]);
    }
}