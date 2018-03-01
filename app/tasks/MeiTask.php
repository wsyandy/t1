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
        $user = Users::findFirstById(39);
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
        $ThisFileInfo = $getID3->analyze(APP_ROOT . "public/temp/5a7bcab1b95e2.mp3");   //分析文件
        $time = $ThisFileInfo['playtime_seconds'];      //获取mp3的长度信息
        echo $ThisFileInfo['playtime_seconds'];         //获取MP3文件时长
        var_dump($ThisFileInfo);
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
//        $user->experience = 1.234567892345678923456789;
//        $user->save();
//        $user->experience = 385000;
//        $user->save();
        echoLine($user->segment, $user->segment_text);
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
        $fp = fopen(APP_ROOT . "public/temp/5a7bcab1b95e2.mp3", "rb");
        //fseek($fp, -128, SEEK_END);
        $tag = fread($fp, 8);
        var_dump($tag, fread($fp, 30));
    }
}