<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午9:49
 */

class MeiTask extends \Phalcon\Cli\Task
{
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
        $user_id = 1001303;

        $user = Users::findFirstById($user_id);
        $opts = ['remark' => '系统赠送' . 1000 . '钻石', 'operator_id' => 1, 'mobile' => $user->mobile];
        \AccountHistories::changeBalance($user_id, ACCOUNT_TYPE_GIVE, 1000, $opts);
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
    }

    function setGoodNumAction()
    {
        $db = \Users::getUserDb();
        $good_num_list_key = 'good_num_list';
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

        $union = Unions::findFirstById(1001);
        echoLine($union);
    }
}