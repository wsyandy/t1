<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/13
 * Time: 下午2:49
 */
require 'CommonParam.php';

class YangTask extends \Phalcon\Cli\Task
{
    use CommonParam;

    function testAction($params)
    {
        $url = "http://chance.com/api/friends";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test2Action($params)
    {
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if (!$user) {
            echoLine("no user");
            return;
        }
        echoLine($user->toDetailJson());
    }

    function test3Action()
    {
        $url = "http://chance.com/api/chats";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid, 'user_id' => 2));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test4Action()
    {
        $url = "http://chance.com/api/emoticon_images";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function audioChaptersAction($params)
    {
        $room_id = $params[0];
        $rank = $params[1];
        $url = "http://chance.com/api/audio_chapters";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'room_id' => $room_id, 'rank' => $rank]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test5Action($params)
    {
        $url = "http://chance.com/api/room_themes";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test6Action()
    {
        $url = "http://chance.com/api/rooms/set_theme";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'id' => 15, 'room_theme_id' => '2']);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test7Action()
    {
        $url = "http://chance.com/api/rooms/close_theme";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'id' => 15]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test8Action()
    {
        $url = "http://chance.com/api/rooms/detail";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'id' => 15]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function openMusicPermissionAction($params)
    {
        $url = "http://chance.com/api/room_seats/open_music_permission";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if (!$user) {
            return echoLine("此用户不存在");
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $room = $user->room;
        if (!$room) {
            return echoLine("此用户的房间不存在");
        }
        $room_seat = RoomSeats::findFirstByRoomId($room->id);
        $body = array_merge($body, ['sid' => $user->sid, 'id' => $room_seat->id]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function closeMusicPermissionAction($params)
    {
        $url = "http://chance.com/api/room_seats/close_music_permission";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if (!$user) {
            return echoLine("此用户不存在");
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $room = $user->room;
        if (!$room) {
            return echoLine("此用户的房间不存在");
        }
        $room_seat = RoomSeats::findFirstByRoomId($room->id);
        $body = array_merge($body, ['sid' => $user->sid, 'id' => $room_seat->id]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function detailAction($params)
    {
        $url = "http://chance.com/api/shares/detail";
        $body = $this->commonBody();
        $id = $params[0];
        $share_source = $params[1];
        $user = \Users::findFirstById($id);
        if (!$user) {
            return echoLine("此用户不存在");
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }

        $body = array_merge($body, ['sid' => $user->sid, 'share_source' => $share_source]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function resultAction($params)
    {
        $url = "http://chance.com/api/shares/result";
        $body = $this->commonBody();

        $id = $params[0];
        $history_id = $params[1];
        $status = $params[2];
        $type = $params[3];

        $user = \Users::findFirstById($id);
        if (!$user) {
            return echoLine("此用户不存在");
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }

        $body = array_merge($body, ['sid' => $user->sid, 'share_history_id' => $history_id, 'status' => $status, 'type' => $type]);
        $res = httpGet($url, $body);
        echoLine($res);
    }


    function bannersAction($params)
    {
        $url = "http://chance.com/api/banners/index";
        $body = $this->commonBody();

        $id = $params[0];

        $user = \Users::findFirstById($id);
        if (!$user) {
            echoLine("此用户不存在");
            return;
        }
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }

        $body = array_merge($body, ['sid' => $user->sid, 'new' => 1, 'hot' => 1]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test9Action()
    {
        $time = time();
        $days = [];
        $hours = [8, 10, 12, 14, 16, 18];
        for ($i = 0; $i < 5; $i++) {
            $day = beginOfDay($time + $i * 60 * 60 * 24);
            $times = [];
            foreach ($hours as $hour) {
                $time_at = $day + $hour * 60 * 60;
                $times[date('H-i', $time_at)] = $time_at;
            }

            $days[date("m月d日", $day)] = $times;
        }
        var_dump($days);
//        echoLine("----------------");
//        $test2 = beginOfDay(time());
//        echoLine($test2 + 60 * 60 * 4);
//        $time3 = strtotime(date('Y-m-d 04:00', $time));
//        echoLine($time3);
//        echoLine(date('Y-m-d-h-i-sa', $time3));
//        echoLine("----------------");
//        $time4 = $time3 + 60 * 60 * 2 - 1;
//        echoLine($time4);
//        $time5 = strtotime(date('Y-m-d 05:59:59', $time));
//        echoLine($time5);
//        echoLine(date('Y-m-d-h-i-sa', $time5));
        $start = date("Y-m-d-H-i-s", strtotime("last sunday next day", time()));
        $end = date("Y-m-d-H-i-s", strtotime("next monday", time()) - 1);
        echoLine($start);
        echoLine($end);

        $time_3 = date("Y-m-d-H-i-s", strtotime(date('Y-m-d H:i:59', $time)));
        $time_4 = date("Y-m-d-H-i-s", strtotime("+10 minute", $time));


        echoLine($time_3);
        echoLine($time_4);
    }

    function test10Action()
    {
        $users = Users::find([
            'limit' => 30
        ]);
        $union = Unions::findFirstById(59);
        foreach ($users as $user) {
            $union->applyJoinUnion($user);
        }
    }

    function test12Action($params)
    {
        $db = Users::getUserDb();

        $command = $params[0];
        if ($command == 1) {
            echoLine("----" . $db->setex("yangxing", 60, 2));
        } else {
            echoLine("++++" . $db->ttl("yangxing"));
        }
    }


    function test13Action($params)
    {
        $url = "http://chance.com/api/users/is_sign_in";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test14Action($params)
    {
        $url = "http://chance.com/api/users/sign_in";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid));
        $res = httpPost($url, $body);
        echoLine($res);
    }

    function goldWorksAction($params)
    {
        $url = "http://chance.com/api/shares/gold_works";
        $body = $this->commonBody();
        $id = $params[0];
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test16Action($params)
    {
        $url = "http://chance.com/api/users/hi_coin_rank_list";
        $body = $this->commonBody();
        $id = $params[0];
        $type = $params[1];
        $page = $params[2];
        $per_page = $params[3];
        if ($params[4]) {
            $url = "http://ctest.yueyuewo.cn/api/users/hi_coin_rank_list";
        }
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid, 'list_type' => $type, 'page' => $page, 'per_page' => $per_page));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function test17Action($params)
    {
        $body = $this->commonBody();
        $id = $params[0];
        $type = $params[1];
        $field = $params[2];
        $page = 1;
        $per_page = 10;
        if ($params[3]) {
            $url = "http://ctest.yueyuewo.cn/api/users/" . $field . "_rank_list";
        } else {
            $url = "http://chance.com/api/users/" . $field . "_rank_list";
        }
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, array('sid' => $user->sid, 'list_type' => $type, 'page' => $page, 'per_page' => $per_page));
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function addFameRankListAction()
    {
        $users = Users::findForeach();
        $i = 0;
        foreach ($users as $user) {

            if ($i > 30) {
                break;
            }

            if ($user->union_id) {
                continue;
            }

            $union = new Unions();
            $union->name = rand(1, 100) . "_xxxx";
            $union->notice = "xxxx";
            $union->need_apply = 0;
            $union->product_channel_id = $user->product_channel_id;
            $union->user_id = $user->id;
            $union->auth_status = AUTH_SUCCESS;
            $union->mobile = $user->mobile;
            $union->type = UNION_TYPE_PRIVATE;
            $union->avatar_status = AUTH_SUCCESS;
            $union->fame_value = mt_rand(1, 100);
            $union->status = STATUS_ON;
            $union->avatar = 'chance/unions/avatar/5ab866d1dd1e0.jpg';
            $union->save();

            $union->updateFameRankList($union->fame_value);

            $i++;
        }
    }

    function fixCharmRankListAction()
    {
        $gift_orders = GiftOrders::findForeach();

        $db = Users::getUserDb();

        foreach ($gift_orders as $gift_order) {
            if ($gift_order != GIFT_ORDER_STATUS_SUCCESS || !$gift_order->gift->isDiamondPayType()) {
                continue;
            }

            $user_id = $gift_order->user_id;
            $charm = $gift_order->amount;


            $day_key = "day_charm_rank_list_" . date("Ymd");
            $start = date("Ymd", strtotime("last sunday next day", time()));
            $end = date("Ymd", strtotime("next monday", time()) - 1);
            $week_key = "week_charm_rank_list_" . $start . "_" . $end;
            $total_key = "total_charm_rank_list_";


            if ($gift_order->created_at >= beginOfDay() && $gift_order->created_at <= endOfDay()) {
                $db->zincrby($day_key, $charm, $user_id);
            }

            if ($gift_order->created_at >= strtotime("last sunday next day", time()) && $gift_order->created_at <= strtotime("next monday", time()) - 1) {
                $db->zincrby($week_key, $charm, $user_id);
            }

            $db->zincrby($total_key, $charm, $user_id);
            echoLine($user_id, $charm);
        }
    }


    function fixWealthRankListAction()
    {
        $db = Users::getUserDb();

        $start = date("Ymd", strtotime("last sunday next day", time()));
        $end = date("Ymd", strtotime("next monday", time()) - 1);

        $week_key = "week_wealth_rank_list_" . $start . "_" . $end;
        $total_key = "total_wealth_rank_list";

        $charm_week_key = "week_charm_rank_list_" . $start . "_" . $end;
        $charm_total_key = "total_charm_rank_list";

        $start_at = beginOfDay(strtotime($start));
        $end_at = endOfDay(strtotime($end));

        echoLine($week_key, $charm_week_key);

        $gift_orders = GiftOrders::find(
            [
                'conditions' => "created_at >= :start: and created_at <= :end_at:",
                'bind' => ['start' => $start_at, 'end_at' => $end_at]
            ]
        );

        $db->zclear($week_key);
        $db->zclear($charm_week_key);
        $db->zclear("total_wealth_rank_list_");
        $db->zclear("total_wealth_rank_list");
        $db->zclear("total_charm_rank_list_");
        $db->zclear("total_charm_rank_list");

        echoLine("sssss", count($gift_orders));
        foreach ($gift_orders as $gift_order) {

            if ($gift_order->status != GIFT_ORDER_STATUS_SUCCESS || !$gift_order->gift->isDiamondPayType()) {
                continue;
            }

            $sender_id = $gift_order->sender_id;
            $amount = $gift_order->amount;

            echoLine($gift_order->user_id, $sender_id, $amount, $week_key, $charm_week_key);

            if ($gift_order->created_at >= $start_at && $gift_order->created_at <= $end_at) {
                $db->zincrby($week_key, $amount, $sender_id);
                $db->zincrby($charm_week_key, $amount, $gift_order->user_id);
            }
        }
    }

    function fixTotalUserRankAction()
    {
        $users = Users::find(['conditions' => 'charm_value > 0 or wealth_value > 0']);

        $db = Users::getUserDb();
        $total_key = "total_charm_rank_list";
        $db->zclear($total_key);

        echoLine(count($users));

        foreach ($users as $user) {

            echoLine($user->id, $user->charm_value, $user->wealth_value);

            if ($user->charm_value > 0) {
                $db->zincrby($total_key, $user->charm_value, $user->id);
            }
        }
    }

    function test18Action()
    {
        $users = Users::findForeach(['conditions' => 'user_type = :user_type: and manufacturer like :manufacturer:',
            'bind' => ['user_type' => USER_TYPE_ACTIVE, 'manufacturer' => '%' . 'wei' . '%']]);

        $model = [];

        $manufacturer = [];

        foreach ($users as $user) {
            if (!in_array($user->device_model, $model)) {
                $model[] = $user->device_model;
            }


            if (!in_array($user->manufacturer, $manufacturer)) {
                $manufacturer[] = $user->manufacturer;
            }
        }

        echoLine($model);
        echoLine($manufacturer);

    }

    function test20Action()
    {
        $start_at = strtotime(201804070000);
        $end_at = strtotime(201804080000);
        echoLine($start_at);
        echoLine($end_at);

        echoLine(date("Y年m月d日H点", $start_at));
        echoLine(date("Y年m月d日H点", $end_at));

    }


    function test19Action()
    {
        $arr1 = [1, 2, 3];
        $arr2 = [2, 3, 4];

        $arr3 = array_merge($arr1, $arr2);
        echoLine($arr3);
        $arr4 = array_unique($arr3);

        echoLine(implode(',', $arr4));

    }


    function qingMingActivityAction($params)
    {
        $gift_id = $params[0];
        $start_at = $params[1];
        $end_at = $params[2];

        $db = Users::getUserDb();

        $charm_key = "qing_ming_activity_charm_list_" . date("Ymd", $start_at) . "_" . date("Ymd", $end_at);
        $wealth_key = "qing_ming_activity_wealth_list_" . date("Ymd", $start_at) . "_" . date("Ymd", $end_at);


        $gift_orders = GiftOrders::find(
            [
                'conditions' => " gift_id = :gift_id: and status = :status:" . " and created_at >= :start: and created_at <= :end_at:",
                'bind' => ['gift_id' => $gift_id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'start' => $start_at, 'end_at' => $end_at],
            ]
        );

        foreach ($gift_orders as $gift_order) {
            $sender_id = $gift_order->sender_id;
            $user_id = $gift_order->user_id;
            $amount = $gift_order->amount;

            $db->zincrby($charm_key, $amount, $user_id);
            $db->zincrby($wealth_key, $amount, $sender_id);
        }
    }

    function test21Action()
    {
        $url = "http://chance.com/iapi/rooms/index";
        $body = $this->commonBody();
        $id = 97;
        $user = \Users::findFirstById($id);
        if ($user->needUpdateInfo()) {
            $user = $this->updateUserInfo($user);
        }
        $body = array_merge($body, ['sid' => $user->sid, 'hot' => 1]);
        $res = httpGet($url, $body);
        echoLine($res);
    }

    function fixGiftOrdersAction()
    {
        $gift_orders = \GiftOrders::find([
            'conditions' => 'product_channel_id is null',
            'order' => 'id desc'
        ]);

        foreach ($gift_orders as $gift_order) {
            $gift_order->product_channel_id = $gift_order->user->product_channel_id;
            if (!$gift_order->update()) {
                debug('update gift_order false', $gift_order->id, $gift_order->user->product_channel_id);
            }
        }
    }
}
