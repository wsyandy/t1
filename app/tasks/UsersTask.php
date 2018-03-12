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
                echoLine("no gift_order");
                continue;
            }

            $experience = 0;

            foreach ($gift_orders as $gift_order) {
                $amount = $gift_order->amount;
                $sender_experience = 0.02 * $amount;
                $experience += $sender_experience;
            }

            $user->experience = $experience;
            $user->level = $user->calculateLevel();
            $user->segment = $user->calculateSegment();
            echoLine($user->experience, $user->level, $user->segment);
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

            if ($hi_coins == $user->hi_coins) {
                //echoLine("no need fix", $user->id, $hi_coins);
                continue;
            }

            $user->hi_coins = $hi_coins;
            //echoLine($total_amount, $rate);
            //echoLine($i, $total_amount, $user->id, $user->hi_coins);
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
}

