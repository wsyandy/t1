<?php

class Couples extends BaseModel
{
    static $_only_cache = true;

    static function createReadyCpInfo($user)
    {
        $cache = \Users::getHotWriteCache();
        $key = self::generateReadyCpInfoKey($user->room_id);
        //初始化
        $body = ['sponsor_id' => $user->id, $user->id => 1];
        $cache->hmset($key, $body);
        info('初始化', $cache->hgetall($key));

        $cache->expire($key, 10 * 60);
        //同时起一个异步推送scoket
        self::delay(10 * 60 - 5)->asyncFinishCp($user, $key);
    }

    static function generateReadyCpInfoKey($room_id)
    {
        return 'ready_cp_info_room_' . $room_id;
    }

    static function updateReadyCpInfo($pursuer, $room_id)
    {
        $cache = \Users::getHotWriteCache();
        $key = self::generateReadyCpInfoKey($room_id);

        $body = ['pursuer_id' => $pursuer->id, $pursuer->id => 1];
        $cache->hmset($key, $body);
        info('更新', $cache->hgetall($key));
    }

    static function cpSeraglioInfo($user, $room_id)
    {
        $cache = \Users::getHotWriteCache();
        $key = self::generateReadyCpInfoKey($room_id);
        $data = $cache->hgetall($key);
        $sponsor_id = fetch($data, 'sponsor_id');
        $pursuer_id = fetch($data, 'pursuer_id');

        if (!$sponsor_id || !$pursuer_id) {
            return false;
        }

        //相互各自保存在自己的后宫里面
        $db = \Users::getUserDb();
        //发起者的后宫
        $sponsor_seraglio_key = self::generateSeraglioKey($sponsor_id);
        //追求者的后宫
        $pursuer_seraglio_key = self::generateSeraglioKey($pursuer_id);

        $db->zadd($sponsor_seraglio_key, 2, $pursuer_id);
        $db->zadd($pursuer_seraglio_key, 1, $sponsor_id);

        //保存时间
        $cp_marriage_time_key = self::generateCpMarriageTimeKey();
        $db->zadd($cp_marriage_time_key, time(), $sponsor_id . '_' . $pursuer_id);

        //删除redis中暂存的信息
        $cache->del($key);
        $body = ['action' => 'game_notice', 'type' => 'over', 'content' => 'cp结束',];
        self::sendCpFinishMessage($user, $body);

        return true;
    }

    static function generateSeraglioKey($user_id)
    {
        return 'seraglio_host_user_' . $user_id;
    }

    //记录组成cp时间
    static function generateCpMarriageTimeKey()
    {
        return 'cp_marriage_time';
    }

    //保存cp后互刷的情侣值
    static function generateCpInfoKey()
    {
        return 'cp_info';
    }

    static function getMarriageTime($sponsor_id, $pursuer_id)
    {
        $db = \Users::getUserDb();
        $key = self::generateCpMarriageTimeKey();
        $time = $db->zscore($key, $sponsor_id . '_' . $pursuer_id);
        if (!$time) {
            $time = time();
        }

        return $time;
    }

    static function findByRelationsForCp($relations_key, $page, $per_page, $current_user_id)
    {
        $user_db = Users::getUserDb();
        $total_entries = $user_db->zcard($relations_key);

        $offset = $per_page * ($page - 1);
        if ($offset >= $total_entries) {
            $users = [];
            $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
            $pagination->clazz = 'Users';
            return $pagination;
        }

        $user_id_scores = $user_db->zrevrange($relations_key, $offset, $offset + $per_page - 1, 'withscores');
        $user_ids = array_keys($user_id_scores);
        info($user_ids);

        $users = Users::findByIds($user_ids);
        foreach ($users as $user) {
            $cp_value_key = self::generateCpInfoForUserKey($current_user_id);
            $score = $user_db->zscore($cp_value_key, $user->id);
            $user->cp_value = $score;
        }

        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }

    static function updateCpInfo($opts)
    {
        info('全部参数', $opts);
        $sender_id = fetch($opts, 'sender_id');
        $receive_id = fetch($opts, 'receive_id');
        $amount = fetch($opts, 'amount');

        $db = \Users::getUserDb();
        $key = self::generateSeraglioKey($sender_id);
        $ids = $db->zrange($key, 0, -1);
        $status = $db->zscore($key, $receive_id);
        if (!$status) {
            //不在后宫中
            info($receive_id, $ids);
            return null;
        }

        //状态    如果为1，说明接收者为当初的发起者，赠送者为追求者
        $member = '';
        switch ($status) {
            case 1:
                $member = $receive_id . '_' . $sender_id;
                break;
            case 2:
                $member = $sender_id . '_' . $receive_id;
                break;
        }


        info('更新情侣值', $amount, $member);
        //520当天总榜，暂已停用


//        $cp_info_key = self::generateCpInfoKey();
//        $db->zincrby($cp_info_key, $amount, $member);

        //同步双方的情侣值
        $sender_key = self::generateCpInfoForUserKey($sender_id);
        $db->zincrby($sender_key, $amount, $receive_id);

        $receive_key = self::generateCpInfoForUserKey($receive_id);
        $db->zincrby($receive_key, $amount, $sender_id);

        //记录周榜情侣值
        $cp_week_charm_key = \Users::generateFieldRankListKey('week', 'cp');
        info('情侣值周榜增加', $cp_week_charm_key, $member);
        $db->zincrby($cp_week_charm_key, $amount, $member);
    }

    static function sendCpFinishMessage($user, $body)
    {
        //在游戏结束回调通知的时候，发送结束通知

        $intranet_ip = $user->getIntranetIp();
        $receiver_fd = $user->getUserFd();

        \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);
    }

    static function generateCpInfoForUserKey($user_id)
    {
        return 'cp_info_for_user_' . $user_id;
    }

    static function checkCpRelation($user_id, $other_user_id, $condition = false)
    {
        $db = \Users::getUserDb();
        $key = self::generateSeraglioKey($user_id);
        $status = $db->zscore($key, $other_user_id);

        //状态  通过user_id生成key，查看other_user_id的身份  如果为1，说明该用户为当初的发起者，当前用户为追求者
        //condition 默认为false   true=>根据身份判断双方身份，直接返回代表身份的ID  false=>仅用于查看判断双方是否是cp关系
        if ($status > 0) {
            if ($condition) {
                $sponsor_id = 0;
                $pursuer_id = 0;
                switch ($status) {
                    case 1:
                        $sponsor_id = $other_user_id;
                        $pursuer_id = $user_id;
                        break;
                    case 2:
                        $sponsor_id = $user_id;
                        $pursuer_id = $other_user_id;
                        break;
                }
                return [$sponsor_id, $pursuer_id];
            }
            return $status;
        }

        return null;

    }

    //获取cp分页列表
    static function findByUsersListForCp($page, $per_page)
    {
        $db = \Users::getUserDb();
        //保存组成cp的时间
        $cp_marriage_time_key = \Couples::generateCpMarriageTimeKey();
        $offset = $per_page * ($page - 1);
        $res = $db->zrevrange($cp_marriage_time_key, $offset, $offset + $per_page - 1, 'withscores');

        $i = 0;
        $all_data = [];
        foreach ($res as $ids_str => $re) {
            $ids = explode('_', $ids_str);
            $all_data[$i]['cp_at_text'] = date('Y-m-d', $re);
            $all_data[$i]['sponsor_nickname'] = \Users::findFirstById($ids[0])->nickname;
            $all_data[$i]['pursuer_nickname'] = \Users::findFirstById($ids[1])->nickname;

            //双方的情侣值皆为同步累加，随意取一方即可
            $cp_score_key = self::generateCpInfoForUserKey($ids[0]);
            $all_data[$i]['score'] = $db->zscore($cp_score_key, $ids[1]);
            $i++;
        }
        if (!$all_data) {
            return null;
        }

        $total_entries = $db->zcard($cp_marriage_time_key);
        $pagination = new PaginationModel($all_data, $total_entries, $page, $per_page);
        $pagination->clazz = 'Couples';

        return $pagination;
    }

    //解决html2canvas 网络头像资源问题，下载文件生成base64
    static function base64EncodeImage($image_file)
    {
        $image_info = getimagesize($image_file);
        $file = fopen($image_file, 'r');
        $image_data = fread($file, filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        fclose($file);
        unlink($image_file);
        return $base64_image;
    }

    //周榜
    static function findCpRankListByKey($key, $page, $per_page)
    {
        $db = \Users::getUserDb();

        $offset = $per_page * ($page - 1);
        $res = $db->zrevrange($key, $offset, $offset + $per_page - 1);

        $all_users = [];
        foreach ($res as $index => $re) {
            $ids = explode('_', $re);
            $users = \Users::findByIds($ids);
            if (isPresent($users)) {
                $all_users[$index][] = $users[0]->toCpJson();
                $all_users[$index][] = $users[1]->toCpJson();

            }
        }
        return $all_users;
    }

    //清空双方cp信息
    static function clearCoupleInfo($sponsor_id, $pursuer_id)
    {
        $user_db = \Users::getUserDb();
        //在自己的后宫集合中删除对方
        $sponsor_seraglio_key = \Couples::generateSeraglioKey($sponsor_id);
        $pursuer_seraglio_key = \Couples::generateSeraglioKey($pursuer_id);
        info($user_db->zrange($sponsor_seraglio_key, 0, -1), '>>>>>>>>', $user_db->zrange($pursuer_seraglio_key, 0, -1));
        $user_db->zrem($sponsor_seraglio_key, $pursuer_id);
        $user_db->zrem($pursuer_seraglio_key, $sponsor_id);

        //清除双方结为cp的时间
        $cp_marriage_time_key = \Couples::generateCpMarriageTimeKey();
        if ($user_db->zscore($cp_marriage_time_key, $sponsor_id . '_' . $pursuer_id) > 0) {
            $user_db->zrem($cp_marriage_time_key, $sponsor_id . '_' . $pursuer_id);
        }


        //删除总榜情侣值
        $cp_info_key = \Couples::generateCpInfoKey();
        if ($user_db->zscore($cp_info_key, $sponsor_id . '_' . $pursuer_id) > 0) {
            $user_db->zrem($cp_info_key, $sponsor_id . '_' . $pursuer_id);
        }

        //删除各自保存的情侣值
        $sender_key = \Couples::generateCpInfoForUserKey($sponsor_id);
        if ($user_db->zscore($sender_key, $pursuer_id) > 0) {
            $user_db->zrem($sender_key, $pursuer_id);
        }

        $sender_key = \Couples::generateCpInfoForUserKey($pursuer_id);
        if ($user_db->zscore($sender_key, $sponsor_id) > 0) {
            $user_db->zrem($sender_key, $sponsor_id);
        }
    }

    //解除cp，推送个推给另外一方
    static function pushClearCoupleMessage($current_user_id, $sponsor_id, $pursuer_id)
    {
        $relieved_user_id = $current_user_id == $sponsor_id ? $sponsor_id : $pursuer_id;
        $accord_relieved_user_id = $current_user_id == $sponsor_id ? $pursuer_id : $sponsor_id;
        $accord_relieved_user = \Users::findFirstById($accord_relieved_user_id);
        $relieved_user = \Users::findFirstById($relieved_user_id);
        if ($relieved_user) {
            $push_data = ['title' => '很可惜', 'body' => '您与' . $accord_relieved_user->nickname . '的情侣关系已被对方解除，情侣值已被清空，并从排行榜中移除。'];
            info($relieved_user->getPushContext(), $relieved_user->getPushReceiverContext());
            \Pushers::push($relieved_user->getPushContext(), $relieved_user->getPushReceiverContext(), $push_data);
        }
    }

    static function asyncFinishCp($user, $key)
    {
        $cache = \Users::getHotWriteCache();
        $sponsor_id = $cache->hget($key, 'sponsor_id');
        if (!$sponsor_id) {
            return;
        }

        $body = ['action' => 'game_notice', 'type' => 'over', 'content' => 'cp结束',];
        self::sendCpFinishMessage($user, $body);
    }
}