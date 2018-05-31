<?php

namespace m;

class CouplesController extends BaseController
{
    function indexAction()
    {
        $user = $this->currentUser();
        $room_id = $this->params('room_id');
        $room_id = intval($room_id);
        $is_show_alert = false;

        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return;
        }

        $lock_key = 'ready_cp_lock_room_' . $room_id;
        $lock = tryLock($lock_key);

        $pursuer = ['avatar_url' => '/m/images/ico_plus.png', 'uid' => '', 'nickname' => '虚位以待'];
        $data = $room->getReadyCpInfo();

        info($this->params(), $data);

        $is_host = $user->isRoomHost($room);
        $sponsor_id = fetch($data, 'sponsor_id');

        //没有初始化数据，且当前用户不是房主
        if (!$sponsor_id && !$is_host) {
            unlock($lock);
            return $this->response->redirect('app://back');
        }

        info('比较数据', $data);
        $pursuer_id = fetch($data, 'pursuer_id');
        //如果当前房间没有初始化数据，说明为房主开启cp，初始化cp数据
        if (!$sponsor_id && $is_host) {
            \Couples::createReadyCpInfo($user);

            $root = $this->getRoot();
            $image_url = $root . 'images/go_cp.png';
            $body = ['action' => 'game_notice', 'type' => 'start', 'content' => 'cp开始',
                'image_url' => $image_url, 'client_url' => "url://m/couples?room_id=" . $room_id];

            \Couples::sendCpFinishMessage($user, $body);
        }

        //当前用户不是发起者，还没有追求者，并且当前用户有麦位（麦位上的人）
        if ($user->id != $sponsor_id && !$pursuer_id && $user->current_room_seat_id) {
            $pursuer = $user->toCpJson();

            //更新数据
            \Couples::updateReadyCpInfo($user, $room_id);
        }

        //双方都已经入驻
        if ($sponsor_id && $pursuer_id) {
            $pursuer = \Users::findFirstById($pursuer_id)->toCpJson();
        }

        //当前用户不是房主且没有麦位，显示弹框
        if (!$user->current_room_seat_id && !$is_host) {
            $is_show_alert = true;
        }

        if ($sponsor_id) {
            $room_host_user = \Users::findFirstById($sponsor_id)->toCpJson();
        } else {
            $room_host_user = $user->toCpJson();
        }

        unlock($lock);

        $this->view->is_host = $is_host;
        $this->view->room_host_user = json_encode($room_host_user, JSON_UNESCAPED_UNICODE);
        $this->view->pursuer = json_encode($pursuer, JSON_UNESCAPED_UNICODE);
        $this->view->current_user_id = $user->id;
        $this->view->room_id = $room_id;
        $this->view->is_show_alert = $is_show_alert;
    }

    function createAction()
    {
        $user = $this->currentUser();
        $sponsor_id = $this->params('room_host_user_id');
        $pursuer_id = $this->params('pursuer_id');
        $room_id = $this->params('room_id');
        $cache = \Users::getHotWriteCache();
        $key = \Couples::generateReadyCpInfoKey($room_id);
        //房主闲的无聊，没事儿对面坑没有就点同意
        if (!$pursuer_id) {
            if ($sponsor_id == $user->id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您还没有求婚者哦，等等会有对的人出现');
            }
        }

        if ($user->id != $sponsor_id && $user->id != $pursuer_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '别羡慕了，赶紧找个对象，去自己的房间发起“CP”吧');
        }

        //判断两者是否已经组成cp，如果已经组成cp，直接跳转cp证页面，推over
        $score = \Couples::checkCpRelation($pursuer_id, $sponsor_id);
        info($score);
        if ($score) {
            $opts = [
                'sponsor_id' => $sponsor_id,
                'pursuer_id' => $pursuer_id
            ];
            $key = \Couples::generateReadyCpInfoKey($room_id);
            $cache->del($key);
            $body = ['action' => 'game_notice', 'type' => 'over', 'content' => 'cp结束',];
            \Couples::sendCpFinishMessage($user, $body);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '看看你们共同的证明！', $opts);
        }

        //当前用户为发起者，查看对方状态，如果小于2，说明还未确认
        if ($user->id == $pursuer_id) {
            $sponsor_status = $cache->hget($key, $sponsor_id);
            if ($sponsor_status < 2) {
                $cache->hincrby($key, $user->id, 1);
                return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '您的另一半还未点击[我愿意]，快通知对方吧');
            } else {
                $cache->hincrby($key, $user->id, 1);
                //成功组成cp，去相互保存对方的id
                $result = \Couples::cpSeraglioInfo($user, $room_id);
                if (!$result) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '已超时，请重新发起');
                }

                $opts = [
                    'sponsor_id' => $sponsor_id,
                    'pursuer_id' => $pursuer_id
                ];

                return $this->renderJSON(ERROR_CODE_SUCCESS, '恭喜有情人终成眷属，是前生造定事，莫错过姻缘！', $opts);
            }
        }

        if ($user->id == $sponsor_id) {
            $pursuer_status = $cache->hget($key, $pursuer_id);
            if ($pursuer_status < 2) {
                $cache->hincrby($key, $user->id, 1);
                return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '您的另一半还未点击[我愿意]，快通知对方吧');
            } else {
                $cache->hincrby($key, $user->id, 1);
                //成功组成cp，去相互保存对方的id
                $result = \Couples::cpSeraglioInfo($user, $room_id);
                if (!$result) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '已超时，请重新发起');
                }

                $opts = [
                    'sponsor_id' => $sponsor_id,
                    'pursuer_id' => $pursuer_id
                ];

                return $this->renderJSON(ERROR_CODE_SUCCESS, '恭喜有情人终成眷属，是前生造定事，莫错过姻缘！', $opts);
            }
        }
    }

    function marriageAction()
    {
        $sponsor_id = $this->params('sponsor_id');
        $pursuer_id = $this->params('pursuer_id');
        $other_user_id = $this->params('other_user_id');

        //如果有other_user_id代表该用户是我的cp列表也过来的
        if ($other_user_id) {
            $user_id = $this->currentUserId();
            //第一个参数id来生成key，查看第二个用户id的身份 1 为发起者 2位追求者
            list($sponsor_id, $pursuer_id) = \Couples::checkCpRelation($user_id, $other_user_id, true);

        }
        if (!$sponsor_id || !$pursuer_id) {
            return $this->response->redirect('app://back');
        }

        info('发起者', $sponsor_id, '追求者', $pursuer_id);
        $sponsor = \Users::findFirstById($sponsor_id);
        $pursuer = \Users::findFirstById($pursuer_id);

        $marriage_at = \Couples::getMarriageTime($sponsor_id, $pursuer_id);

        if (isBlank($sponsor) || isBlank($pursuer)) {
            return $this->response->redirect('app://back');
        }

        $couple_file_name = 'couple_' . md5(uniqid(mt_rand())) . '.jpg';
        $couple_new_file = APP_ROOT . 'temp/' . $couple_file_name;
        // StoreFile::download($sponsor->avatar, $couple_new_file);
        $sponsor->avatar_base64_url = \Couples::base64EncodeImage(\StoreFile::download($sponsor->avatar, $couple_new_file));
        $pursuer->avatar_base64_url = \Couples::base64EncodeImage(\StoreFile::download($pursuer->avatar, $couple_new_file));

        $is_show_clear_cp = false;
        if (isDevelopmentEnv()) {
            $is_show_clear_cp = true;
        }

        $this->view->is_show_clear_cp = $is_show_clear_cp;
        $this->view->sponsor = json_encode($sponsor->toCpJson(), JSON_UNESCAPED_UNICODE);
        $this->view->pursuer = json_encode($pursuer->toCpJson(), JSON_UNESCAPED_UNICODE);
        $this->view->marriage_at_text = date('Y-m-d', $marriage_at);
    }

    function myCouplesAction()
    {
        $user = $this->currentUser();
        if ($this->request->isAjax()) {
            $page = $this->params('page', 1);
            $per_page = 10;

            $relations_key = \Couples::generateSeraglioKey($user->id);
            //获取我的cp列表全部内容
            $users = \Couples::findByRelationsForCp($relations_key, $page, $per_page, $user->id);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $users->toJson('users', 'toCpJson'));

        }
        $this->view->title = '我的后宫';
        $this->view->user = json_encode($user->toCpJson());

    }

    function getPursuerUserAction()
    {
        $room_id = $this->params('room_id');

        $room = \Rooms::findFirstById($room_id);

        if (!$room) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $cache = \Users::getHotWriteCache();
        $key = \Couples::generateReadyCpInfoKey($room_id);
        $data = $cache->hgetall($key);

        $pursuer_id = fetch($data, 'pursuer_id');

        if (!$pursuer_id) {
            return $this->renderJSON(ERROR_CODE_SUCCESS);
        }

        $pursuer = \Users::findFirstById($pursuer_id);

        if (!$pursuer) {
            $pursuer = ['avatar_url' => '/m/images/ico_plus.png', 'uid' => '', 'nickname' => '虚位以待'];
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误', ['pursuer' => $pursuer]);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['pursuer' => $pursuer->toCpJson()]);
    }

    function kickOutAction()
    {
        $room_id = $this->params('room_id');
        $cache = \Users::getHotWriteCache();
        $key = \Couples::generateReadyCpInfoKey($room_id);
        $cache->del($key);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '清除成功');
    }

    //解除cp关系
    function relieveCoupleAction()
    {
        $current_user_id = $this->currentUserId();
        $first_user_id = $this->params('first_user_id');
        $second_user_id = $this->params('second_user_id');
        //第一个参数id来生成key，查看第二个用户id的身份 1 为发起者 2位追求者
        list($sponsor_id, $pursuer_id) = \Couples::checkCpRelation($first_user_id, $second_user_id, true);
        info('发起者', $sponsor_id, '追求者', $pursuer_id);

        if (!$sponsor_id || !$pursuer_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '你们已经不是cp关系了哦');
        }

        \Couples::clearCoupleInfo($sponsor_id, $pursuer_id);
        \Couples::sendRelieveCpSysTemMessage($current_user_id, $sponsor_id, $pursuer_id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '成功解除CP！');
    }
}