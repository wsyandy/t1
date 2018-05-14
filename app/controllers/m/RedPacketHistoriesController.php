<?php

namespace m;

class RedPacketHistoriesController extends BaseController
{
    function indexAction()
    {
        $user = $this->currentUser();
        $red_packet_type = \RedPackets::$RED_PACKET_TYPE;
        if ($user->user_role != USER_ROLE_HOST_BROADCASTER) {
            unset($red_packet_type['nearby']);
        }
        info('类型',$red_packet_type);

        $diamond = $user->diamond;
        $this->view->diamond = $diamond;
        $this->view->user = $user;
        $this->view->red_packet_type = $red_packet_type;

    }

    function createAction()
    {
        $user = $this->currentUser();

        $diamond = $this->params('diamond');
        $num = $this->params('num');
        $sex = $this->params('sex', USER_SEX_COMMON);
        $red_packet_type = $this->params('red_packet_type');
        $nearby_distance = $this->params('nearby_distance', 0);
        if ($diamond < 100 || $num < 10) {
            return $this->renderJSON(ERROR_CODE_FAIL, '红包金额不得小于100钻或者个数不得小于10个');
        }
        if ($user->diamond < $diamond) {
            $to_pay_url = '';
            return $this->renderJSON(ERROR_CODE_FAIL, '余额不足', ['to_pay_url' => $to_pay_url]);
        }

        $room = \Rooms::findFirstById($user->current_room_id);
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '当前房间不存在');
        }
        $opts = [
            'diamond' => $diamond,
            'num' => $num,
            'status' => STATUS_ON,
            'user_id' => $user->id,
            'current_room_id' => $user->current_room_id,
            'sex' => $sex,
            'red_packet_type' => $red_packet_type,
            'nearby_distance' => $nearby_distance
        ];

        //创建红包
        $send_red_packet_history = \RedPackets::createReadPacket($room, $opts);

        if ($send_red_packet_history) {
            $opts = ['remark' => '发送红包扣除' . $diamond, 'mobile' => $user->mobile, 'target_id' => $send_red_packet_history->id];
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DISTRIBUTE_PAY, $diamond, $opts);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '发布成功', ['send_red_packet_history' => $send_red_packet_history->toJson()]);
    }

    function stateAction()
    {
        $this->view->title = '红包说明';
    }

    function grabRedPacketsAction()
    {
        $user = $this->currentUser();
        $red_packet_id = $this->params('red_packet_id');
        $red_packet = \RedPackets::findFirstById($red_packet_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '抢红包');
    }

    function redPacketListAction()
    {
        $user = $this->currentUser();
        $room_id = $this->params('room_id');
        $page = $this->params('page', 1);
        $pre_page = 10;

        $red_packets = \RedPackets::findRedPacketList($room_id, $page, $pre_page);
        if ($red_packets) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '红包列表', $red_packets->toJson('red_packets', 'toSimpleJson'));
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '暂无红包消息');
    }

    function detailAction()
    {
        $this->view->title = '红包详情';
    }

}