<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 04/01/2018
 * Time: 16:40
 */
class UsersTask extends \Phalcon\Cli\Task
{

    function findByIdAction($params)
    {
        echoLine(Users::findFirstById($params[0]));
    }

    /**
     * 导入用户
     */
    function importUserAction($opts = array())
    {
        $filename = fetch($opts, 0, 'user_detail.log');
        $path = APP_ROOT . 'log/' . $filename;
        $from_dev = false;
        if (preg_match('/^dev_/', $filename)) {
            $from_dev = true;
        }

        echoLine($path, $from_dev);

        $yuanfen = new \Yuanfen($path, $from_dev);
        $yuanfen->parseFile();
    }

    function silentUserAction()
    {
        $user_id = 2;
        while (true) {
            $user = \Users::findById($user_id);
            if (isBlank($user)) {
                break;
            }
            if ($user && $user->isSilent() && isBlank($user->avatar)) {
                \Yuanfen::addSilentUser($user);
            }
            $user_id += 1;
        }
    }

    function exportAuthedUsersAction()
    {
        \Users::exportAuthedUser();
    }

    function importAuthedUsersAction()
    {
        \Users::importAuthedUser();
    }

    function exportAvatar()
    {
        $hot_cache = \Albums::getHotReadCache();

        //1男 2女 3通用
        $auth_types = [1, 2, 3];

        foreach ($auth_types as $auth_type) {

            $f = fopen(APP_ROOT . "log/avatar_url_sex_{$auth_type}.log", 'w');
            $ids = $hot_cache->zrange("albums_auth_type_{$auth_type}_list_user_id_1", 0, -1);

            $albums = Albums::findByIds($ids);

            foreach ($albums as $album) {
                $avatar_url = $album->getImageUrl();
                fwrite($f, $avatar_url . "\r\n");
            }

            fclose($f);

            $hot_cache->zclear("albums_auth_type_{$auth_type}_list_user_id_1");
        }
    }

    function getSilentUsersAction()
    {
        $cond = [
            'conditions' => 'user_type = :user_type: and avatar_status = :avatar_status:',
            'bind' => ['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS]
        ];

        $users = Users::find($cond);

        foreach ($users as $user) {
            echoLine($user->id);
            if ($user->current_room_id) {
                echoLine($user->id, $user->current_room_id, $user->current_room->name);
                //$user->current_room->exitSilentRoom($user);
            }
        }

        echoLine(count($users));
    }

    function resetAction($params)
    {
        if (!isset($params[0]) || !isset($params[1])) {
            echoLine($params);
            return false;
        }

        $user_id = $params[0];
        $new_user_id = $params[1];

        $user = Users::findFirstById($user_id);
        $new_user = Users::findFirstById($new_user_id);

//        if ($new_user->user_type != USER_TYPE_SILENT) {
//            echoLine("非法操作 用户不是沉默用户");
//            return;
//        }

        $data = $user->toData();
        echoLine($data);

        foreach ($data as $k => $v) {
            if ('id' == $k) {
                continue;
            }
            $new_user->$k = $v;
        }

        $new_user->save();

        $new_user->sid = $new_user->generateSid('d.');
        $new_user->save();

        $user->mobile = '1';
        $user->user_status = USER_STATUS_OFF;
        $user->avatar_status = AUTH_FAIL;
        $user->room_id = 0;
        $user->current_room_id = 0;
        $user->current_room_seat_id = 0;
        $user->user_role = 0;
        $user->gold = 0;
        $user->diamond = 0;
        $user->hi_coins = 0;
        $user->charm_value = 0;
        $user->wealth_value = 0;
        $user->union_id = 0;
        $user->experience = 0;
        $user->level = 0;
        $user->segment = '';
        $user->third_name = '';
        $user->login_type = '';
        $user->third_unionid = '';
        $user->user_role_at = 0;
        $user->sid = $user->generateSid('d.');
        $user->save();

        //用户订单
        $gift_orders = GiftOrders::findBy(['user_id' => $user_id]);
        echoLine(count($gift_orders));

        foreach ($gift_orders as $gift_order) {
            $gift_order->user_id = $new_user_id;
            $gift_order->save();
        }

        $gift_orders = GiftOrders::findBy(['sender_id' => $user_id]);
        echoLine(count($gift_orders));

        foreach ($gift_orders as $gift_order) {
            $gift_order->sender_id = $new_user_id;
            $gift_order->save();
        }

        //用户礼物
        $user_gifts = UserGifts::findBy(['user_id' => $user_id]);
        echoLine(count($user_gifts));

        foreach ($user_gifts as $user_gift) {
            $user_gift->user_id = $new_user_id;
            $user_gift->save();
        }

        //订单
        $orders = Orders::findBy(['user_id' => $user_id]);
        echoLine(count($orders));

        foreach ($orders as $order) {
            $order->user_id = $new_user_id;
            $order->save();
        }

        //支付
        $payments = Payments::findBy(['user_id' => $user_id]);

        foreach ($payments as $payment) {
            $payment->user_id = $new_user_id;
            $payment->save();
        }

        //账户
        $account_histories = AccountHistories::findBy(['user_id' => $user_id]);

        foreach ($account_histories as $account_history) {
            $account_history->user_id = $new_user_id;
            $account_history->save();
        }

        //相册
        $albums = Albums::findBy(['user_id' => $user_id]);

        foreach ($albums as $album) {
            $album->user_id = $new_user_id;
            $album->save();
        }

        //房间信息
        $room = Rooms::findFirstByUserId($user_id);

        if ($room) {
            $room->user_id = $new_user_id;
            $room->save();
        }

        $room_seat = RoomSeats::findFirstByUserId($user_id);

        if ($room_seat) {
            $room_seat->user_id = $new_user_id;
            $room_seat->save();
        }

        //通话记录
        $voice_calls = VoiceCalls::findBy(['sender_id' => $user_id]);

        foreach ($voice_calls as $voice_call) {
            $voice_call->sender_id = $new_user_id;
            $voice_call->save();
        }

        $voice_calls = VoiceCalls::findBy(['receiver_id' => $user_id]);
        foreach ($voice_calls as $voice_call) {
            $voice_call->receiver_id = $new_user_id;
            $voice_call->save();
        }

        //关注关系
        $user_db = Users::getUserDb();
        $follow_user_ids = $user_db->zrange('follow_list_user_id' . $user_id, 0, -1);
        $followed_user_ids = $user_db->zrange('followed_list_user_id' . $user_id, 0, -1);

        if (count($follow_user_ids) > 0) {
            $follow_users = Users::findByIds($follow_user_ids);

            foreach ($follow_users as $follow_user) {
                $user->unFollow($follow_user);
                $new_user->follow($follow_user);
            }
        }

        if (count($followed_user_ids) > 0) {
            $followed_users = Users::findByIds($followed_user_ids);

            foreach ($followed_users as $followed_user) {
                $followed_user->unFollow($user);
                $followed_user->follow($new_user);
            }
        }

        //好友关系
        $add_key = 'add_friend_list_user_id_' . $user_id;
        $new_add_key = 'add_friend_list_user_id_' . $new_user_id;
        $add_user_ids = $user_db->zrange($add_key, 0, -1, true);

        foreach ($add_user_ids as $add_user_id => $time) {
            $user_db->zadd($new_add_key, $time, $add_user_id);
            $user_db->zrem('added_friend_list_user_id_' . $add_user_id, $user_id);
            $user_db->zadd('added_friend_list_user_id_' . $add_user_id, $time, $new_user_id);
        }

        $user_db->zclear($add_key);

        $added_key = 'added_friend_list_user_id_' . $user_id;
        $new_added_key = 'added_friend_list_user_id_' . $new_user_id;
        $added_user_ids = $user_db->zrange($added_key, 0, -1, true);

        foreach ($added_user_ids as $added_user_id => $time) {
            $user_db->zadd($new_added_key, $time, $added_user_id);
            $user_db->zrem('add_friend_list_user_id_' . $added_user_id, $user_id);
            $user_db->zadd('add_friend_list_user_id_' . $added_user_id, $time, $new_user_id);
        }

        $user_db->zclear($added_key);

        $add_total_key = 'friend_total_list_user_id_' . $user_id;
        $new_add_total_key = 'friend_total_list_user_id_' . $new_user_id;
        $add_total_user_ids = $user_db->zrange($add_total_key, 0, -1, true);

        foreach ($add_total_user_ids as $add_total_user_id => $time) {
            $user_db->zadd($new_add_total_key, $time, $add_total_user_id);
            $user_db->zrem('friend_total_list_user_id_' . $add_total_user_id, $user_id);
            $user_db->zadd('friend_total_list_user_id_' . $add_total_user_id, $time, $new_user_id);
        }

        $user_db->zclear($add_total_key);


        $user_introduce_key = "add_friend_introduce_user_id" . $user_id;
        $new_user_introduce_key = "add_friend_introduce_user_id" . $new_user_id;
        $user_introduces = $user_db->hgetall($user_introduce_key);

        foreach ($user_introduces as $id => $introduce) {
            $user_db->hset($new_user_introduce_key, $id, $introduce);
        }

        $user_db->hclear($user_introduce_key);

        $friend_list_key = 'friend_list_user_id_' . $user_id;
        $new_friend_list_key = 'friend_list_user_id_' . $new_user_id;
        $friend_list_user_ids = $user_db->zrange($friend_list_key, 0, -1, true);

        foreach ($friend_list_user_ids as $friend_list_user_id => $time) {
            $user_db->zadd($new_friend_list_key, $time, $friend_list_user_id);
            $user_db->zrem('friend_list_user_id_' . $friend_list_user_id, $user_id);
            $user_db->zadd('friend_list_user_id_' . $friend_list_user_id, $time, $new_user_id);
        }

        $user_db->zclear($friend_list_key);

        //黑名单
        $black_user_ids = $user_db->zrange('black_list_user_id' . $user_id, 0, -1);
        $blacked_user_ids = $user_db->zrange('black_list_user_id' . $user_id, 0, -1);

        if (count($black_user_ids) > 0) {
            $black_users = Users::findByIds($black_user_ids);

            foreach ($black_users as $black_user) {
                $user->black($black_user);
                $new_user->unBlack($black_user);
            }
        }


        if (count($blacked_user_ids) > 0) {
            $blacked_users = Users::findByIds($blacked_user_ids);

            foreach ($blacked_users as $blacked_user) {
                $blacked_user->unBlack($user);
                $blacked_user->black($new_user);
            }
        }

        //举报
        $complaints = Complaints::findBy(['complainer_id' => $user_id]);

        foreach ($complaints as $complaint) {
            $complaint->complainer_id = $new_user_id;
            $complaint->save();
        }

        $complaints = Complaints::findBy(['respondent_id' => $user_id]);

        foreach ($complaints as $complaint) {
            $complaint->respondent_id = $new_user_id;
            $complaint->save();
        }

        $user_music_key = "user_musics_id" . $user_id;
        $new_user_music_key = "user_musics_id" . $new_user_id;

        $musics = Musics::findBy(['user_id' => $user_id]);

        foreach ($musics as $music) {
            $music->user_id = $new_user_id;
            $music->update();
        }

        $music_ids = $user_db->zrange($user_music_key, 0, -1, true);

        foreach ($music_ids as $music_id => $time) {
            $user_db->zadd($new_user_music_key, $time, $music_id);
        }

        $user_db->zclear($user_music_key);
    }

    function updateSilentUserAvatarAction()
    {
        $auth_types = [1, 2, 3];

        //1男 2女 3通用
        foreach ($auth_types as $auth_type) {

            if ($auth_type == 1) {
                $sex = 1;
            } elseif (2 == $auth_type) {
                $sex = 0;
            } else {
                $sex = null;
            }

            $hot_cache = Users::getHotWriteCache();
            $key = "silent_user_update_avatar_user_ids";
            $file = APP_ROOT . "log/avatar_url_sex_{$auth_type}.log";
            $content = file_get_contents($file);
            $content = explode(PHP_EOL, $content);
            $avatar_urls = array_filter($content);

            foreach ($avatar_urls as $avatar_url) {
                $avatar_url = trim($avatar_url);
                $source_filename = APP_ROOT . 'temp/avatar_' . md5(uniqid(mt_rand())) . '.jpg';
                if (!httpSave($avatar_url, $source_filename)) {
                    info('get avatar error', $avatar_url);
                    continue;
                }

                $filter_user_ids = $hot_cache->zrange($key, 0, -1);

                $cond = ['conditions' => 'avatar_status = ' . AUTH_SUCCESS . ' and user_type = ' . USER_TYPE_SILENT];

                if (!is_null($sex)) {
                    $cond['conditions'] .= " and sex = {$sex}";
                }

                if (count($filter_user_ids) > 0) {
                    $cond['conditions'] .= " and id not in (" . implode(',', $filter_user_ids) . ")";
                }

                $user = Users::findFirst($cond);

                if (!$user) {
                    echoLine('no user error', $auth_type);
                    continue;
                }

                $user->updateAvatar($source_filename);
                echoLine($user->id);
                $hot_cache->zadd($key, time(), $user->id);

                if (file_exists($source_filename)) {
                    unlink($source_filename);
                }
            }
        }
    }

    function fixUserLoginTypeAction()
    {
        $users = Users::find(['conditions' => '(mobile != "" or mobile is not null) and user_status = 1']);

        foreach ($users as $user) {
            $user->login_type = USER_LOGIN_TYPE_MOBILE;
            $user->update();
        }
    }

    //上线需修复资料
    function fixUserLevelAction()
    {
        $gift_orders = GiftOrders::findForeach();

        foreach ($gift_orders as $gift_order) {
            echoLine($gift_order->id, $gift_order->user_id, $gift_order->sender_id);
            Users::updateExperience($gift_order->id);
        }
    }

    //上线需修复资料
    function fixUserSegmentAction()
    {
        $users = Users::find(['conditions' => "level > 0"]);

        foreach ($users as $user) {
            echoLine($user->id, $user->calculateSegment());
            $user->segment = $user->calculateSegment();
            $user->save();
        }
    }

    //上线需修复资料
    function fixExperienceAction()
    {
        $users = Users::find(['conditions' => 'avatar_status = :avatar_status:', 'bind' => ['avatar_status' => AUTH_SUCCESS]]);

        foreach ($users as $user) {

            $gift_orders = GiftOrders::findBy(['sender_id' => $user->id]);

            if (count($gift_orders) < 1) {
                //echoLine("no gift_order");
                continue;
            }

            $experience = 0;

            foreach ($gift_orders as $gift_order) {
                $amount = $gift_order->amount;
                $sender_experience = 0.02 * $amount;
                $experience += $sender_experience;
            }

            if ($experience - $user->experience >= 0.02) {
                echoLine($user->id, $user->experience, $experience);
            }

            $user->experience = $experience;
            $user->level = $user->calculateLevel();
            $user->segment = $user->calculateSegment();

            $user->update();
        }
    }

    //上线需修复资料
    function fixUserHiCoinsAction()
    {
        $users = Users::find(['conditions' => 'hi_coins > 0']);
        echoLine(count($users));

        $i = 0;

        foreach ($users as $user) {
            $i++;

            $total_amount = UserGifts::sum(['conditions' => 'user_id = :user_id:', 'bind' => ['user_id' => $user->id], 'column' => 'total_amount']);

            if ($total_amount > 100000000) {
                continue;
            }

            //echoLine($total_amount, $user->id, $user->hi_coins);

            $product_channel = $user->product_channel;

            if (!$product_channel) {
                echoLine($user->id);
                continue;
            }

            $rate = $product_channel->rateOfDiamondToHiCoin();

            if ($total_amount < 1) {
                echoLine("======", $i, $total_amount, $user->id, $user->hi_coins);
                continue;
            }

            $hi_coins = $total_amount / $rate;


            $widthdraw_hi_coins = WithdrawHistories::sum(['conditions' => 'user_id = :user_id: and status = :status:',
                'bind' => ['user_id' => $user->id, 'status' => WITHDRAW_STATUS_SUCCESS], 'column' => 'amount']);

            if ($widthdraw_hi_coins > 0) {
                $hi_coins = $hi_coins - $widthdraw_hi_coins;
            }

            if ($hi_coins - $user->hi_coins >= 0.04) {
                echoLine("总金额", $total_amount, "用户id", $user->id, "用户hicoins", $user->hi_coins, "hicoins", $hi_coins, "已提现", $widthdraw_hi_coins);
            } else {
                continue;
            }

            $user->hi_coins = $hi_coins;
            $user->update();
        }
    }

    function initUsersAction()
    {
        while (true) {
            $user = new Users();
            $user->user_type = USER_TYPE_SILENT;
            $user->user_status = USER_STATUS_OFF;
            $user->sex = mt_rand(0, 1);
            $user->product_channel_id = 1;
            $user->login_name = '';
            $user->nickname = '';
            $user->avatar = '';
            $user->platform = '';
            $user->province_id = 0;
            $user->city_id = 0;
            $user->ip = '';
            $user->last_at = time();
            $user->mobile = '';
            $user->device_id = 0;
            $user->push_token = '';
            $user->sid = '';
            $user->version_code = '';
            $user->openid = '';
            $user->password = '';
            $user->fr = '';
            $user->partner_id = 0;
            $user->subscribe = 0;
            $user->event_at = 0;
            $user->latitude = 0;
            $user->longitude = 0;
            $user->geo_province_id = 0;
            $user->geo_city_id = 0;
            $user->ip_province_id = 0;
            $user->ip_city_id = 0;
            $user->register_at = 0;
            $user->mobile_operator = 0;
            $user->api_version = '';
            $user->monologue = '';
            $user->room_id = 0;
            $user->height = 0;
            $user->interests = '';
            $user->gold = 0;
            $user->diamond = 0;
            $user->birthday = 0;
            $user->current_room_seat_id = 0;
            $user->user_role = 0;
            $user->current_room_id = 0;
            $user->geo_hash = '';
            $user->platform_version = '';
            $user->version_name = '';
            $user->manufacturer = '';
            $user->device_no = '';
            $user->client_status = 0;
            $user->user_role_at = 0;
            $user->hi_coins = 0;
            $user->third_unionid = '';
            $user->login_type = '';
            $user->save();

            echoLine($user->id);
            if ($user->id >= 1000000) {
                break;
            }
        }
    }

    //修复没有产品渠道的用户
    function fixProductChannelIdAction()
    {
        $cond = [
            'conditions' => 'product_channel_id is null'
        ];
        $users = Users::find($cond);
        foreach ($users as $user) {
            if (!$user->product_channel_id) {
                $user->product_channel_id = 1;
                echoLine($user->id);
                $user->save();
            }
        }
    }

    //上线需修复资料
    function fixCharmAndWealthAction()
    {
        $users = Users::find(['conditions' => 'avatar_status = :avatar_status:', 'bind' => ['avatar_status' => AUTH_SUCCESS]]);

        echoLine(count($users));

        foreach ($users as $user) {

            $gift_orders = GiftOrders::find([
                'conditions' => "sender_id = :user_id: or user_id = :user_id: and status = :status:",
                'bind' => ['user_id' => $user->id, 'status' => GIFT_ORDER_STATUS_SUCCESS]
            ]);

            $charm = 0;
            $wealth = 0;

            if (count($gift_orders) < 1) {
                echoLine("no gift_order");
                continue;
            }

            foreach ($gift_orders as $gift_order) {
                if ($gift_order->sender_id == $user->id) {
                    $wealth += $gift_order->amount;
                }
                if ($gift_order->user_id == $user->id) {
                    $charm += $gift_order->amount;
                }
            }

            $user->charm = $charm;
            $user->wealth = $wealth;

            echoLine($user->id, "charm：" . $user->charm, "wealth：" . $user->wealth);
            $user->update();
        }
    }

    function fixPayAmountAction()
    {
        $orders = Orders::find(['columns' => 'distinct user_id']);

        $user_ids = [];

        foreach ($orders as $order) {
            $user_ids[] = $order->user_id;
        }

        $users = Users::find(['conditions' => 'id in (' . implode(',', $user_ids) . ')']);
        echoLine(count($users));

        foreach ($users as $user) {
            $user->pay_amount = Orders::sum([
                'conditions' => 'user_id = ' . $user->id,
                'column' => 'amount'
            ]);

            echoLine($user->pay_amount);

            $user->update();
        }
    }

    function fixRoomIdAndCurrentRoomIdAction()
    {
        $cond = [
            "conditions" => "room_id is null or current_room_id is null"
        ];
        $users = Users::findForeach($cond);
        foreach ($users as $user) {
            if (!$user->current_room_id) {
                echo "+++++";
                $user->current_room_id = 0;
            }
            if (!$user->room_id) {
                echo "------";
                $user->room_id = 0;
            }
            echoLine($user->id, $user->room_id, $user->current_room_id);
            $user->update();
        }
    }
}

