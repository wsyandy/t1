<?php

class Couples extends BaseModel
{
    static $_only_cache = true;

    static function createReadyCpInfo($user)
    {
        $cache = \Users::getHotWriteCache();
        $key = self::generateReadyCpInfoKey($user->room_id);
        //初始化
        $time = date('YmdHis');
        $body = ['sponsor_id' => $user->id, $user->id => 1, 'start_at' => $time];
        $cache->hmset($key, $body);
        info('初始化', $cache->hgetall($key));

        $expire_time = 10 * 60;
        if (isDevelopmentEnv()) {
            $expire_time = 2 * 60;
        }
        $cache->expire($key, $expire_time);
        //同时起一个异步推送socket
        self::delay($expire_time - 3)->asyncFinishCp($user, $key, $time);
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

        //同步双方的情侣值
        self::updateCoupleTotalValue($sender_id, $receive_id, $amount);

        //记录周榜情侣值
        self::updateCoupleWeekValue($member, $amount);
        //统计各自的周榜情侣值
        self::updateCoupleWeekValueForUser($sender_id, $receive_id, $amount);

        //统计日榜情侣值
        self::updateCoupleDayValue($member, $amount);

        //统计各自日榜情侣值
        self::updateCoupleDayValueForUser($sender_id, $receive_id, $amount);

        //统计总榜情侣值
        self::updateTotalCoupleValue($member, $amount);

    }

    //双方各自的同步总cp值
    static function updateCoupleTotalValue($sender_id, $receive_id, $amount)
    {
        $db = \Users::getUserDb();
        $sender_key = self::generateCpInfoForUserKey($sender_id);
        $db->zincrby($sender_key, $amount, $receive_id);

        $receive_key = self::generateCpInfoForUserKey($receive_id);
        $db->zincrby($receive_key, $amount, $sender_id);
    }

    //记录周榜情侣值
    static function updateCoupleWeekValue($member, $amount)
    {
        $db = \Users::getUserDb();
        $cp_week_charm_key = \Users::generateFieldRankListKey('week', 'cp');
        info('情侣值周榜增加', $cp_week_charm_key, $member);
        $db->zincrby($cp_week_charm_key, $amount, $member);
    }

    //记录各自的一周的情侣值
    static function updateCoupleWeekValueForUser($sender_id, $receive_id, $amount)
    {
        $db = \Users::getUserDb();
        $sender_key = self::generateCoupleKeyForUser('week', $sender_id);
        $db->zincrby($sender_key, $amount, $receive_id);

        $receive_key = self::generateCoupleKeyForUser('week', $receive_id);
        $db->zincrby($receive_key, $amount, $sender_id);

        info('情侣值周榜增加', $sender_key, $receive_key, $amount);
    }

    //做了一个总的 暂时停用
//    static function generateCoupleWeekValueKey($user_id, $time = '')
//    {
//        if (!$time) {
//            $time = time();
//        }
//        return 'couple_week_value_' . $user_id . '_' . date("Ymd", beginOfWeek($time)) . '_' . date("Ymd", endOfWeek($time));
//    }

    static function sendCpFinishMessage($user, $body)
    {
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
        if (!$image_file) {
            return null;
        }

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
        $res = $db->zrevrange($key, $offset, $offset + $per_page - 1, 'withscores');

        $all_users = [];
        $index = 0;
        foreach ($res as $ids_str => $score) {
            $ids = explode('_', $ids_str);
            $users = \Users::findByIds($ids);
            if (isPresent($users)) {
                $all_users[$index][] = $users[0]->toRankListJson();
                $all_users[$index][] = $users[1]->toRankListJson();
                $all_users[$index]['score'] = $score;
                $index++;
            }
        }
        return $all_users;
    }

    //api周榜
    static function findCpRankListByKeyForClient($key, $page, $per_page)
    {
        $db = \Users::getUserDb();

        $offset = $per_page * ($page - 1);
        $res = $db->zrevrange($key, $offset, $offset + $per_page - 1, 'withscores');

        $all_users = [];
        $index = 0;
        foreach ($res as $ids_str => $score) {
            $ids = explode('_', $ids_str);
            $users = \Users::findByIds($ids);
            if (isPresent($users)) {
                $all_users[$index]['sponsor'] = $users[0]->toRankListJson();
                $all_users[$index]['pursuer'] = $users[1]->toRankListJson();
                $all_users[$index]['score'] = $score;
                $index++;
            }
        }
        return $all_users;
    }

    //清空双方cp信息
    static function clearCoupleInfo($sponsor_id, $pursuer_id)
    {
        //清除对应用户户中各自的cp信息
        self::clearCoupleInfoForUser($sponsor_id, $pursuer_id);
        //清除双方结为cp的时间
        self::clearCpMarriageTime($sponsor_id, $pursuer_id);

        //删除总榜情侣值
        self::clearTotalCoupleInfo($sponsor_id, $pursuer_id);

        //删除当前周情侣榜,双方周情侣值，只清除当前周
        self::clearWeekCoupleInfo($sponsor_id, $pursuer_id);

        //清除当前日榜情侣榜、双方日情侣值，只清除当天
        self::clearDayCoupleInfo($sponsor_id, $pursuer_id);
    }

    static function clearDayCoupleInfo($sponsor_id, $pursuer_id)
    {
        $user_db = \Users::getUserDb();
        $cp_week_charm_key = \Users::generateFieldRankListKey('day', 'cp');
        if ($user_db->zscore($cp_week_charm_key, $sponsor_id . '_' . $pursuer_id)) {
            $user_db->zrem($cp_week_charm_key, $sponsor_id . '_' . $pursuer_id);
        }

        //删除双方天情侣值，只清除当天
        $sponsor_cp_week_key = self::generateCoupleKeyForUser('day', $sponsor_id);
        if ($user_db->zscore($sponsor_cp_week_key, $pursuer_id)) {
            $user_db->zrem($sponsor_cp_week_key, $pursuer_id);
        }

        $pursuer_cp_week_key = self::generateCoupleKeyForUser('day', $pursuer_id);
        if ($user_db->zscore($pursuer_cp_week_key, $sponsor_id)) {
            $user_db->zrem($pursuer_cp_week_key, $sponsor_id);
        }
    }

    static function clearCoupleInfoForUser($sponsor_id, $pursuer_id)
    {
        $user_db = \Users::getUserDb();
        //在自己的后宫集合中删除对方
        $sponsor_seraglio_key = \Couples::generateSeraglioKey($sponsor_id);
        $pursuer_seraglio_key = \Couples::generateSeraglioKey($pursuer_id);
        info($user_db->zrange($sponsor_seraglio_key, 0, -1), '>>>>>>>>', $user_db->zrange($pursuer_seraglio_key, 0, -1));
        $user_db->zrem($sponsor_seraglio_key, $pursuer_id);
        $user_db->zrem($pursuer_seraglio_key, $sponsor_id);

        //删除各自保存的情侣值
        $sponsor_key = \Couples::generateCpInfoForUserKey($sponsor_id);
        if ($user_db->zscore($sponsor_key, $pursuer_id) > 0) {
            $user_db->zrem($sponsor_key, $pursuer_id);
        }

        $pursuer_key = \Couples::generateCpInfoForUserKey($pursuer_id);
        if ($user_db->zscore($pursuer_key, $sponsor_id) > 0) {
            $user_db->zrem($pursuer_key, $sponsor_id);
        }
    }

    //清除双方结为cp的时间
    static function clearCpMarriageTime($sponsor_id, $pursuer_id)
    {
        $user_db = \Users::getUserDb();
        $cp_marriage_time_key = \Couples::generateCpMarriageTimeKey();
        if ($user_db->zscore($cp_marriage_time_key, $sponsor_id . '_' . $pursuer_id) > 0) {
            $user_db->zrem($cp_marriage_time_key, $sponsor_id . '_' . $pursuer_id);
        }
    }

    //删除总榜情侣值
    static function clearTotalCoupleInfo($sponsor_id, $pursuer_id)
    {
        $user_db = \Users::getUserDb();
        $cp_info_key = \Users::generateFieldRankListKey('total', 'cp');
        if ($user_db->zscore($cp_info_key, $sponsor_id . '_' . $pursuer_id) > 0) {
            $user_db->zrem($cp_info_key, $sponsor_id . '_' . $pursuer_id);
        }
    }

    //删除当前周情侣榜,双方周情侣值，只清除当前周
    static function clearWeekCoupleInfo($sponsor_id, $pursuer_id)
    {
        $user_db = \Users::getUserDb();
        $cp_week_charm_key = \Users::generateFieldRankListKey('week', 'cp');
        if ($user_db->zscore($cp_week_charm_key, $sponsor_id . '_' . $pursuer_id)) {
            $user_db->zrem($cp_week_charm_key, $sponsor_id . '_' . $pursuer_id);
        }

        //删除双方周情侣值，只清除当前周
        $sponsor_cp_week_key = self::generateCoupleKeyForUser('week', $sponsor_id);
        if ($user_db->zscore($sponsor_cp_week_key, $pursuer_id)) {
            $user_db->zrem($sponsor_cp_week_key, $pursuer_id);
        }

        $pursuer_cp_week_key = self::generateCoupleKeyForUser('week', $pursuer_id);
        if ($user_db->zscore($pursuer_cp_week_key, $sponsor_id)) {
            $user_db->zrem($pursuer_cp_week_key, $sponsor_id);
        }
    }

    //解除cp，推送系统给另外一方
    static function sendRelieveCpSysTemMessage($current_user_id, $sponsor_id, $pursuer_id)
    {
        //当前用户为解除者，判断谁为被解除这
        //自愿解除
        $accord_relieved_user_id = $current_user_id == $sponsor_id ? $sponsor_id : $pursuer_id;
        //被解除
        $relieved_user_id = $current_user_id == $sponsor_id ? $pursuer_id : $sponsor_id;
        $accord_relieved_user = \Users::findFirstById($accord_relieved_user_id);
        $relieved_user = \Users::findFirstById($relieved_user_id);
        if ($relieved_user) {
            $content = '您与' . $accord_relieved_user->nickname . '的情侣关系已被对方解除，情侣值已被清空，并从排行榜中移除。';
            $result = \Chats::sendSystemMessage($relieved_user, CHAT_CONTENT_TYPE_TEXT, $content);
            info($result);
        }
    }

    static function asyncFinishCp($user, $key, $time)
    {
        $cache = \Users::getHotWriteCache();
        $pursuer_id = $cache->hget($key, 'pursuer_id');
        $start_at = $cache->hget($key, 'start_at');
        if ($pursuer_id || $start_at != $time) {
            return;
        }

        $cache->expire($key, 0);
        $body = ['action' => 'game_notice', 'type' => 'over', 'content' => 'cp超时，自动关闭'];
        self::sendCpFinishMessage($user, $body);
    }

    //统计日榜情侣值
    static function updateCoupleDayValue($member, $amount)
    {
        $db = \Users::getUserDb();
        $cp_week_charm_key = \Users::generateFieldRankListKey('day', 'cp');
        info('情侣值周榜增加', $cp_week_charm_key, $member);
        $db->zincrby($cp_week_charm_key, $amount, $member);
    }


    //统计各自日榜情侣值
    static function updateCoupleDayValueForUser($sender_id, $receive_id, $amount)
    {
        $db = \Users::getUserDb();
        $sender_key = self::generateCoupleKeyForUser('day', $sender_id);
        $db->zincrby($sender_key, $amount, $receive_id);

        $receive_key = self::generateCoupleKeyForUser('day', $receive_id);
        $db->zincrby($receive_key, $amount, $sender_id);

        info('情侣值日榜增加', $sender_key, $receive_key, $amount);
    }

    //统计总榜情侣值
    static function updateTotalCoupleValue($member, $amount)
    {
        $db = \Users::getUserDb();
        $cp_week_charm_key = \Users::generateFieldRankListKey('total', 'cp');
        info('情侣值总榜增加', $cp_week_charm_key, $member);
        $db->zincrby($cp_week_charm_key, $amount, $member);
    }

    //根据不同的类型生成对应不成的key  total/week/day  用户针对于用户个人存储
    static function generateCoupleKeyForUser($type, $user_id, $opts = [])
    {
        switch ($type) {
            case 'day':
                $date = fetch($opts, 'date', date("Ymd"));
                $key = 'couple_day_value_' . $user_id . '_' . $date;
                break;
            case
            'week':
                $start_at = fetch($opts, 'start', date("Ymd", beginOfWeek()));
                $end_at = fetch($opts, 'end', date("Ymd", endOfWeek()));
                $key = 'couple_week_value_' . $user_id . '_' . $start_at . '_' . $end_at;
                break;
            case 'total':
//                $key = 'cp_info_for_user_' . $user_id;
                //用的地方太多，暂时这么写，以防数据错乱，初步完成后，再调整
                $key = self::generateCpInfoForUserKey($user_id);
                break;
            default:
                return '';
        }

        return $key;
    }
}