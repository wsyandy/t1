<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/2/7
 * Time: 下午3:49
 */
class KangTask extends \Phalcon\Cli\Task
{

    function redAction($params){
        $red_id = $params[0];
        $user_id = $params[1];

        $red = RedPackets::findFirstById($red_id);
        $user = Users::findFirstById($user_id);

        $red->getRedPacketDiamond($user->id);
        
    }

    function xxAction(){

        $diamond = 1000;
        $num = 10;
        $red_packet_type = RED_PACKET_TYPE_FOLLOW;

        $avg_diamond = ceil($diamond / $num);
        $min_diamond = 1;
        $max_diamond = ceil($diamond * 0.35);

        if ($red_packet_type == RED_PACKET_TYPE_NEARBY) {
            $min_diamond = 50;
        }
        if ($red_packet_type == RED_PACKET_TYPE_FOLLOW || $red_packet_type == RED_PACKET_TYPE_STAY_AT_ROOM) {
            $min_diamond = 5;
        }

        $user_rate = mt_rand(1, 100);
        if ($user_rate < mt_rand(60, 80)) {
            echoLine('均', $avg_diamond);
            if ($avg_diamond - ceil($diamond * 0.1) < $min_diamond) {
                $get_diamond = mt_rand($min_diamond, $avg_diamond + ceil($diamond * 0.1));
            } else {
                $get_diamond = mt_rand($avg_diamond - ceil($diamond * 0.1), $avg_diamond + ceil($diamond * 0.1));
            }
        } else {
            if (mt_rand(1, 100) < 50) {
                echoLine('小', $min_diamond);
                if($min_diamond > ceil($diamond * 0.06)){
                    $get_diamond = mt_rand($min_diamond, $min_diamond * 2);
                }else{
                    $get_diamond = mt_rand($min_diamond, ceil($diamond * 0.06));
                }
            } else {
                echoLine('大', $max_diamond);
                $get_diamond = mt_rand(ceil($diamond * 0.2), $max_diamond);
            }
        }

        echoLine($user_rate, $get_diamond);
    }

    function fixDisAction()
    {

        $account_histories = \AccountHistories::find(['conditions' => '(fee_type=:fee_type1: or fee_type=:fee_type2: or fee_type=:fee_type3:)',
            'bind' => ['fee_type1' => ACCOUNT_TYPE_DISTRIBUTE_REGISTER, 'fee_type2' => ACCOUNT_TYPE_DISTRIBUTE_PAY,
                'fee_type3' => ACCOUNT_TYPE_DISTRIBUTE_EXCHANGE],
            'order' => 'id asc'
        ]);

        foreach ($account_histories as $account_history) {
            $sms_dis = SmsDistributeHistories::findFirstById($account_history->target_id);
            if ($sms_dis) {
                $account_history->target_id = $sms_dis->user_id;
                $account_history->save();
                echoLine($account_history->id, $account_history->target_id);
            }
        }
    }

    function hmAction()
    {
        $sd = Users::getUserDb();
        $sd->hset('hsxxx', 10, 11);
        $sd->hset('hsxxx', 20, 22);
        $sd->hset('hsxxx', 30, 33);
        $sd->hset('hsxxx', 40, 44);

        $data = $sd->hmget('hsxxx', [40, 20, 30]);
        echoLine($data);
    }

    function commonBody()
    {
        $body = array(
            'debug' => 1,
            'code' => 'yuewan',
            'dno' => 'dnotest',
            'sid' => 'sidtest',
            'man' => 'apple',
            'mod' => 'iphone',
            'an' => '1.0',
            'h' => 'h',
            'fr' => 'local',
            'pf' => 'ios',
            'pf_ver' => '10.0.1',
            'verc' => '15',
            'ver' => '1.0',
            'ts' => time(),
            'net' => 'wifi',
        );
        return $body;
    }

    function testActiveAction()
    {
        $url = 'http://www.chance_php.com/api/devices/active';
        $body = array_merge($this->commonBody(), array(
            'ua' => 'ios',
            'ei' => '11111',
            'imei' => '1111',
            'if' => '1111',
            'idfa' => '1111',
        ));
        $res = httpPost($url, $body);
        var_dump($res);
    }

    function testRegisterAction()
    {
        $url = 'http://www.chance_php.com/api/users/register';
        $mobile = '13800000000';
        $auth_code = '1234';
        $password = 'test12';
        $sms_token = '';
        $body = array(
            'sms_token' => $sms_token,
            'auth_code' => $auth_code,
            'password' => $password, 'mobile' => $mobile);
        $body = array_merge($body, $this->commonBody());
        $res = httpPost($url, $body);
        var_dump($res);
    }

    function testCreateEmchatAction()
    {
        $url = 'http://www.chance_php.com/api/users/emchat';
        $body = array_merge($this->commonBody(), array('sid' => '1s2867faffa7acb625226c6eb1e2dca91b29'));

        $res = httpPost($url, $body);
        var_dump($res);
    }

    function freshAttrsAction()
    {
        $user = \Users::findById(1);
        $user->platform = 'ios';
        $user->save();
    }

    function testProfileAction()
    {
        $url = "http://www.chance_php.com/api/users/detail";
        $body = array_merge($this->commonBody(), array('sid' => '2s36fc9464a3b37466a88951d0318c90a3b6'));

        $res = httpPost($url, $body);
        var_dump($res);
    }

    function testUserGiftsAction()
    {
        $user = \Users::findById(2);
        if ($user) {
            echo count($user->user_gifts) . PHP_EOL;
        }

        $cond = array();
        $results = \UserGifts::find();

        echo count($results) . PHP_EOL;
        //echo json_encode($results, JSON_UNESCAPED_UNICODE);

        $user_gift = \UserGifts::findLast();
        echo json_encode($user_gift->toJson(), JSON_UNESCAPED_UNICODE);
    }

    function testUserGiftsIndexAction()
    {
        $url = "http://www.chance_php.com/api/user_gifts";
        $body = array_merge($this->commonBody(), array('sid' => '2s36fc9464a3b37466a88951d0318c90a3b6', 'page' => 2));

        $res = httpPost($url, $body);
        var_dump($res);
    }


    function geoAction()
    {

        $users = Users::findForeach();
        foreach ($users as $user) {
            if ($user->latitude < $user->longitude) {
                $geo_hash = new \geo\GeoHash();
                $hash = $geo_hash->encode($user->latitude / 10000, $user->longitude / 10000);
                info($user->id, $user->latitude, $user->longitude, $hash);
                if ($hash) {
                    $user->geo_hash = $hash;
                }
                $user->update();
            }
        }

        $user = Users::findFirstById(8);
        $users = $user->nearby(1, 10);
        foreach ($users as $user) {
            echoLine($user->id);
        }
    }

    function disAction()
    {
        $user = Users::findFirstById(8);
        $users = $user->nearby(1, 10);
        $user->calDistance($users);

        echoLine($users);

        foreach ($users as $user) {
            echoLine($user->id, $user->geo_hash, $user->distance);
        }

        echoLine('cc', $users->count());
    }

    //初始化头像
    function initAvatarAction()
    {
        $res = StoreFile::upload(APP_ROOT . "public/images/avatar.png", APP_NAME . '/users/avatar/default_avatar.png');
        echoLine($res);

        echoLine(StoreFile::getUrl('chance/users/avatar/default_avatar.png '));
    }

    /**
     * 测试我的账户api
     */
    function accountAction()
    {
        $url = 'http://www.chance_php.com/api/users/account';
        $body = $this->commonBody();

        $user = \Users::findById(2);
        $body = array_merge($body, array('sid' => $user->sid));

        $res = httpGet($url, $body);
        var_dump($res);
    }

    /**
     * 测试产品api
     */
    function productsAction()
    {
        $url = 'http://www.chance_php.com/api/products';
        $body = $this->commonBody();

        $user = \Users::findById(2);
        $body = array_merge($body, array('sid' => $user->sid));

        $res = httpGet($url, $body);
        var_dump($res);
    }

    //重置用户
    function resetAction()
    {
        $user_id = 137;
        $new_user_id = 6;

        $user = Users::findFirstById($user_id);
        $new_user = Users::findFirstById($new_user_id);

        if ($new_user->user_type != USER_TYPE_SILENT) {
            echoLine("非法操作 用户不是沉默用户");
            //return;
        }

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
        $user->room_id = 0;
        $user->current_room_id = 0;
        $user->current_room_seat_id = 0;
        $user->user_role = 0;
        $user->gold = 0;
        $user->diamond = 0;
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
        $room = Rooms::findFirstById($user_id);

        if ($room) {
            $room->user_id = $new_user_id;
            $room->save();
        }

        $room_seat = RoomSeats::findFirstById($user_id);

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
    }

    function giveDiamondAction()
    {
        $users = Users::findBy(['user_type' => USER_TYPE_SILENT]);
        $amount = 10000;

        foreach ($users as $user) {
            $opts = ['remark' => '系统赠送' . $amount . '钻石', 'mobile' => $user->mobile, 'operator_id' => 1];

            if ($amount > 0) {
                \AccountHistories::changeBalance($user, ACCOUNT_TYPE_GIVE, $amount, $opts);
            }
        }
    }

    function fixHiCoinsAction()
    {
        $users = Users::find([
            'conditions' => 'user_type = :user_type: and mobile is not null',
            'bind' => ['user_type' => USER_TYPE_ACTIVE],
            'order' => 'id desc'
        ]);

        foreach ($users as $user) {
            $user_gifts = UserGifts::find(
                [
                    'conditions' => 'user_id = :user_id:',
                    'bind' => ['user_id' => $user->id],
                    'order' => 'id desc'
                ]
            );

            $total_amount = 0;
            foreach ($user_gifts as $user_gift) {
                $total_amount = $total_amount + $user_gift->total_amount;
            }
            $hi_coins = $total_amount / 10;
            echoLine($hi_coins);
            $user->hi_coins = $hi_coins;
            $user->save();
        }
    }

    function callAction()
    {

        $hot = Users::getHotWriteCache();
        $hot->set("key1", 1);
        $hot->set("key2", 2);
        $hot->set("key3", 3);
        $hot->set("key4", 4);
        echoLine($hot->get("key2"));
        $keys = $hot->keys('key2*');
        echoLine($keys);
    }

    function call2Action()
    {

        $name = 'aaa:';
        $m = ['aaa:11', 'aaa:12', 'aaa:13', 'aaa:14'];
        $m2 = array_map(function ($a) use ($name) {
            return str_replace($name, '', $a);
        }, $m);

        echoLine($m2);
    }

    function fixUserAction($params)
    {
        $user = Users::findFirstById($params[0]);
        if (isset($params[1])) {
            $user->third_unionid = $params[1];
            $user->save();
        } else {
            $user->third_unionid = '';
            $user->login_name = '';
        }

        $user->save();

        echoLine($user);
    }

    function findUserAction($params)
    {
        $third_unionid = $params[0];
        $third_name = 'qq';
        $user = \Users::findFirstByThirdUnionid(ProductChannels::findFirstById(1), $third_unionid, $third_name);
        echoLine($user);
    }

    function newUserAction()
    {

        $device = Devices::findFirstById(1);
        $user = \Users::registerForClientByDevice($device, true);
        echoLine($user);
    }

    function fixUidAction($params)
    {

        $cond = ['conditions' => 'id>=:min_id: and id<=:max_id: and uid < 0', 'bind' => ['min_id' => $params[0], 'max_id' => $params[1]]];
        echoLine($cond);
        $users = Users::findForeach($cond);
        foreach ($users as $user) {
            $user->uid = $user->id;
            $user->save();
        }
    }

    function fixRUidAction($params)
    {
        $cond = ['conditions' => 'id>=:min_id: and id<=:max_id: and user_id > 0 and uid < 0', 'bind' => ['min_id' => $params[0], 'max_id' => $params[1]]];
        echoLine($cond);
        $rooms = Rooms::findForeach($cond);
        foreach ($rooms as $room) {
            $room->uid = $room->id;
            $room->save();
        }
    }

    function fixUUidAction($params)
    {
        $cond = ['conditions' => 'id>=:min_id: and id<=:max_id: and uid < 0', 'bind' => ['min_id' => $params[0], 'max_id' => $params[1]]];
        echoLine($cond);
        $unions = Unions::findForeach($cond);
        foreach ($unions as $union) {
            $union->uid = $union->id;
            $union->save();
        }
    }


    function isGoodNum($num)
    {
        // 由3个以内数字组成的号码
        $num_array = array_unique(str_split($num));
        if (count($num_array) <= 3) {
            //echoLine('good 3', $num);
            return true;
        }

        //匹配6位以上递增
        if (preg_match('/(?:0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){5}\\d/', $num)) {
            //echoLine('匹配6位以上递增', $num);
            return true;
        }
        // 匹配6位以上递降
        if (preg_match('/(?:9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)){5}\\d/', $num)) {
            //echoLine('匹配6位以上递降', $num);
            return true;
        }

        // 匹配4-9位连续的数字
        if (preg_match('/(?:(?:0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)){3,}|(?:9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)){3,})\\d/', $num)) {
            //echoLine('匹配4-9位连续的数字', $num);
            return true;
        }

        //匹配3位以上的重复数字
        if (preg_match("/([\\d])\\1{2,}/", $num)) {
            //echoLine('匹配3位以上的重复数字', $num);
            return true;
        }

        //AABB
        if (preg_match('/^\\d{0,3}(\\d)\\1(\\d)\\2\\d{0,3}$/', $num)) {
            //echoLine('AABB ',$num);
            return true;
        }

        // AAABBB
        if (preg_match('/^\\d{0,3}(\\d)\\1\\1(\\d)\\2\\2\\d{0,3}$/', $num)) {
            //echoLine('AAABBB', $num);
            return true;
        }

        // ABCABC
        if (preg_match('/^\\d{0,3}(\\d)(\\d)(\\d)\\1\\2\\3\\d{0,3}$/', $num)) {
            //echoLine('ABCABC', $num);
            return true;
        }

        if (preg_match("/^(520|1314|2018)/", $num)) {
            //echoLine('good 开头520|1314', $num);
            return true;
        }

        if (preg_match("/(1314)$/", $num)) {
            //echoLine('good 结尾1314', $num);
            return true;
        }

        return false;
    }

    function goodNoAction($params)
    {

        $min_id = $params[0];
        $min_max = $params[1];

        $user = Users::findLast();
        if ($min_id < $user->id + 10000) {
            $min_id = $user->id + 10000;
        }

        echoLine($min_id, $min_max, 'user', $user->id);

        $user_db = Users::getUserDb();
        $good_no_uid = 'good_no_uid_list';
        $not_good_no_uid = 'not_good_no_uid_list';

        $count = 0;
        for ($i = $min_id; $i < $min_max; $i++) {
            if ($this->isGoodNum($i)) {
                $count++;
                $user_db->zadd($good_no_uid, $i, $i);
            } else {
                $user_db->zadd($not_good_no_uid, $i, $i);
            }
        }

        echoLine('count', $count);
    }

    function testUidAction()
    {

        $user = Users::findFirstById(1);
        $user->generateUid2();
    }

    function noAction($params)
    {

        $min_id = $params[0];
        $min_max = $params[1];

        echoLine($min_id, $min_max);

        $count = 0;
        for ($i = $min_id; $i < $min_max; $i++) {
            if ($this->isGoodNum($i)) {
                $count++;
            } else {
                echoLine('not good', $i);
            }
        }

        echoLine('count', $count);
    }

    function fixThirdNameAction()
    {

        $third_name = 'sina';
        $new_third_name = 'sinaweibo';

        $cond['conditions'] = 'third_name = :third_name:';
        $cond['bind'] = ['third_name' => $third_name];
        $cond['order'] = 'id desc';

        $users = Users::findForeach($cond);

        foreach ($users as $user) {
            $user->third_name = $new_third_name;
            $user->save();
            echoLine($user->id, $user->third_name);
        }

        $cond['conditions'] = 'third_name = :third_name:';
        $cond['bind'] = ['third_name' => $third_name];
        $cond['order'] = 'id desc';
        $third_auths = ThirdAuths::findForeach($cond);

        foreach ($third_auths as $third_auth) {
            $third_auth->third_name = $new_third_name;
            $third_auth->save();
            echoLine($third_auth->id, $third_auth->third_name);
        }

    }

    function sendMsgAction()
    {

        $product_channel = ProductChannels::findFirstById(1);

        $content = <<<EOF
系统通知：
2018年5月15日0点将停止荣耀等级特权赠送ID活动；
5月15日之前升级段位的用户，平台按现有活动赠送标准给予特权ID奖励；
5月15日0点之后升级段位的用户，平台将不再给予特权ID奖励；
请把各位用户把握住机会噢！
EOF;

        $body = '';
        $platforms = ['ios', 'android'];

        if (isProduction()) {
            foreach ($platforms as $platform) {
                GeTuiMessages::globalPush($product_channel, $platform, $content, $body);
            }
        }

        $users = Users::find([
            'conditions' => 'product_channel_id = :product_channel_id: and register_at > 0 and user_type = :user_type: and last_at >= :last_at:',
            'bind' => ['product_channel_id' => 1, 'user_type' => USER_TYPE_ACTIVE, 'last_at' => time() - 30 * 86400],
            'columns' => 'id'
        ]);

        echoLine(count($users));

        $delay = 1;
        $user_ids = [];
        $num = 0;

        foreach ($users as $user) {

            $num++;
            $user_ids[] = $user->id;

            if ($num >= 50) {
                echoLine($delay, $user_ids);
                Chats::delay($delay)->batchSendTextSystemMessage($user_ids, $content);
                $delay = mt_rand(1, 3600);
                $user_ids = [];
                $num = 0;
            }
        }
    }

    public function slowSqlAction()
    {

        $this->db = Users::di('db');
        $sql = "select pid, query, query_start from pg_stat_activity where state='active'";
        $result = $this->db->query($sql);

        $max_diff = 2;
        $results = array();
        $kill_sql = array();
        $now = time();
        while ($body = $result->fetch()) {
            $results[md5($body['query'])][] = $body['pid'];
            $diff = $now - strtotime($body['query_start']);
            echoLine($diff, $body);
            if ($diff >= $max_diff) {
                $kill_sql[] = md5($body['query']);
            }
        }

        if (count($kill_sql) > 0) {
            foreach ($kill_sql as $key) {
                $value = $results[$key];
                // 进程数大于等于5, 如果有一个sql超出时间
                if ($value && count($value) >= 2) {
                    foreach ($value as $k => $v) {
                        echoLine($k, $v);
                    }
                }
            }

        }
    }

    function fixGeoAction($params)
    {

        $cond = ['conditions' => 'id>=:min_id: and id<=:max_id:', 'bind' => ['min_id' => $params[0], 'max_id' => $params[1]]];
        echoLine($cond);
        $users = Users::findForeach($cond);
        foreach ($users as $user) {
            $user->updateGeoHashRank();
        }
    }

    function fixGeo2Action()
    {

        $block_near_by_user_ids = Users::getBlockedNearbyUserIds();
        $users = Users::findByIds($block_near_by_user_ids);

        foreach ($users as $user) {
            $user->delGeoHashRank();
        }

    }

    function sliceAction()
    {

        $room_ids = [12, 15, 18, 19, 30, 40, 58, 78, 98];

        $total_num = count($room_ids);
        if ($total_num < 1) {
            echoLine(date('c'), 'error no room');
            return;
        }

        $per_page = 2;
        $loop_num = ceil($total_num / $per_page);
        $offset = 0;

        for ($i = 0; $i < $loop_num; $i++) {
            $slice_ids = array_slice($room_ids, $offset, $per_page);
            $offset += $per_page;
            echoLine($slice_ids);
        }
    }

    function fixGoldAction()
    {

        $golds = GoldHistories::find(['conditions' => '(order_id > 0 or gift_order_id > 0 or hi_coin_history_id > 0 or activity_id > 0) and target_id is null and created_at < :created_at:',
            'bind' => ['created_at' => beginOfDay(strtotime('20180505'))]
        ]);

        foreach ($golds as $gold) {
            if ($gold->order_id) {
                $gold->target_id = $gold->order_id;
            }
            if ($gold->gift_order_id) {
                $gold->target_id = $gold->gift_order_id;
            }
            if ($gold->hi_coin_history_id) {
                $gold->target_id = $gold->hi_coin_history_id;
            }
            if ($gold->activity_id) {
                $gold->target_id = $gold->activity_id;
            }

            $gold->save();
            echoLine($gold->id, $gold->target_id);
        }


    }

}