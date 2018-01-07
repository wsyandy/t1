<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/28
 * Time: 上午10:47
 */

namespace api;

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

        //限制搜索条件
        //$rooms = \Rooms::findPagination(['conditions' => 'status = ' . STATUS_ON, 'order' => 'last_at desc'], $page, $per_page);
        $rooms = \Rooms::findPagination(['order' => 'last_at desc'], $page, $per_page);
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
        if ($room) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '已创建', ['id' => $room->id,
                'name' => $room->name, 'channel_name' => $room->channel_name]);
        }

        $room = \Rooms::createRoom($this->currentUser(), $name);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['id' => $room->id,
            'name' => $room->name, 'channel_name' => $room->channel_name]);
    }

    //进入房间
    function enterAction()
    {
        $room_id = $this->params('id', 0); // 进入指定房间
        $password = $this->params('password', '');
        $user_id = $this->params('user_id', 0); // 进入指定用户所在的房间

        if ($user_id) {
            $user = \Users::findFirstById($user_id);
            if (!$user || $user->current_room_id < 1) {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不在房间');
            }

            $room = $user->current_room;
        } else {
            $room = \Rooms::findFirstById($room_id);
            if (!$room) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
            }
        }

        if ($room->lock && $room->password != $password) {
            return $this->renderJSON(ERROR_CODE_FORM, '密码错误');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['id' => $room->id,
            'name' => $room->name, 'channel_name' => $room->channel_name]);
    }

    //更新房间信息
    function updateAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room->updateRoom($this->params());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }

    // 进入房间获取信息
    function detailAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room->enterRoom($this->currentUser());

        $key = $this->currentProductChannel()->getChannelKey($room->channel_name, $this->currentUser()->id);
        $app_id = $this->currentProductChannel()->getImAppId();

        $res = $room->toJson();
        $res['channel_key'] = $key;
        $res['app_id'] = $app_id;

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', $res);
    }

    function exitAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room->exitRoom($this->currentUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function lockAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }
        $password = $this->params('password');
        if (!$password) {
            return $this->renderJSON(ERROR_CODE_FAIL, '密码不能为空');
        }

        $room->password = $password;
        $room->lock = true;
        $room->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function unlockAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room->password = '';
        $room->lock = false;
        $room->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    // 公屏设置
    function openChatAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room->chat = true;
        $room->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function closeChatAction()
    {
        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room->chat = false;
        $room->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
    }

    function usersAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 8);

        $room_id = $this->params('id', 0);
        $room = \Rooms::findFirstById($room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $users = $room->findUsers($page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', $users->toJson('users', 'toSimpleJson'));
    }


    // 踢出房间
    function kickingAction()
    {

        if (!$this->otherUser()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room = \Rooms::findFirstById($this->params('id', 0));
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数非法');
        }

        $room->exitRoom($this->otherUser());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['id' => $room->id,
            'name' => $room->name, 'channel_name' => $room->channel_name]);
    }

}