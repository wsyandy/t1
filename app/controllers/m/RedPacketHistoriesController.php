<?php

namespace m;

class RedPacketHistoriesController extends BaseController
{
    function indexAction()
    {

    }

    function createAction()
    {
        $user = $this->currentUser();

        $diamond = $this->params('diamond');
        $num = $this->params('num');
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
            'status' => STATUS_WAIT,
            'user_id' => $user->id,
            'current_room_id' => $user->current_room_id
        ];

        //创建红包
        $send_red_packet_history = \SendRedPacketHistories::createReadPacket($room, $opts);
        if ($send_red_packet_history) {
            $opts = ['remark' => '发送红包扣除' . $diamond, 'mobile' => $user->mobile, 'target_id' => $send_red_packet_history->id];
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DISTRIBUTE_PAY, $diamond, $opts);
        }
    }

}