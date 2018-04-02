<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/12
 * Time: 下午3:20
 */

namespace m;

class GamesController extends BaseController
{
    function indexAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $hot_cache = \Rooms::getHotWriteCache();
        $room_key = "game_room_" . $room_id;
        $room_info_key = "game_room_" . $room_id . '_info';
        $hot_cache->zadd($room_key, time(), $this->currentUser()->id);
        $num = $hot_cache->zcard($room_key);
        $cache_room_host_id = $hot_cache->hget($room_info_key, 'room_host_id');

        // 解散房间
        if ($cache_room_host_id == $this->currentUser()->id) {
            $hot_cache->del($room_key);
            $hot_cache->del($room_info_key);
        }

        // 发起者必须是主播
        if ($num == 1 && ($this->currentUser()->user_role != USER_ROLE_NO && $this->currentUser()->user_role != USER_ROLE_AUDIENCE)) {
            $pay_type = 'free';
            $amount = 0;
            $room_host_id = $this->currentUser()->id;
            $hot_cache->hset($room_info_key, 'room_host_id', $room_host_id);
            $hot_cache->expire($room_info_key, 600);
            $hot_cache->expire($room_key, 600);
        } else {
            $info = $hot_cache->hgetall($room_info_key);
            info($info);
            $room_host_id = fetch($info, 'room_host_id');
            $pay_type = fetch($info, 'pay_type');
            $amount = fetch($info, 'amount');
        }

        info($this->currentUser()->id, 'host', $room_host_id, 'role', $this->currentUser()->user_role, $this->currentUser()->current_room_id, $room_key, 'num', $num, $pay_type, $amount);

        $this->view->current_user = $this->currentUser();
        $this->view->room_host_id = $room_host_id;
        $this->view->pay_type = $pay_type;
        $this->view->amount = $amount;
    }

    // 提交入场费
    function feeAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $room_info_key = "game_room_" . $room_id . '_info';
        $hot_cache = \Rooms::getHotWriteCache();
        $info = $hot_cache->hgetall($room_info_key);
        $room_host_id = fetch($info, 'room_host_id');
        $pay_type = fetch($info, 'pay_type');
        $amount = fetch($info, 'amount');

        $current_user = $this->currentUser();
        if ($room_host_id == $current_user->id) {
            // free diamond gold
            $pay_type = $this->params('pay_type', '');
            $amount = $this->params('amount', 0);
            if (!$pay_type || $pay_type == 'free' && $amount != 0 || $pay_type != 'free' && $amount == 0) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            $hot_cache->hset($room_info_key, 'pay_type', $pay_type);
            $hot_cache->hset($room_info_key, 'amount', $amount);
        }

        info($this->currentUser()->id, 'role', $this->currentUser()->user_role, $room_info_key, $pay_type, $amount);

        if ($pay_type == PAY_TYPE_DIAMOND && $current_user->diamond < $amount) {
            return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
        }

        if ($pay_type == PAY_TYPE_GOLD && $current_user->gold < $amount) {
            return $this->renderJSON(ERROR_CODE_FAIL, '金币不足');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function waitAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;

        $room_info_key = "game_room_" . $room_id . '_info';
        $hot_cache = \Rooms::getHotWriteCache();
        $room_host_id = $hot_cache->hget($room_info_key, 'room_host_id');
        $body = [];
        $body['user_id'] = $this->currentUser()->id;
        $body['source'] = $this->currentProductChannel()->code;
        $body['nickname'] = $this->currentUser()->nickname;
        $body['avatar_url'] = $this->currentUser()->avatar_url;
        $body['sex'] = $this->currentUser()->sex;
        $body['room_id'] = $room_id;
        $body['host'] = $this->currentUser()->id == $room_host_id;
        $body['nonce_str'] = randStr(20);
        $body['back_url'] = urlencode($this->getRoot() . 'm/game?sid=' . $this->currentUser()->sid);
        $body['notify_url'] = urlencode($this->getRoot() . 'm/game/notify?sid=' . $this->currentUser()->sid);

        $str = paramsToStr($body);

        $url = 'https://tyt.momoyuedu.cn/?' . $str;
        info($this->currentUser()->id, 'url', $url);

        $user = $this->currentUser();
        $this->view->url = $url;
        $this->view->current_user = $user;
        $this->view->room_host_id = $room_host_id;
    }

    function enterAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $hot_cache = \Rooms::getHotWriteCache();
        $room_key = "game_room_" . $room_id;
        $user_ids = $hot_cache->zrange($room_key, 0, -1);
        if (count($user_ids) < 1) {
            return $this->renderJSON(ERROR_CODE_FAIL, '比赛已解散');
        }

        $users = \Users::findByIds($user_ids);
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $room_info_key = "game_room_" . $room_id . '_info';
        $hot_cache = \Rooms::getHotWriteCache();
        $info = $hot_cache->hgetall($room_info_key);
        $room_host_id = fetch($info, 'room_host_id');
        $pay_type = fetch($info, 'pay_type');
        $amount = fetch($info, 'amount');
        $can_enter = fetch($info, 'can_enter');
        $can_enter_at = fetch($info, 'can_enter_at');
        if ($can_enter_at && time() - $can_enter_at > 30) {
            return $this->renderJSON(ERROR_CODE_FAIL, '比赛已开始,暂无法进入');
        }

        $data = $users->toJson('users', 'toSimpleJson');
        $data['can_enter'] = intval($can_enter);

        //扣除入场费
        if ($can_enter) {
            if ($pay_type == PAY_TYPE_DIAMOND) {
                $opts = ['remark' => '游戏支出钻石' . $amount, 'mobile' => $this->currentUser()->mobile];
                $result = \AccountHistories::changeBalance($this->currentUser()->id, ACCOUNT_TYPE_GAME_EXPENSES, $amount, $opts);
                if (!$result) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
                }
            }

            if ($pay_type == PAY_TYPE_GOLD) {
                $opts = ['remark' => '游戏支出金币' . $amount, 'mobile' => $this->currentUser()->mobile];
                $result = \GoldHistories::changeBalance($this->currentUser()->id, GOLD_TYPE_GAME_EXPENSES, $amount, $opts);
                if (!$result) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '金币不足');
                }
            }
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $data);
    }

    function startAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $room_info_key = "game_room_" . $room_id . '_info';
        $hot_cache = \Rooms::getHotWriteCache();
        $info = $hot_cache->hgetall($room_info_key);
        $room_host_id = fetch($info, 'room_host_id');
        $pay_type = fetch($info, 'pay_type');
        $amount = fetch($info, 'amount');
        //扣除入场费
        if ($pay_type == PAY_TYPE_DIAMOND) {
            $opts = ['remark' => '游戏支出钻石' . $amount, 'mobile' => $this->currentUser()->mobile];
            $result = \AccountHistories::changeBalance($this->currentUser()->id, ACCOUNT_TYPE_GAME_EXPENSES, $amount, $opts);
            if (!$result) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }
        }

        if ($pay_type == PAY_TYPE_GOLD) {
            $opts = ['remark' => '游戏支出金币' . $amount, 'mobile' => $this->currentUser()->mobile];
            $result = \GoldHistories::changeBalance($this->currentUser()->id, GOLD_TYPE_GAME_EXPENSES, $amount, $opts);
            if (!$result) {
                return $this->renderJSON(ERROR_CODE_FAIL, '金币不足');
            }
        }

        // 进入游戏
        $hot_cache->hset($room_info_key, 'can_enter', 1);
        $hot_cache->hset($room_info_key, 'can_enter_at', time());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function exitAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $hot_cache = \Rooms::getHotWriteCache();
        $room_key = "game_room_" . $room_id;
        $room_info_key = "game_room_" . $room_id . '_info';
        $room_host_id = $hot_cache->hget($room_info_key, 'room_host_id');
        $can_enter = $hot_cache->hget($room_info_key, 'can_enter');
        if ($can_enter) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已开始游戏');
        }

        if ($room_host_id == $this->currentUser()->id) {
            // 解散比赛
            $hot_cache->del($room_key);
            $hot_cache->del($room_info_key);
        } else {
            $hot_cache->zrem($room_key, $this->currentUser()->id);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function notifyAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $hot_cache = \Rooms::getHotWriteCache();
        $room_info_key = "game_room_" . $room_id . '_info';
        $room_host_id = $hot_cache->hget($room_info_key, 'room_host_id');

        if ($room_host_id != $this->currentUser()->id) {
            echo 'jsonpcallback({"error_code":-1,"error_reason":"error"})';
            return;
        }

        $rank1 = $this->params('rank1');
        $rank2 = $this->params('rank2');
        $rank3 = $this->params('rank3');

        info('rank', $rank1, $rank2, $rank3);

        echo 'jsonpcallback({"error_code":0,"error_reason":"ok"})';
    }

}