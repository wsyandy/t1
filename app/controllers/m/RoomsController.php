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

        if ($this->request->isAjax()) {

            $total_room_id_key = \Rooms::getTotalRoomUserNumListKey();

            $hot_cache = \Users::getHotWriteCache();
            $total_room_ids = $hot_cache->zrange($total_room_id_key, 0, -1);
            $user = $this->currentUser();
            $current_user_room_id = $user->room_id;

            if ($current_user_room_id && in_array($current_user_room_id, $total_room_ids)) {
                unset($total_room_ids[array_search($current_user_room_id, $total_room_ids)]);
            }

            $room = null;

            if (isPresent($total_room_ids)) {
                $room_id = $total_room_ids[array_rand($total_room_ids)];
                $room = \Rooms::findFirstById($room_id);
            }

            if (!$room || $room->lock) {

                $cond = [
                    'conditions' => 'online_status = ' . STATUS_ON . ' and status = ' . STATUS_ON .
                        ' and user_id != :user_id: and product_channel_id = :product_channel_id: and lock = :lock:',
                    'bind' => ['user_id' => $user->id, 'product_channel_id' => $user->product_channel_id, 'lock' => 'false'],
                ];

                $room = \Rooms::findFirst($cond);
            }

            $url = "app://rooms/detail?id=" . $room->id;

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => $url]);
        }

        $this->view->code = $code;
        $this->view->sid = $sid;
        $this->view->user = $this->currentUser();
        $this->view->title = "匹配";
    }

    function findRoomAction()
    {

    }

    function wealthRankListAction()
    {
        $code = $this->params('code');
        $sid = $this->params('sid');
        $room_id = $this->params('room_id', 0);

        $this->view->user_id = $this->currentUserId();
        $this->view->room_id = $room_id;
        $this->view->code = $code;
        $this->view->sid = $sid;
        $this->view->title = "贡献榜";
    }


    function findWealthRankListAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);
        $list_type = $this->params('list_type');

        $room_id = $this->params('room_id');

        $user = $this->currentUser();

        if (isBlank($room_id)) {
            $room_id = $user->current_room_id;
        }

        $room = \Rooms::findFirstById($room_id);

        if (isBlank($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $key = $room->generateRoomWealthRankListKey($list_type);

        $users = \Users::findFieldRankListByKey($key, 'wealth', $page, $per_page);

        $res = $users->toJson('users', 'toRankListJson');

        $current_rank = $user->myRoomWealthRankByKey($key);

        if ($res) {
            $res['current_rank'] = $current_rank;
        }


        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $res);
    }
}