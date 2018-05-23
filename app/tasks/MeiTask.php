<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午9:49
 */

class MeiTask extends \Phalcon\Cli\Task
{

    function test61Action()
    {
        $user = Users::findFirstById(6);
        echoLine($user->current_room_signal_status, $user->current_room_channel_status);
    }

    function test60Action()
    {
        $room = Rooms::findFirstById(137);

        $body = ['action' => 'send_topic_msg', 'user_id' => 1, 'nickname' => '呵呵', 'sex' => 1,
            'avatar_url' => '', 'avatar_small_url' => '', 'content' => '晚上好',
            'channel_name' => $room->channel_name, 'content_type' => 'text'
        ];

        $user = Users::findFirstById(117);

        $res = $room->pushToUser($user, $body);

        echoLine($res);

        $need_version_control = false;
        if ($content_type == 'red_packet') {
            $need_version_control = true;
        }

        $this->push($body, $need_version_control);


        $db = Users::getUserDb();
        $key = "room_manager_1";
        $db->hset($key, 2, json_encode(['time' => 12222, 'is_de' => false]));
        echoLine($db->hgetall($key));
    }

    function test59Action()
    {
        $content = file_get_contents(APP_ROOT . "temp/test.txt");
        $sids = explode(PHP_EOL, $content);

        $user_ids = [];

        foreach ($sids as $sid) {
            $user_ids[] = intval($sid);
        }

        $user_ids = array_unique(array_filter($user_ids));

        print_r($user_ids);
    }

    function test58Action()
    {
        Rooms::asyncAllNoticePush('果然to韩笑：陪伴是最长情的告白', ['expire_time' => 10]);
    }

    function test57Action()
    {
        $db = \Users::getUserDb();

        $sender_key = Couples::generateSeraglioKey(1084173);
        echoLine($db->zscore($sender_key, 1076267));

        $receive_key = Couples::generateSeraglioKey(1076267);
        echoLine($db->zscore($receive_key, 1084173));

    }

    function test46Action()
    {
        echoLine(Couples::checkCpRelation(1300364, 1028930));
        echoLine(Couples::getMarriageTime(1028930, 1300364));

        $db = \Users::getUserDb();
        $key = Couples::generateCpMarriageTimeKey();
        $db->zadd($key, time(), '1195090_1103162');

        //发起者的后宫
        $sponsor_seraglio_key = Couples::generateSeraglioKey(1195090);
        //追求者的后宫
        $pursuer_seraglio_key = Couples::generateSeraglioKey(1103162);

        $db->zadd($sponsor_seraglio_key, 2, 1103162);
        $db->zadd($pursuer_seraglio_key, 1, 1195090);

        //保存时间
        $cp_marriage_time_key = Couples::generateCpMarriageTimeKey();
        $db->zadd($cp_marriage_time_key, time(), 1195090 . '_' . 1103162);

        $db = \Users::getUserDb();

        $cp_info_key = Couples::generateCpInfoKey();
        $db->zadd($cp_info_key, 2520, 1195090 . '_' . 1103162);

        $db = \Users::getUserDb();
        $res = $db->zrange($cp_info_key, 0, -1, 'withscores');
        echoLine($res);

        $db = \Users::getUserDb();
        $sender_key = Couples::generateCpInfoForUserKey(1195090);
        $db->zadd($sender_key, 2520, 1103162);

        $receive_key = Couples::generateCpInfoForUserKey(1103162);
        $db->zadd($receive_key, 2520, 1195090);


        $db = \Users::getUserDb();
        $key = Couples::generateCpMarriageTimeKey();
        echoLine($db->zcard($key));
        $datas = $db->zrange($key, 0, -1, 'withscores');

        foreach ($datas as $id_key => $time) {
            $date = date("Ymd H:i:s", $time);

            $user_ids = explode("_", $id_key);
            $user_ids = array_filter($user_ids);

            if (in_array(1159082, $user_ids) || in_array(1199287, $user_ids)) {

                echoLine($id_key, $date);
                //$db->zadd($key, $time, '1300364_1028930');
                //$db->zrem($key, $id_key);
                if (count($user_ids) < 2) {
                    //echoLine($id_key, $date);
                    //$db->zrem($key, $id_key);
                }
            }

//            if (count($user_ids) < 2) {
//                echoLine($id_key, $date);
//                $db->zrem($key, $id_key);
//            }
        }


        $db = \Users::getUserDb();
        $cp_info_key = Couples::generateCpInfoKey();
        $db->zadd($cp_info_key, 82519 + 220, '1300364_1028930');

        $db = \Users::getUserDb();
        $res = $db->zrange($cp_info_key, 0, -1, 'withscores');
        echoLine($res);

        $db = \Users::getUserDb();
        $sender_key = Couples::generateCpInfoForUserKey(1001303);
        //$db->zadd($sender_key, 82519, 1028930);
        $res = $db->zrange($sender_key, 0, -1, 'withscores');
        echoLine($res);

        $receive_key = Couples::generateCpInfoForUserKey(1028930);
        $db->zadd($receive_key, 220, 1300364);

        $res = $db->zrange($receive_key, 0, -1, 'withscores');
        echoLine($res);
    }

    function test45Action()
    {
        $union = Unions::findFirstById(1346);

        $cond = [
            'conditions' => 'room_union_id = :union_id: and created_at >= :start: and created_at <= :end: and room_id > 0',
            'bind' => ['union_id' => $union->id, 'start' => beginOfMonth(), 'end' => endOfMonth()],
            'columns' => 'distinct room_id'
        ];

        $gift_orders = GiftOrders::find($cond);

        echoLine(count($gift_orders));


        $cond = [
            'conditions' => 'receiver_union_id = :union_id: and created_at >= :start: and created_at <= :end:',
            'bind' => ['union_id' => $union->id, 'start' => beginOfMonth(), 'end' => endOfMonth()],
            'columns' => 'distinct user_id'
        ];

        $gift_orders = GiftOrders::find($cond);

        echoLine(count($gift_orders));

        $cond = [
            'conditions' => 'sender_union_id = :union_id: and created_at >= :start: and created_at <= :end:',
            'bind' => ['union_id' => $union->id, 'start' => beginOfMonth(), 'end' => endOfMonth()],
            'columns' => 'distinct sender_id'
        ];

        $gift_orders = GiftOrders::find($cond);

        echoLine(count($gift_orders));
    }

    function test44Action()
    {

        $array = [1001 => 123, 1002 => 344, 1003 => 456, 1004 => 345, 1005 => 900];
        echoLine(array_slice($array, 4, 1));
        $user = Users::findFirstById(1001303);
        echoLine($user->getOnlineToken(), $user->getUserFd());

        $hot_cache = Users::getHotReadCache();
        $user_key = 'socket_fd_user_id_6179ffe1a09d94a6c743e67452355f178a401';
        echoLine($hot_cache->get($user_key));
        $fd_key = "socket_push_fd_6179ffe1a09d94a6c743e67452355f178a401";
        $fd = $hot_cache->get($fd_key);
        echoLine($fd);

        echoLine(Rooms::generateAbnormalExitRoomListKey(), $hot_cache->zscore(Rooms::generateAbnormalExitRoomListKey(), 27 . "_" . 1001303));

        $union = Unions::findFirstById(1026);
        $key = $union->generateUsersKey();
        $ssdb = Users::getUserDb();
        echoLine($ssdb->zscore($key, 1001187));
        $user = Users::findFirstById(1001187);

//        if (isBlank($user->union_id) || $user->union_id != $union->id) {
//            return [ERROR_CODE_FAIL, '此用户已不再此家族'];
//        }

        $union_history = UnionHistories::findFirstBy(['user_id' => $user->id, 'union_id' => $union->id],
            'id desc');

        if ($union_history) {
            $union_history->status = STATUS_OFF;
            $union_history->exit_at = time();
            $union_history->save();
        }

        $user->union_id = 0;
        $user->union_type = 0;
        $user->union_charm_value = 0;
        $user->union_wealth_value = 0;


        $db = Users::getUserDb();
        $db->zrem($union->generateUsersKey(), $user->id);
        $db->zrem($union->generateApplyExitUsersKey(), $user->id);
        $db->zrem($union->generateRefusedUsersKey(), $user->id);
        $db->zrem($union->generateNewUsersKey(), $user->id);
        $db->zrem($union->generateCheckUsersKey(), $user->id);
        $db->zrem($union->generateAllApplyExitUsersKey(), $user->id);

        $user->update();

        return [ERROR_CODE_SUCCESS, '操作成功'];

        echoLine(Users::randomSilentUser());
        $gift_order = GiftOrders::findFirstById(1957528);
        $amount = $gift_order->amount;
        echoLine($amount);
        $gift_order->room_id = 1008720;
        $gift_order->room_union_id = 1068;
        $gift_order->save();

        $order = Orders::findFirstById(69669);

        $product = $order->product;
        $opts = ['order_id' => $order->id, 'target_id' => $order->id, 'mobile' => $order->mobile];

        if ($product->diamond) {
            $opts['remark'] = '购买' . $product->full_name;
            AccountHistories::changeBalance($order->user_id, ACCOUNT_TYPE_BUY_DIAMOND, $product->diamond, $opts);
        }

        if ($product->gold) {
            $opts['remark'] = '购买金币';
            GoldHistories::changeBalance($order->user_id, GOLD_TYPE_BUY_GOLD, $product->gold, $opts);
        }

        echoLine($order);


        $hot_cache = Rooms::getHotWriteCache();
        $user_ids = $hot_cache->zrange(Rooms::generateExitRoomByServerListKey(), 0, -1);
        echoLine(count($user_ids));

        $users = Users::findByIds($user_ids);

        foreach ($users as $user) {

            if ($user->last_at <= time() - 15 * 60 && $user->current_room_id) {
                echoLine($user->id, $user->current_room_id);
                $current_room = $user->current_room;
                $current_room_seat_id = $user->current_room_seat_id;
                $current_room->exitRoom($user, true);
                $current_room->pushExitRoomMessage($user, $current_room_seat_id);
            }

        }
    }

    function freshRoomUserIdAction()
    {
        $cond = [
            'conditions' => 'user_type = :user_type:',
            'bind' => ['user_type' => USER_TYPE_SILENT]
        ];

        $rooms = Rooms::find($cond);

        $cond1 = [
            'conditions' => 'user_type = :user_type: and avatar_status = :avatar_status: and id >= 100000 and (room_id = 0 or room_id is null)',
            'bind' => ['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS]
        ];

        $users = Users::find($cond1);
        $user_ids = [];

        foreach ($users as $user) {
            $user_ids[] = $user->id;
        }

        if (count($user_ids) > 0) {
            foreach ($rooms as $room) {

                if (mt_rand(1, 100) > 90) {
                    echoLine("continue");
                    continue;
                }

                $index = array_rand($user_ids);
                $user_id = $user_ids[$index];

                $old_user = Users::findFirstById($room->user_id);

                $user = Users::findFirstById($user_id);
                $room->user_id = $user_id;
                $user->room_id = $room->id;

                $room->update();
                $user->update();

                $old_user->room_id = 0;
                $old_user->update();

                $users = $room->findTotalUsers();
                echoLine(count($users));

                foreach ($users as $user1) {
                    $user1->current_room_id = $room->id;
                    $user1->current_room_seat_id = 0;
                    $user1->update();
                }

                unset($user_ids[$index]);
            }
        }
    }

    function getUserIpAction()
    {
        $user = Users::findFirstById(31290);
        echoLine($user->user_role);
        echoLine($user->getIntranetIp());
        echoLine($user->getOnlineToken());
    }

    function initSilentUserFansAction()
    {
        $cond = [
            'conditions' => 'user_type = :user_type: and avatar_status = :avatar_status:',
            'bind' => ['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS]
        ];

        $users = Users::find($cond);
        $user_ids = [];

        foreach ($users as $user) {
            $user_ids[] = $user->id;
        }

        $user_db = Users::getUserDb();

        foreach ($users as $user) {

            $rand_num = mt_rand(1, 100);
            $rand_num1 = mt_rand(1, 100);
            $rand_num2 = mt_rand(1, 100);

            $follow_key = 'follow_list_user_id' . $user->id;
            $followed_key = 'followed_list_user_id' . $user->id;
            $friend_key = 'friend_list_user_id_' . $user->id;
            $friend_num = $user->friendNum();
            $follow_num = $user->followNum();
            $followed_num = $user->followedNum();

            $follow_user_ids = $user_db->zrange($follow_key, 0, -1);
            $followed_user_ids = $user_db->zrange($followed_key, 0, -1);
            $friend_user_ids = $user_db->zrange($friend_key, 0, -1);

            $new_follow_user_ids = array_diff($user_ids, $follow_user_ids);
            $new_followed_user_ids = array_diff($user_ids, $followed_user_ids);
            $new_friend_user_ids = array_diff($user_ids, $friend_user_ids);

            if ($rand_num <= 80) {
                $num = mt_rand(0, 5);
            } else {
                $num = mt_rand(6, 10);
            }

            $num = $num - $friend_num;

            if ($num > 0) {

                $ids = array_rand($new_friend_user_ids, $num);

                if (!is_array($ids)) {

                    $id = $ids;
                    $ids = [];

                    if ($id) {
                        $ids[] = $id;
                    }
                }

                echoLine($ids);

                if (count($ids) > 0) {
                    $friend_users = Users::findByIds($ids);

                    foreach ($friend_users as $friend_user) {
                        $user->addFriend($friend_user);
                        $friend_user->agreeAddFriend($user);
                    }
                } else {
                    echoLine($ids, $new_friend_user_ids, $num);
                }
            }

            if ($rand_num1 <= 70) {
                $num = mt_rand(0, 15);
            } else {
                $num = mt_rand(16, 30);
            }

            $num = $num - $follow_num;

            if ($num > 0) {

                $ids = array_rand($new_follow_user_ids, $num);

                if (!is_array($ids)) {

                    $id = $ids;
                    $ids = [];

                    if ($id) {
                        $ids[] = $id;
                    }
                }


                if (count($ids) > 0) {

                    $follow_users = Users::findByIds($ids);

                    foreach ($follow_users as $follow_user) {
                        $user->follow($follow_user);
                    }
                } else {
                    echoLine($ids, $new_follow_user_ids, $num);
                }
            }


            if ($rand_num2 <= 60) {
                $num = mt_rand(0, 15);
            } else {
                $num = mt_rand(16, 30);
            }

            $num = $num - $followed_num;

            if ($num > 0) {

                $ids = array_rand($new_followed_user_ids, $num);

                if (!is_array($ids)) {

                    $id = $ids;
                    $ids = [];

                    if ($id) {
                        $ids[] = $id;
                    }
                }

                if (count($ids) > 0) {
                    $followed_users = Users::findByIds($ids);

                    foreach ($followed_users as $followed_user) {
                        $followed_user->follow($user);
                    }
                } else {
                    echoLine($ids, $new_followed_user_ids, $num);
                }
            }
        }
    }

    function fixActiveRoomAction()
    {
        $rooms = Rooms::findBy(['online_status' => STATUS_OFF, 'user_type' => USER_TYPE_SILENT]);

        foreach ($rooms as $room) {
            if ($room->getRealUserNum() > 0) {
                echoLine($room->getRealUserNum(), $room->id);
            }
        }
    }

    function testMp3Action()
    {
//        $fp = fopen(APP_ROOT . "public/temp/5a7d4437e1dc7.mp3", "rb");
//        fseek($fp, -128, SEEK_END);
//        $tag = fread($fp, 3);
//        var_dump($tag, fread($fp, 30));

        $getID3 = new getID3();    //实例化类
        $ThisFileInfo = $getID3->analyze(APP_ROOT . "public/temp/test_music.mp4");   //分析文件
        //$time = $ThisFileInfo['playtime_seconds'];      //获取mp3的长度信息
        //echo $ThisFileInfo['playtime_seconds'];         //获取MP3文件时长
        $filename = APP_ROOT . "public/temp/5a7d444ae6cec.mp3";
        $fp = fopen($filename, "rb");
        $res = fread($fp, 32774);
        var_dump($getID3->GetFileFormat($res));
    }

    function testActiveRoomAction()
    {
        $cond = ['conditions' => '(online_status = :online_status: and user_type = :user_type:) or
         (status = :status: and user_type = :user_type1:)',
            'bind' => ['status' => STATUS_ON, 'online_status' => STATUS_ON, 'user_type' => USER_TYPE_SILENT, 'user_type1' => USER_TYPE_ACTIVE],
            'order' => 'last_at desc', 'limit' => 60];

        $rooms = Rooms::find($cond);

        foreach ($rooms as $room) {
            echoLine($room->id, $room->user_id);
        }

        $hot_cache = Rooms::getHotWriteCache();
        $user_ids = $hot_cache->zrange('wait_enter_silent_room_list_room_id', 0, -1);
        echoLine($user_ids);
    }

    function testUserLevelAction()
    {
        $user = Users::findFirstById(1);
        $user->experience = 385999;
        $user->level = $user->calculateLevel();
        $user->segment = $user->calculateSegment();
//        $user->save();
        echoLine($user->level, $user->segment, $user->segment_text);
    }

    function testUserLevelTextAction()
    {
        for ($i = 0; $i <= 36; $i++) {
            $user = Users::findFirstById(1);
            $user->level = $i;
            echoLine($user->level, $user->level_text);
        }
    }

    function checkMp3Action()
    {
        $filename = APP_ROOT . "public/temp/test_music.mp3";

        if (is_readable($filename)) {
            echoLine("is_readable");
        }

        $fp = fopen($filename, "rb");
//        //fseek($fp, -128, SEEK_END);
        $tag = fread($fp, 8);
//        //$tag = strstr($tag, "TAG");
//        //echoLine($tag);
//        var_dump($tag);

        $encode = mb_detect_encoding($tag, ["ASCII", "UTF-8", "GB2312", "GBK", "BIG5", "JIS", "EUC-JP", 'ISO-8859-1']);
        echoLine($encode, $tag);

    }

    function testGiftNumAction()
    {
        $user = Users::findFirstById(117);
        echoLine($user->getReceiveGiftNum());
    }

    function testShareHistoriesAction()
    {
        $share_history = new ShareHistories();
        $share_history->user_id = 117;
        $share_history->save();
    }

    function testIsSetFieldAction()
    {
        $music = Musics::findFirstById(1);
        $music->down_at = "";
        echoLine($music->toSimpleJson());
    }

    function pregMp3Action()
    {
        $filename = APP_ROOT . "public/temp/test_music2.mp3";
        $fp = fopen($filename, 'rb');
        $head = fread($fp, 8);

        $encode = mb_detect_encoding($head, ["ASCII", "UTF-8", "GB2312", "GBK", "BIG5", "JIS", "EUC-JP", 'ISO-8859-1']);

        echoLine($encode, $head);

        if ('ISO-8859-1' == $encode) {

            $pattern = '^\\xFF[\\xE2-\\xE7\\xF2-\\xF7\\xFA-\\xFF][\\x00-\\x0B\\x10-\\x1B\\x20-\\x2B\\x30-\\x3B\\x40-\\x4B\\x50-\\x5B\\x60-\\x6B\\x70-\\x7B\\x80-\\x8B\\x90-\\x9B\\xA0-\\xAB\\xB0-\\xBB\\xC0-\\xCB\\xD0-\\xDB\\xE0-\\xEB\\xF0-\\xFB]';

            if (preg_match('/' . $pattern . '/s', $head)) {
                echoLine("success");
            }

        } else {

            $head = trim($head);
            echoLine($head);

            if (strstr($head, 'ID3') !== false) {
                echoLine("success");
            }
        }

        fclose($fp);
    }

    function freshMusicStatusAction()
    {
        $musics = Musics::findForeach();

        foreach ($musics as $music) {
            $music->hot = 1;
            $music->update();
        }
    }

    function fixUserSegmentAction()
    {
        $users = Users::find(['conditions' => 'experience > 0']);

        foreach ($users as $user) {

            $user_level = $user->calculateLevel();

            if ($user_level != $user->level) {
                echoLine($user->id, $user_level, $user->level);
                $user->level = $user_level;
            }

            $user_segment = $user->calculateSegment();

            if ($user_segment != $user->segment) {
                echoLine($user->id, $user_segment, $user->segment);
                $user->segment = $user_segment;
            }

            //$user->update();
        }

    }

    function fixRoomStatusAction()
    {
        $room = Rooms::findFirstById(416);
        $room->status = STATUS_OFF;
        $room->online_status = STATUS_OFF;
        $room->save();

        $share_histories = ShareHistories::findForeach();
        foreach ($share_histories as $share_history) {
            echoLine($share_history->data, $share_history->id);

        }

        $musics = Musics::findForeach();

        foreach ($musics as $music) {
            if (!$music->rank) {
                $music->rank = 1;
                $music->update();
            }
        }
    }

    function testTimeAction()
    {
        $time = time();
        $millisecond_time = millisecondTime();
        echoLine($time, $millisecond_time, $millisecond_time / 1000, microtime());

        echoLine(date("Ymd h:i:s", $time));
        echoLine(date("Ymd h:i:s", $millisecond_time));
    }

    function getConnectInfoAction()
    {
        $user = Users::findFirstById(117);
        $online_token = $user->getOnlineToken();
        echoLine($user->getUserFd(), $user->getIntranetIp(), $online_token);

        $current_room = \Rooms::findRoomByOnlineToken($online_token);
        $current_room_seat = \RoomSeats::findRoomSeatByOnlineToken($online_token);

        echoLine($current_room);
        echoLine($current_room_seat);
    }

    function unbindThirdAccountAction()
    {
        $user = Users::findFirstById(31194);
        echoLine($user->third_name, $user->third_unionid, $user->login_type);
        $user->third_name = 'test';
        $user->third_unionid = 'test';
        $user->login_type = USER_LOGIN_TYPE_SINAWEIBO;
        $user->update();
    }

    function userInfoAction()
    {
        $user = Users::findFirstById(31285);
        Users::uploadWeixinAvatar(31286, 'http://thirdqq.qlogo.cn/qqapp/1106728586/2C7E9E4E4D5D99C561239D414DFA3F4A/100');
        echoLine($user);

        $product_channel = ProductChannels::findFirstById(1);
        $user = \Users::findFirstByThirdUnionid($product_channel, '2997469905', 'sina');
        $user = Users::findFirstById(31279);
        echoLine($user);
    }

    function uploadDefaultAvatarAction()
    {
        $default_avatar0 = APP_ROOT . "public/images/default_avatar0.png";
        $default_avatar1 = APP_ROOT . "public/images/default_avatar1.png";

        StoreFile::upload($default_avatar0, APP_NAME . '/users/avatar/default_avatar0.png');
        StoreFile::upload($default_avatar1, APP_NAME . '/users/avatar/default_avatar1.png');
    }

    function testSecondAction()
    {
        echoLine(millisecondTime());

        Chats::sendSystemMessage(4, CHAT_CONTENT_TYPE_TEXT, "ss");

        $emchat = new \Emchat();
        $action = 'admin_message';
        $target_type = 'users';

        $ext = ['id' => 1, 'sender_id' => 1, 'receiver_id' => 4, 'created_at' => time(), 'content' => 'ddd', 'content_type' => 'text/plain'];

        $emchat->sendText("系统", 4, "3333");
    }

    function testGiftOrderAction()
    {
        echoLine(GiftOrders::findFirstById(30351));

        $num = GiftOrders::sum(['conditions' => 'user_id = :user_id: and gift_id=:gift_id:',
            'bind' => ['user_id' => 31279, 'gift_id' => 10], 'column' => 'gift_num']);
        echoLine($num);
    }

    function goodNumAction()
    {
        $db = \Users::getUserDb();
        $good_num_list_key = 'good_num_list';
        echoLine($db->zrange($good_num_list_key, 0, -1));
        $db->zadd($good_num_list_key, time(), 1000777);
    }

    function smsHistoryAction()
    {
        $sms_history = SmsHistories::findFirstById(554);
        echoLine($sms_history);

        $devices = Devices::findBy(['platform' => 'ios']);

        foreach ($devices as $device) {
            echoLine($device->idfa);
        }
    }

    function zipAction()
    {
        $path = APP_ROOT . "temp/test.txt";
        $filename = APP_ROOT . "temp/test.zip";
        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CREATE);   //打开压缩包
        $zip->addFile($path, basename($path));   //向压缩包中添加文件
        $zip->close();  //关闭压缩包

        $gift_resources = GiftResources::findForeach();
        foreach ($gift_resources as $resource) {
            $resource->resource_code = $resource->id;
            $resource->save();
        }

        $rooms = Rooms::getOfflineSilentRooms();
        echoLine(count($rooms));
    }

    function getRoomUserNum()
    {
        $room = Rooms::findFirstById(1000245);
        echoLine($room->getUserNum(), $room->getSilentUserNum());

        $gifts = Gifts::findForeach();

        foreach ($gifts as $gift) {
            if (!$gift->render_type) {
                $gift->render_type = 'gif';
                $gift->update();
            }
        }
    }

    function giveDiamondAction()
    {
        $user_id = 1103162;

        $user = Users::findFirstById($user_id);
        $opts = ['remark' => '系统赠送' . 60000 . '钻石', 'operator_id' => 1, 'mobile' => $user->mobile];
        \AccountHistories::changeBalance($user_id, ACCOUNT_TYPE_GIVE, 60000, $opts);
    }

    function createUnionAction()
    {
        $union = new Unions();
        $union->name = "test";
        $union->stauts = STATUS_ON;
        $union->auth_status = AUTH_WAIT;
        $union->save();
    }

    function createWithdrawHistoriesAction()
    {
        $withdraw_history = new WithdrawHistories();
        $withdraw_history->type = WITHDRAW_TYPE_UNION;
        $withdraw_history->union_id = 8;
        $withdraw_history->amount = 1000;
        $withdraw_history->status = WITHDRAW_STATUS_WAIT;
        $withdraw_history->save();

        echoLine(Users::findFirstById(1001347));
    }

    function getUnionInfoAction()
    {
        $union = Unions::findFirstById(1);
//        $union->user_id = 3;
//        echoLine($union->created_at_text);
        $union->amount = 100000;
        $union->update();
    }

    function fixStatAction()
    {
        $stats = Stats::find(['order' => 'id desc']);

        foreach ($stats as $stat) {
            $data = $stat->data;

            if ($data) {
                $stat->data_hash = json_decode($data, true);

                echoLine($stat->data_hash);
                $stat->newArpu();
                $stat->arpu();
                $stat->paidArpu();
                $stat->newPaidArpu();
                $stat->data = json_encode($stat->data_hash, JSON_UNESCAPED_UNICODE);
                $stat->update();
                echoLine($stat->data_hash);
            }
        }
    }

    function fixBirthDayAction()
    {
        $time = strtotime('1962-12-31');
        echoLine($time);
        $user = Users::findFirstById(1);
        $user->birthday = $time;
        $user->update();
        echoLine($user->birthday_text);
        echoLine(date("Ymd", -4954550400));
    }

    function fixUserConinsAction()
    {
        $withdraw_histories = WithdrawHistories::findForeach();

        foreach ($withdraw_histories as $history) {
            if (WITHDRAW_STATUS_SUCCESS != $history->status) {
                echoLine($history);

                $user = $history->user;
                $user->hi_coins += $history->amount;
                $user->update();
            }
        }

        $user = Users::findFirstById(1003455);
        $user->hi_coins = $user->hi_coins - 184;
        $user->update();
        echoLine($user->hi_coins);

        $total_amount = UserGifts::sum(['conditions' => 'user_id = :user_id:', 'bind' => ['user_id' => 1000555], 'column' => 'total_amount']);

        echoLine($total_amount / (100 / 4.5));

        $rooms = Rooms::find(['conditions' => 'user_type = :user_type: and lock != :lock:',
            'bind' => ['user_type' => USER_TYPE_ACTIVE, 'lock' => true]]);

        echoLine(count($rooms));

        $user = Users::findFirstById(379);
        echoLine($user->id_card_auth, $user->id_card_auth_text);
    }

    function addPermitIpAction()
    {
        $ip_list = "permit_ip_list";
        $hot_cache = \Users::getHotWriteCache();
        // 116.226.124.121 116.226.124.47
        $hot_cache->zadd($ip_list, time(), '116.226.124.121');

        $user = Users::findFirstById(31253);
        $user->province_id = 0;
        $user->city_id = 0;
        $user->sex = 0;
        $user->avatar = '';
        $user->save();

        $union_history = UnionHistories::findFirstBy(
            ['user_id' => 1003557, 'union_id' => 2, 'status' => STATUS_ON], 'id desc');
        $union_history->join_at = time() - 60 * 10;
        $union_history->update();
    }

    function setGoodNumAction()
    {
        $db = \Users::getUserDb();
        $good_num_list_key = 'good_num_list';
        $db->zrem($good_num_list_key, 1001);
        $db->zadd($good_num_list_key, time(), 1001);
        $db->zadd($good_num_list_key, time(), 1002);
        $db->zadd($good_num_list_key, time(), 1111);

        $array = [];
        for ($i = 1000; $i < 100000000; $i++) {
//            if (preg_match("/(?:(?:0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){5}|(?:9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)){5})\d/", $i)) {
//                $array[] = $i;
//                echoLine($i);
//                continue;
//            }

//            if (preg_match("/(?:(?:0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){3,}|(?:9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)){3,})\d/", $i)) {
//                $array[] = $i;
//                echoLine($i);
//                continue;
//            }

            if (preg_match("/([\d])\1{2,}/", $i)) {
                $array[] = $i;
                echoLine($i);
                continue;
            }
        }

        echoLine(count($array));

//        $db->zadd($good_num_list_key, time(), 10001);

        $union = Unions::findFirstById(1002);
        $union->type = UNION_TYPE_PRIVATE;
        $union->notice = '呵呵';
        $union->name = '呵呵';
        $union->user_id = 1001303;
        $union->auth_status = AUTH_SUCCESS;
        $union->avatar = '';
        $union->save();

        $user = Users::findFirstById(1001303);
        $user->union_id = $union->id;
        $user->union_type = $union->type;
        $user->update();
        echoLine($union);

        $user = Users::findFirstById(1001316);
        $user->union_id = 0;
        $user->union_type = 0;
        $user->update();
        echoLine($user->union_id);

        $union = Unions::findFirstById(4);
        echoLine($union);


    }

    function getChatListAction()
    {
        $user = Users::findFirstById(1001314);
        $user->union_id = 0;
        $user->union_type = 0;
        $user->update();
        $user = Users::findFirstById(31426);
        $chats = \Chats::findChatsList($user, 1, 20, SYSTEM_ID);

        foreach ($chats as $chat) {
            echoLine($chat, $chat->created_at_text);
        }

        echoLine(Users::findFirstById(1014243));

        $union_recommend_key = "union_recommend_list";

        $user_db = Users::getUserDb();

        $per_page = 10;
        $page = 1;
        $offset = $per_page * ($page - 1);
        $union_ids = $user_db->zrevrange($union_recommend_key, $offset, $offset + $per_page - 1, 'withscores');
        echoLine($union_ids);
        $unions = Unions::findByIds($union_ids);
        $total_entries = $user_db->zcard($union_recommend_key);
        echoLine($total_entries, count($unions));


        $pagination = new PaginationModel($unions, $total_entries, $page, $per_page);
    }

    function initRoomRealNumAction()
    {
        $rooms = Rooms::findForeach();
        $hot_cache = Rooms::getHotWriteCache();

        foreach ($rooms as $room) {
            if ($room->user_num > 0) {
                $user_ids = $hot_cache->zrange($room->getUserListKey(), 0, -1, true);
                echoLine($room->id);
                $real_user_list_key = $room->getRealUserListKey();
                foreach ($user_ids as $user_id => $time) {
                    $user = Users::findFirstById($user_id);

                    if ($user->isSilent()) {
                        echoLine("silent user", $user_id);
                        continue;
                    }

                    $hot_cache->zadd($real_user_list_key, $time, $user_id);
                }
            }
        }
    }

    function initSilentRoomsAction()
    {
        $name_file = APP_ROOT . "doc/room_topic.xls";
        $names = readExcel($name_file);

        foreach ($names as $name) {
            $title = $name[0];
            $topic = $name[1];

            $room = Rooms::findFirstByName($title);

            if ($room) {
                continue;
            }

            $cond['conditions'] = '(room_id = 0 or room_id is null) and user_type = ' . USER_TYPE_SILENT;
            $user = Users::findFirst($cond);

            $room = Rooms::createRoom($user, $title);
            $room->topic = $topic;
            $room->status = STATUS_OFF;
            $room->save();
        }
    }

    function generateStableRoomAction()
    {
        $per_page = 2;
        $last_room = Rooms::findLast();
        $last_room_id = $last_room->id;
        $total_page = ceil($last_room_id / $per_page);
        $page = mt_rand(1, $total_page);
        $rooms = Rooms::getOfflineSilentRooms($page, $per_page);

        echoLine(count($rooms));
        foreach ($rooms as $room) {
            $user = $room->user;

            if ($user->isInAnyRoom()) {
                info($user->id, $user->current_room_id, $room->id);
                continue;
            }

            Rooms::enterSilentRoom($room->id, $user->id);
            info($room->id);
        }
    }

    function initRoomsAction()
    {
        while (true) {
            $room = new Rooms();
            $room->status = STATUS_OFF;
            $room->online_status = STATUS_OFF;
            $room->product_channel_id = 1;
            $room->user_type = USER_TYPE_SILENT;
            $room->name = '';
            $room->topic = '';
            $room->user_id = 0;
            $room->password = '';
            $room->last_at = 0;
            $room->room_seat_id = 0;
            $room->audio_id = 0;
            $room->room_theme_id = 0;
            $room->save();

            echoLine($room->id);

            if ($room->id >= 1000000) {
                break;
            }
        }

        $users = Users::find(['conditions' => 'user_type = ' . USER_TYPE_ACTIVE . ' and (mobile != "" or mobile is not null)']);
        echoLine(count($users));
    }

    function getTotalRoomUserNunAction()
    {
        $hot_cache = Rooms::getHotWriteCache();
        $room_ids = $hot_cache->zrange(Rooms::getTotalRoomUserNumListKey(), 0, -1, true);

        foreach ($room_ids as $room_id => $num) {

            $room = Rooms::findFirstById($room_id);

            if ($num != $room->user_num) {
                echoLine($num, $room->user_num, $room_id);
            }
        }

        $hot_cache->zincrby(Rooms::getTotalRoomUserNumListKey(), 1, $this->id);
    }

    function initTotalUserNumAction()
    {
        $rooms = Rooms::findBy(['user_type' => USER_TYPE_ACTIVE]);
        $hot_cache = Rooms::getHotWriteCache();

        foreach ($rooms as $room) {
            if ($room->user_num > 0) {
                echoLine($room->user_num);
                $hot_cache->zadd(Rooms::getTotalRoomUserNumListKey(), $room->user_num, $room->id);
            }
        }
    }

    function initGiftOrdersAction()
    {
        $gift_orders = GiftOrders::findForeach();

        foreach ($gift_orders as $gift_order) {
            $gift_order->sendder_union_id = 8;
            $gift_order->receiver_union_id = 8;
            $gift_order->update();
        }
    }

    function initUnionsAction()
    {
        while (true) {
            $union = new Unions();
            $union->status = STATUS_OFF;
            $union->user_id = 0;
            $union->name = '';
            $union->product_channel_id = 0;
            $union->save();

            if ($union->id >= 1000) {
                echoLine($union->id);
                break;
            }
        }
    }

    function fixUnionIdAction()
    {
        $users = Users::find(['conditions' => 'union_id > 0']);

        foreach ($users as $user) {
            $user->uninon_id = 0;
            $user->uninon_type = 0;
            $user->update();
        }

        $orders = Orders::find(['conditions' => 'union_id > 0']);

        foreach ($orders as $order) {
            $order->union_id = 0;
            $order->union_type = 0;
            $order->update();
        }

        $account_histories = AccountHistories::find(['conditions' => 'union_id > 0']);

        foreach ($account_histories as $account_history) {
            $account_history->union_id = 0;
            $account_history->union_type = 0;
            $account_history->update();
        }

        $gift_orders = GiftOrders::find(['conditions' => 'sender_union_id > 0 or receiver_union_id > 0']);

        foreach ($gift_orders as $gift_order) {
            $gift_order->sender_union_id = 0;
            $gift_order->sender_union_type = 0;
            $gift_order->receiver_union_id = 0;
            $gift_order->receiver_union_type = 0;
            $gift_order->update();
        }

        $rooms = Rooms::find(['conditions' => 'union_id > 0']);

        foreach ($rooms as $room) {
            $room->union_id = 0;
            $room->union_type = 0;
            $room->update();
        }

        $withdraw_histories = WithdrawHistories::find(['conditions' => 'union_id > 0']);;

        foreach ($withdraw_histories as $withdraw_history) {
            $withdraw_history->union_id = 0;
            $withdraw_history->union_type = 0;
            $withdraw_history->update();
        }

        $union_histories = UnionHistories::find(['conditions' => 'union_id > 0']);;

        foreach ($union_histories as $union_history) {
            $union_history->union_id = 0;
            $union_history->union_type = 0;
            $union_history->update();
        }

        $unions = Unions::findForeach();

        foreach ($unions as $union) {
            $union->delete();
        }
    }

    function getUserUnionIdAction()
    {
        //9D9D2EDD2E70F03F1801882E6B8FC548 3B46F3B3FD190B9A4B21AFFE16149AAA 2C7E9E4E4D5D99C561239D414DFA3F4A
        $url = "https://graph.qq.com/oauth2.0/me";

        $res = httpGet($url, ['access_token' => '3B46F3B3FD190B9A4B21AFFE16149AAA', 'unionid' => 1], ['Content-Type' => 'text/json']);

        echoLine($res->raw_body);

        $user = Users::findFirstById(1001315);
        $user->sex = 0;
        $user->update();
    }

    function fixUserGiftAction()
    {
        $gift_orders = GiftOrders::findBy(['gift_id' => 8, 'name' => 'YSL口红']);

        foreach ($gift_orders as $gift_order) {

            $user = $gift_order->user;
            $user_gift = UserGifts::findFirstBy(['user_id' => $user->id, 'gift_id' => $gift_order->gift_id]);

            if ($user_gift) {
                $fix_user_gift = UserGifts::findFirstBy(['user_id' => $user->id, 'gift_id' => 27]);

                if ($fix_user_gift) {
                    $fix_user_gift->num += $user_gift->num;
                    $fix_user_gift->total_amount += $user_gift->total_amount;
                    $fix_user_gift->update();
                    $user_gift->delete();

                } else {

                    $user_gift->gift_id = 27;
                    $user_gift->update();
                    echoLine($user->id);
                }
            }

            $gift_order->gift_id = 27;
            $gift_order->update();
        }

        echoLine(count($gift_orders));
    }

    function readExceWordAction()
    {
        $file = APP_ROOT . "doc/words/word1.xlsx";
        $word_arrays = readExcel($file);

        foreach ($word_arrays as $word_array) {
            if (isset($word_array[3])) {
                $word = $word_array[3];
                $word = trim($word);

                if (!$word) {
                    echoLine("=====");
                    continue;
                }

                echoLine($word);

                $banned_word = BannedWords::findFirstByWord($word);

                if ($banned_word) {
                    continue;
                }

                $new_banned_word = new BannedWords();
                $new_banned_word->word = $word;
                $new_banned_word->save();
            }
        }
    }

    function readTextAction()
    {
        $files = [];

        foreach (glob(APP_ROOT . 'doc/words/*.txt') as $filename) {
            $basename = basename($filename);
            $files[] = $basename;
        }

        foreach ($files as $file) {

            $file = APP_ROOT . "doc/words/" . $file;

            $f = fopen($file, 'r');

            while ($word = fgets($f)) {

                $word = trim($word);

                if (!$word) {
                    echoLine("=====");
                    continue;
                }

                echoLine($word);

                $banned_word = BannedWords::findFirstByWord($word);

                if ($banned_word) {
                    continue;
                }

                $new_banned_word = new BannedWords();
                $new_banned_word->word = $word;
                $new_banned_word->save();
            }

            fclose($f);
        }
    }

    function bannedWordAction()
    {
        $res = BannedWords::checkWord("嫖");
        echoLine($res);

        $start = time() - 61 * 60;
        $end = time() - 60;

        $cond = [
            'conditions' => 'room_id > 0 and created_at >= :start: and created_at <= :end:',
            'bind' => ['start' => $start, 'end' => $end],
            'columns' => 'distinct room_id'];

        $gift_orders = GiftOrders::find($cond);

        $has_income_room_ids = [];

        foreach ($gift_orders as $gift_order) {

            $room = Rooms::findFirstById($gift_order->room_id);

            if (!$room) {
                info($gift_order->room_id);
                continue;
            }

            if (!$room->checkRoomSeat()) {
                info("room_seat_is_null", $room->id);
                continue;
            }

            if ($room->isForbiddenHot()) {
                info("isForbiddenHot", $room->id);
                continue;
            }

            if ($room->getRealUserNum() < 1) {
                info("room_no_user", $room->id);
                continue;
            }

            if ($room->lock) {
                info("room_seat_is_lock", $room->id);
                continue;
            }

            if ($room->isHot()) {
                continue;
            }

            $cond = [
                'conditions' => 'room_id = :room_id: and created_at >= :start: and created_at <= :end:',
                'bind' => ['start' => $start, 'end' => $end, 'room_id' => $room->id],
                'column' => 'amount'
            ];

            $income = GiftOrders::sum($cond);

            $has_income_room_ids[$room->id] = $income;
        }

        arsort($has_income_room_ids);

        info($has_income_room_ids);
        $total_room_ids = [];

        foreach ($has_income_room_ids as $has_income_room_id) {

            $total_room_ids[] = $has_income_room_id;

            if (count($total_room_ids) >= 20) {
                info($total_room_ids, count($total_room_ids), 20);
                break;
            }
        }

        echoLine($total_room_ids);

    }

    function fixHiCoinHistoriesAction()
    {
        $gift_orders = GiftOrders::findForeach();

        foreach ($gift_orders as $gift_order) {
            if ($gift_order->user->isSilent()) {
                echoLine($gift_order->id, $gift_order->user_id);
            }
        }

        foreach ($gift_orders as $gift_order) {

            $user = $gift_order->user;

            if (!$user) {
                continue;
            }

            $hi_coin_history = HiCoinHistories::findFirstBy(['user_id' => $user->id, 'gift_order_id' => $gift_order->id]);

            if (!$hi_coin_history) {

                $hi_coin_history = new HiCoinHistories();
                $hi_coin_history->user_id = $user->id;
                $hi_coin_history->gift_order_id = $gift_order->id;
                $amount = $gift_order->amount;
                $hi_coins = $amount * $user->rateOfDiamondToHiCoin();
                $hi_coins = intval($hi_coins * 10000) / 10000;
                $hi_coin_history->hi_coins = $hi_coins;
                $hi_coin_history->fee_type = HI_COIN_FEE_TYPE_RECEIVE_GIFT;
                $hi_coin_history->remark = "接收礼物总额: $amount 收益:" . $hi_coins;
                $hi_coin_history->product_channel_id = $user->product_channel_id;
                $hi_coin_history->union_id = $user->union_id;
                $hi_coin_history->union_type = $user->union_type;
                $hi_coin_history->created_at = $gift_order->created_at;
                $hi_coin_history->save();
            }
        }
    }

    function fixUserHiCoinsAction()
    {
        $users = Users::find(['conditions' => 'hi_coins > 0']);
        echoLine(count($users));

        $i = 0;
        $boracast_num = 0;


        foreach ($users as $user) {
            $i++;

            $total_amount = UserGifts::sum(['conditions' => 'user_id = :user_id:', 'bind' => ['user_id' => $user->id], 'column' => 'total_amount']);
            //echoLine($total_amount, $user->id, $user->hi_coins);

            $rate = $user->rateOfDiamondToHiCoin();

            if ($rate == 0.05) {
                $boracast_num++;
            }

            //echoLine($rate);

            if ($total_amount < 1) {
                echoLine("======", $i, $total_amount, $user->id, $user->hi_coins);
                continue;
            }

            $hi_coins = $total_amount * $rate;

            $widthdraw_hi_coins = WithdrawHistories::sum(['conditions' => 'user_id = :user_id: and status = :status:',
                'bind' => ['user_id' => $user->id, 'status' => WITHDRAW_STATUS_SUCCESS], 'column' => 'amount']);

            if ($widthdraw_hi_coins > 0) {
                $hi_coins = $hi_coins - $widthdraw_hi_coins;
            }

            $hi_coins = intval($hi_coins * 10000) / 10000;

            if ($hi_coins - $user->hi_coins >= 0.001) {
                echoLine($rate, "总金额", $total_amount, "用户id", $user->id, "用户hicoins", $user->hi_coins, "hicoins", $hi_coins, "已提现", $widthdraw_hi_coins);
            } else {
                continue;
            }

//            $user->hi_coins = $hi_coins;
//            $user->update();
        }

        echoLine($boracast_num);
    }

    function testDataTypeAction()
    {
//        $user = Users::findFirstById(1);
//        $user->experience = 123441;
//        $user->update();

        $user = Users::findFirstById(1);
        echoLine($user->experience);

        $hi_coin_histories = HiCoinHistories::find();

        foreach ($hi_coin_histories as $hi_coin_history) {
            $hi_coin_history->delete();
        }


        $users = Users::find(['conditions' => 'hi_coins > 0']);

        foreach ($users as $user) {
            $old_hi_coin_history = \HiCoinHistories::findUserLast($user->id);

            if (!$old_hi_coin_history || abs($old_hi_coin_history->balance - $user->hi_coins) >= 0.001) {
                echoLine($user->id, $old_hi_coin_history->balance, $user->hi_coins);
            }
        }

        $withdraw_histories = WithdrawHistories::findBy(['status' => WITHDRAW_STATUS_SUCCESS]);

        foreach ($withdraw_histories as $withdraw_history) {

            $user = $withdraw_history->user;

            if (!$user) {
                continue;
            }

            $hi_coin_history = HiCoinHistories::findFirstBy(['user_id' => $withdraw_history->user->id, 'withdraw_history_id' => $withdraw_history->id]);

            if (!$hi_coin_history) {

                $hi_coin_history = new HiCoinHistories();
                $hi_coin_history->user_id = $user->id;
                $hi_coin_history->withdraw_history_id = $withdraw_history->id;
                $amount = $withdraw_history->amount;
                $hi_coin_history->hi_coins = $amount;
                $hi_coin_history->fee_type = HI_COIN_FEE_TYPE_WITHDRAW;
                $hi_coin_history->remark = "提现金额:" . $amount;
                $hi_coin_history->product_channel_id = $user->product_channel_id;
                $hi_coin_history->union_id = $user->union_id;
                $hi_coin_history->union_type = $user->union_type;
                $hi_coin_history->created_at = $withdraw_history->created_at;
                $hi_coin_history->save();
            }
        }
    }

    function enterAction()
    {
        $user = Users::findFirstById(52);
        $room = $user->room;

        $users = $room->selectSilentUsers(2);

        foreach ($users as $user) {

            if ($user->isInAnyRoom()) {
                info("user_in_other_room", $user->id, $user->current_room_id, $room->id);
                continue;
            }

            $user_gift = \UserGifts::findFirstOrNew(['user_id' => $user->id, 'gift_id' => 47]);
            $gift = \Gifts::findFirstById(47);

            $gift_amount = $gift->amount;
            $gift_num = 1;
            $user_gift->gift_id = $gift->id;
            $user_gift->name = $gift->name;
            $user_gift->amount = $gift_amount;
            $user_gift->num = $gift_num + intval($user_gift->num);
            $user_gift->total_amount = $gift_amount * $gift_num + intval($user_gift->total_amount);
            $user_gift->pay_type = $gift->pay_type;
            $user_gift->gift_type = $gift->type;
            $user_gift->status = STATUS_ON;
            $user_gift->expire_at = time() + 86400 * 365;
            $user_gift->save();

            Rooms::addWaitEnterSilentRoomList($user->id);
            Rooms::enterSilentRoom($room->id, $user->id);
        }
    }

    function fixRankListAction()
    {
        $db = Users::getUserDb();

        $fields = ['charm', 'wealth'];
        $list_types = ['day', 'week', 'total'];

        foreach ($fields as $field) {
            foreach ($list_types as $list_type) {
                $db->zclear("last_" . $list_type . "_" . $field . "_rank_list");
            }
        }

    }

    function fixUserDataAction()
    {
        $user = Users::findFirstById(1063193);
        //$user->third_unionid = '';
        //$user->update();

        $new_user = Users::findFirstById(1057828);

        $orders = Orders::findBy(['user_id' => $user->id]);
        $payments = Payments::findBy(['user_id' => $user->id]);
        $gift_orders = GiftOrders::findBy(['user_id' => $user->id]);
        $send_gift_orders = GiftOrders::findBy(['sender_id' => $user->id]);
        $user_gifts = UserGifts::findBy(['user_id' => $user->id]);
        $union_histories = UnionHistories::findBy(['user_id' => $user->id]);
        $hi_coins_histories = HiCoinHistories::findBy(['user_id' => $user->id]);
        $account_histories = AccountHistories::findBy(['user_id' => $user->id], 'id desc');
        $gold_histories = GoldHistories::findBy(['user_id' => $user->id], 'id desc');

        echoLine("ssss");
//        foreach ($orders as $order) {
//            $order->user_id = $new_user->id;
//            $order->update();
//        }
//
//        foreach ($payments as $payment) {
//            $payment->user_id = $new_user->id;
//            $payment->update();
//        }

        foreach ($gift_orders as $gift_order) {
            $gift_order->user_id = $new_user->id;
            $gift_order->update();
        }

        foreach ($send_gift_orders as $send_gift_order) {
            $send_gift_order->sender_id = $new_user->id;
            $send_gift_order->update();
        }

        foreach ($user_gifts as $user_gift) {
            $new_user_gift = UserGifts::findFirstBy(['gift_id' => $gift_order->gift_id, 'user_id' => $gift_order->user_id]);

            if (!$new_user_gift) {
                $user_gift->user_id = $new_user->id;
                $user_gift->update();
                continue;
            }

            $new_user_gift->num += $user_gift->num;
            $new_user_gift->total_amount += $user_gift->total_amount;
            $new_user_gift->expire_at += $user_gift->expire_at;
            $new_user->update();
        }

//        foreach ($union_histories as $union_history) {
//            $new_union_history = new UnionHistories();
//            $new_union_history->user_id = $new_user->id;
//            $new_union_history->status = $union_history->status;
//            $new_union_history->union_id = $union_history->union_id;
//            $new_union_history->join_at = $union_history->join_at;
//            $new_union_history->exit_at = $union_history->exit_at;
//            $new_union_history->created_at = $union_history->created_at;
//            $new_union_history->updated_at = $union_history->updated_at;
//            $new_union_history->union_type = $union_history->union_type;
//            $new_union_history->save();
//        }

//        foreach ($hi_coins_histories as $hi_coins_history) {
//            $new_hi_coins_history = new HiCoinHistories();
//            $new_hi_coins_history->user_id = $new_user->id;
//            $new_hi_coins_history->product_channel_id = $new_user->product_channel_id;
//            $new_hi_coins_history->gift_order_id = $hi_coins_history->gift_order_id;
//            $new_hi_coins_history->remark = $hi_coins_history->remark;
//            $new_hi_coins_history->hi_coins = $hi_coins_history->hi_coins;
//            $new_hi_coins_history->fee_type = $hi_coins_history->fee_type;
//            $new_hi_coins_history->union_type = $hi_coins_history->union_type;
//            $new_hi_coins_history->union_id = $hi_coins_history->union_id;
//            $new_hi_coins_history->reward_at = $hi_coins_history->reward_at;
//            $new_hi_coins_history->withdraw_history_id = $hi_coins_history->withdraw_history_id;
//            $new_hi_coins_history->operator_id = $hi_coins_history->operator_id;
//            $new_hi_coins_history->save();
//        }

        foreach ($gold_histories as $gold_history) {
            $new_gold_history = new GoldHistories();
            $new_gold_history->user_id = $new_user->id;
            $new_gold_history->product_channel_id = $new_user->product_channel_id;
            $new_gold_history->remark = $gold_history->remark;
            $new_gold_history->amount = $gold_history->amount;
            $new_gold_history->fee_type = $gold_history->fee_type;
            $new_gold_history->operator_id = $gold_history->operator_id;
            $new_gold_history->save();
        }

        foreach ($account_histories as $account_history) {
            $new_account_history = new AccountHistories();
            $new_account_history->user_id = $new_user->id;
            $new_account_history->amount = $account_history->amount;
            $new_account_history->order_id = $account_history->order_id;
            $new_account_history->gift_order_id = $account_history->gift_order_id;
            $new_account_history->fee_type = $account_history->fee_type;
            $new_account_history->remark = $account_history->remark;
            $new_account_history->operator_id = $account_history->operator_id;
            $new_account_history->mobile = $account_history->mobile;
            $new_account_history->union_id = $account_history->union_id;
            $new_account_history->union_type = $account_history->union_type;
            $new_account_history->save();
        }

        $new_user->experience += $user->experience;
        $new_user->hi_coins += $user->hi_coins;
        $new_user->diamond += $user->hi_coins;
        $new_user->pay_amount += $user->pay_amount;
        $new_user->union_id = $user->union_id;
        $new_user->union_type = $user->union_type;
        $new_user->id_card_auth = $user->id_card_auth;
        $new_user->charm_value += $user->charm_value;
        $new_user->wealth_value += $user->wealth_value;
        $new_user->union_charm_value += $user->union_charm_value;
        $new_user->union_wealth_value += $user->union_wealth_value;
        $new_user->update();
    }

    function testAction()
    {
        info(valueToStr(1032442333444333));

        $user = Users::findById(1195377);
        $user->organisation = 0;
        $user->update();

    }

    function giveGoldAction()
    {
        $user_id = 31654;
        $user = Users::findFirstById(31654);
        $amount = 2334;
        $opts = ['operator_id' => 1, 'remark' => "系统赠送金币" . $amount . "个"];
        $gold_histories = GoldHistories::changeBalance($user_id, GOLD_TYPE_GIVE, $amount, $opts);
    }

    function fixUserRegisterAtAction()
    {
        $num = Users::count(['conditions' => 'register_at = created_at and id > 31361 and id < 1030412']);
        echoLine($num);
        $cond = ['conditions' => '(third_unionid is not null) and register_at is null'];
        echoLine(Users::count($cond));
        $users = Users::findForeach($cond);

        $i = 0;

        foreach ($users as $user) {
            echoLine($user->id, $user->login_type_text, $user->third_unionid, $user->created_at_text, $user->register_at_text);
            ///$user->register_at = $user->created_at;
            //$user->update();
            $i++;
        }

        echoLine($i);

        $user = Users::findFirstById(1062410);
        echoLine($user->register_at);
        $user->register_at = $user->created_at;
        $user->update();
        echoLine($user->register_at);

        $stat_db = Stats::getStatDb();
        $all_stat_key = 'stats_keys_' . date('Ymd', (time() - 1800));
        $total = $stat_db->zcard($all_stat_key);
        info($all_stat_key, 'total', $total);

        $stat_keys = $stat_db->zrevrange($all_stat_key, 0, -1);
        $day = "stats_" . date('Ymd', time());
        $fields = Stats::$STAT_FIELDS;

        foreach ($stat_keys as $stat_key) {
            $date_key = $day . "_user_" . $stat_key . "_register_num";
            if ($stat_db->get($date_key)) {
                echoLine($date_key, $stat_db->get($date_key));
            }
        }
    }


    function oldVersionUsersAction()
    {

        $users = Users::count(['conditions' => 'register_at > 0 and platform = "android" and version_code != ""']);
        echoLine($users);
        $i = 0;

        foreach ($users as $user) {
            if ($user->version_code < 5 && $user->last_at > time() - 15 * 86400) {
                $i++;
            }
        }

        echoLine($i);
    }

    function clearRankInfo()
    {
        $db = Users::getUserDb();

        $day_key = "day_charm_rank_list_" . date("Ymd");
        $start = date("Ymd", strtotime("last sunday next day", time()));
        $end = date("Ymd", strtotime("next monday", time()) - 1);
        $week_key = "week_charm_rank_list_" . $start . "_" . $end;
        $total_key = "total_charm_rank_list";

        $score = $db->zscore($day_key, 1057791);
        $db->zadd($day_key, $score, 153717);
        $db->zrem($day_key, 1057791);

        $score = $db->zscore($week_key, 1057791);
        $db->zadd($week_key, $score, 153717);
        $db->zrem($week_key, 1057791);

        $score = $db->zscore($total_key, 1057791);
        $db->zadd($total_key, $score, 153717);
        $db->zrem($total_key, 1057791);
    }

    function check()
    {
        $with_draw_histories = WithdrawHistories::find(['conditions' => 'status = ' . WITHDRAW_STATUS_WAIT]);

        foreach ($with_draw_histories as $draw_history) {
            $hi_coin_history = HiCoinHistories::findFirstBy(['user_id' => $draw_history->user_id, 'fee_type' => HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND]);

            if ($hi_coin_history) {
                if ($draw_history->user->hi_coins < $draw_history->amount) {
                    echoLine($draw_history->user_id);
                }
            }
        }

        $orders = Orders::find(['conditions' => 'created_at >= ' . beginOfDay()]);
    }

    function fixRoomInfoAction()
    {
        $room = Rooms::findFirstById(1010149);

        $hot_cache = Rooms::getHotReadCache();
        $key = $room->getRealUserListKey();

        $user_ids = $hot_cache->zrevrange($key, 0, -1, true);

        foreach ($user_ids as $user_id => $time) {
            echoLine($user_id, date("Y-m-d H:i:s", $time));
        }

        $device = Devices::findFirstById(82301);
        $device->device_no = '';
        $device->save();
    }

    function checkGiftOrderToHiCoinsAction()
    {
        $gift_orders = GiftOrders::findBy(['receiver_union_id' => 1001, 'pay_type' => GIFT_PAY_TYPE_DIAMOND]);
        foreach ($gift_orders as $gift_order) {
            $hi_coin_history = HiCoinHistories::findFirstBy(['gift_order_id' => $gift_order->id]);

            if (!$hi_coin_history) {
                echoLine($gift_order->id);
            }

            if (!$hi_coin_history) {
                echoLine($gift_order->id);
            }
        }

        $gift_order = GiftOrders::findFirstById(57817);
        echoLine($gift_order);

        $current_day = intval(date('d'));
        $time = time() - $current_day * 86400 - 3600;
        $start = beginOfMonth($time);
        $end = endOfMonth($time);

        $gift_orders = GiftOrders::find(
            [
                'conditions' => 'sender_union_id = 1001 and pay_type = "diamond" and created_at >= :start: and created_at <= :end:',
                'bind' => ['start' => $start, 'end' => $end],
                'columns' => 'distinct user_id'
            ]);

        $user_ids = [];

        foreach ($gift_orders as $gift_order) {
            $user_ids[] = $gift_order->user_id;
        }


        echoLine($user_ids);
        $users = Users::findByIds($user_ids);

        $res = ['用户id', '财富值'];
        $data = [];

        foreach ($users as $user) {
            $amount = GiftOrders::sum([
                'conditions' => 'sender_id = ' . $user->id . ' and sender_union_id = 1001 and pay_type = "diamond" and created_at >= :start: and created_at <= :end:',
                'bind' => ['start' => $start, 'end' => $end],
                'column' => 'amount'
            ]);

            if ($amount > 0) {
                echoLine($user->id, $amount);
                $data[] = [$user->id, $amount];
            }
        }

        $res = writeExcel($res, $data, 'union_income_1001.xls', true);
        echoLine($res);

        $union = Unions::findFirstById(1009);
        $user = Users::findFirstById(1010438);
        echoLine($user->union_id);
        echoLine($union);


        echoLine(date("Ymd H:i:s", beginOfWeek()));
        echoLine(date("Ymd H:i:s", endOfWeek()));

        $withdraw_history = WithdrawHistories::findFirstById(71);
        $withdraw_history->delete();

        $withdraw_histories = WithdrawHistories::find(
            [
                'conditions' => 'status = :status:',
                'bind' => ['status' => WITHDRAW_STATUS_WAIT]
            ]);

        foreach ($withdraw_histories as $withdraw_history) {

            if ($withdraw_history) {
                $withdraw_history->status = WITHDRAW_STATUS_FAIL;
                $withdraw_history->save();
            }
        }

        $hi_coin_histories = HiCoinHistories::find(['conditions' => 'withdraw_history_id > 0']);

        foreach ($hi_coin_histories as $hi_coin_history) {
            if ($hi_coin_history->withdraw_history_id) {
                $withdraw_history = WithdrawHistories::findFirstById($hi_coin_history->withdraw_history_id);

                if ($withdraw_history) {
                    echoLine($withdraw_history);
                    $withdraw_history->status = WITHDRAW_STATUS_SUCCESS;
                    $withdraw_history->update();
                }

            }
        }

    }

    function fixRoomIncome()
    {
        $cond = [
            'conditions' => 'room_id > 0 and pay_type = :pay_type: and gift_type = :gift_type: and status = :status:',
            'bind' => ['pay_type' => GIFT_PAY_TYPE_DIAMOND, 'gift_type' => GIFT_TYPE_COMMON, 'status' => GIFT_ORDER_STATUS_SUCCESS]
        ];

        $gift_orders = GiftOrders::findForeach($cond);
        $room_db = Users::getUserDb();

        foreach ($gift_orders as $gift_order) {

            if ($gift_order->sender->isSilent()) {
                continue;
            }

            $amount = $gift_order->amount;
            $room_id = $gift_order->room_id;

            if ($amount > 0 && $room_id) {

                //info($room_id, $amount);

                $room = Rooms::findFirstById($room_id);

                if ($room) {

                    $created_at = $gift_order->created_at;
                    $stat_at = date("Ymd", $created_at);

                    echoLine($room->generateStatIncomeDayKey($stat_at), $room->generateSendGiftUserDayKey($stat_at), $room->generateSendGiftNumDayKey($stat_at), $amount, $gift_order->gift_num, $room_id, $gift_order->sender_id);

                    $room_db->zadd($room->generateSendGiftUserDayKey($stat_at), $created_at, $gift_order->sender_id);

                    if ($created_at >= beginOfDay()) {
                        continue;
                    }

                    $room_db->zincrby($room->generateStatIncomeDayKey($stat_at), $amount, $room_id);
                    $room_db->zincrby($room->generateSendGiftNumDayKey($stat_at), $gift_order->gift_num, $room_id);
                }
            }
        }
    }

    function fixRoomIncomeTodayAction()
    {
        $cond = [
            'conditions' => 'room_id > 0 and pay_type = :pay_type: and gift_type = :gift_type: and status = :status: and created_at >= :start: and created_at <= :end:',
            'bind' => ['pay_type' => GIFT_PAY_TYPE_DIAMOND, 'gift_type' => GIFT_TYPE_COMMON, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'start' => beginOfDay(), 'end' => endOfDay()],
            'columns' => 'distinct room_id'
        ];

        $gift_orders = GiftOrders::find($cond);

        $room_db = Users::getUserDb();

        $room_ids = [];

        foreach ($gift_orders as $gift_order) {

            if ($gift_order->room_id) {
                $room_ids[] = $gift_order->room_id;
            }
        }

        $rooms = Rooms::findByIds($room_ids);

        echoLine(count($rooms));
        foreach ($rooms as $room) {
            $cond = [
                'conditions' => 'room_id = :room_id: and pay_type = :pay_type: and gift_type = :gift_type: and status = :status: and created_at >= :start: and created_at <= :end:',
                'bind' => ['pay_type' => GIFT_PAY_TYPE_DIAMOND, 'room_id' => $room->id, 'gift_type' => GIFT_TYPE_COMMON, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'start' => beginOfDay(), 'end' => endOfDay()],
                'column' => 'amount'
            ];

            $amount = GiftOrders::sum($cond);

            $cond = [
                'conditions' => 'room_id = :room_id: and pay_type = :pay_type: and gift_type = :gift_type: and status = :status: and created_at >= :start: and created_at <= :end:',
                'bind' => ['pay_type' => GIFT_PAY_TYPE_DIAMOND, 'room_id' => $room->id, 'gift_type' => GIFT_TYPE_COMMON, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'start' => beginOfDay(), 'end' => endOfDay()],
                'column' => 'gift_num'
            ];

            $gift_num = GiftOrders::sum($cond);

            echoLine($room->id, $amount, $gift_num);

            $room_db->zadd($room->generateStatIncomeDayKey(date("Ymd")), $amount, $room->id);
            $room_db->zadd($room->generateSendGiftNumDayKey(date("Ymd")), $gift_num, $room->id);
        }
    }

    function checkHicoinAction()
    {
        $cond = [
            'conditions' => 'pay_type = :pay_type: and gift_type = :gift_type: and status = :status:',
            'bind' => ['pay_type' => GIFT_PAY_TYPE_DIAMOND, 'gift_type' => GIFT_TYPE_COMMON, 'status' => GIFT_ORDER_STATUS_SUCCESS]
        ];


        $gift_orders = GiftOrders::findForeach($cond);

        foreach ($gift_orders as $gift_order) {

            $hi_coin_history = HiCoinHistories::findFirstBy(['user_id' => $gift_order->user_id, 'gift_order_id' => $gift_order->id]);

            if (!$hi_coin_history) {
                echoLine($gift_order->id, $gift_order->user_id);
            }
        }

        $db = Users::getUserDb();
        $charm_key = "qing_ming_activity_charm_list_" . 20180405 . "_" . 20180407;
        $wealth_key = "qing_ming_activity_wealth_list_" . 20180405 . "_" . 20180407;
        $db->zrem($charm_key, 100102);
        $db->zrem($wealth_key, 100102);
    }

    function test1Action()
    {
        echoLine(valueToStr(1310200));
    }


    function pushSystemMessageAction()
    {
        $content = <<<EOF
#幸运号码#恭喜您抽中幸运号码，请联系客服QQ：3407150190获得幸运号，Hi语音感谢您的支持。Hi语音官方客服
EOF;

        $user_ids = [1028930, 1000842, 1087735, 1004867, 1039689
            , 1059364, 1051860, 1088331, 1053196, 1067607, 1007017, 1057113
        ];
        //$users = Users::findForeach(['conditions' => 'register_at > 0']);

        foreach ($user_ids as $user_id) {
            Chats::sendSystemMessage($user_id, CHAT_CONTENT_TYPE_TEXT, $content);
        }

        $db = \Users::getUserDb();
        $res = $db->zincrby('www', 1, 33);
        echoLine($res);

        $gift_order = GiftOrders::findFirstById(50880);
        echoLine($gift_order);


        $activity = ActivityHistories::findFirstById(181);
        echoLine($activity);

        $prize_types = [2 => 10, 4 => 10, 6 => 10, 7 => 100, 8 => 10];

        foreach ($prize_types as $prize_type => $num) {
            $key = 'lucky_draw_prize_' . $prize_type;
            $cache = \Users::getHotReadCache();
            $res = $cache->get($key);
            echoLine($res, $prize_type);
            $cache->set($key, $num);
        }
    }

    function testRandomAction()
    {
        $random = 76;
        $type = 5;

        switch ($random) {

            case 1 <= $random && $random <= 40: //40%
                $type = 5;
                break;
            case $random > 40 && $random <= 65: //25%
                $type = 3;
                break;
            case $random > 65 && $random <= 75: //10%
                $type = 1;
                break;
            case $random > 75 && $random <= 85: //10%
                $type = 7;
                break;
            case $random > 85 && $random <= 86: //1%
                $type = 2;
                break;
            case $random < 86 && $random <= 89: //3%
                $type = 4;
                break;
            case $random < 89 && $random <= 93: //4%
                $type = 8;
                break;
            case $random > 93 && $random <= 100: //7%
                $type = 6;
                break;
        }
        echoLine($type);

        $key = 'lucky_draw_num_activity_id_' . 3; //减去用户抽取次数
        $day_user_key = 'lucky_draw_activity_id_' . 3 . '_user' . date("Y-m-d"); //记录每天抽奖的人数
        $day_num_key = 'lucky_draw_activity_id_' . 3 . '_num' . date("Y-m-d"); //记录每天抽奖的次数

        $db = \Users::getUserDb();

        echoLine($db->zcard($day_user_key), $db->get($day_num_key));

        $day_user_key = 'obtain_lucky_draw_activity_id_3_user' . date("Y-m-d"); //记录每天获得抽奖的人数
        $day_num_key = 'obtain_lucky_draw_activity_id_3_num' . date("Y-m-d"); //记录每天获得抽奖的次数
        echoLine($db->zcard($day_user_key), $db->get($day_num_key));
    }

    function uploadSystemAvatarAction()
    {
        $file = APP_ROOT . "/public/images/system_avatar.png";
        $user = Users::findFirstById(1);

        $user->updateAvatar($file);

        $client = new \services\SwooleClient('172.16.253.39', 9508, 1);
        if (!$client->connect()) {
            info("Exce connect fail");
            return false;
        }

        $device = Devices::findFirstById(7);
        if ($device->inWhiteList()) {
            echoLine("ssss");
        }
    }

    function orderAgesAction()
    {
        $payments = Payments::findForeach(['conditions' => 'pay_status = :pay_status:', 'bind' => ['pay_status' => PAYMENT_PAY_STATUS_SUCCESS]]);

        $age_user_num = ['total' => 0];
        $age_amount_num = ['total' => 0];
        $user_ids = [];

        foreach ($payments as $payment) {

            $age = $payment->user->age;

            if (!$age) {
                echoLine($payment->user->id, "年龄为空");
                continue;
            }

            $age_amount_num['total'] += $payment->paid_amount;

            if (isset($age_user_num[$age])) {
                $age_amount_num[$age] += $payment->paid_amount;
            } else {
                $age_amount_num[$age] = $payment->paid_amount;
            }

            if (in_array($payment->user_id, $user_ids)) {
                continue;
            }

            $user_ids[] = $payment->user_id;

            $age_user_num['total'] += 1;

            if (isset($age_user_num[$age])) {
                $age_user_num[$age] += 1;
            } else {
                $age_user_num[$age] = 1;
            }
        }

        echoLine("付费总人数:{$age_user_num['total']}", "付费总金额:{$age_amount_num['total']}");

        $total = $age_user_num['total'];
        $total_amount = $age_amount_num['total'];

        foreach ($age_user_num as $age => $num) {

            if ('total' == $age) {
                echoLine($age, $num);
                continue;
            }

            $rate = sprintf("%0.2f", $num * 100 / $total);
            $avg = sprintf("%0.2f", $age_amount_num[$age] / $age_user_num[$age]);
            echoLine($age . "岁付费人数:{$age_user_num[$age]},占比{$rate}%, 付费总额:{$age_amount_num[$age]},人均{$avg}");
        }
    }


    function idcardAuthIncomeAction()
    {
        $payments = Payments::findForeach(['conditions' => 'pay_status = :pay_status:', 'bind' => ['pay_status' => PAYMENT_PAY_STATUS_SUCCESS]]);
        $recharge_amount = 0;
        $hi_coins = 0;
        $withdraw_hi_coins = 0;
        $exchange_hi_coins = 0;

        foreach ($payments as $payment) {
            $user = $payment->user;

            if ($user->isIdCardAuth() && !$user->isCompanyUser()) {
                $recharge_amount += $payment->amount;
                echoLine($user->id, $payment->amount);
            }
        }

        $hi_coin_histories = HiCoinHistories::findForeach(
            [
                'conditions' => 'fee_type != :fee_type1: and fee_type != :fee_type2: and fee_type != :fee_type3:',
                'bind' => ['fee_type1' => HI_COIN_FEE_TYPE_WITHDRAW, 'fee_type2' => HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND, 'fee_type3' => HI_COIN_FEE_TYPE_WITHDRAW_RETURN]
            ]);

        foreach ($hi_coin_histories as $hi_coin_history) {
            $user = $hi_coin_history->user;

            if ($user->isIdCardAuth() && !$user->isCompanyUser()) {
                $hi_coins += $hi_coin_history->hi_coins;
                echoLine("hi_coins", $hi_coin_history->hi_coins);
            }
        }

        $hi_coin_histories = HiCoinHistories::findForeach(
            [
                'conditions' => 'fee_type = :fee_type1:',
                'bind' => ['fee_type1' => HI_COIN_FEE_TYPE_WITHDRAW]
            ]);

        foreach ($hi_coin_histories as $hi_coin_history) {
            $user = $hi_coin_history->user;

            if ($user->isIdCardAuth() && !$user->isCompanyUser()) {
                $withdraw_hi_coins += abs($hi_coin_history->hi_coins);
                echoLine("withdraw_hi_coins", $hi_coin_history->hi_coins);
            }
        }

        $hi_coin_histories = HiCoinHistories::findForeach(
            [
                'conditions' => 'fee_type = :fee_type1:',
                'bind' => ['fee_type1' => HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND]
            ]);

        foreach ($hi_coin_histories as $hi_coin_history) {
            $user = $hi_coin_history->user;

            if ($user->isIdCardAuth() && !$user->isCompanyUser()) {
                $exchange_hi_coins += abs($hi_coin_history->hi_coins);
                echoLine("exchange_hi_coins", $hi_coin_history->hi_coins);
            }
        }

        echoLine("充值：{$recharge_amount} 总收益：{$hi_coins} 提现：{$withdraw_hi_coins} 兑换：{$exchange_hi_coins}");
    }

    function test2Action()
    {
        $array = [2 => 4, 3 => 5, 6 => 1];

        uksort($array, function ($a, $b) use ($array) {

            echoLine($a, $b);
            if ($array[$a] == $array[$b]) {
                return 0;
            }
            return $array[$a] > $array[$b] ? 1 : -1;
        });

        print_r($array);
    }

    function test3Action()
    {
        $users = Users::findForeach(['conditions' => '(login_type != "" or login_type is not null) and register_at < 1 
         and created_at <= ' . beginOfDay()]);

        $id = 0;

        foreach ($users as $user) {
            $id++;
            $user->register_at = $user->created_at;
            $user->update();
        }

        echoLine($id);
    }

    function fixUnionAction()
    {
        $unions = Unions::findFirstById(1201);
        echoLine($unions->created_at_text);
        $unions = Unions::findBy(['status' => STATUS_OFF]);

        foreach ($unions as $union) {
            if ($union->userNum() > 0) {
                $union->status = STATUS_ON;
                $union->update();
                echoLine($union->id, $union->created_at_text);
            }
        }

        $room = Rooms::findFirstById(136800);
        echoLine($room->product_channel_id);


        $account_histories = AccountHistories::find(['conditions' => 'fee_type = :fee_type: and (hi_coin_history_id = 0 or hi_coin_history_id is null)',
            'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND]]);

        echoLine(count($account_histories));

        $amount = 0;

        foreach ($account_histories as $account_history) {
            $amount += $account_history->amount;
            echoLine($account_history->id, $account_history->user_id);
        }

        echoLine($amount);

        //1083050 1001315 1017233 1058027
        $ids = [];

        $account_histories = AccountHistories::sum(['conditions' => 'fee_type = :fee_type: and (hi_coin_history_id = 0 or hi_coin_history_id is null) and user_id = 1017233',
            'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND],
            'column' => 'amount'
        ]);

        echoLine($account_histories);

        foreach ($ids as $id) {

        }

        //{"1058027":196978790,"1060201":11010270,"1017233":141520,"1060180":116360,"1001315":30000,"1083050":330}

        $opts = ['remark' => '系统扣除'];
        AccountHistories::changeBalance(1060201, ACCOUNT_TYPE_DEDUCT, 11010270, $opts);
    }

    function testWebSoketAction()
    {
        $endpoints = Users::config('job_queue');
        echoLine($endpoints);

        $i = 0;

        while (true) {

            $i++;
            $user = Users::findFirstById(52);
            echoLine($user->online_token, $user->getIntranetIp());
            \services\SwooleUtils::send('push', $user->getIntranetIp(), 9508, ['body' => ['action' => $i], 'fd' => $user->getUserFd()]);

            if ($i >= 10) {
                break;
            }
        }
    }

    function test()
    {
        $ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];

        foreach ($ids as $id) {
            $withdraw_history = WithdrawHistories::findFirstBy(['user_id' => $id, 'status' => WITHDRAW_STATUS_WAIT]);

            if ($withdraw_history) {
                echoLine($withdraw_history);
            }
        }


        $user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];

        $withdraw_user_ids = [];

        foreach ($user_ids as $user_id) {

            $account_histories = AccountHistories::findBy(['user_id' => $user_id, 'fee_type' => ACCOUNT_TYPE_BUY_GIFT]);

            foreach ($account_histories as $account_history) {
                $gift_order = GiftOrders::findFirstById($account_history->gift_order_id);

                $withdraw_history = WithdrawHistories::findFirst([
                    'conditions' => 'user_id = :user_id: and status = :status: and created_at >= :start:',
                    'bind' => ['user_id' => $gift_order->user_id, 'status' => WITHDRAW_STATUS_WAIT, 'start' => strtotime('2018-04-12 12:00:00')]
                ]);

                if ($withdraw_history) {
                    $withdraw_user_ids[] = $withdraw_history->user_id;
                    echoLine($withdraw_history);
                }
            }
        }

        $withdraw_user_ids = array_unique($withdraw_user_ids);

        $res = [];

        foreach ($withdraw_user_ids as $id) {
            $res[] = $id;
        }

        echoLine($res);
    }

    function withdrawUsersAction()
    {
        $user_ids = [1122732, 1133128, 1106044, 1001061, 1133256, 1060417, 1017179, 1000439, 1032237, 1128048, 1000555];
    }

    function sendGiftAction()
    {
        $user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];
        $gift_order_user_ids = [];
        $times = [];

        foreach ($user_ids as $sender_id) {

            $account_history = AccountHistories::findFirst(['conditions' => 'fee_type = :fee_type: and user_id = :user_id: and (hi_coin_history_id = 0 or hi_coin_history_id is null)',
                'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND, 'user_id' => $sender_id],
                'order' => 'id asc'
            ]);

            $start = $account_history->created_at;

            $times[$sender_id] = $start;

            $sender = Users::findFirstById($sender_id);

            $gift_orders = GiftOrders::find(
                [
                    'conditions' => "created_at >= :start: and sender_id = :sender_id:",
                    'bind' => ['start' => $start, 'sender_id' => $sender_id]
                ]);

            foreach ($gift_orders as $gift_order) {
                $gift_order_user_ids[$sender->id][] = $gift_order->user_id;
            }
        }


        echoLine($gift_order_user_ids);

        foreach ($gift_order_user_ids as $sender_id => $user_ids) {

            $user_ids = array_unique($user_ids);

            $start = $times[$sender_id];

            echoLine($sender_id, date("Ymd H:i:s", $start), count($user_ids));

            $send_amount = [];

            foreach ($user_ids as $user_id) {

                $amount = GiftOrders::sum(['conditions' => 'sender_id = :sender_id: and user_id = :user_id: and created_at >= :created_at:',
                    'bind' => ['pay_type' => GIFT_PAY_TYPE_DIAMOND, 'created_at' => $start, 'sender_id' => $sender_id, 'user_id' => $user_id],
                    'column' => 'amount'
                ]);

                $send_amount[$user_id] = $amount;
            }

            arsort($send_amount);

            foreach ($send_amount as $user_id => $amount) {
                $user = Users::findFirstById($user_id);
                echoLine("发送礼物用户id:", $sender_id, "接收礼物用户id:" . $user_id, "金额:", $amount, "所属家族:", $user->union->name);
            }
        }
    }

    function resetHiCoinAction()
    {
        $user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];

        foreach ($user_ids as $user_id) {
            $hi_coin_history = HiCoinHistories::findUserLast($user_id);

            if ($hi_coin_history) {
                HiCoinHistories::createHistory($user_id, ['hi_coins' => $hi_coin_history->balance, 'fee_type' => HI_COIN_FEE_TYPE_DEDUCT, 'remark' => '系统扣除']);
            }
        }


    }

    //修复资料
    function userInfo1Action()
    {
        // 1060417 => 99990, 1001061 => 88010,
        $array = [
            1060417 => 99990, 1001061 => 88010, 800000 => 10181, 1057113 => 10099, 1133256 => 9999, 1065466 => 5727,
            15385 => 2737, 1123218 => 2686, 1015602 => 2628, 1109473 => 2150, 1035515 => 2020, 1088683 => 2010,
            1033519 => 2000, 1082051 => 1354, 1003062 => 1324, 1092719 => 1314, 1014008 => 1314, 1071355 => 1314, 1140747 => 1314, 1013703 => 1314,
            1133128 => 1314, 1106044 => 1314, 1122732 => 1314, 1131616 => 1314,
            1012820 => 1314, 1088531 => 1000, 1125188 => 1000, 1057537 => 1000, 1057532 => 1000,
            1066765 => 1000, 1057582 => 1000, 1065736 => 1000, 1074121 => 323, 1108632 => 307,
            1110827 => 299, 1057538 => 149, 153717 => 133, 1133777 => 99, 1017179 => 80, 1098596 => 50, 1026983 => 40,
            1100617 => 40, 1124029 => 25, 1089028 => 25, 1129304 => 25, 1115596 => 10, 1121799 => 5,
            1128551 => 5, 1071467 => 5, 1040859 => 5, 1142400 => 5];

        foreach ($array as $user_id => $amount) {

            $user = Users::findFirstById($user_id);
            $amount = 0;

            $send_user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];

            foreach ($send_user_ids as $send_user_id) {

                $account_history = AccountHistories::findFirst(['conditions' => 'fee_type = :fee_type: and user_id = :user_id: and (hi_coin_history_id = 0 or hi_coin_history_id is null)',
                    'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND, 'user_id' => $send_user_id],
                    'order' => 'id asc'
                ]);

                $start = $account_history->created_at;

                $send_amount = GiftOrders::sum(['conditions' => 'pay_type = :pay_type: and gift_type = :gift_type: and sender_id = :sender_id: and user_id = :user_id: and created_at >= :created_at:',
                    'bind' => ['pay_type' => GIFT_PAY_TYPE_DIAMOND, 'gift_type' => GIFT_TYPE_COMMON, 'created_at' => $start, 'sender_id' => $send_user_id, 'user_id' => $user_id],
                    'column' => 'amount'
                ]);

                $amount += $send_amount;
            }

            if ($user->isIdCardAuth()) {
                $rate = 6 / 100;
            } else {
                $rate = 4.5 / 100;
            }

            $hi_coins = $amount * $rate;

            echoLine($amount, $hi_coins);
            HiCoinHistories::createHistory($user_id, ['hi_coins' => $hi_coins, 'fee_type' => HI_COIN_FEE_TYPE_DEDUCT, 'remark' => '系统扣除']);

            echoLine("注册时间", date("Y-m-d H:i:s", $user->register_at), "接收礼物用户id:" . $user_id, "金额:", $amount, "所属家族:", $user->union->name, "段位:", $user->segment_text, '支付金额:', $user->pay_amount);
        }
    }

    function fixWithDrawInfoAction()
    {
        $withdraw_histories = WithdrawHistories::find(
            [
                'conditions' => 'created_at >= :created_at:',
                'bind' => ['created_at' => strtotime("2018-04-12 12:00:00")]
            ]);

        foreach ($withdraw_histories as $withdraw_history) {
            $withdraw_history->status = WITHDRAW_STATUS_FAIL;
            $withdraw_history->update();
        }

        echoLine(count($withdraw_histories));

    }


    function clearRank()
    {
        $db = Users::getUserDb();


        $day_key = "day_charm_rank_list_" . date("Ymd");
        $start = date("Ymd", strtotime("last sunday next day", time()));
        $end = date("Ymd", strtotime("next monday", time()) - 1);
        $week_key = "week_charm_rank_list_" . $start . "_" . $end;
        $total_key = "total_charm_rank_list";


        $array = [
            1060417 => 99990, 1001061 => 88010, 800000 => 10181, 1057113 => 10099, 1133256 => 9999, 1065466 => 5727,
            15385 => 2737, 1123218 => 2686, 1015602 => 2628, 1109473 => 2150, 1035515 => 2020, 1088683 => 2010,
            1033519 => 2000, 1082051 => 1354, 1003062 => 1324, 1092719 => 1314, 1014008 => 1314, 1071355 => 1314, 1140747 => 1314, 1013703 => 1314,
            1133128 => 1314, 1106044 => 1314, 1122732 => 1314, 1131616 => 1314,
            1012820 => 1314, 1088531 => 1000, 1125188 => 1000, 1057537 => 1000, 1057532 => 1000,
            1066765 => 1000, 1057582 => 1000, 1065736 => 1000, 1074121 => 323, 1108632 => 307,
            1110827 => 299, 1057538 => 149, 153717 => 133, 1133777 => 99, 1017179 => 80, 1098596 => 50, 1026983 => 40,
            1100617 => 40, 1124029 => 25, 1089028 => 25, 1129304 => 25, 1115596 => 10, 1121799 => 5,
            1128551 => 5, 1071467 => 5, 1040859 => 5, 1142400 => 5];

        foreach ($array as $user_id => $amount) {

            $send_user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];
            $amount = 0;

            foreach ($send_user_ids as $send_user_id) {

                $account_history = AccountHistories::findFirst(['conditions' => 'fee_type = :fee_type: and user_id = :user_id: and (hi_coin_history_id = 0 or hi_coin_history_id is null)',
                    'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND, 'user_id' => $send_user_id],
                    'order' => 'id asc'
                ]);

                $start = $account_history->created_at;

                $send_amount = GiftOrders::sum(['conditions' => 'pay_type = :pay_type: and gift_type = :gift_type: and sender_id = :sender_id: and user_id = :user_id: and created_at >= :created_at:',
                    'bind' => ['pay_type' => GIFT_PAY_TYPE_DIAMOND, 'gift_type' => GIFT_TYPE_COMMON, 'created_at' => $start, 'sender_id' => $send_user_id, 'user_id' => $user_id],
                    'column' => 'amount'
                ]);

                $amount += $send_amount;
            }


            if ($amount > 0 && $db->zscore($total_key, $user_id) > 0) {

                echoLine($amount, $db->zscore($total_key, $user_id), $db->zscore($day_key, $user_id));

                $db->zincrby($day_key, -$amount, $user_id);
                $db->zincrby($week_key, -$amount, $user_id);
                $db->zincrby($total_key, -$amount, $user_id);

                echoLine($amount, $db->zscore($total_key, $user_id), $db->zscore($day_key, $user_id));
            }
        }
    }

    //魅力值 家族声望值
    function fixGiftOrdersAction()
    {
        $user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];

        foreach ($user_ids as $user_id) {


            $account_history = AccountHistories::findFirst(['conditions' => 'fee_type = :fee_type: and user_id = :user_id: and (hi_coin_history_id = 0 or hi_coin_history_id is null)',
                'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND, 'user_id' => $user_id],
                'order' => 'id asc'
            ]);

            $start = $account_history->created_at;

            $gift_orders = GiftOrders::find(
                [
                    'conditions' => 'created_at >= :start: and sender_id = :sender_id: and pay_type = :pay_type:',
                    'bind' => ['start' => $start, 'sender_id' => $user_id, 'pay_type' => GIFT_PAY_TYPE_DIAMOND]
                ]);

            foreach ($gift_orders as $gift_order) {

                $db = Users::getUserDb();

                $sender_union_id = $gift_order->sender_union_id;
                $receiver_union_id = $gift_order->receiver_union_id;
                $amount = $gift_order->amount;
                $sender = $gift_order->sender;
                $receiver = $gift_order->user;

                if ($sender_union_id) {
                    $sender_union = Unions::findFirstById($sender_union_id);
                    $sender_union->fame_value -= $amount;
                    $sender_union->update();

                    $start = date("Ymd", strtotime("last sunday next day", time()));
                    $end = date("Ymd", strtotime("next monday", time()) - 1);
                    $week_key = "total_union_fame_value_" . $start . "_" . $end;
                    $day_key = "total_union_fame_value_day_" . date("Ymd");
                    $db->zincrby($day_key, -$amount, $sender_union->id);
                    $db->zincrby($week_key, -$amount, $sender_union->id);

                    if ($db->zscore($day_key, $sender_union->id) < 1) {
                        $db->zrem($day_key, $sender_union->id);
                    }

                    if ($db->zscore($week_key, $sender_union->id) < 1) {
                        $db->zrem($week_key, $sender_union->id);
                    }

                    $sender->union_wealth_value -= $amount;

                    echoLine($sender_union_id, $sender->id, $amount);
                }

                $sender_experience = $amount * 0.02;
                $sender->experience -= $sender_experience;
                $sender_level = $sender->calculateLevel();
                $sender->level = $sender_level;
                $sender->segment = $sender->calculateSegment();
                $sender->wealth_value -= $amount;
                $sender->update();

                if ($receiver_union_id) {

                    if ($receiver_union_id != $sender_union_id) {
                        $receiver_union = Unions::findFirstById($receiver_union_id);
                        $receiver_union->fame_value -= $amount;
                        $receiver_union->update();

                        $start = date("Ymd", strtotime("last sunday next day", time()));
                        $end = date("Ymd", strtotime("next monday", time()) - 1);
                        $week_key = "total_union_fame_value_" . $start . "_" . $end;
                        $day_key = "total_union_fame_value_day_" . date("Ymd");
                        $db->zincrby($day_key, -$amount, $receiver_union_id->id);
                        $db->zincrby($week_key, -$amount, $receiver_union_id->id);

                        if ($db->zscore($day_key, $receiver_union->id) < 1) {
                            $db->zrem($day_key, $receiver_union->id);
                        }

                        if ($db->zscore($week_key, $receiver_union->id) < 1) {
                            $db->zrem($week_key, $receiver_union->id);
                        }
                    }

                    echoLine($receiver_union->id, $receiver->id, $amount);

                    $receiver->union_charm_value -= $amount;
                }

                $receiver->charm_value -= $amount;
                $receiver->update();

                echoLine($gift_order->created_at_text, $gift_order->room_id, $gift_order->sender_union_id, $gift_order->receiver_union_id);
            }
        }
    }

    //统计房间流水
    function fixRoomIncomeAction()
    {
        $user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];
        $db = Users::getUserDb();

        foreach ($user_ids as $user_id) {


            $account_history = AccountHistories::findFirst(['conditions' => 'fee_type = :fee_type: and user_id = :user_id: and (hi_coin_history_id = 0 or hi_coin_history_id is null)',
                'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND, 'user_id' => $user_id],
                'order' => 'id asc'
            ]);

            $start = $account_history->created_at;

            $gift_orders = GiftOrders::find(
                [
                    'conditions' => 'gift_type = :gift_type: and created_at >= :start: and sender_id = :sender_id: and pay_type = :pay_type:',
                    'bind' => ['gift_type' => GIFT_TYPE_COMMON, 'start' => $start, 'sender_id' => $user_id, 'pay_type' => GIFT_PAY_TYPE_DIAMOND]
                ]);

            foreach ($gift_orders as $gift_order) {
                $room_id = $gift_order->room_id;
                $amount = $gift_order->amount;

                if ($room_id && $db->zscore("stat_room_income_list", $room_id)) {

                    $db->zincrby('stat_room_income_list', -$amount, $room_id);

                    if ($db->zscore("stat_room_income_list", $room_id) < 1) {
                        $db->zrem("stat_room_income_list", $room_id);
                    }
                }

                $room = Rooms::findFirstById($room_id);

                $stat_at = date("Ymd", $gift_order->created_at);
                echoLine($stat_at, $amount, $room_id);

                $send_gift_user_key = $room->generateSendGiftUserDayKey($stat_at);
                $send_gift_num_key = $room->generateSendGiftNumDayKey($stat_at);
                $gift_income_key = $room->generateStatIncomeDayKey($stat_at);

                $db->zrem($send_gift_user_key, $gift_order->sender_id);
                $db->zincrby($send_gift_num_key, -1, $room_id);
                $db->zincrby($gift_income_key, -$amount, $room_id);

                if ($db->zscore($gift_income_key, $room_id) < 1) {
                    $db->zrem($gift_income_key, $room_id);
                }

                if ($db->zscore($send_gift_num_key, $room_id) < 1) {
                    $db->zrem($send_gift_num_key, $room_id);
                }
            }
        }
    }


    //统计房间流水
    function clearGiftOrderAction()
    {
        $user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];

        foreach ($user_ids as $user_id) {


            $account_history = AccountHistories::findFirst(['conditions' => 'fee_type = :fee_type: and user_id = :user_id: and (hi_coin_history_id = 0 or hi_coin_history_id is null)',
                'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND, 'user_id' => $user_id],
                'order' => 'id asc'
            ]);

            $start = $account_history->created_at;

            $gift_orders = GiftOrders::find(
                [
                    'conditions' => 'gift_type = :gift_type: and created_at >= :start: and sender_id = :sender_id: and pay_type = :pay_type:',
                    'bind' => ['gift_type' => GIFT_TYPE_COMMON, 'start' => $start, 'sender_id' => $user_id, 'pay_type' => GIFT_PAY_TYPE_DIAMOND]
                ]);

            foreach ($gift_orders as $gift_order) {
                $user = $gift_order->user;
                $gift_num = $gift_order->gift_num;
                $gift_id = $gift_order->gift_id;

                $user_gift = UserGifts::findFirstBy([
                    'gift_id' => $gift_id,
                    'user_id' => $user->id,
                ]);

                if ($user_gift) {
                    echoLine($user_gift->id, $user->id, $user_gift->num, $gift_num);
                    $user_gift_num = $user_gift->num;
                    $new_num = $user_gift_num - $gift_num;
                    $user_gift->num = $new_num;
                    $user_gift->total_amount = $new_num * $user_gift->amount;
                    $user_gift->update();
                }
            }
        }
    }

    function fixCarAction()
    {
        $user_ids = [1060201, 1058027, 1060180, 1017233, 1001315, 1083050];

        foreach ($user_ids as $user_id) {


            $account_history = AccountHistories::findFirst(['conditions' => 'fee_type = :fee_type: and user_id = :user_id: and (hi_coin_history_id = 0 or hi_coin_history_id is null)',
                'bind' => ['fee_type' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND, 'user_id' => $user_id],
                'order' => 'id asc'
            ]);

            $start = $account_history->created_at;

            $gift_orders = GiftOrders::find(
                [
                    'conditions' => 'gift_type = :gift_type: and created_at >= :start: and sender_id = :sender_id: and pay_type = :pay_type:',
                    'bind' => ['gift_type' => GIFT_TYPE_CAR, 'start' => $start, 'sender_id' => $user_id, 'pay_type' => GIFT_PAY_TYPE_DIAMOND]
                ]);

            foreach ($gift_orders as $gift_order) {
                $user = $gift_order->user;
                $gift_num = $gift_order->gift_num;
                $gift_id = $gift_order->gift_id;

                $user_gift = UserGifts::findFirstBy([
                    'gift_id' => $gift_id,
                    'user_id' => $user->id,
                ]);

                if ($user_gift) {
                    echoLine($user_gift->id, $user->id, $user_gift->num, $gift_num, $user_gift->gift->name);
                    $user_gift->delete();
                }

                $gift_order->status = GIFT_ORDER_STATUS_FREEZE;
                $gift_order->update();
            }
        }
    }

    function fixUserRank()
    {
        $db = Users::getUserDb();


        $day_key = "day_charm_rank_list_" . date("Ymd");
        $start = date("Ymd", strtotime("last sunday next day", time()));
        $end = date("Ymd", strtotime("next monday", time()) - 1);
        $week_key = "week_charm_rank_list_" . $start . "_" . $end;
        $total_key = "total_charm_rank_list";

        $user_id = 1001061;

        $amount = 18888 * 2 + 5888 + 9999;

        $db->zincrby($day_key, -$amount, $user_id);
        $db->zincrby($week_key, -$amount, $user_id);
        $db->zincrby($total_key, -$amount, $user_id);

        echoLine($amount, $db->zscore($total_key, $user_id), $db->zscore($day_key, $user_id));

        $send_user_ids_key = "wake_up_user_send_gift_key";
        $hot_cache = Users::getHotWriteCache();
        echoLine($hot_cache->zrange($send_user_ids_key, 0, -1));
    }

    function findSilentUserAction()
    {
        $sex = 1;
        $type = 1;
        $file = APP_ROOT . "log/avatar_url_sex_{$type}.log";
        $content = file_get_contents($file);
        $content = explode(PHP_EOL, $content);
        $avatar_urls = array_filter($content);

        $users = Users::findForeach(
            [
                'conditions' => 'id > 500000 and user_type = :user_type: and (register_at < 1 or register_at is null) and avatar_status != :avatar_status:',
                'bind' => ['user_type' => USER_TYPE_SILENT, 'avatar_status' => AUTH_SUCCESS],
                'limit' => 1351
            ]);

        $i = 0;


        foreach ($users as $user) {

            if (!isset($avatar_urls[$i])) {
                echoLine("avatar is null", $i);
                continue;
            }

            $avatar_url = $avatar_urls[$i];
            $avatar_url = trim($avatar_url);
            $source_filename = APP_ROOT . 'temp/avatar_' . md5(uniqid(mt_rand())) . '.jpg';

            if (!httpSave($avatar_url, $source_filename)) {
                info('get avatar error', $avatar_url);
                continue;
            }

            $user->updateAvatar($source_filename);
            echoLine($user->id);

            if (file_exists($source_filename)) {
                unlink($source_filename);
            }

            $user->sex = $sex;
            $user->update();
            $i++;
        }

        echoLine($i);

        echoLine(count($users));


        $user = Users::findFirstById(21);
        $push_data = ['title' => '测试', 'body' => '测试'];
        echoLine($user->getPushContext(), $user->getPushReceiverContext());
        \Pushers::push($user->getPushContext(), $user->getPushReceiverContext(), $push_data);


        $hot_cache = Users::getHotWriteCache();
        $online_silent_room = Rooms::findFirstById(136810);
        $key = $online_silent_room->getUserListKey();
        $user_ids = $hot_cache->zrange($key, 0, -1);
        $users = Users::findByIds($user_ids);

        foreach ($users as $user) {
            $online_silent_room->exitSilentRoom($user);
        }

        //http://mt-development.img-cn-hangzhou.aliyuncs.com/chance/users/avatar_url_sex_1.log
        //http://mt-development.img-cn-hangzhou.aliyuncs.com/chance/users/avatar_url_sex_2.log
        //http://mt-development.img-cn-hangzhou.aliyuncs.com/chance/users/avatar_url_sex_3.log

        //http://mt-development.img-cn-hangzhou.aliyuncs.com/chance/users/grab_nickname_from_female.log
        //http://mt-development.img-cn-hangzhou.aliyuncs.com/chance/users/grab_nickname_from_male.log
        //http://mt-development.img-cn-hangzhou.aliyuncs.com/chance/users/grab_nickname_from_unknow.log

        $res = StoreFile::upload(APP_ROOT . "doc/words/grab_nickname_from_female.log", APP_NAME . "/users/grab_nickname_from_female.log");
        echoLine(StoreFile::getUrl($res));
        $res = StoreFile::upload(APP_ROOT . "doc/words/grab_nickname_from_male.log", APP_NAME . "/users/grab_nickname_from_male.log");
        echoLine(StoreFile::getUrl($res));
        $res = StoreFile::upload(APP_ROOT . "doc/words/grab_nickname_from_unknow.log", APP_NAME . "/users/grab_nickname_from_unknow.log");
        echoLine(StoreFile::getUrl($res));
    }

    function testEmojiAction()
    {
        $str = "五钻扫中2💄3🎤";

        $str = preg_replace_callback(
            '/./u',
            function ($match) {
                return strlen($match[0]) >= 4 ? '*' : $match[0];
            },
            $str);

        echoLine($str);

        $cache_key = 'send_auth_code_user_' . 1001443;
        $hot_cache = Users::getHotWriteCache();
        $hot_cache->del($cache_key);

        $cache_key = 'send_auth_code_device_' . 6;
        $hot_cache->del($cache_key);


        $content = <<<EOF
【系统提醒】
为保障您的资金安全，HI语音进行更完善的安全系统升级
请您重新绑定自己的银行卡（包括之前提现的用户）
已经申请提现还未到账的用户请重新提交提现申请
上周提现金额将会在48小时内到账。本周提现金额在下周二到账。
Hi语音为给您造成的不便表示歉意。
EOF;
        $withdraw_histories = WithdrawHistories::findBy(['withdraw_account_type' => 2, 'status' => WITHDRAW_STATUS_WAIT]);

        foreach ($withdraw_histories as $withdraw_history) {

            echoLine($withdraw_history->id);
            $withdraw_history->status = WITHDRAW_STATUS_FAIL;
            $withdraw_history->save();

            Chats::sendTextSystemMessage($withdraw_history->user_id, $content);
        }

        $withdraw_accounts = WithdrawAccounts::findForeach();

        foreach ($withdraw_accounts as $withdraw_account) {
            $withdraw_account->status = STATUS_OFF;
            $withdraw_account->update();
        }
    }

    function fixUserRoomAction()
    {
        $user = Users::findFirstById(9);
        $room = $user->current_room;
        $room->exitSilentRoom($user);

        $users = Users::find(
            [
                'conditions' => 'id < 1000000 and current_room_id > 0 and avatar_status != :avatar_status:',
                'bind' => ['avatar_status' => AUTH_SUCCESS]
            ]);

        foreach ($users as $user) {
            $room = $user->current_room;
            $room->exitSilentRoom($user);
            echoLine($user->id);
        }
        echoLine(count($users));
    }

    function fixCompanyUserAction()
    {
        $ips = ['116.226.119.13', '116.226.120.117', '139.227.253.40'];
        $db = Users::getUserDb();

        $day_key = "day_charm_rank_list_" . date("Ymd");
        $start = date("Ymd", strtotime("last sunday next day", time()));
        $end = date("Ymd", strtotime("next monday", time()) - 1);
        $week_key = "week_charm_rank_list_" . $start . "_" . $end;
        $total_key = "total_charm_rank_list";

        $wealth_day_key = "day_wealth_rank_list_" . date("Ymd");
        $start = date("Ymd", strtotime("last sunday next day", time()));
        $end = date("Ymd", strtotime("next monday", time()) - 1);
        $wealth_week_key = "week_wealth_rank_list_" . $start . "_" . $end;
        $wealth_total_key = "total_wealth_rank_list";

        foreach ($ips as $ip) {
            $users = Users::findByIp($ip);
            foreach ($users as $user) {
                $user->organisation = USER_ORGANISATION_COMPANY;
                $user->update();
                echoLine($user->id);

                $db->zrem($day_key, $user->id);
                $db->zrem($week_key, $user->id);
                $db->zrem($total_key, $user->id);

                $db->zrem($wealth_day_key, $user->id);
                $db->zrem($wealth_week_key, $user->id);
                $db->zrem($wealth_total_key, $user->id);
            }
        }

        $users = \Users::findFieldRankList('day', 'wealth', 1, 10);
        echoLine(count($users));
    }

    function fixUserRankAction()
    {
        $users = Users::findByIp('61.158.149.145');
        echoLine(count($users));

        foreach ($users as $user) {
            echoLine($user->id);
        }
        $room = Rooms::findFirstById(1010149);
        echoLine($room->getChannelName());

        $day_key = "day_charm_rank_list_" . date("Ymd");
        $wealth_day_key = "day_wealth_rank_list_" . date("Ymd");
        $gift_orders = GiftOrders::find(['conditions' => "created_at >= :start: and status = :status: and pay_type = :pay_type:",
            'bind' => ['start' => beginOfDay(), 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => PAY_TYPE_DIAMOND]]);
        $db = Users::getUserDb();
        $db->zclear($day_key);
        $db->zclear($wealth_day_key);

        foreach ($gift_orders as $gift_order) {
            $user = $gift_order->user;
            $sender = $gift_order->sender;

            $value = $gift_order->amount;
            $db->zincrby($day_key . "_" . $user->product_channel_id, $value, $user->id);
            $db->zincrby($day_key, $value, $user->id);

            $db->zincrby($wealth_day_key . "_" . $sender->product_channel_id, $value, $sender->id);
            $db->zincrby($wealth_day_key, $value, $sender->id);
        }


        $start = date("Ymd", strtotime("last sunday next day", time()));
        $end = date("Ymd", strtotime("next monday", time()) - 1);

        $db = Users::getUserDb();
        $key = "week_charm_rank_list_" . $start . "_" . $end;
        $user_ids = $db->zrange($key, 0, -1);

        foreach ($user_ids as $user_id) {
            $amount = GiftOrders::sum(
                [
                    'conditions' => 'created_at >= :start: and created_at <= :end: and status = :status: and pay_type = :pay_type: and user_id = :user_id:',
                    'bind' => ['start' => beginOfDay(strtotime($start)), 'end' => endOfDay(strtotime($end)),
                        'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => PAY_TYPE_DIAMOND, 'user_id' => $user_id],
                    'column' => 'amount'
                ]);

            $value = $db->zscore($key, $user_id);

            if ($amount != $value) {
                echoLine($amount, $value);
                $db->zadd($key, $amount, $user_id);
            }
        }

        $key = "week_wealth_rank_list_" . $start . "_" . $end;

        $user_ids = $db->zrange($key, 0, -1);

        foreach ($user_ids as $user_id) {
            $amount = GiftOrders::sum(
                [
                    'conditions' => 'created_at >= :start: and created_at <= :end: and status = :status: and pay_type = :pay_type: and sender_id = :sender_id:',
                    'bind' => ['start' => beginOfDay(strtotime($start)), 'end' => endOfDay(strtotime($end)),
                        'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => PAY_TYPE_DIAMOND, 'sender_id' => $user_id],
                    'column' => 'amount'
                ]);

            $value = $db->zscore($key, $user_id);

            if ($amount != $value) {
                echoLine($amount, $value);
                $db->zadd($key, $amount, $user_id);
            }
        }

        $key = "total_charm_rank_list";
        $wealth_key = "total_wealth_rank_list";

        $db = Users::getUserDb();
        $user_ids = $db->zrange($key, 0, -1);

        foreach ($user_ids as $user_id) {
            $amount = GiftOrders::sum(
                [
                    'conditions' => 'status = :status: and pay_type = :pay_type: and user_id = :user_id:',
                    'bind' => ['status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => PAY_TYPE_DIAMOND, 'user_id' => $user_id],
                    'column' => 'amount'
                ]);

            $value = $db->zscore($key, $user_id);

            if ($amount != $value) {
                echoLine($amount, $value);
                $db->zadd($key, $amount, $user_id);
            }
        }

        $db = Users::getUserDb();
        $wealth_key = "total_wealth_rank_list";
        $user_ids = $db->zrange($wealth_key, 0, -1);

        foreach ($user_ids as $user_id) {
            $amount = GiftOrders::sum(
                [
                    'conditions' => 'status = :status: and pay_type = :pay_type: and sender_id = :sender_id:',
                    'bind' => ['status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => PAY_TYPE_DIAMOND, 'sender_id' => $user_id],
                    'column' => 'amount'
                ]);

            $value = $db->zscore($wealth_key, $user_id);

            if ($amount != $value) {
                echoLine($amount, $value);
                //$db->zadd($wealth_key, $amount, $user_id);
            }
        }

        echoLine(valueToStr(451496));
    }

    function fixUnionRankAction()
    {
        $key = "total_union_fame_value_day_" . date('Ymd');
        $db = Users::getUserDb();
        $union_ids = $db->zrange($key, 0, -1);

        foreach ($union_ids as $union_id) {

            $amount = GiftOrders::sum(
                [
                    'conditions' =>
                        'status = :status: and pay_type = :pay_type: and (sender_union_id = :sender_union_id: or receiver_union_id = :receiver_union_id:) 
                        and created_at >= :start: and created_at <= :end:',
                    'bind' => ['status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => PAY_TYPE_DIAMOND, 'start' => beginOfDay(), 'end' => endOfDay(),
                        'sender_union_id' => $union_id, 'receiver_union_id' => $union_id],
                    'column' => 'amount'
                ]);

            $value = $db->zscore($key, $union_id);

            if ($amount != $value) {
                $db->zadd($key, $amount, $union_id);
                echoLine($union_id, $amount, $value);
            }
        }

        $db = Users::getUserDb();
        $start = date("Ymd", strtotime("last sunday next day", time()));
        $end = date("Ymd", strtotime("next monday", time()) - 1);
        $key = "total_union_fame_value_" . $start . "_" . $end;

        $union_ids = $db->zrange($key, 0, -1);

        foreach ($union_ids as $union_id) {

            $amount = GiftOrders::sum(
                [
                    'conditions' =>
                        'status = :status: and pay_type = :pay_type: and (sender_union_id = :sender_union_id: or receiver_union_id = :receiver_union_id:) 
                        and created_at > :start: and created_at <= :end:',
                    'bind' => ['status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => PAY_TYPE_DIAMOND, 'start' => beginOfDay(strtotime($start)), 'end' => endOfDay(strtotime($end)),
                        'sender_union_id' => $union_id, 'receiver_union_id' => $union_id],
                    'column' => 'amount'
                ]);


            $value = $db->zscore($key, $union_id);

            if ($amount != $value) {
                $db->zadd($key, $amount, $union_id);
                echoLine($key, $union_id, $amount, $value);
            }
        }

    }

    function exportWithdrawHistoryAction()
    {
        $withdraw_histories = WithdrawHistories::findBy(['status' => WITHDRAW_STATUS_WAIT]);
        $titles = ['日期', '用户id', '姓名', '账户', '收款银行', '收款支行', '收款地区', '提现金额'];
        $data = [];
        foreach ($withdraw_histories as $withdraw_history) {
            $user_id = $withdraw_history->user_id;

            $old_withdraw_history = WithdrawHistories::findFirstBy(['withdraw_account_type' => 2, 'user_id' => $user_id, 'status' => WITHDRAW_STATUS_FAIL]);

            if ($old_withdraw_history) {
                echoLine($user_id, $old_withdraw_history->id);

                $withdraw_account = $withdraw_history->withdraw_account;

                if (isPresent($withdraw_account)) {
                    $account_bank_name = $withdraw_account->account_bank_name;
                    $bank_account_location = $withdraw_account->bank_account_location;
                    $area = $withdraw_account->province_name . ',' . $withdraw_account->city_name;

                    $data[] = [$withdraw_history->created_at_text, $withdraw_history->user_id, $withdraw_history->user_name,
                        $withdraw_history->account, $account_bank_name, $bank_account_location, $area, $withdraw_history->amount
                    ];
                }
            }
        }

        echoLine($data);

        $temp_file = 'export_withdraw_history_' . date('Ymd') . '.xls';
        $uri = writeExcel($titles, $data, $temp_file, true);
        echoLine($uri);
    }

    function gameWebsocketMessageAction()
    {
        $user = Users::findFirstById(1);

        $image_url = "";
        $body = ['action' => 'game_launched', 'content' => $user->nickname . "发起了跳一跳游戏", 'client_url' => ""];

        $intranet_ip = $user->getIntranetIp();
        $receiver_fd = $user->getUserFd();

        \services\SwooleUtils::send('push', $intranet_ip, Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);
    }

    function fixWeekRoomWealthRankListAction()
    {
        $time = time();
        $start_at = beginOfWeek();
        $end_at = endOfWeek();

        $cond = [
            'conditions' => 'created_at >= :start: and created_at <= :end: and room_id > 0 and gift_type = :gift_type: and pay_type = :pay_type:',
            'bind' => ['start' => $start_at, 'end' => $end_at, 'gift_type' => GIFT_TYPE_COMMON, 'pay_type' => GIFT_PAY_TYPE_DIAMOND],
            'columns' => 'distinct room_id'
        ];

        $gift_orders = GiftOrders::find($cond);

        $db = Users::getUserDb();

        $start = date("Ymd", $start_at);
        $end = date("Ymd", $end_at);


        foreach ($gift_orders as $gift_order) {

            $room_id = $gift_order->room_id;

            if (isPresent($room) && $gift_order->amount) {
                echoLine($gift_order->created_at_text, $gift_order->amount);

                $week_room_wealth_rank_key = $room->generateRoomWealthRankListKey('week', ['start' => $start, 'end' => $end]);
                $db->zclear("room_wealth_rank_List_week__room_id_{$room->id}_20180416_20180422");
                $db->zclear("room_wealth_rank_List_week__room_id_{$room->id}20180416_20180422");
                $db->zincrby($week_room_wealth_rank_key, $gift_order->amount, $gift_order->sender_id);

                echoLine('success', $week_room_wealth_rank_key);

            } else {

                echoLine('false', $gift_order->id, $gift_order->room_id, $gift_order->amount);

            }
        }
    }

    function fixDayRoomWealthRankListAction()
    {
        $time = time();

        $start_at = beginOfDay($time);
        $end_at = endOfDay($time);

        $cond = [
            'conditions' => 'created_at >= :start: and created_at <= :end: and room_id > 0 and gift_type = :gift_type: and pay_type = :pay_type:',
            'bind' => ['start' => $start_at, 'end' => $end_at, 'gift_type' => GIFT_TYPE_COMMON, 'pay_type' => GIFT_PAY_TYPE_DIAMOND],
            'order' => 'id desc'
        ];

        $gift_orders = GiftOrders::find($cond);

        $db = Users::getUserDb();

        $date = date("Ymd", $time);

        foreach ($gift_orders as $gift_order) {

            $room = $gift_order->room;

            if (isPresent($room) && $gift_order->amount) {

                $day_room_wealth_rank_key = $room->generateRoomWealthRankListKey('day', ['date' => $date]);
                $db->zclear("room_wealth_rank_List_day_" . "_room_id_1009620_" . 20180419);
                $db->zclear("room_wealth_rank_List_day_" . "_room_id{$room->id}_" . $date);
                $db->zincrby($day_room_wealth_rank_key, $gift_order->amount, $gift_order->sender_id);

                echoLine('success', $day_room_wealth_rank_key);
            } else {
                echoLine('false', $gift_order->id, $gift_order->room_id, $gift_order->amount);
            }
        }


        $hot_cache = Users::getHotWriteCache();
        echoLine($hot_cache->get('send_auth_code_user_1139546'));
        $user_id = fetch($opts, 'user_id');
        $device_id = fetch($opts, 'device_id');
        $cache_key = '';
        if ($user_id) {
            $cache_key = 'send_auth_code_user_' . $user_id;
        }
        if ($device_id) {
            $hot_cache = Users::getHotWriteCache();
            echoLine($hot_cache->get('send_auth_code_device_134507'));
        }
    }

    function test22Action()
    {
        $content = file_get_contents(APP_ROOT . "temp/test.txt");
        $content = trim($content);
        //$content = base64_decode($content);

        //data:image/octet-stream;base64
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $content, $result)) {
            $type = $result[2];
            echoLine($type);
            if (in_array($type, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                $new_file = APP_ROOT . "temp/test" . '.' . $type;
                if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $content)))) {
                    $img_path = str_replace('../../..', '', $new_file);
                    echo '图片上传成功</br>![](' . $img_path . ')';
                } else {
                    echo '图片上传失败</br>';

                }
            } else {
                //文件类型错误
                echo '图片上传类型错误';
            }

        } else {
            //文件错误
            echo '文件错误';
        }
    }

    function test23Action()
    {
        $unions = Unions::findBy(['auth_status' => AUTH_NONE]);
        echoLine(count($unions));

        $union = Unions::findFirstById(1);
        $union->auth_status = AUTH_WAIT;
        $union->update();

        $db = \Users::getUserDb();
        $good_num_list_key = 'good_num_list';
        $db->zadd($good_num_list_key, time(), 1234567);

        echoLine($db->zscore($good_num_list_key, 1234567));
    }

    function test24Action()
    {
        $hot_cache = Users::getHotWriteCache();
        $key = "silent_user_key";
        $silent_user_ids = $hot_cache->zrange($key, 0, -1);
        $silent_users = Users::findByIds($silent_user_ids);

//        $res = httpGet('http://mt-development.img-cn-hangzhou.aliyuncs.com/avatar/2018/02/05/e2f48818d52c9ecf2c46a589fa2a3d79.jpg');
//        echoLine($res->code);

        $rooms = Rooms::findForeach(['order' => 'last_at desc']);
        $i = 0;
        foreach ($rooms as $room) {


            $user = $room->user;

            if (!$user) {
                continue;
            }

            $res = httpGet($user->avatar_small_url);

            if (200 != $res->code) {
                $silent_user = $silent_users[array_rand($silent_user_ids)];
                $user->avatar = $silent_user->avatar;
                $user->update();
                echoLine($user->id, $silent_user->id);
            }

            $i++;

            if ($i >= 5000) {
                break;
            }
        }
    }

    function test25Action()
    {

        $gift_ids = [69, 70, 71, 72];

        $start = date("Ymd", beginOfWeek());
        $end = date("Ymd", endOfWeek());
        $gift_id = 70;
        $key = "gift_charm_week_list_activity_stat_gift_id_" . $gift_id . "_start_" . $start . "_end_" . $end;
        $user_db = Users::getUserDb();
        $data = $user_db->zrevrange($key, 0, -1, 'withscores');

        foreach ($data as $user_id => $amount) {

            $total_amount = GiftOrders::sum(
                [
                    'conditions' => 'user_id = :user_id: and gift_id = :gift_id:',
                    'bind' => ['user_id' => $user_id, 'gift_id' => 70],
                    'column' => 'amount'
                ]);

            if ($total_amount != $amount) {
                echoLine($user_id, $total_amount, $amount);
            }

            //$user_db->zadd($key, $total_amount, $user_id);
        }

        echoLine(count($user_db->zrange($key, 0, -1)));

        $user = Users::findFirstById(1001314);
        echoLine($user->push_token);

        $hot_cache = Users::getHotReadCache();


        $room = Rooms::findFirstById(1008434);
        echoLine($room->types);
    }

    function test26Action()
    {
        $cond = ['conditions' => 'name like :name:', 'bind' => ['name' => '%积分卡书法家%']];

        $room_category_word = RoomCategoryKeywords::findFirst($cond);
        echoLine($room_category_word);

        $rooms = Rooms::find(['conditions' => 'last_at >= :last_at:', 'bind' => ['last_at' => time() - 3600], 'columns' => 'id']);
        echoLine(count($rooms));

        foreach ($rooms as $room) {
            //$room = Rooms::findFirstById($room->id);
//            echoLine($room->room_category_types);
            Rooms::updateRoomTypes($room->id);
        }
    }

    function test27Action()
    {
        $activity = Activities::findFirstById(2);
        $key = "gift_minutes_list_activity_stat_gift_id_" . 24 . "_start_" . $activity->start_at . "_end_" . $activity->end_at;

        $users = Users::findForeach();
        $user_db = Users::getUserDb();
        foreach ($users as $user) {

            $user_db->zincrby($key, 10, $user->id);
        }

        $room = Rooms::findFirstById(21);
        $user = Users::findFirstById(1);
        $room->pushTopTopicMessage($user, "哇哦！xxx刚刚砸出一万钻大奖！还不快来砸金蛋，试试手气~");
    }

    function test28Action()
    {
        $payments = Payments::findByUserId(1275312);

        foreach ($payments as $payment) {
            $payment->user_id = 1296027;
            $payment->update();
        }

        $account_history = AccountHistories::findFirstById(567972);
        $account_history->user_id = 1296027;
        $account_history->update();

        $order = Orders::findFirstById(45334);
        $order->user_id = 1296027;
        $order->update();


        $account_histories = AccountHistories::findByUserId(1296027);

        foreach ($account_histories as $account_history) {
            if ($account_history->id == 567972) {
                continue;
            }

            $account_history->balance += 60;
            $account_history->update();
        }

        $user = Users::findFirstById(1296027);
        $user->diamond = 170;
        $user->pay_amount = 66;
        $user->update();

        echoLine(date("Ymd H:i:s", beginOfDay()));
        echoLine(date("Ymd H:i:s", endOfDay()));
        echoLine(date("Ymd H:i:s", strtotime('20180503 24:00:00')));

        $user_db = Users::getUserDb();
        $key = 'union_user_total_hi_coins_rank_list_union_id_' . 1068;
        $user_ids = $user_db->zrange($key, 0, -1, 'withscores');

        $total = 0;

        foreach ($user_ids as $user_id => $score) {
            $total += $score;
        }

        echoLine($total);

        $cond = [
            'conditions' => 'created_at >= :start: and created_at <= :end:',
            'bind' => ['start' => beginOfMonth(strtotime('2018-04-01')), 'end' => endOfMonth(strtotime('2018-04-30'))],
            'column' => 'hi_coins'
        ];

        $num = HiCoinHistories::sum($cond);

        echoLine($num);


        /*1068,1001,1189,1014,1011,1036,1186,1026,1098*/

        $user_db = Users::getUserDb();
        $day = 20180429;
        $day_key = 'union_user_day_hi_coins_rank_list_' . $day . '_union_id_' . 1068;
        $user_ids = $user_db->zrange($day_key, 0, -1, 'withscores');

        $total = 0;

        foreach ($user_ids as $user_id => $score) {
            $total += $score;
        }

        echoLine($total);

        $cond = [
            'conditions' => 'union_id = :union_id: and created_at >= :start: and created_at <= :end: and fee_type = :fee_type:',
            'bind' => ['union_id' => 1042, 'start' => beginOfDay(strtotime('2018-04-29')), 'end' => endOfDay(strtotime('2018-04-29')), 'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT],
            'column' => 'hi_coins'
        ];

        $num = HiCoinHistories::sum($cond);

        echoLine($num);

        $user_db = Users::getUserDb();
        $month_start = 20180401;
        $month_end = 20180430;
        $month_key = 'union_user_month_hi_coins_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . 1068;
        $user_ids = $user_db->zrange($month_key, 0, -1, 'withscores');

        $total = 0;

        foreach ($user_ids as $user_id => $score) {
            $total += $score;
        }

        echoLine($total);

        $cond = [
            'conditions' => 'union_id = :union_id: and created_at >= :start: and created_at <= :end: and fee_type = :fee_type:',
            'bind' => ['union_id' => 1068, 'start' => beginOfMonth(strtotime('2018-04-01')), 'end' => endOfMonth(strtotime('2018-04-01')), 'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT],
            'column' => 'hi_coins'
        ];

        $num = HiCoinHistories::sum($cond);

        echoLine($num);


    }

    function get()
    {
        $gift_orders = GiftOrders::find([
            'conditions' => 'status = :status:',
            'bind' => [
                'status' => GIFT_ORDER_STATUS_FREEZE
            ],
            'columns' => 'id'
        ]);

        //1008720
        $amount = GiftOrders::sum(
            [
                'conditions' => 'room_id = :room_id: and room_union_id = :room_union_id: and created_at >= :start: and 
                created_at <= :end: and  status = :status: and gift_type = :gift_type: and pay_type = :pay_type:',
                'bind' => ['room_id' => 1008720, 'room_union_id' => 1068, 'start' => beginOfDay(strtotime('2018-04-09')),
                    'end' => endOfDay(strtotime('2018-04-09')), 'status' => GIFT_ORDER_STATUS_SUCCESS, 'gift_type' => GIFT_TYPE_COMMON,
                    'pay_type' => GIFT_PAY_TYPE_DIAMOND],
                'column' => 'amount'
            ]
        );

        echoLine($amount);

        $ids = [];
        foreach ($gift_orders as $gift_order) {
            $gift_order = GiftOrders::findFirstById($gift_order->id);

            echoLine($gift_order->amount, $gift_order->room_id, $gift_order->room_union_id);

//            $hi_coin_history = HiCoinHistories::findFirstBy(['gift_order_id' => $gift_order->id]);
//
//            if($hi_coin_history) {
//                //            echoLine($hi_coin_history->union_id);
//                $hi_coin_history->hi_coins = 0;
//                $hi_coin_history->remark = $hi_coin_history->remark . "异常收到礼物";
//                $hi_coin_history->update();
//
//                echoLine($hi_coin_history->hi_coins, $hi_coin_history->union_id);
//            }
        }

    }

    function findUsers()
    {
        $user_db = Users::getUserDb();
        $key = 'union_user_total_hi_coins_rank_list_union_id_' . 1068;
        $user_ids = $user_db->zrange($key, 0, -1, 'withscores');

        $total = 0;

        foreach ($user_ids as $user_id => $score) {
            $total += $score;

            $cond = [
                'conditions' => 'union_id = :union_id: and user_id = :user_id: and created_at <= :end: and fee_type = :fee_type:',
                'bind' => ['union_id' => 1068, 'start' => beginOfMonth(strtotime('2018-04-01')),
                    'end' => endOfMonth(strtotime('2018-04-01')),
                    'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT, 'user_id' => $user_id],
                'column' => 'hi_coins'
            ];

            $num = HiCoinHistories::sum($cond);

            if ($score != $num) {
                echoLine($user_id, $score, $num);
            }
        }
    }

    function fixRoomIncome1Action()
    {
        $start_at = beginOfDay(strtotime('2018-03-13'));
        $room_db = Users::getUserDb();

        for ($day = $start_at; $day < beginOfDay(); $day += 86400) {

            $stat_at = date("Ymd", $day);

            $rooms = Rooms::dayStatRooms($stat_at);

            foreach ($rooms as $room) {

                $room_total_income = $room->getDayIncome($stat_at);

                $amount = GiftOrders::sum(
                    [
                        'conditions' => 'room_id = :room_id: and created_at >= :start: and 
                created_at <= :end: and  status = :status: and gift_type = :gift_type: and pay_type = :pay_type:',
                        'bind' => ['room_id' => $room->id, 'start' => beginOfDay(strtotime($stat_at)),
                            'end' => endOfDay(strtotime($stat_at)), 'status' => GIFT_ORDER_STATUS_SUCCESS, 'gift_type' => GIFT_TYPE_COMMON,
                            'pay_type' => GIFT_PAY_TYPE_DIAMOND],
                        'column' => 'amount'
                    ]
                );

                if ($amount != $room_total_income) {
                    echoLine($amount, $room_total_income, $stat_at, $room->id);
                    $room_db->zadd($room->generateStatIncomeDayKey($stat_at), $amount, $room->id);
                }
            }
        }
    }

    function exportAction()
    {
        $user_db = Users::getUserDb();
        $month_key = 'union_user_month_hi_coins_rank_list_start_' . 20180401 . '_end_' . 20180430 . '_union_id_' . 1068;

        $user_ids = $user_db->zrevrange($month_key, 0, -1, 'withscores');

        $hi_coin = 0;

        $titles = ['用户Id', "hi币收益"];

        $data = [];

        foreach ($user_ids as $user_id => $score) {
            $score = sprintf("%0.2f", $score / 1000);
            $hi_coin += $score;
            $data[] = [$user_id, $score];
        }

        $file = "hi_coins.xls";
        $res = writeExcel($titles, $data, $file, true);

        echoLine($res, StoreFile::getUrl($res));

        $user_db = Users::getUserDb();
        $charm_key = 'union_user_month_charm_rank_list_start_' . 20180401 . '_end_' . 20180430 . '_union_id_' . 1068;
        echoLine($user_db->zcard($charm_key));
    }

    function fixDrawCarAction()
    {
        $cond = [
            'conditions' => 'sender_id = :sender_id: and gift_type = :gift_type: and created_at >= :start: and created_at <= :end:',
            'bind' => [
                'sender_id' => SYSTEM_ID, 'gift_type' => GIFT_TYPE_CAR, 'type' => GIFT_ORDER_TYPE_ACTIVITY_LUCKY_DRAW,
                'start' => beginOfDay(strtotime('2018-05-04')), 'end' => strtotime('2018-05-04 14:27:43'),
            ],
            'columns' => 'id'
        ];
        $gift_orders = GiftOrders::find($cond);

        echoLine(count($gift_orders));
        $db = Users::getUserDb();

        foreach ($gift_orders as $gift_order) {

            $gift_order = GiftOrders::findFirstById($gift_order->id);

            echoLine($gift_order->amount, $gift_order - $gift_order->user_id, $gift_order->receiver_union_id);

            continue;
            $user = $gift_order->user;
            $amount = $gift_order->amount;
            $charm_value = $amount;
            $receiver_union_id = $gift_order->receiver_union_id;

            $user->charm_value -= $charm_value;

            $time = $gift_order->created_at;
            $date = date("Ymd", $time);
            $start = date("Ymd", beginOfWeek($time));
            $end = date("Ymd", endOfWeek($time));
            $field = 'charm';

            $day_key = Users::generateFieldRankListKey('day', $field, ['date' => $date]);
            $week_key = Users::generateFieldRankListKey('week', $field, ['start' => $start, 'end' => $end]);
            $total_key = Users::generateFieldRankListKey('total', $field);


            $db->zincrby($day_key . "_" . $user->product_channel_id, -$charm_value, $user_id);
            $db->zincrby($day_key, -$charm_value, $user_id);

            $db->zincrby($week_key . "_" . $user->product_channel_id, -$charm_value, $user_id);
            $db->zincrby($week_key, -$charm_value, $user_id);

            $db->zincrby($total_key . "_" . $user->product_channel_id, -$charm_value, $user_id);
            $db->zincrby($total_key, -$charm_value, $user_id);


            $union = Unions::findFirstById($receiver_union_id);

            if (isPresent($union) && $union->type == UNION_TYPE_PRIVATE) {

                $user->union_charm_value -= $charm_value;

                $lock_key = "update_union_fame_lock_" . $id;
                $lock = tryLock($lock_key);
                $union->fame_value -= $charm_value;
                $union->update();

                $date = date("Ymd", $time);
                $start = date("Ymd", beginOfWeek($time));
                $end = date("Ymd", endOfWeek($time));

                $week_key = Unions::generateFameValueRankListKey('week', ['date' => $date]);
                $day_key = Unions::generateFameValueRankListKey('day', ['start' => $start, 'end' => $end]);

                $db->zincrby($day_key, -$charm_value, $union->id);
                $db->zincrby($day_key . "_" . $union->product_channel_id, -$charm_value, $union->id);
                $db->zincrby($week_key, -$charm_value, $union->id);
                $db->zincrby($week_key . "_" . $union->product_channel_id, -$charm_value, $union->id);

                $union->updateFameRankList($value, $opts);
                unlock($lock);

            }

            $user->update();
        }

        $amount = GiftOrders::sum(
            [
                'conditions' => 'created_at >= :start: and created_at <= :end: and  status = :status: and 
                gift_type = :gift_type: and pay_type = :pay_type:',
                'bind' => ['start' => beginOfDay(strtotime('2018-04-01')),
                    'end' => endOfDay(strtotime('2018-04-30')), 'status' => GIFT_ORDER_STATUS_SUCCESS, 'gift_type' => GIFT_TYPE_COMMON,
                    'pay_type' => GIFT_PAY_TYPE_DIAMOND],
                'column' => 'amount'
            ]
        );

        echoLine($amount);

        $fee_types = [HI_COIN_FEE_TYPE_RECEIVE_GIFT => '接收礼物', HI_COIN_FEE_TYPE_HOST_REWARD => '主播奖励',
            HI_COIN_FEE_TYPE_UNION_HOST_REWARD => '家族长奖励', HI_COIN_FEE_TYPE_WITHDRAW => '提现', HI_COIN_FEE_TYPE_ROOM_REWARD => '房间流水奖励',
            HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND => 'Hi币兑钻石', HI_COIN_FEE_TYPE_WITHDRAW_RETURN => '提现失败返还'];

        $cost = 0;
        $income = 0;
        $withdraw = 0;


        foreach ($fee_types as $fee_type => $text) {

            $cond = [
                'conditions' => 'created_at >= :start: and created_at <= :end: and fee_type = :fee_type:',
                'bind' => [
                    'start' => beginOfMonth(strtotime('2018-04-01')), 'end' => endOfMonth(strtotime('2018-04-30')),
                    'fee_type' => $fee_type
                ],
                'column' => 'hi_coins'
            ];

            $num = HiCoinHistories::sum($cond);

            if (in_array($fee_type, [HI_COIN_FEE_TYPE_HI_COIN_EXCHANGE_DIAMOND])) {

                $income += abs($num);

            } else {

                if (in_array($fee_type, [HI_COIN_FEE_TYPE_WITHDRAW_RETURN, HI_COIN_FEE_TYPE_WITHDRAW])) {
                    $withdraw += $num;
                } else {
                    $cost += abs($num);
                }
            }

            echoLine($text, $num);
        }

        $balance = $income - $cost;
        echoLine($income, $cost, $balance, $withdraw);


        $cond = [
            'conditions' => 'created_at >= :start: and created_at <= :end:',
            'bind' => [
                'start' => beginOfMonth(strtotime('2018-04-01')), 'end' => endOfMonth(strtotime('2018-04-30')),
                'fee_type' => $fee_type
            ],
            'column' => 'hi_coins'
        ];

        $num = HiCoinHistories::sum($cond);

        echoLine($num);

        static $FEE_TYPE = [
            ACCOUNT_TYPE_BUY_DIAMOND => '购买钻石',
            ACCOUNT_TYPE_BUY_GIFT => '购买礼物',
            ACCOUNT_TYPE_GIVE => '系统赠送',
            ACCOUNT_TYPE_CREATE_UNION => '创建家族',
            ACCOUNT_TYPE_CREATE_UNION_REFUND => '创建家族返还',
            ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND => 'Hi币兑钻石',
            ACCOUNT_TYPE_GAME_INCOME => '游戏收入',
            ACCOUNT_TYPE_GAME_EXPENSES => '游戏支出',
            ACCOUNT_TYPE_DEDUCT => '系统扣除',
            ACCOUNT_TYPE_DISTRIBUTE_REGISTER => '分销注册',
            ACCOUNT_TYPE_DISTRIBUTE_PAY => '分销充值',
            ACCOUNT_TYPE_DRAW_INCOME => '转盘抽奖收入',
            ACCOUNT_TYPE_DRAW_EXPENSES => '转盘抽奖支出',
            ACCOUNT_TYPE_RELEASE_WISH_EXPENSES => '发布愿望支出',
            ACCOUNT_TYPE_GUARD_WISH_EXPENSES => '守护愿望支出'

        ];

        $cond = [
            'conditions' => 'fee_type = :fee_type1: or fee_type = :fee_type2:',
            'bind' => ['fee_type1' => ACCOUNT_TYPE_BUY_DIAMOND, 'fee_type2' => ACCOUNT_TYPE_HI_COIN_EXCHANGE_DIAMOND]
        ];

        $gift_orders = GiftOrders::find([
            'conditions' => 'status = :status:',
            'bind' => [
                'status' => GIFT_ORDER_STATUS_FREEZE
            ],
            'columns' => 'id'
        ]);

        $gift_order_ids = [];

        foreach ($gift_orders as $gift_order) {
            $gift_order_ids[] = $gift_order->id;
            $gift_order = GiftOrders::findFirstById($gift_order->id);
        }

        echoLine($gift_order_ids);

        $user_ids = [1058027, 1060201, 1017233, 1060180, 1083050];

        foreach ($user_ids as $user_id) {
            $account_histories = AccountHistories::findByUserId($user_id);
            echoLine(count($account_histories));

            foreach ($account_histories as $account_history) {
                $account_history->delete();
            }
        }

        $cond = [
            'conditions' => 'created_at >= :start: and  created_at <= :end: ',
            'bind' => ['start' => beginOfMonth(strtotime('2018-03-01')), 'end' => endOfMonth(strtotime('2018-03-31')), 'fee_type' => ACCOUNT_TYPE_DEDUCT],
            'column' => 'amount'
        ];


        $amount = AccountHistories::sum($cond);
        echoLine($amount);

        $account_histories = AccountHistories::findByFeeType(ACCOUNT_TYPE_DEDUCT);

        foreach ($account_histories as $account_history) {
            echoLine($account_history->user_id);
            $account_history->delete();
        }

        $cond = [
            'conditions' => 'created_at >= :start: and created_at <= :end: and fee_type = :fee_type:',
            'bind' => [
                'start' => beginOfMonth(strtotime('2018-04-01')), 'end' => endOfMonth(strtotime('2018-04-30')),
                'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT
            ],
            'column' => 'hi_coins'
        ];

        $num = HiCoinHistories::sum($cond);
        echoLine($num);

        $id_card_auth = IdCardAuths::findFirstById(4927);
        $id_card_auth->delete();

        $gift_ids = [151, 146, 63, 147, 143];

        foreach ($gift_ids as $gift_id) {
            echoLine($gift_id);
            $backpack = new Backpacks();
            $backpack->target_id = $gift_id;
            $backpack->user_id = 52;
            $backpack->number = mt_rand(1, 10);
            $backpack->status = STATUS_ON;
            $backpack->type = 1;
            $backpack->save();
        }
    }

    function test40Action()
    {
        $hot_cache = Users::getHotWriteCache();
        $key = 'room_active_last_at_list_3';
        $time = time();

        for ($i = 1; $i <= 200; $i++) {

            $hot_cache->zadd($key, $time--, $i);

            $total = $hot_cache->zcard($key);
        }

        if ($total >= 100) {
            $hot_cache->zremrangebyrank($key, 0, $total - 100);
        }

        echoLine($hot_cache->zrevrange($key, 0, -1), $hot_cache->zcard($key));

        $hot_cache = Users::getHotWriteCache();
        $key = 'room_active_last_at_list_3';
        $user_num = $hot_cache->zcount($key, '-inf', time() - 15 * 60);
        echoLine($user_num);

        echoLine(array_diff([1, 3, 4], [1]));

        $hot_room_ids = [1000 => 12, 10001 => 13, 10004 => 14, 1005 => 11];

        uksort($hot_room_ids, function ($a, $b) use ($hot_room_ids) {

            if ($hot_room_ids[$a] > $hot_room_ids[$b]) {
                return -1;
            }

            return 1;
        });

        $hot_room_ids = array_slice($hot_room_ids, 0, 5, true);

        print_r($hot_room_ids);

    }

    function test41Action()
    {

        $attrs = [
            'sender_id' => SYSTEM_ID,
            'receiver_id' => 196,
            'content' => 'dddd',
            'content_type' => CHAT_CONTENT_TYPE_TEXT_NEWS,
            'image_url' => 'http://mt-development.img-cn-hangzhou.aliyuncs.com/chance/users/avatar/20180404105ac4331fc4652.jpg',
            'title' => '测试',
            'url' => 'url://m/activities'
        ];

        \Chats::createChat($attrs);

        //return \Chats::createChat($attrs);

        $hot_cache = Rooms::getHotWriteCache();
        $cond = ['conditions' => 'user_type = :user_type: and status = :status: and online_status = :online_status: 
        and theme_type = :theme_type: and user_id > 0',
            'bind' => ['user_type' => USER_TYPE_SILENT, 'status' => STATUS_OFF, 'online_status' => STATUS_OFF,
                'theme_type' => ROOM_THEME_TYPE_NORMAL
            ],
            'order' => 'last_at desc', 'limit' => 100];

        $rooms = Rooms::find($cond);

        foreach ($rooms as $room) {

            if (!$room->user) {
                continue;
            }

            echoLine($room->id);
            $room->enterRoom($room->user);
            $room->status = STATUS_OFF;
            $room->online_status = STATUS_OFF;
            $room->update();
            $hot_cache->zadd('ios_auth_room_list', time(), $room->id);
        }
        echoLine(count($rooms));

        $room_ids = [90, 91, 92, 95, 115, 117, 111, 122, 180, 107];

        $hot_cache = Rooms::getHotWriteCache();
        $room_ids = $hot_cache->zrange('ios_auth_room_list', 0, -1);

        foreach ($room_ids as $room_id) {
            $room = Rooms::findFirstById($room_id);
            $room->online_status = STATUS_ON;
            $room->update();
        }

        foreach ($room_ids as $id) {
            $hot_cache->zadd('ios_auth_room_list', time(), $id);
        }
    }

    function test42Action()
    {
        $hot_cache = Users::getHotWriteCache();
        $res = $hot_cache->setex("key_test_get", 13, 23);
        echoLine($res);

        $user = Users::findFirstById(1247538);
        echoLine($user->geo_city_id, $user->city_id);

        $users = Users::find([
            'conditions' => 'city_id = :city_id: or geo_city_id = :city_id1: and ip_city_id = :city_id2:',
            'bind' => ['city_id' => 33, 'city_id1' => 33, 'city_id2' => 33],
            'columns' => 'id'
        ]);
        echoLine(count($users));


    }
}