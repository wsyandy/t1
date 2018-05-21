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
            $room_id = $this->params('room_id');
            $page = $this->params('page');
            $per_page = $this->params('per_page', 8);

            $conds = ['conditions' => 'status=:status:', 'bind' => ['status' => STATUS_ON]];
            $conds['order'] = 'rank desc';

            $games = \Games::findPagination($conds, $page, $per_page);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $games->toJson('games', 'toSimpleJson'));
        }
    }

    function tytAction()
    {

        // 必须在房间才可玩游戏
        $room_id = $this->currentUser()->current_room_id;
        if (!$room_id || $room_id != $this->currentUser()->current_room_id) {
            info('room_id错误：', $room_id);
            $this->response->redirect('app://back');
            return;
        }

        $game = \Games::findFirstById($this->params('game_id'));
        if (!$game) {
            info('无当前指定游戏：', $this->params('game_id'));
            $this->response->redirect('app://back');
            return;
        }

        $can_create_game = false;
        if ($this->currentUser()->user_role != USER_ROLE_NO && $this->currentUser()->user_role != USER_ROLE_AUDIENCE) {
            $can_create_game = true;
        }

        $pay_type = '';
        $amount = 0;

        $game_history = \GameHistories::findFirst(['conditions' => 'room_id=:room_id: and status!=:status: and game_id=:game_id:',
            'bind' => ['room_id' => $room_id, 'status' => GAME_STATUS_END, 'game_id' => $game->id], 'order' => 'id desc']);

        if ($game_history) {

            $game_host_user_id = $game_history->user_id;
            $game_history_id = $game_history->id;

            $hot_cache = \GameHistories::getHotWriteCache();
            $room_enter_key = "game_room_enter_" . $game_history_id;
            $total_user_num = $hot_cache->zcard($room_enter_key);

            // 房主再次发起游戏，不是等待状态
            if ($game_history && $game_history->user_id == $this->currentUser()->id
                && $total_user_num <= 1 && $game_history->status != GAME_STATUS_WAIT || time() - $game_history->created_at > 1800
            ) {
                $game_history_id = 0;
                $game_history->status = GAME_STATUS_END;
                $game_history->save();
            }

            if ($game_history->status != GAME_STATUS_END) {

                $can_create_game = false;
                $start_data = json_decode($game_history->start_data, true);
                $pay_type = fetch($start_data, 'pay_type');
                $amount = fetch($start_data, 'amount');

                $can_enter = $game_history->canEnter();
                if ($game_host_user_id == $this->currentUser()->id && $can_enter) {
                    $this->response->redirect("/m/games/wait?game_history_id={$game_history->id}&sid={$this->currentUser()->sid}");
                    return;
                }
            }

        } else {
            $game_history_id = 0;
            $game_host_user_id = $this->currentUser()->id;
        }

        $game_host_user = \Users::findFirstById($game_host_user_id);

        $this->view->current_user = $this->currentUser();
        $this->view->game_host_user = $game_host_user;
        $this->view->pay_type = $pay_type;
        $this->view->amount = $amount;
        $this->view->can_create_game = $can_create_game;
        $this->view->game_history_id = $game_history_id;
        $this->view->game = $game;

    }

    // 提交入场费
    function feeAction()
    {
        $current_user = $this->currentUser();
        $room_id = $current_user->current_room_id;
        $game_history_id = $this->params('game_history_id');
        $game_history = \GameHistories::findFirstById($game_history_id);
        if ($game_history) {

            if (!$game_history->canEnter()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '本次比赛已开始,您暂时无法进入');
            }

            $start_data = json_decode($game_history->start_data, true);
            $amount = fetch($start_data, 'amount');
            $pay_type = fetch($start_data, 'pay_type');
        } else {

            $game = \Games::findFirstById($this->params('game_id'));
            $game_history = \GameHistories::findFirst(['conditions' => 'room_id=:room_id: and status!=:status: and game_id=:game_id:',
                'bind' => ['room_id' => $room_id, 'status' => GAME_STATUS_END, 'game_id' => $game->id], 'order' => 'id desc']);
            if ($game_history) {
                return $this->renderJSON(ERROR_CODE_FAIL, $game_history->user->nickname . '已发起游戏，请刷新');
            }

            $pay_type = $this->params('pay_type', '');
            $amount = $this->params('amount', 0);
            if (!$pay_type || $pay_type == 'free' && $amount != 0 || $pay_type != 'free' && $amount == 0) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            $start_data = ['pay_type' => $pay_type, 'amount' => $amount];
            $game_history = new \GameHistories();
            $game_history->game_id = $game->id;
            $game_history->user_id = $current_user->id;
            $game_history->room_id = $room_id;
            $game_history->status = GAME_STATUS_WAIT;
            $game_history->start_data = json_encode($start_data, JSON_UNESCAPED_UNICODE);
        }

        if ($pay_type == PAY_TYPE_DIAMOND && $current_user->diamond < $amount) {
            return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
        }

        if ($pay_type == PAY_TYPE_GOLD && $current_user->gold < $amount) {
            return $this->renderJSON(ERROR_CODE_FAIL, '金币不足');
        }

        if (!$game_history_id) {

            $game_history->create();

            \GameHistories::delay(900)->asyncCloseGame($game_history->id);

            $root = $this->getRoot();
            $image_url = $root . 'images/go_game.png';
            $body = ['action' => 'game_notice', 'type' => 'start', 'content' => $current_user->nickname . "发起了跳一跳游戏",
                'image_url' => $image_url, 'client_url' => "url://m/games/tyt?game_id=" . $game_history->game_id];

            $intranet_ip = $current_user->getIntranetIp();
            $receiver_fd = $current_user->getUserFd();

            \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);
        }

        // 用户队列
        $hot_cache = \GameHistories::getHotWriteCache();
        $room_wait_key = "game_room_wait_" . $game_history->id;
        $hot_cache->zadd($room_wait_key, time(), $this->currentUser()->id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['game_history_id' => $game_history->id]);
    }

    function waitAction()
    {

        $game_history_id = $this->params('game_history_id');
        $game_history = \GameHistories::findFirstById($game_history_id);

        $hot_cache = \GameHistories::getHotWriteCache();
        $room_wait_key = "game_room_wait_" . $game_history->id;
        if (!$hot_cache->zscore($room_wait_key, $this->currentUser()->id)) {
            info('已退出', $this->currentUser()->id, $this->params());
            $this->response->redirect("/m/games/tyt?game_id={$game_history->game_id}&sid={$this->currentUser()->sid}");
            return;
        }

        $body = [];
        $body['user_id'] = $this->currentUser()->id;
        $body['source'] = $this->currentProductChannel()->code;
        $body['nickname'] = $this->currentUser()->nickname;
        $body['avatar_url'] = $this->currentUser()->avatar_url;
        $body['sex'] = $this->currentUser()->sex;
        $body['room_id'] = $game_history_id;
        $body['nonce_str'] = randStr(20);
        $body['back_url'] = urlencode($this->getRoot() . 'm/games/back?sid=' . $this->currentUser()->sid . '&game_history_id=' . $game_history_id);
        $body['notify_url'] = urlencode($this->getRoot() . 'm/games/notify?sid=' . $this->currentUser()->sid . '&game_history_id=' . $game_history_id);

        $str = paramsToStr($body);

        if (isDevelopmentEnv()) {
            $url = 'http://tyt.momoyuedu.cn/?' . $str;
        } else {
            $url = 'http://tyt.momoyuedu.cn/?' . $str;
        }

        info('url', $this->currentUser()->id, 'url', $url);

        $this->view->url = $url;
        $this->view->current_user = $this->currentUser();
        $this->view->game_host_user_id = $game_history->user_id;
        $this->view->game_history_id = $game_history_id;
    }

    function startAction()
    {

        $game_history_id = $this->params('game_history_id');
        $game_history = \GameHistories::findFirstById($game_history_id);
        $start_data = json_decode($game_history->start_data, true);
        $amount = fetch($start_data, 'amount');
        $pay_type = fetch($start_data, 'pay_type');

        //扣除入场费
        if ($pay_type == PAY_TYPE_DIAMOND) {
            $opts = ['remark' => '游戏支出钻石' . $amount, 'mobile' => $this->currentUser()->mobile];
            $result = \AccountHistories::changeBalance($this->currentUser(), ACCOUNT_TYPE_GAME_EXPENSES, $amount, $opts);
            if (!$result) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }
        }

        if ($pay_type == PAY_TYPE_GOLD) {
            $opts = ['remark' => '游戏支出金币' . $amount, 'mobile' => $this->currentUser()->mobile];
            $result = \GoldHistories::changeBalance($this->currentUser(), GOLD_TYPE_GAME_EXPENSES, $amount, $opts);
            if (!$result) {
                return $this->renderJSON(ERROR_CODE_FAIL, '金币不足');
            }
        }

        // 扣款成功
        $hot_cache = \GameHistories::getHotWriteCache();
        $room_enter_key = "game_room_enter_" . $game_history->id;
        $hot_cache->zadd($room_enter_key, time(), $this->currentUser()->id);
        $hot_cache->expire($room_enter_key, 180);
        $room_user_quit_key = "game_room_user_quit_" . $game_history->id;
        $hot_cache->zrem($room_user_quit_key, $this->currentUser()->id);

        $game_history->status = GAME_STATUS_PLAYING;
        $game_history->enter_at = time();
        $game_history->save();

        \GameHistories::delay(200)->asyncCloseGame($game_history->id);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function enterAction()
    {

        $game_history_id = $this->params('game_history_id');
        $game_history = \GameHistories::findFirstById($game_history_id);

        $hot_cache = \GameHistories::getHotWriteCache();
        $room_wait_key = "game_room_wait_" . $game_history->id;
        if (!$hot_cache->zscore($room_wait_key, $this->currentUser()->id)) {
            info('已退出', $this->currentUser()->id, $this->params());
            return $this->renderJSON(ERROR_CODE_FAIL, '您已退出游戏');
        }

        if ($game_history->status == GAME_STATUS_END) {
            return $this->renderJSON(ERROR_CODE_FAIL, '发起者解散游戏',
                ['url' => 'm/games/back?sid=' . $this->currentUser()->sid . '&game_history_id=' . $game_history_id]);
        }

        $user_ids = $hot_cache->zrange($room_wait_key, 0, -1);
        $users = \Users::findByIds($user_ids);

        $data = $users->toJson('users', 'toSimpleJson');

        if ($game_history->status == GAME_STATUS_WAIT) {
            $data['can_enter'] = 0;
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $data);
        }

        //扣除入场费
        if ($game_history->user_id != $this->currentUser()->id) {

            $start_data = json_decode($game_history->start_data, true);
            $amount = fetch($start_data, 'amount');
            $pay_type = fetch($start_data, 'pay_type');

            if ($pay_type == PAY_TYPE_DIAMOND) {
                $opts = ['remark' => '游戏支出钻石' . $amount, 'mobile' => $this->currentUser()->mobile];
                $result = \AccountHistories::changeBalance($this->currentUser(), ACCOUNT_TYPE_GAME_EXPENSES, $amount, $opts);
                if (!$result) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
                }
            }

            if ($pay_type == PAY_TYPE_GOLD) {
                $opts = ['remark' => '游戏支出金币' . $amount, 'mobile' => $this->currentUser()->mobile];
                $result = \GoldHistories::changeBalance($this->currentUser(), GOLD_TYPE_GAME_EXPENSES, $amount, $opts);
                if (!$result) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '金币不足');
                }
            }

            // 扣款成功
            $data['can_enter'] = 1;
            $room_enter_key = "game_room_enter_" . $game_history->id;
            $hot_cache->zadd($room_enter_key, time(), $this->currentUser()->id);
            $hot_cache->expire($room_enter_key, 180);
            $room_user_quit_key = "game_room_user_quit_" . $game_history->id;
            $hot_cache->zrem($room_user_quit_key, $this->currentUser()->id);

        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $data);
    }

    function exitAction()
    {

        $game_history_id = $this->params('game_history_id');
        $game_history = \GameHistories::findFirstById($game_history_id);

        if ($game_history->status != GAME_STATUS_WAIT) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        $hot_cache = \GameHistories::getHotWriteCache();
        $room_wait_key = "game_room_wait_" . $game_history->id;

        if ($game_history->user_id == $this->currentUser()->id) {
            // 解散比赛
            $game_history->status = GAME_STATUS_END;
            $game_history->save();
            info('房主解散游戏', $this->params());
        } else {
            $hot_cache->zrem($room_wait_key, $this->currentUser()->id);
            info('用户退出游戏', $this->params());
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function backAction()
    {

        $current_user = $this->currentUser();

        $game_history_id = $this->params('game_history_id');
        $game_history = \GameHistories::findFirstById($game_history_id);

        // 中途退出
        if ($game_history->status != GAME_STATUS_END) {

            $hot_cache = \GameHistories::getHotWriteCache();
            $room_enter_key = "game_room_enter_" . $game_history->id;
            $total_user_num = $hot_cache->zcard($room_enter_key);
            if ($total_user_num == 1) {
                $game_history->status = GAME_STATUS_END;
                $game_history->save();
            }

            // 主动退出
            if ($this->params('quit')) {
                $room_user_quit_key = "game_room_user_quit_" . $game_history->id;
                info('主动退出', $this->params(), $game_history->id);
                $hot_cache->zadd($room_user_quit_key, time(), $current_user->id);
            }
        }

        $end_data = json_decode($game_history->end_data, true);
        $rank_data = fetch($end_data, 'rank_data', []);
        $total_user_num = fetch($end_data, 'enter_user_num');

        $user_datas = [];
        foreach ($rank_data as $user_id => $settlement_amount) {
            if ($user_id) {
                $user = \Users::findFirstById($user_id);
                $user_datas[] = ['id' => $user_id, 'nickname' => $user->nickname, 'avatar_url' => $user->avatar_url, 'settlement_amount' => $settlement_amount];
            }
        }

        $start_data = json_decode($game_history->start_data, true);
        $amount = fetch($start_data, 'amount');
        $pay_type = fetch($start_data, 'pay_type');

        info('入场费为=>', $amount, '游戏人数：' . $total_user_num);
        $this->view->current_user = $this->currentUser();
        $this->view->pay_type = $pay_type;
        $this->view->amount = $amount;
        $this->view->user_num = count($user_datas);
        $this->view->total_amount = $amount * $total_user_num;
        $this->view->back_url = 'app://back';
        $this->view->users = json_encode($user_datas, JSON_UNESCAPED_UNICODE);
    }

    function notifyAction()
    {

        $rank1 = $this->params('rank1');
        $rank2 = $this->params('rank2');
        $rank3 = $this->params('rank3');

        if ($rank1 != $this->currentUser()->id) {
            echo 'jsonpcallback({"error_code":-1,"error_reason":"error"})';
            return;
        }

        $game_history_id = $this->params('game_history_id');
        $game_history = \GameHistories::findFirstById($game_history_id);
        if (!$game_history || $game_history->status == GAME_STATUS_END) {
            info('已结算', $this->currentUser()->id, $game_history);
            echo 'jsonpcallback({"error_code":0,"error_reason":"ok"})';
            return;
        }

        $lock_key = 'game_histories_lock_' . $game_history_id;
        $hot_cache = \GameHistories::getHotWriteCache();
        if (!$hot_cache->set($lock_key, 1, ['NX', 'EX' => 2])) {
            echo 'jsonpcallback({"error_code":0,"error_reason":"ok"})';
            return;
        }

        $room_user_quit_key = "game_room_user_quit_" . $game_history->id;

        $room_enter_key = "game_room_enter_" . $game_history->id;
        $total_user_num = $hot_cache->zcard($room_enter_key);
        info($this->currentUserId(), '游戏人数', $total_user_num);

        $start_data = json_decode($game_history->start_data, true);
        $amount = fetch($start_data, 'amount');
        $pay_type = fetch($start_data, 'pay_type');

        $game_history->status = GAME_STATUS_END;
        $game_history->save();

        $rank1_user = \Users::findFirstById($rank1);
        $rank2_user = \Users::findFirstById($rank2);
        $rank3_user = \Users::findFirstById($rank3);
        $rank1_amount = 0;
        $rank2_amount = 0;
        $rank3_amount = 0;

        $end_data = ['enter_user_num' => $total_user_num];
        $rank_data = [];

        $total_amount = $total_user_num * $amount * 0.9;
        if ($total_user_num > 3) {
            $total_amount = ($total_user_num - 3) * $amount * 0.9;
        }

        if ($total_user_num == 1) {
            $rank1_amount = ceil($total_amount);
            if ($hot_cache->zscore($room_user_quit_key, $rank1)) {
                $rank1_amount = '退出';
            }

        } elseif ($total_user_num == 2) {
            $rank1_amount = ceil($total_amount);
            $rank2_amount = 0;
            if ($hot_cache->zscore($room_user_quit_key, $rank1)) {
                $rank1_amount = '退出';
            }

        } elseif ($total_user_num == 3) {

            $rank1_amount = round($total_amount * 0.8);
            $rank2_amount = round($total_amount * 0.2);
            $rank3_amount = 0;

            if ($hot_cache->zscore($room_user_quit_key, $rank1)) {
                $rank1_amount = '退出';
            }

            if ($hot_cache->zscore($room_user_quit_key, $rank2)) {
                $rank2_amount = '退出';
            }

        } else {

            $rank1_amount = $amount + round($total_amount * 0.7);
            $rank2_amount = $amount + round($total_amount * 0.2);
            $rank3_amount = $amount + round($total_amount * 0.1);

            if ($hot_cache->zscore($room_user_quit_key, $rank1)) {
                $rank1_amount = '退出';
            }

            if ($hot_cache->zscore($room_user_quit_key, $rank2)) {
                $rank2_amount = '退出';
            }

            if ($hot_cache->zscore($room_user_quit_key, $rank3)) {
                $rank3_amount = '退出';
            }
        }

        if ($rank1_user) {
            $rank_data[$rank1] = $rank1_amount;
        }
        if ($rank2_user) {
            $rank_data[$rank2] = $rank2_amount;
        }
        if ($rank3_user) {
            $rank_data[$rank3] = $rank3_amount;
        }

        $end_data['rank_data'] = $rank_data;
        $game_history->end_data = json_encode($end_data, JSON_UNESCAPED_UNICODE);
        $game_history->save();

        info($this->currentUser()->id, $game_history->id, $end_data);

        // rank1
        if ($rank1_user && intval($rank1_amount)) {
            if ($pay_type == PAY_TYPE_DIAMOND) {
                $opts = ['remark' => '游戏收入钻石' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                $result = \AccountHistories::changeBalance($rank1_user, ACCOUNT_TYPE_GAME_INCOME, $rank1_amount, $opts);
            }
            if ($pay_type == PAY_TYPE_GOLD) {
                $opts = ['remark' => '游戏收入金币' . $rank1_amount, 'mobile' => $rank1_user->mobile];
                $result = \GoldHistories::changeBalance($rank1_user, GOLD_TYPE_GAME_INCOME, $rank1_amount, $opts);
            }
        }

        // rank2
        if ($rank2_user && intval($rank2_amount)) {

            if ($pay_type == PAY_TYPE_DIAMOND) {
                $opts = ['remark' => '游戏收入钻石' . $rank2_amount, 'mobile' => $rank2_user->mobile];
                $result = \AccountHistories::changeBalance($rank2_user, ACCOUNT_TYPE_GAME_INCOME, $rank2_amount, $opts);
            }
            if ($pay_type == PAY_TYPE_GOLD) {
                $opts = ['remark' => '游戏收入金币' . $rank2_amount, 'mobile' => $rank2_user->mobile];
                $result = \GoldHistories::changeBalance($rank2_user, GOLD_TYPE_GAME_INCOME, $rank2_amount, $opts);
            }
        }

        // rank3
        if ($rank3_user && intval($rank3_amount)) {

            if ($pay_type == PAY_TYPE_DIAMOND) {
                $opts = ['remark' => '游戏收入钻石' . $rank3_amount, 'mobile' => $rank3_user->mobile];
                $result = \AccountHistories::changeBalance($rank3_user, ACCOUNT_TYPE_GAME_INCOME, $rank3_amount, $opts);
            }
            if ($pay_type == PAY_TYPE_GOLD) {
                $opts = ['remark' => '游戏收入金币' . $rank3_amount, 'mobile' => $rank3_user->mobile];
                $result = \GoldHistories::changeBalance($rank3_user, GOLD_TYPE_GAME_INCOME, $rank3_amount, $opts);
            }
        }

        //在游戏结束回调通知的时候，发送结束通知
        $current_user = $this->currentUser();
        $body = ['action' => 'game_notice', 'type' => 'over', 'content' => "游戏结束",];

        $intranet_ip = $current_user->getIntranetIp();
        $receiver_fd = $current_user->getUserFd();

        \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);

        echo 'jsonpcallback({"error_code":0,"error_reason":"ok"})';
    }

    function getGameUserInfoAction()
    {
        $current_user = $this->currentUser();
        $room = $current_user->room;
        $is_host = $current_user->isRoomHost($room);
        $data = [
            'username' => $current_user->nickname,
            'room_id' => $current_user->room_id,
            'user_id' => $current_user->id,
            'avater_url' => $current_user->avatar_small_url,
            'site' => $current_user->current_room_seat_id == 0 ? 1 : $current_user->current_room_seat_id,
            'owner' => $is_host==true ? 0 : 1,
        ];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['data'=>$data]);

    }

}