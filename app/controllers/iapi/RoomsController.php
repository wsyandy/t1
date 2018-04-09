<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:47
 */

namespace iapi;

class RoomsController extends BaseController
{
    // Signaling Key 用于登录信令系统;
    function signalingKeyAction()
    {
        $key = $this->currentProductChannel()->getSignalingKey($this->currentUser()->id);
        $app_id = $this->currentProductChannel()->getImAppId();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['app_id' => $app_id, 'signaling_key' => $key]);
    }

    //Channel Key 用于加入频道;
    function channelKeyAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $key = $this->currentProductChannel()->getChannelKey($room->channel_name, $this->currentUser()->id);
        $app_id = $this->currentProductChannel()->getImAppId();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['app_id' => $app_id, 'channel_key' => $key]);
    }

    function indexAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 8);
        $hot = intval($this->params('hot', 0));
        $new = intval($this->params('new', 0));
        $product_channel_id = $this->currentProductChannelId();

        $opts = ['product_channel_id' => $product_channel_id, 'hot' => $hot, 'new' => $new];

        $rooms = \Rooms::searchRooms($opts, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $rooms->toJson('rooms', 'toSimpleJson'));
    }

    //创建房间
    function createAction()
    {
        $name = $this->params('name');
        if (isBlank($name)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstByUserId($this->currentUser()->id);
        if (isBlank($room)) {
            $room = \Rooms::createRoom($this->currentUser(), $name);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', $room->toBasicJson());
    }

    //更新房间信息
    function updateAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        if (!$this->currentUser()->isRoomHost($room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限');
        }

        $room->updateRoom($this->params());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }

}