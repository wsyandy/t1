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

    function fixUserLevelAction()
    {
        $gift_orders = GiftOrders::findForeach();

        foreach ($gift_orders as $gift_order) {
            echoLine($gift_order->id, $gift_order->user_id, $gift_order->sender_id);
            Users::updateExperience($gift_order->id);
        }
    }

    function fixUserSegmentAction()
    {
        $users = Users::find(['conditions' => "level > 0"]);

        foreach ($users as $user) {
            echoLine($user->id, $user->calculateSegment());
            $user->segment = $user->calculateSegment();
            $user->save();
        }
    }

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

            $user->hi_coins = $total_amount / 25;
            echoLine($i, $total_amount, $user->hi_coins);
            $user->update();
        }
    }
}

