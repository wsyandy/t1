<?php

namespace m;

class CouplesController extends BaseController
{
    function indexAction()
    {
        $user = $this->currentUser();
        $room_id = $this->params('room_id');
        $is_show_my_cp = false;
        if ($user->room_id == $room_id) {
            $is_show_my_cp = true;
        }
        $pursuer = ['avatar_url' => '/m/images/ico_plus.png', 'uid' => '', 'nickname' => '虚位以待'];
        $cache = \Users::getHotWriteCache();
        $key = \Couples::generateReadyCpInfoKey($room_id);
        $data = $cache->hgetall($key);
        $room_host_user = $user->toCpJson();
        info('比较数据', $data);
        $sponsor_id = fetch($data, 'sponsor_id');
        $pursuer_id = fetch($data, 'pursuer_id');
        //如果当前房间没有初始化数据，说明为房主开启cp，初始化cp数据
        if (!$data && $room_id == $user->room_id) {
            \Couples::createReadyCpInfo($user);

            $root = $this->getRoot();
            $image_url = $root . 'images/go_cp.png';
            $body = ['action' => 'game', 'type' => 'start', 'content' => 'cp开始',
                'image_url' => $image_url, 'client_url' => "url://m/couples?room_id=" . $room_id];

            \Couples::sendCpFinishMessage($user, $body);
        } else if ($user->id != $sponsor_id && !$pursuer_id) {
            //当前用户不是发起者，并且追求者还没有入驻过
            $room_host_id = $sponsor_id;
            $room_host_user = \Users::findFirstById($room_host_id)->toCpJson();
            $pursuer = $user->toCpJson();

            //更新数据
            \Couples::updateReadyCpInfo($user, $room_id);
        } else {
            info('cp数据', $data);
            //数据已经完善的情况下，不考虑用户不是房主却进到这个页面的情况
            $room_host_id = $sponsor_id;
            $room_host_user = \Users::findFirstById($room_host_id)->toCpJson();
            if ($pursuer_id) {
                $pursuer = \Users::findFirstById($pursuer_id)->toCpJson();
            }
        }

        $this->view->is_show_my_cp = $is_show_my_cp;
        $this->view->room_host_user = json_encode($room_host_user, JSON_UNESCAPED_UNICODE);
        $this->view->pursuer = json_encode($pursuer, JSON_UNESCAPED_UNICODE);
        $this->view->current_user_id = $user->id;
        $this->view->room_id = $room_id;

    }

    function createAction()
    {
        $user = $this->currentUser();
        $id = $this->params('id');
        $room_id = $this->params('room_id');
        $cache = \Users::getHotWriteCache();
        $key = \Couples::generateReadyCpInfoKey($room_id);
        $status = $cache->hget($key, 'status');
        $data = $cache->hgetall($key);
        $sponsor_id = fetch($data, 'sponsor_id');
        $pursuer_id = fetch($data, 'pursuer_id');
        //房主闲的无聊，没事儿对面坑没有就点同意
        if (!$pursuer_id) {
            if ($sponsor_id == $id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '您还没有求婚者哦，等等会有对的人出现');
            }
        }

        if ($id == $pursuer_id || $id == $sponsor_id) {
            if ($status < 1) {
                $cache->hincrby($key, 'status', 1);
                return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '快通知对方吧');
            } else {
                $cache->hincrby($key, 'status', 1);
                //成功组成cp，去相互保存对方的id
                \Couples::cpSeraglioInfo($user, $room_id);
                $opts = [
                    'sponsor_id' => $sponsor_id,
                    'pursuer_id' => $pursuer_id
                ];

                return $this->renderJSON(ERROR_CODE_SUCCESS, '恭喜有情人终成眷属，是前生造定事，莫错过姻缘！', $opts);
            }
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '躁动了吗？心动不如行动，快去跟人家表白吧');
        }
    }

    function marriageAction()
    {
        $sponsor_id = $this->params('sponsor_id');
        $pursuer_id = $this->params('pursuer_id');
        $other_user_id = $this->params('other_user_id');
        if ($other_user_id) {
            $user_db = \Users::getUserDb();
            $user_id = $this->currentUserId();
            $relations_key = \Couples::generateSeraglioKey($user_id);
            $status = $user_db->zscore($relations_key, $other_user_id);
            info('状态', $status);
            //状态    如果为1，说明该用户为当初的发起者，当前用户为追求者
            switch ($status) {
                case 1:
                    $sponsor_id = $other_user_id;
                    $pursuer_id = $user_id;
                    break;
                case 2:
                    $sponsor_id = $user_id;
                    $pursuer_id = $other_user_id;
                    break;
            }
        }
        info($sponsor_id, $pursuer_id);
        $sponsor = \Users::findFirstById($sponsor_id);
        $pursuer = \Users::findFirstById($pursuer_id);
        $marriage_at = \Couples::getMarriageTime($sponsor_id, $pursuer_id);

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
}