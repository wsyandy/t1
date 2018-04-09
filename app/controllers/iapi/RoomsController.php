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

    //进入房间
    function enterAction()
    {
        $room_id = $this->params('id', 0); // 进入指定房间
        $password = $this->params('password', '');
        $user_id = $this->params('user_id', 0); // 进入指定用户所在的房间

        if ($room_id) {

            $room = \Rooms::findFirstById($room_id);

            if (!$room) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
            }

        } else {

            $user = \Users::findFirstById($user_id);

            if (!$user || $user->current_room_id < 1) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户已不在房间');
            }

            $room = $user->current_room;
        }


        if ($room->isForbidEnter($this->currentUser())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您被禁止禁入房间,请稍后尝试');
        }

        $current_user_id = $this->currentUser()->id;
        $current_room_id = $this->currentUser()->current_room_id;

        //房间加锁并且不是房主且用户不在这个房间检验密码 从h5进入
        if (!$room->checkFilterUser($current_user_id)) {
            if ($room->lock && $room->user_id != $current_user_id && $current_room_id != $room->id && $room->password != $password) {
                return $this->renderJSON(ERROR_CODE_FORM, '密码错误');
            }
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功',
            ['id' => $room->id, 'uid' => $room->uid, 'name' => $room->name, 'channel_name' => $room->channel_name]);

    }

    // 进入房间获取信息
    function detailAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }


        //如果进入其他房间时 用户身上有房间 先退出房间
        $current_room = $this->currentUser()->current_room;

        info($this->currentUser()->sid, $this->currentUser()->current_room_id, $room_id);

        if ($current_room && $current_room->id != $room_id) {
            info($this->currentUser()->sid, $current_room->id, $room_id);
            $current_room->exitRoom($this->currentUser());
        }

        $room->enterRoom($this->currentUser());

        $key = $this->currentProductChannel()->getChannelKey($room->channel_name, $this->currentUser()->id);
        $app_id = $this->currentProductChannel()->getImAppId();

        //好友上线开播提醒(同一个用户一个小时之内只提醒一次)
//        $this->currentUser()->pushFriendIntoRoomRemind();

        //关注的人开播提醒(同一个用户一个小时之内只提醒一次)
//        $this->currentUser()->pushFollowedIntoRoomRemind();

        $res = $room->toJson();
        $res['channel_key'] = $key;
        $res['app_id'] = $app_id;
        $res['user_chat'] = $this->currentUser()->canChat($room);
        $res['system_tips'] = $this->currentProductChannel()->system_news;
        $res['user_role'] = $this->currentUser()->user_role;

        $user_car_gift = $this->currentUser()->getUserCarGift();

        if ($user_car_gift) {
            $res['user_car_gift'] = $user_car_gift->toSimpleJson();
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', $res);
    }

    //房间基本信息
    function basicInfoAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $room->toBasicJson());
    }


}