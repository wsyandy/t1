<?php

namespace m;

class JumpsController extends BaseController
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
    }

    function notifyGameStatusAction()
    {
        $current_user = $this->currentUser();
        $raw_body = $this->params();

        info('游戏推过来的数据', $raw_body);
        $type = fetch($raw_body, 'type');
        $game_id = fetch($raw_body, 'game_id');

        if ($type == 'start') {

            $game_history = new \GameHistories();
            $game_history->game_id = $game_id;
            $game_history->user_id = $current_user->id;
            $game_history->room_id = $current_user->room_id;
            $game_history->status = GAME_STATUS_WAIT;
            $game_history->start_data = json_encode($start_data, JSON_UNESCAPED_UNICODE);

            $root = $this->getRoot();
            $image_url = $root . 'images/go_game.png';
            $body = ['action' => 'game_notice', 'type' => $type, 'content' => $current_user->nickname . "发起了跳一跳游戏",
                'image_url' => $image_url, 'client_url' => "url://m/games/tyt?game_id=" . $game_id];

            \Games::sendGameMessage($current_user, $body);
        } else {

            $body = ['action' => 'game_notice', 'type' => $type, 'content' => "游戏结束",];
            \Games::sendGameMessage($current_user, $body);
        }
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
            'owner' => $is_host == true ? 0 : 1,
        ];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['data' => $data]);

    }
}