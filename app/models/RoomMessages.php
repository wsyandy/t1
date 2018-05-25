<?php

trait RoomMessages
{
    function pushRoomNoticeMessage($content, $opts = [])
    {
        $room_id = fetch($opts, 'room_id');
        $expire_time = fetch($opts, 'expire_time');
        $client_url = '';
        $room = Rooms::findFirstById($room_id);

        //当前房间不带client_url
        if ($room_id && $room && $room_id != $this->id && !$room->lock) {
            $client_url = 'app://m/rooms/detail?id=' . $room_id;
        }

        $body = ['action' => 'room_notice', 'channel_name' => $this->channel_name, 'expire_time' => $expire_time,
            'content' => $content, 'client_url' => $client_url];

        $this->push($body, true);
    }

    //全服通知
    static function asyncAllNoticePush($content, $opts = [])
    {
        $hot = fetch($opts, 'hot');
        $room_id = fetch($opts, 'room_id');
        $expire_time = fetch($opts, 'expire_time');
        $type = fetch($opts, 'type', 'notice');

        if ($hot) {

            $room = Rooms::findFirstById($room_id);

            //热门房间单独推送
            if (!$room->isInHotList()) {
                $room->pushRoomNoticeMessage($content, ['room_id' => $room_id, 'expire_time' => $expire_time]);
            }

            $hot_cache = Users::getHotWriteCache();
            $hot_room_list_key = Rooms::getHotRoomListKey();
            $hot_total_room_list_key = Rooms::getTotalRoomListKey(); //新的用户总的队列

            $hot_room_ids = $hot_cache->zrevrange($hot_room_list_key, 0, 9);
            $hot_total_room_ids = $hot_cache->zrevrange($hot_total_room_list_key, 0, 9);

            $room_ids = array_merge($hot_room_ids, $hot_total_room_ids);
            $room_ids = array_unique($room_ids);

            $rooms = Rooms::findByIds($room_ids);

        } else {
            $cond = ['conditions' => 'user_type = :user_type: and last_at >= :last_at:',
                'bind' => ['user_type' => USER_TYPE_ACTIVE, 'last_at' => time() - 10 * 3600], 'order' => 'last_at desc', 'limit' => 100];
            $rooms = Rooms::find($cond);
        }

        $system_user = Users::findFirstById(1);

        foreach ($rooms as $room) {

            if ('notice' == $type) {
                $room->pushRoomNoticeMessage($content, ['room_id' => $room_id, 'expire_time' => $expire_time]);
            } else {
                $room->pushTopTopicMessage($system_user, $content);
            }
        }
    }

    //全服通知
    static function allNoticePush($gift_order)
    {

        $opts = ['room_id' => $gift_order->room_id];

        $max_amount = 100000;
        $min_amount = 50000;

        if (isDevelopmentEnv()) {
            $max_amount = 1000;
            $min_amount = 500;
        }

        $push = false;
        $expire_time = 5;

        if ($gift_order->amount >= $max_amount) {
            $expire_time = 10;
            $push = true;
        }

        if ($gift_order->amount >= $min_amount && $gift_order->amount < $max_amount) {
            $opts['hot'] = 1;
            $expire_time = 6;
            $push = true;
        }

        if ($push) {
            $opts['expire_time'] = $expire_time;
            info($gift_order->id, $gift_order->sender_id, $gift_order->user_id, $gift_order->amount, $opts);
            Rooms::delay()->asyncAllNoticePush($gift_order->allNoticePushContent(), $opts);
        }
    }

    function pushBoomIncomeMessage($total_income, $cur_income, $status = STATUS_ON)
    {
        $body = [
            'action' => 'boom_gift',
            'boom_gift' => [
                'expire_at' => Rooms::getBoomGiftExpireAt($this->id),
                'client_url' => 'url://m/backpacks',
                'svga_image_url' => BoomHistories::getSvgaImageUrl(),
                'total_value' => (int)$total_income,
                'show_rank' => 1000000,
                'current_value' => (int)$cur_income,
                'render_type' => 'svga',
                'status' => $status,
                'image_color' => 'blue'
            ]
        ];

        debug($this->id, $body);

        $this->push($body, true);
    }

    function pushEnterRoomMessage($user)
    {

        $body = ['action' => 'enter_room', 'user_id' => $user->id, 'nickname' => $user->nickname, 'sex' => $user->sex,
            'avatar_url' => $user->avatar_url, 'avatar_small_url' => $user->avatar_small_url, 'channel_name' => $this->channel_name,
            'segment' => $user->segment, 'segment_text' => $user->segment_text
        ];

        $user_car_gift = $user->getUserCarGift();

        if ($user_car_gift) {
            $body['user_car_gift'] = $user_car_gift->toSimpleJson();
        }

        $this->push($body);
    }

    function pushExitRoomMessage($user, $current_room_seat_id = 0, $to_self = false)
    {

        $body = ['action' => 'exit_room', 'user_id' => $user->id, 'channel_name' => $this->channel_name];

        $current_room_seat = RoomSeats::findFirstById($current_room_seat_id);
        if ($current_room_seat) {
            $body['room_seat'] = $current_room_seat->toSimpleJson();
        }

        //指定用户
        if ($to_self) {
            $this->pushToUser($user, $body);
        } else {
            $this->push($body);
        }
    }

    function pushTopTopicMessage($user, $content = "", $content_type = null)
    {
        if (!$content) {
            $messages = Rooms::$TOP_TOPIC_MESSAGES;
            $content = $messages[array_rand($messages)];
        }

        $body = ['action' => 'send_topic_msg', 'user_id' => $user->id, 'nickname' => $user->nickname, 'sex' => $user->sex,
            'avatar_url' => $user->avatar_url, 'avatar_small_url' => $user->avatar_small_url, 'content' => $content,
            'channel_name' => $this->channel_name, 'content_type' => $content_type
        ];

        $need_version_control = false;

        if ($content_type == 'red_packet') {
            $need_version_control = true;
        }

        $this->push($body, $need_version_control);
    }

    function pushUpMessage($user, $current_room_seat)
    {
        $body = ['action' => 'up', 'channel_name' => $this->channel_name, 'room_seat' => $current_room_seat->toSimpleJson()];
        $this->push($body);
    }

    function pushDownMessage($user, $current_room_seat)
    {
        $body = ['action' => 'down', 'channel_name' => $this->channel_name, 'room_seat' => $current_room_seat->toSimpleJson()];

        $this->push($body);
    }

    function pushGiftMessage($user, $receiver, $gift, $gift_num)
    {
        $sender_nickname = $user->nickname;
        $receiver_nickname = $receiver->nickname;

        if (isDevelopmentEnv()) {
            $sender_nickname .= $user->id;
            $receiver_nickname .= $receiver->id;

        }

        $data = $gift->toSimpleJson();
        $data['num'] = $gift_num;
        $data['sender_id'] = $user->id;
        $data['sender_nickname'] = $sender_nickname;
        $data['sender_room_seat_id'] = $user->current_room_seat_id;
        $data['receiver_id'] = $receiver->id;
        $data['receiver_nickname'] = $receiver_nickname;
        $data['receiver_room_seat_id'] = $receiver->current_room_seat_id;
        $data['pay_type'] = $gift->pay_type;
        $data['total_amount'] = $gift_num * $gift->amount;

        $body = ['action' => 'send_gift', 'notify_type' => 'bc', 'channel_name' => $this->channel_name, 'gift' => $data];

        $this->push($body);
    }

    function pushRedPacketMessage($user, $num, $url, $notify_type = 'ptp')
    {
        $body = ['action' => 'red_packet', 'notify_type' => $notify_type, 'red_packet' => ['num' => $num, 'client_url' => $url]];
        info('推送红包信息', $body);
        if ($user->canReceiveBoomGiftMessage()) {
            $this->pushToUser($user, $body);
        }
    }

    function pushPkMessage($pk_history_datas)
    {
        $body = ['action' => 'pk', 'pk_history' => [
            'pk_type' => $pk_history_datas['pk_type'],
            'left_pk_user' => ['id' => $pk_history_datas['left_pk_user_id'], 'score' => $pk_history_datas[$pk_history_datas['left_pk_user_id']]],
            'right_pk_user' => ['id' => $pk_history_datas['right_pk_user_id'], 'score' => $pk_history_datas[$pk_history_datas['right_pk_user_id']]]
        ]
        ];
        $this->push($body, true);
    }

    function push($body, $check_user_version = false)
    {
        $users = $this->findTotalRealUsers();

        if (count($users) < 1) {

            if ($this->user) {
                debug($this->user->sid);
            }

            info("no_users", $this->id, $body);
            return;
        }

        foreach ($users as $user) {

            //推送校验新版本
            if ($check_user_version && !$user->canReceiveBoomGiftMessage()) {
                info("old_version_user", $user->sid);
                continue;
            }

            $res = $this->pushToUser($user, $body);
            if ($res) {
                break;
            }
        }
    }

    //指定用户推送消息
    function pushToUser($user, $body)
    {
        $intranet_ip = $user->getIntranetIp();
        $receiver_fd = $user->getUserFd();
        $payload = ['body' => $body, 'fd' => $receiver_fd];

        if (!$intranet_ip) {
            info("user_already_close", $user->id, $user->sid, 'room', $this->id, $payload);
            return false;
        }

        $res = \services\SwooleUtils::send('push', $intranet_ip, self::config('websocket_local_server_port'), $payload);
        if ($res) {
            info('push ok ', $user->id, $user->sid, 'room', $this->id, $payload);
            return true;
        }

        info("Exce push", $user->id, $user->sid, 'room', $this->id, $payload);

        return false;
    }
}