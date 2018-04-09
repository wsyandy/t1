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
        if ($this->request->isAJax()) {
            $page = $this->params('page');
            $per_page = $this->params('per_page', 8);

            $conds = ['conditions' => 'status = ' . STATUS_ON];
            $conds['order'] = 'id desc';

            $games = \Games::findPagination($conds, $page, $per_page);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $games->toJson('games','toSimpleJson'));
        }
    }

    function tytAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $hot_cache = \Rooms::getHotWriteCache();
        $room_key = "game_room_" . $room_id;
        $room_wait_key = "game_room_wait_" . $room_id;
        $room_info_key = "game_room_" . $room_id . '_info';
        $current_user_id = intval($this->currentUser()->id);
        $cache_room_host_id = $hot_cache->hget($room_info_key, 'room_host_id');

        // 解散房间
        if ($cache_room_host_id == $this->currentUser()->id) {
            $hot_cache->del($room_key);
            $hot_cache->del($room_wait_key);
            $hot_cache->del($room_info_key);
        }

        $hot_cache->zadd($room_key, time(), $current_user_id);
        $user_num = $hot_cache->zcard($room_key);

        info('cache', $room_key, $this->currentUser()->id, $user_num);

        // 发起者必须是主播
        if ($user_num == 1 && ($this->currentUser()->user_role != USER_ROLE_NO && $this->currentUser()->user_role != USER_ROLE_AUDIENCE)) {
            $pay_type = 'free';
            $amount = 0;
            $room_host_id = $this->currentUser()->id;
            $hot_cache->hset($room_info_key, 'room_host_id', $room_host_id);
            $hot_cache->expire($room_info_key, 600);
            $hot_cache->expire($room_key, 600);
            $hot_cache->expire($room_wait_key, 600);
        } else {
            $info = $hot_cache->hgetall($room_info_key);
            info($room_info_key, $info);
            $room_host_id = fetch($info, 'room_host_id');
            $pay_type = fetch($info, 'pay_type');
            $amount = fetch($info, 'amount');
            // 修复数据
            if (!$pay_type && $user_num) {
                $hot_cache->del($room_key);
                $hot_cache->del($room_wait_key);
                $hot_cache->del($room_info_key);
            }
        }

        $room_host_nickname = '';
        $room_host_user = \Users::findFirstById($room_host_id);
        if ($room_host_user) {
            $room_host_nickname = $room_host_user->nickname;
        }
        info($this->currentUser()->id, 'host', $room_host_id, 'role', $this->currentUser()->user_role, $this->currentUser()->current_room_id, $room_key, 'num', $user_num, $pay_type, $amount);

        $this->view->current_user = $this->currentUser();
        $this->view->room_host_id = $room_host_id;
        $this->view->room_host_nickname = $room_host_nickname;
        $this->view->pay_type = $pay_type;
        $this->view->amount = $amount;
        $this->view->room_id = $room_id;
    }

    // 提交入场费
    function feeAction()
    {
        info($this->params());
        $room_id = $this->params('room_id');
        $room_info_key = "game_room_" . $room_id . '_info';
        $hot_cache = \Rooms::getHotWriteCache();
        $info = $hot_cache->hgetall($room_info_key);
        $room_host_id = fetch($info, 'room_host_id');
        $pay_type = fetch($info, 'pay_type');
        $amount = fetch($info, 'amount');

        $can_enter_at = fetch($info, 'can_enter_at');
        if ($can_enter_at && time() - $can_enter_at > 30) {
            return $this->renderJSON(ERROR_CODE_FAIL, '比赛已开始,暂无法进入');
        }

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

        $room_wait_key = "game_room_wait_" . $room_id;
        $hot_cache->zadd($room_wait_key, time(), $this->currentUser()->id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function waitAction()
    {
        info($this->params());
        $room_id = $this->params('room_id');
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
        $body['nonce_str'] = randStr(20);
        $body['back_url'] = urlencode($this->getRoot() . 'm/games/back?sid=' . $this->currentUser()->sid . '&room_id=' . $room_id);
        $body['notify_url'] = urlencode($this->getRoot() . 'm/games/notify?sid=' . $this->currentUser()->sid . '&room_id=' . $room_id);

        $str = paramsToStr($body);

        if (isDevelopmentEnv()) {
            $url = 'http://tyt.momoyuedu.cn/?' . $str;
        } else {
            $url = 'https://tyt.momoyuedu.cn/?' . $str;
        }

        info($this->currentUser()->id, 'url', $url);

        $user = $this->currentUser();
        $this->view->url = $url;
        $this->view->current_user = $user;
        $this->view->room_host_id = $room_host_id;
        $this->view->room_id = $room_id;
    }

    function enterAction()
    {
        info($this->params());
        $room_id = $this->params('room_id');
        $hot_cache = \Rooms::getHotWriteCache();
        $room_wait_key = "game_room_wait_" . $room_id;
        $user_ids = $hot_cache->zrange($room_wait_key, 0, -1);
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
        if ($can_enter && $room_host_id != $this->currentUser()->id) {
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

            // 扣款成功
            $room_enter_key = "game_room_enter_" . $room_id;
            $hot_cache->zadd($room_enter_key, time(), $this->currentUser()->id);
            $hot_cache->expire($room_enter_key, 600);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $data);
    }

    function startAction()
    {
        info($this->params());
        $room_id = $this->params('room_id');
        $room_key = "game_room_" . $room_id;
        $room_wait_key = "game_room_wait_" . $room_id;
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

        // 扣款成功
        $room_enter_key = "game_room_enter_" . $room_id;
        $hot_cache->del($room_enter_key);

        $hot_cache->zadd($room_enter_key, time(), $this->currentUser()->id);
        $hot_cache->expire($room_enter_key, 200);

        // 进入游戏
        $hot_cache->hset($room_info_key, 'can_enter', 1);
        $hot_cache->hset($room_info_key, 'can_enter_at', time());

        // 游戏时长160秒
        $hot_cache->expire($room_info_key, 200);
        $hot_cache->expire($room_key, 200);
        $hot_cache->expire($room_wait_key, 200);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function exitAction()
    {
        info($this->params());
        $room_id = $this->params('room_id');
        $hot_cache = \Rooms::getHotWriteCache();
        $room_key = "game_room_" . $room_id;
        $room_wait_key = "game_room_wait_" . $room_id;
        $room_info_key = "game_room_" . $room_id . '_info';
        $room_host_id = $hot_cache->hget($room_info_key, 'room_host_id');
        $can_enter = $hot_cache->hget($room_info_key, 'can_enter');
        if ($can_enter) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已开始游戏');
        }

        if ($room_host_id == $this->currentUser()->id) {
            // 解散比赛
            $hot_cache->del($room_key);
            $hot_cache->del($room_wait_key);
            $hot_cache->del($room_info_key);
        } else {
            $hot_cache->zrem($room_key, $this->currentUser()->id);
            $hot_cache->zrem($room_wait_key, $this->currentUser()->id);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function backAction()
    {
        info($this->params());
        $room_id = $this->params('room_id');
        $hot_cache = \Rooms::getHotWriteCache();
        $room_settlement_key = 'game_room_settlement_' . $room_id;
        $info = $hot_cache->hgetall($room_settlement_key);
        $pay_type = fetch($info, 'pay_type');
        $amount = fetch($info, 'amount');
        $total_user_num = fetch($info, 'user_num');

        info($info);

        $user_ids = [];
        if (fetch($info, 'rank1')) {
            $user_ids[fetch($info, 'rank1')] = fetch($info, 'rank1_amount');
        }

        //{"pay_type":"diamond","amount":"1","rank1":"257","rank1_amount":"2","rank2":"6","rank2_amount":"1"}

        if (fetch($info, 'rank2')) {
            $user_ids[fetch($info, 'rank2')] = fetch($info, 'rank2_amount');
        }
        if (fetch($info, 'rank3')) {
            $user_ids[fetch($info, 'rank3')] = fetch($info, 'rank3_amount');
        }

        $user_datas = [];
        foreach ($user_ids as $user_id => $settlement_amount) {
            $user = \Users::findFirstById($user_id);
            $user_datas[] = ['id' => $user_id, 'nickname' => $user->nickname, 'avatar_url' => $user->avatar_url, 'settlement_amount' => $settlement_amount];
        }

        info('user_info', $user_datas);

        $this->view->current_user = $this->currentUser();
        $this->view->pay_type = $pay_type;
        $this->view->amount = $amount;
        $this->view->user_num = count($user_datas);
        $this->view->total_amount = $amount * $total_user_num;
        $this->view->back_url = 'app://home';
        $this->view->users = json_encode($user_datas, JSON_UNESCAPED_UNICODE);
    }

    function notifyAction()
    {
        info($this->params());
        $rank1 = $this->params('rank1');
        $rank2 = $this->params('rank2');
        $rank3 = $this->params('rank3');

        if ($rank1 != $this->currentUser()->id) {
            echo 'jsonpcallback({"error_code":-1,"error_reason":"error"})';
            return;
        }

        $rank1_user = \Users::findFirstById($rank1);
        $rank2_user = \Users::findFirstById($rank2);
        $rank3_user = \Users::findFirstById($rank3);

        $room_id = $this->params('room_id');
        info($this->currentUser()->id, 'room', $room_id, 'rank', $rank1, $rank2, $rank3);

        $hot_cache = \Rooms::getHotWriteCache();
        $room_key = "game_room_" . $room_id;
        $room_wait_key = "game_room_wait_" . $room_id;
        $room_info_key = "game_room_" . $room_id . '_info';
        $room_enter_key = "game_room_enter_" . $room_id;
        $room_settlement_key = 'game_room_settlement_' . $room_id;
        $hot_cache->del($room_settlement_key);

        $user_num = $hot_cache->zcard($room_enter_key);
        $info = $hot_cache->hgetall($room_info_key);
        $pay_type = fetch($info, 'pay_type');
        $amount = fetch($info, 'amount');

        info($room_info_key, $info);

        $hot_cache->hset($room_settlement_key, 'pay_type', $pay_type);
        $hot_cache->hset($room_settlement_key, 'amount', $amount);
        $hot_cache->hset($room_settlement_key, 'user_num', $user_num);

        if ($pay_type != 'free') {
            if ($user_num == 1) {
                $rank1_amount = $user_num * $amount;
                if ($pay_type == PAY_TYPE_DIAMOND) {
                    $opts = ['remark' => '游戏收入钻石' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                    $result = \AccountHistories::changeBalance($rank1_user->id, ACCOUNT_TYPE_GAME_INCOME, $rank1_amount, $opts);
                }
                if ($pay_type == PAY_TYPE_GOLD) {
                    $opts = ['remark' => '游戏收入金币' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                    $result = \GoldHistories::changeBalance($rank1_user->id, GOLD_TYPE_GAME_INCOME, $rank1_amount, $opts);
                }
                $hot_cache->hset($room_settlement_key, 'rank1', $rank1);
                $hot_cache->hset($room_settlement_key, 'rank1_amount', $rank1_amount);

            } elseif ($user_num == 2) {
                $rank1_amount = $user_num * $amount;
                $rank2_amount = 0;
                if ($pay_type == PAY_TYPE_DIAMOND) {
                    $opts = ['remark' => '游戏收入钻石' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                    $result = \AccountHistories::changeBalance($rank1_user->id, ACCOUNT_TYPE_GAME_INCOME, $rank1_amount, $opts);
                }
                if ($pay_type == PAY_TYPE_GOLD) {
                    $opts = ['remark' => '游戏收入金币' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                    $result = \GoldHistories::changeBalance($rank1_user->id, GOLD_TYPE_GAME_INCOME, $rank1_amount, $opts);
                }
                $hot_cache->hset($room_settlement_key, 'rank1', $rank1);
                $hot_cache->hset($room_settlement_key, 'rank1_amount', $rank1_amount);

                $hot_cache->hset($room_settlement_key, 'rank2', $rank2);
                $hot_cache->hset($room_settlement_key, 'rank2_amount', $rank2_amount);

            } elseif ($user_num == 3) {

                $total_amount = $user_num * $amount;
                $rank1_amount = round($total_amount * 0.8);
                $rank2_amount = round($total_amount * 0.2);
                $rank3_amount = 0;
                // rank1
                if ($pay_type == PAY_TYPE_DIAMOND) {
                    $opts = ['remark' => '游戏收入钻石' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                    $result = \AccountHistories::changeBalance($rank1_user->id, ACCOUNT_TYPE_GAME_INCOME, $rank1_amount, $opts);
                }
                if ($pay_type == PAY_TYPE_GOLD) {
                    $opts = ['remark' => '游戏收入金币' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                    $result = \GoldHistories::changeBalance($rank1_user->id, GOLD_TYPE_GAME_INCOME, $rank1_amount, $opts);
                }

                // rank2
                if ($pay_type == PAY_TYPE_DIAMOND) {
                    $opts = ['remark' => '游戏收入钻石' . $rank2_amount, 'mobile' => $rank2_user->mobile];
                    $result = \AccountHistories::changeBalance($rank2_user->id, ACCOUNT_TYPE_GAME_INCOME, $rank2_amount, $opts);
                }
                if ($pay_type == PAY_TYPE_GOLD) {
                    $opts = ['remark' => '游戏收入金币' . $rank2_amount, 'mobile' => $rank2_user->mobile];
                    $result = \GoldHistories::changeBalance($rank2_user->id, GOLD_TYPE_GAME_INCOME, $rank2_amount, $opts);
                }

                $hot_cache->hset($room_settlement_key, 'rank1', $rank1);
                $hot_cache->hset($room_settlement_key, 'rank1_amount', $rank1_amount);

                $hot_cache->hset($room_settlement_key, 'rank2', $rank2);
                $hot_cache->hset($room_settlement_key, 'rank2_amount', $rank2_amount);

                $hot_cache->hset($room_settlement_key, 'rank3', $rank3);
                $hot_cache->hset($room_settlement_key, 'rank3_amount', $rank3_amount);
            } else {

                $total_amount = ($user_num - 3) * $amount;
                $rank1_amount = $amount + round($total_amount * 0.7);
                $rank2_amount = $amount + round($total_amount * 0.2);
                $rank3_amount = $amount + round($total_amount * 0.1);

                // rank1
                if ($pay_type == PAY_TYPE_DIAMOND) {
                    $opts = ['remark' => '游戏收入钻石' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                    $result = \AccountHistories::changeBalance($rank1_user->id, ACCOUNT_TYPE_GAME_INCOME, $rank1_amount, $opts);
                }
                if ($pay_type == PAY_TYPE_GOLD) {
                    $opts = ['remark' => '游戏收入金币' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                    $result = \GoldHistories::changeBalance($rank1_user->id, GOLD_TYPE_GAME_INCOME, $rank1_amount, $opts);
                }

                // rank2
                if ($pay_type == PAY_TYPE_DIAMOND) {
                    $opts = ['remark' => '游戏收入钻石' . $rank2_amount, 'mobile' => $rank2_user->mobile];
                    $result = \AccountHistories::changeBalance($rank2_user->id, ACCOUNT_TYPE_GAME_INCOME, $rank2_amount, $opts);
                }
                if ($pay_type == PAY_TYPE_GOLD) {
                    $opts = ['remark' => '游戏收入金币' . $rank2_amount, 'mobile' => $rank2_user->mobile];
                    $result = \GoldHistories::changeBalance($rank2_user->id, GOLD_TYPE_GAME_INCOME, $rank2_amount, $opts);
                }

                // rank3
                if ($pay_type == PAY_TYPE_DIAMOND) {
                    $opts = ['remark' => '游戏收入钻石' . $rank3_amount, 'mobile' => $rank3_user->mobile];
                    $result = \AccountHistories::changeBalance($rank3_user->id, ACCOUNT_TYPE_GAME_INCOME, $rank3_amount, $opts);
                }
                if ($pay_type == PAY_TYPE_GOLD) {
                    $opts = ['remark' => '游戏收入金币' . $rank3_amount, 'mobile' => $rank3_user->mobile];
                    $result = \GoldHistories::changeBalance($rank3_user->id, GOLD_TYPE_GAME_INCOME, $rank3_amount, $opts);
                }

                $hot_cache->hset($room_settlement_key, 'rank1', $rank1);
                $hot_cache->hset($room_settlement_key, 'rank1_amount', $rank1_amount);

                $hot_cache->hset($room_settlement_key, 'rank2', $rank2);
                $hot_cache->hset($room_settlement_key, 'rank2_amount', $rank2_amount);

                $hot_cache->hset($room_settlement_key, 'rank3', $rank3);
                $hot_cache->hset($room_settlement_key, 'rank3_amount', $rank3_amount);
            }

            $hot_cache->expire($room_settlement_key, 200);
        }

        $info = $hot_cache->hgetall($room_settlement_key);
        info('info', $room_settlement_key, $info);
        // 解散比赛
        $hot_cache->del($room_key);
        $hot_cache->del($room_wait_key);
        $hot_cache->del($room_enter_key);
        $hot_cache->del($room_info_key);

        echo 'jsonpcallback({"error_code":0,"error_reason":"ok"})';
    }

}