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
        info('类型', $red_packet_type);

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
            'nearby_distance' => $nearby_distance,
            'balance_diamond' => $diamond,
            'balance_num' => $num
        ];

        //创建红包
        $send_red_packet_history = \RedPackets::createReadPacket($user, $room, $opts);

        if ($send_red_packet_history) {
            $opts = ['remark' => '发送红包扣除' . $diamond, 'mobile' => $user->mobile, 'target_id' => $send_red_packet_history->id];
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_DISTRIBUTE_PAY, $diamond, $opts);

            $room = $user->current_room;
            $room->has_red_packet = STATUS_ON;
            $room->update();
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
        $red_packet_type = $this->params('red_packet_type');
        $sex = $this->params('sex');

        $cache = \Users::getUserDb();
        $key = \RedPackets::generateRedPacketForRoomKey($user->current_room_id, $user->id);
        $user_nickname = $cache->hget($key, 'user_nickname');

        if ($this->request->isAjax()) {
            list($balance_diamond, $balance_num) = \RedPackets::checkRedPacketInfoForRoom($red_packet_id);

            $cache = \Users::getUserDb();
            $key = \RedPackets::generateRedPacketForRoomKey($user->current_room_id, $user->id);
            $score = $cache->zscore($key, $red_packet_id);
            if ($score) {
                return $this->renderJSON(ERROR_CODE_FAIL, '已抢过');
            }

            //未做=>还要加个一个用户在房主房间待的时长的和如果是有附近人的限制的话，判断其距离还有是否需要关注房主

            if ($balance_diamond <= 0 || $balance_num <= 0) {
                return $this->renderJSON(ERROR_CODE_FAIL, '已经抢光啦');
            }

            if ($sex != USER_SEX_COMMON) {
                if ($sex != $user->sex) {
                    $sex_content = $sex == USER_SEX_MALE ? '小哥哥' : '小姐姐';
                    return $this->renderJSON(ERROR_CODE_FAIL, '这个红包只有' . $sex_content . '才可以抢哦！');
                }
            }


            list($error_code, $error_reason, $get_diamond) = \RedPackets::grabRedPacket($user->current_room_id, $user, $red_packet_id);
            if (!$error_code) {
                //在这里增加钻石
                $opts = ['remark' => '红包获取钻石' . $get_diamond, 'mobile' => $this->currentUser()->mobile];
                \AccountHistories::changeBalance($this->currentUser()->id, ACCOUNT_TYPE_GAME_EXPENSES, $get_diamond, $opts);

                return $this->renderJSON($error_code, $error_reason, ['get_diamond' => $get_diamond]);
            }

            return $this->renderJSON($error_code, $error_reason);
        }

        $this->view->red_packet_id = $red_packet_id;
        $this->view->red_packet_type = $red_packet_type;
        $this->view->user_nickname = $user_nickname;
    }

    function redPacketsListAction()
    {
        if ($this->request->isAjax()) {
            $room_id = $this->params('room_id');
            $page = $this->params('page', 1);
            $pre_page = $this->params('pre_page', 10);

            $red_packets = \RedPackets::findRedPacketList($room_id, $page, $pre_page);
            if ($red_packets) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '红包列表', $red_packets->toJson('red_packets', 'toSimpleJson'));
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '暂无红包信息');
        }
        $this->view->titile = '红包列表';
    }

    function detailAction()
    {
        $red_packet_id = $this->params('id');
        $red_packet = \RedPackets::findFirstById($red_packet_id);
        $this->view->red_packet = $red_packet->toBasicJson();
    }

    function getRedPacketUsersAction()
    {
        $user = $this->currentUser();
        $room_id = $this->params('room_id');
        $red_packet_id = $this->params('red_packet_id');
        $cache = \Users::getUserDb();
        $key = \RedPackets::generateRedPacketInRoomForUserKey($user->current_room_id, $red_packet_id);
        $ids = $cache->zrange($key, 0, -1);
        $ids = [257, 117];
        $users = \Users::findByIds($ids);

        $get_red_packet_users = [];
        foreach ($users as $index => $user) {
            $key = \RedPackets::generateRedPacketInRoomForUserKey($room_id, $red_packet_id);
            $get_diamond = $cache->zscore($key, $user->id);
            $get_red_packet_users[] = array_merge($user->toChatJson(), ['get_diamond' => $get_diamond]);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['get_red_packet_users' => $get_red_packet_users]);
    }

}