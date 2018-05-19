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
            $status = $user_db->zscore($relations_key, $user->id);
            //状态    如果为1，说明该用户为当初的发起者，当前用户为追求者
            switch ($status) {
                case 1:
                    $member = $user->id . '_' . $current_user_id;
                    break;
                case 2:
                    $member = $current_user_id . '_' . $user->id;
                    break;
            }
            $cp_info_key = self::generateCpInfoKey();
            $score = $user_db->zscore($cp_info_key, $member);
            $user->cp_value = $score;
        }

        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }

    static function updateCpInfo($opts)
    {
        $time = fetch($opts, 'time');
        if (date('Y-m-d', $time) != '2018-05-20' && isProduction()) {
            return null;
        }
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
        $cp_info_key = self::generateCpInfoKey();
        $db->zincrby($cp_info_key, $amount, $member);

        $sender_key = self::generateCpInfoForUserKey($sender_id);
        $db->zincrby($sender_key, $amount, $receive_id);

        $receive_key = self::generateCpInfoForUserKey($receive_id);
        $db->zincrby($receive_key, $amount, $sender_id);
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

    static function checkCpRelation($pursuer_id, $sponsor_id)
    {
        $db = \Users::getUserDb();
        $key = self::generateSeraglioKey($pursuer_id);
        $score = $db->zscore($key, $sponsor_id);

        return $score;

    }
}