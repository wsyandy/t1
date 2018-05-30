<?php

namespace m;

class RedPacketsController extends BaseController
{

    function indexAction()
    {
        $user = $this->currentUser();
        $red_packet_type = \RedPackets::$RED_PACKET_TYPE;
        if ($user->user_role != USER_ROLE_HOST_BROADCASTER) {
            unset($red_packet_type[RED_PACKET_TYPE_NEARBY]);
        }

        info($user->id, $user->user_role_text, '类型', $red_packet_type);

        $this->view->user = $user;
        $this->view->diamond = $user->diamond;
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

        $room = $user->current_room;
        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您不在当前房间哦，请重进！');
        }

        if(!$user->isCompanyUser()){
            if ($num < 5) {
                return $this->renderJSON(ERROR_CODE_FAIL, '红包个数不能少于5个');
            }
            if ($num > 100) {
                return $this->renderJSON(ERROR_CODE_FAIL, '红包个数不能大于100个');
            }

            if ($red_packet_type == RED_PACKET_TYPE_NEARBY && $diamond < 10000) {
                return $this->renderJSON(ERROR_CODE_FAIL, '红包金额不能小于10000钻');

            } elseif (($red_packet_type == RED_PACKET_TYPE_FOLLOW || $red_packet_type == RED_PACKET_TYPE_STAY_AT_ROOM)
                && $diamond < 1000) {

                return $this->renderJSON(ERROR_CODE_FAIL, '红包金额不能小于1000钻');
            } else {
                if ($diamond < 100) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '红包金额不能小于100钻');
                }
            }
        }

        if ($user->diamond < $diamond) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您的钻石余额不足，请充值后再发红包');
        }

        $opts = [
            'diamond' => $diamond,
            'num' => $num,
            'balance_diamond' => $diamond,
            'balance_num' => $num,
            'status' => STATUS_ON,
            'user_id' => $user->id,
            'room_id' => $room->id,
            'sex' => $sex,
            'red_packet_type' => $red_packet_type,
            'nearby_distance' => $nearby_distance
        ];

        //创建红包
        $send_red_packet_history = \RedPackets::createRedPacket($user, $room, $opts);
        if (!$send_red_packet_history) {
            return $this->renderJSON(ERROR_CODE_FAIL, '系统错误');
        }

        $opts = ['remark' => '发送红包扣除' . $diamond, 'mobile' => $user->mobile, 'target_id' => $send_red_packet_history->id];
        $account_history = \AccountHistories::changeBalance($user, ACCOUNT_TYPE_RED_PACKET_EXPENSES, $diamond, $opts);
        if (!$account_history) {
            $send_red_packet_history->status = STATUS_OFF;
            $send_red_packet_history->save();
            return $this->renderJSON(ERROR_CODE_FAIL, '余额不足');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '发布成功', ['send_red_packet_history' => $send_red_packet_history->toJson()]);
    }

    function stateAction()
    {
        $this->view->title = '红包说明';
    }

    // 房间红包列表
    function redPacketsListAction()
    {
        $user = $this->currentUser();
        $room_id = $this->params('room_id');

        if ($this->request->isAjax()) {

            $page = $this->params('page', 1);
            $per_page = $this->params('per_page', 10);
            //用户进来的时间
            $room = $user->current_room;
            if (!$room) {
                $room = \Rooms::findFirstById($room_id);
            }

            $red_packets = \RedPackets::findRedPacketList($user, $room, $page, $per_page);
            if ($red_packets) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '红包列表',
                    $red_packets->toJson('red_packets', 'toSimpleJson')
                );
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '暂无红包信息');
        }

        $this->view->titile = '红包列表';
        $this->view->room_id = $room_id;
    }

    function grabRedPacketsAction()
    {

        $user = $this->currentUser();
        $red_packet_id = $this->params('red_packet_id');

        $red_packet = \RedPackets::findFirstById($red_packet_id);
        if (!$red_packet) {
            return $this->response->redirect('app://back');
        }

        $sex = $red_packet->sex;
        $red_packet_type = $red_packet->red_packet_type;
        //用户进来的时间
        $room = $red_packet->room;
        $distance_start_at = 0;

        $user_nickname = $red_packet->user->nickname;
        $user_avatar_url = $red_packet->user->avatar_small_url;

        if ($this->request->isAjax()) {

            if ($red_packet->status == STATUS_OFF) {
                return $this->renderJSON(ERROR_CODE_FAIL, '已经抢光啦');
            }

            $cache = \Users::getUserDb();
            $user_red_key = \RedPackets::generateUserRedPacketsKey($user->id);
            $score = $cache->zscore($user_red_key, $red_packet_id);
            if ($score) {
                return $this->renderJSON(ERROR_CODE_BLOCKED_ACCOUNT, '已抢过');
            }

            //时间限制
            if ($red_packet_type == RED_PACKET_TYPE_STAY_AT_ROOM) {
                $distance_start_at = $red_packet->getDistanceStartTime($user);
                if ($distance_start_at > 0) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '不要心急，您还没在房间待满三分钟哦！');
                }
            }

            //当类型为附近的人的时候才会对用户性别有要求
            if ($red_packet_type == RED_PACKET_TYPE_NEARBY) {
                //未做=>距离的判断
                if ($sex != USER_SEX_COMMON && $sex != $user->sex) {
                    $sex_content = $sex == USER_SEX_MALE ? '小哥哥' : '小姐姐';
                    return $this->renderJSON(ERROR_CODE_FAIL, '这个红包只有' . $sex_content . '才可以抢哦！');
                }

                $red_users = [$red_packet->user];
                $this->currentUser()->calDistance($red_users);
                $distance = $red_users[0]->distance;
                $distance = doubleval($distance) * 1000;
                if ($red_packet->nearby_distance < $distance) {
                    $geo_distance = sprintf("%0.2f", $red_packet->nearby_distance / 1000);
                    return $this->renderJSON(ERROR_CODE_FAIL, '这个红包只有' . $geo_distance . 'km内才可以抢哦！');
                }
            }

            //是否关注房主
            if ($red_packet_type == RED_PACKET_TYPE_FOLLOW) {

                if ($room->user_id == $user->id) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '不能抢自己的红包哦');
                }

                if (!$user->isFollow($room->user)) {
                    $client_url = '/m/red_packets/followers';
                    return $this->renderJSON(ERROR_CODE_FORM, '需要关注房主才可领取', ['client_url' => $client_url]);
                }
            }

            $get_diamond = $red_packet->grabRedPacket($user);
            $error_reason = '手慢了，红包抢完了！';
            if ($get_diamond) {
                $error_reason = '抢到' . $user_nickname . '发的钻石红包';
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, $error_reason, ['get_diamond' => $get_diamond]);
        }

        $this->view->red_packet = $red_packet;
        $this->view->user_nickname = $user_nickname;
        $this->view->user_avatar_url = $user_avatar_url;
        $this->view->room_user_nickname = $room->user->nickname;
        $this->view->room_user_avatar_url = $room->user->avatar_small_url;
        $this->view->distance_start_at = $distance_start_at;
    }

    function detailAction()
    {
        $red_packet_id = $this->params('red_packet_id');
        $red_packet = \RedPackets::findFirstById($red_packet_id);
        $this->view->red_packet = $red_packet->toBasicJson();
    }

    function getRedPacketUsersAction()
    {

        $red_packet_id = $this->params('red_packet_id');
        $red_packet = \RedPackets::findFirstById($red_packet_id);
        if (!$red_packet) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $cache = \Users::getUserDb();
        $user_list_key = $red_packet->generateRedPacketUserListKey();
        $user_ids = $cache->zrange($user_list_key, 0, -1, true);

        $users = \Users::findByIds(array_keys($user_ids));

        $get_red_packet_users = [];
        foreach ($users as $user) {

            $get_diamond_at = fetch($user_ids, $user->id, time());
            $key = \RedPackets::generateUserRoomRedPacketsKey($red_packet->room_id, $user->id);
            $get_diamond = $cache->zscore($key, $red_packet_id);

            $get_red_packet_users[] = array_merge($user->toChatJson(), ['get_diamond_at' => date('H:i', $get_diamond_at), 'get_diamond' => $get_diamond]);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['get_red_packet_users' => $get_red_packet_users]);
    }

    //关注房主并领取红包
    function followersAction()
    {
        $red_packet_id = $this->params('red_packet_id');
        $red_packet = \RedPackets::findFirstById($red_packet_id);
        $room = $red_packet->room;
        if (!$red_packet || !$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $user = $this->currentUser();
        if ($user->current_room_id != $room->id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您不在当前房间哦，请重进！');
        }

        if ($user->id == $room->user_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '不能关注自己哦');
        }

        $user->follow($room->user);

        $get_diamond = $red_packet->grabRedPacket($user);

        $error_reason = '手慢了，红包抢完了！';
        if ($get_diamond) {
            $user_nickname = $red_packet->user->nickname;
            if (mb_strlen($user_nickname) > 5) {
                $user_nickname = mb_substr($user_nickname, 0, 5) . '...';
            }
            $error_reason = '抢到' . $user_nickname . '发的钻石红包';
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, $error_reason, ['get_diamond' => $get_diamond]);
    }

}