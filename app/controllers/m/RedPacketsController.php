<?php

namespace m;

class RedPacketsController extends BaseController
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
        $sex = $this->params('sex');
        $red_packet_type = $this->params('red_packet_type');
        $nearby_distance = $this->params('nearby_distance', 0);
        if (isDevelopmentEnv()) {
            if ($diamond < 100 || $num < 2) {
                return $this->renderJSON(ERROR_CODE_FAIL, '红包金额不得小于100钻或者个数不得小于2个');
            }
        } else {
            if ($red_packet_type == RED_PACKET_TYPE_NEARBY) {
                if ($diamond < 1000 || $num < 10) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '红包金额不得小于1000钻或者个数不得小于10个');
                }
            } else {
                if ($diamond < 100 || $num < 10) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '红包金额不得小于100钻或者个数不得小于10个');
                }
            }
        }


        if ($user->diamond < $diamond) {
            $to_pay_url = '';
            return $this->renderJSON(ERROR_CODE_FAIL, '您的钻石余额不足，请充值后再发红包', ['to_pay_url' => $to_pay_url]);
        }

        if (isBlank($user->current_room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您不在当前房间哦，请重进！');
        }
        $room = $user->current_room;

        $opts = [
            'diamond' => $diamond,
            'num' => $num,
            'status' => STATUS_ON,
            'user_id' => $user->id,
            'room_id' => $user->current_room_id,
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
            \AccountHistories::changeBalance($user, ACCOUNT_TYPE_RED_PACKET_EXPENSES, $diamond, $opts);
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

        $red_packet = \RedPackets::findFirstById($red_packet_id);
        //用户进来的时间
        $room = $red_packet->room;
        $distance_start_at = $red_packet->getDistanceStartTime($room, $user->id);

        $user_nickname = $red_packet->user->nickname;
        $user_avatar_url = $red_packet->user->avatar_url;

        if ($this->request->isAjax()) {
            $lock_key = 'grab_red_packet_' . $red_packet_id;
            $lock = tryLock($lock_key);
            $cache = \Users::getUserDb();
            $key = \RedPackets::generateRedPacketForRoomKey($room->id, $user->id);
            $score = $cache->zscore($key, $red_packet_id);
            if ($score) {
                unlock($lock);
                return $this->renderJSON(ERROR_CODE_BLOCKED_ACCOUNT, '已抢过');
            }

            if ($red_packet->status == STATUS_OFF) {
                unlock($lock);
                return $this->renderJSON(ERROR_CODE_FAIL, '已经抢光啦');
            }

            //时间限制
            if ($red_packet_type == RED_PACKET_TYPE_STAY_AT_ROOM) {
                $time = $red_packet->getDistanceStartTime($room, $user->id);
                if ($time > 0) {
                    unlock($lock);
                    return $this->renderJSON(ERROR_CODE_FAIL, '不要心急，您还没有待满三分钟哦！');
                }
            }

            //当类型为附近的人的时候才会对用户性别有要求
            if ($red_packet_type == RED_PACKET_TYPE_NEARBY) {
                //未做=>距离的判断
                if ($sex != USER_SEX_COMMON) {
                    if ($sex != $user->sex) {
                        $sex_content = $sex == USER_SEX_MALE ? '小哥哥' : '小姐姐';
                        unlock($lock);
                        return $this->renderJSON(ERROR_CODE_FAIL, '这个红包只有' . $sex_content . '才可以抢哦！');
                    }
                }
            }

            //是否关注房主
            if ($red_packet_type == RED_PACKET_TYPE_ATTENTION) {
                info('房主id', $room->user_id);
                if ($room->user_id == $user->id) {
                    unlock($lock);
                    return $this->renderJSON(ERROR_CODE_FAIL, '房主好意思抢自己的红包嘛');
                }
                $follow_key = 'follow_list_user_id' . $user->id;
                $follow_ids = $cache->zrange($follow_key, 0, -1);
                if (!in_array($room->user_id, $follow_ids)) {
                    $client_url = '/m/red_packets/followers';
                    unlock($lock);
                    return $this->renderJSON(ERROR_CODE_FORM, '需要关注房主才可领取', ['client_url' => $client_url]);
                }
            }

            list($error_code, $get_diamond) = \RedPackets::grabRedPacket($user, $room, $red_packet_id);
            $error_reason = '手慢了，红包抢完了！';
            if ($get_diamond) {
                if (mb_strlen($user_nickname) > 5) {
                    $user_nickname = mb_substr($user_nickname, 0, 5) . '...';
                }
                $error_reason = '抢到' . $user_nickname . '发的钻石红包';
                //在这里增加钻石
                $opts = ['remark' => '红包获取钻石' . $get_diamond, 'mobile' => $this->currentUser()->mobile, 'target_id' => $red_packet_id];
                \AccountHistories::changeBalance($this->currentUser(), ACCOUNT_TYPE_RED_PACKET_INCOME, $get_diamond, $opts);
            }
            unlock($lock);

            return $this->renderJSON($error_code, $error_reason, ['get_diamond' => $get_diamond]);
        }

        $this->view->red_packet = $red_packet;
        $this->view->user_nickname = $user_nickname;
        $this->view->user_avatar_url = $user_avatar_url;
        $this->view->distance_start_at = $distance_start_at;
        $this->view->user_id = $room->user_id;
    }

    function redPacketsListAction()
    {
        $user = $this->currentUser();
        $room_id = $this->params('room_id');
        if ($this->request->isAjax()) {
            $page = $this->params('page', 1);
            $pre_page = $this->params('pre_page', 10);
            //用户进来的时间
            $room = $user->current_room;
            if (isBlank($room)) {
                $room = \Rooms::findFirstById($room_id);
            }
            $red_packets = \RedPackets::findRedPacketList($room, $page, $pre_page, $user);
            if ($red_packets) {
                $user_get_red_packet_ids = \RedPackets::UserGetRedPacketIds($room->id, $user->id);
                return $this->renderJSON(ERROR_CODE_SUCCESS, '红包列表', array_merge(
                        $red_packets->toJson('red_packets', 'toSimpleJson'), ['user_get_red_packet_ids' => $user_get_red_packet_ids])
                );
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '暂无红包信息');
        }
        $this->view->titile = '红包列表';
        $this->view->room_id = $room_id;
    }

    function detailAction()
    {
        $red_packet_id = $this->params('red_packet_id');
        $red_packet = \RedPackets::findFirstById($red_packet_id);
        $this->view->red_packet = $red_packet->toBasicJson();
    }

    function getRedPacketUsersAction()
    {
        $room_id = $this->params('room_id');
        info('房间id', $room_id);
        $red_packet_id = $this->params('red_packet_id');
        $cache = \Users::getUserDb();
        $user_key = \RedPackets::generateRedPacketInRoomForUserKey($room_id, $red_packet_id);
        $ids = $cache->zrange($user_key, 0, -1);

        $users = \Users::findByIds($ids);

        $get_red_packet_users = [];
        foreach ($users as $index => $user) {
            $key = \RedPackets::generateRedPacketForRoomKey($room_id, $user->id);
            $get_diamond_at = $cache->zscore($user_key, $user->id);
            $get_diamond = $cache->zscore($key, $red_packet_id);
            info('获取的钻石的时间', $get_diamond_at, $user_key);
            info('获取的钻石', $get_diamond, $key);
            $get_red_packet_users[] = array_merge($user->toChatJson(), ['get_diamond_at' => date('H:i', $get_diamond_at), 'get_diamond' => $get_diamond]);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['get_red_packet_users' => $get_red_packet_users]);
    }

    //关注房主并领取红包
    function followersAction()
    {
        $red_packet_id = $this->params('red_packet_id');
        $user = $this->currentUser();
        if ($user->id == $this->otherUser()->id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '不能关注自己哦');
        }
        $red_packet = \RedPackets::findFirstById($red_packet_id);

        $user->follow($this->otherUser());

        if (isBlank($user->current_room)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您不在当前房间哦，请重进！');
        }

        list($error_code, $get_diamond) = \RedPackets::grabRedPacket($user, $user->current_room, $red_packet_id);
        $error_reason = '手慢了，红包抢完了！';
        if ($get_diamond) {
            $user_nickname = $red_packet->user->nickname;
            if (mb_strlen($user_nickname) > 5) {
                $user_nickname = mb_substr($user_nickname, 0, 5) . '...';
            }
            $error_reason = '抢到' . $user_nickname . '发的钻石红包';
            //在这里增加钻石
            $opts = ['remark' => '红包获取钻石' . $get_diamond, 'mobile' => $this->currentUser()->mobile, 'target_id' => $red_packet_id];
            \AccountHistories::changeBalance($this->currentUser(), ACCOUNT_TYPE_RED_PACKET_INCOME, $get_diamond, $opts);
        }

        return $this->renderJSON($error_code, $error_reason, ['get_diamond' => $get_diamond]);
    }
}