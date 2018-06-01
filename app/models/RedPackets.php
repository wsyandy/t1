<?php

class RedPackets extends BaseModel
{

    /**
     * @type Users
     */
    private $_user;
    /**
     * @type Rooms
     */
    private $_room;

    public $distance_start_at = 0;
    // 是否已领取
    public $is_grabbed = false;

    static $VALIDATES = [
        'id' => ['null' => '不能为空'],
        'user_id' => ['null' => '不能为空'],
        'num' => ['null' => '不能为空'],
        'diamond' => ['null' => '不能为空'],
        'status' => ['null' => '不能为空'],
        'room_id' => ['null' => '不能为空'],
        'red_packet_type' => ['null' => '不能为空'],
        'nearby_distance' => ['null' => '不能为空'],
        'balance_diamond' => ['null' => '不能为空'],
        'balance_num' => ['null' => '不能为空']
    ];

    static $RED_PACKET_TYPE = [RED_PACKET_TYPE_ALL => '都可领取', RED_PACKET_TYPE_FOLLOW => '关注房主可领取',
        RED_PACKET_TYPE_STAY_AT_ROOM => '在房间待满3分钟可领取', RED_PACKET_TYPE_NEARBY => '附近人可领取'];

    static $STATUS = [STATUS_ON => '进行中', STATUS_OFF => '结束'];

    static $SEX = [USER_SEX_MALE => '男', USER_SEX_FEMALE => '女', USER_SEX_COMMON => '不限'];

    function beforeCreate()
    {
        if (!$this->sex && $this->sex !== USER_SEX_FEMALE) {
            $this->sex = USER_SEX_COMMON;
        }
    }

    function afterCreate()
    {

        // 分包
        $this->subPackets();

        $cache = \Users::getUserDb();
        if ($this->sex == USER_SEX_COMMON) {
            $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id, 0);
            $cache->zadd($underway_red_packet_list_key, time(), $this->id);
            $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id, 1);
            $cache->zadd($underway_red_packet_list_key, time(), $this->id);
        } else {
            $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id, $this->sex);
            $cache->zadd($underway_red_packet_list_key, time(), $this->id);
        }

        if ($this->diamond >= 10000) {
            $this->user->has_red_packet = STATUS_ON;
            $this->user->update();

            if ($this->red_packet_type != RED_PACKET_TYPE_NEARBY) {
                $this->room->has_red_packet = STATUS_ON;
                $this->room->update();
            }
        }
    }

    function afterUpdate()
    {
        if ($this->hasChanged('status') && $this->status == STATUS_OFF) {

            // 分包
            $hot_cache = \RedPackets::getHotWriteCache();
            $hot_cache->del("red_packets_shuffle_diamond_" . $this->id);

            $cache = \Users::getUserDb();
            if ($this->sex == USER_SEX_COMMON) {
                $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id, 0);
                $cache->zrem($underway_red_packet_list_key, $this->id);
                $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id, 1);
                $cache->zrem($underway_red_packet_list_key, $this->id);
            } else {
                $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id, $this->sex);
                $cache->zrem($underway_red_packet_list_key, $this->id);
            }

            info('红包已经结束回收，删除对应进行中的红包id', $underway_red_packet_list_key, $this->id, $this->status);

            //|| $this->user->isCompanyUser() && $this->diamond >= 100
            if ($this->diamond >= 10000) {
                $this->user->has_red_packet = STATUS_OFF;
                $this->user->update();
                if ($this->room) {
                    $this->room->has_red_packet = STATUS_OFF;
                    $this->room->update();
                }
            }

            //红包抢完公屏
            $content = $this->user->nickname . '发的红包已抢完';
            if ($this->balance_diamond) {
                $content = $this->user->nickname . '发的红包已过期';
            }

            self::delay()->asyncSendRedPacketMessageToUsers($this->user_id, $this->room_id, ['type' => 'finish', 'content' => $content]);
        }
    }

    function toSimpleJson()
    {

        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_nickname' => $this->user->nickname,
            'diamond' => $this->diamond,
            'num' => $this->num,
            'status_text' => $this->status_text,
            'created_at_text' => $this->created_at_text,
            'user_avatar_url' => $this->user->avatar_small_url,
            'red_packet_type' => $this->red_packet_type,
            'sex' => $this->sex,
            'distance_start_at' => $this->distance_start_at,
            'is_grabbed' => $this->is_grabbed
        ];

        if (isDevelopmentEnv()) {
            $data['user_nickname'] = $this->user->nickname . '-id:' . $this->id;
        }

        return $data;
    }

    function toBasicJson()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_avatar_url' => $this->user->avatar_small_url,
            'user_nickname' => $this->user->nickname,
            'diamond' => $this->diamond,
            'num' => $this->num,
            'balance_diamond' => $this->balance_diamond,
            'balance_num' => $this->balance_num,
            'current_room_id' => $this->room_id
        ];
    }

    //正在进行中的红包的key
    static function getUnderwayRedPacketListKey($current_room_id, $sex)
    {
        return 'room_underway_red_packet_list_' . $current_room_id . '_sex' . $sex;
    }

    function generateRedPacketUserListKey()
    {
        return 'red_packet_user_list_' . $this->id;
    }

    function generateRedPacketUserDiamondKey()
    {
        return 'red_packet_user_diamond_' . $this->id;
    }

    static function generateUserRoomRedPacketsKey($room_id, $user_id)
    {
        return 'red_packet_list_room_' . $room_id . '_user_' . $user_id;
    }

    static function generateUserRedPacketsKey($user_id)
    {
        return 'red_packet_list_user_' . $user_id;
    }

    static function generateRedPacketUrl($room_id)
    {
        return 'url://m/red_packets/red_packets_list?room_id=' . $room_id;
    }

    static function findLastNearby($user, $geo_distance, $sex)
    {
        $geo_distance = intval($geo_distance);

        $red_packet = self::findFirst(['conditions' => 'status=:status: and user_id=:user_id: and red_packet_type=:red_packet_type: and nearby_distance>=:nearby_distance: and (sex=:sex1: or sex=:sex:)',
            'bind' => ['status' => STATUS_ON, 'user_id' => $user->id, 'red_packet_type' => RED_PACKET_TYPE_NEARBY, 'nearby_distance' => $geo_distance, 'sex1' => 2, 'sex' => $sex],
            'order' => 'id desc']);

        debug($user->id, $geo_distance, 'sex', $user->sex, $red_packet);

        return $red_packet;
    }

    static function createRedPacket($user, $room, $opts)
    {

        $red_packet = new \RedPackets();

        foreach (['user_id', 'room_id', 'status', 'red_packet_type', 'diamond', 'num', 'balance_num', 'balance_diamond', 'sex', 'nearby_distance'] as $column) {
            $red_packet->$column = fetch($opts, $column);
        }

        $red_packet->user = $user;
        $red_packet->room = $room;

        if (!$red_packet->create()) {
            return null;
        }

        $time = 24 * 60 * 60;
        if (isDevelopmentEnv()) {
            $time = 5 * 60;
        }

        // 红包退款
        self::delay($time)->asyncCheckRefund($red_packet->id);

        $push_data = [
            'type' => 'create',
            'content' => $user->nickname . '在房间内发红包，手快有，手慢无，赶紧去抢吧',
            'red_packet_type' => $red_packet->red_packet_type,
            'sex' => $red_packet->sex,
            'diamond' => $red_packet->diamond
        ];

        self::delay()->asyncSendRedPacketMessageToUsers($user->id, $room->id, $push_data);

        return $red_packet;
    }

    static function asyncCheckRefund($red_packet_id)
    {

        $red_packet = \RedPackets::findFirstById($red_packet_id);
        if (!$red_packet || $red_packet->status == STATUS_OFF) {
            info('已结束', $red_packet_id);
            return;
        }

        $user = $red_packet->user;
        if ($red_packet->balance_diamond > 0) {

            $amount = $red_packet->balance_diamond;
            $opts = ['remark' => '红包余额返还钻石' . $amount, 'mobile' => $user->mobile, 'target_id' => $red_packet->id];
            \AccountHistories::changeBalance($user, ACCOUNT_TYPE_RED_PACKET_RESTORATION, $amount, $opts);

            \Chats::sendTextSystemMessage($user, "红包退款通知：红包超过24小时未被领取，" . $amount . "钻已返还到您的账户，请注意查收~");
        }

        info('回收红包', $red_packet->id, $red_packet->user_id, $red_packet->room_id, $red_packet->balance_diamond);

        $red_packet->status = STATUS_OFF;
        $red_packet->update();
    }

    // 房间里的红包
    static function findRedPacketList($user, $room, $page, $per_page)
    {

        $cache_db = \Users::getUserDb();
        $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($room->id, $user->sex);
        $total = $cache_db->zcard($underway_red_packet_list_key);
        $offset = ($page - 1) * $per_page;
        if ($offset >= $total) {
            info('越界', $user->id, $room->id, $page, $per_page, $total, $offset);
        }

        $red_packet_ids = $cache_db->zrevrange($underway_red_packet_list_key, $offset, $offset + $per_page - 1);
        $red_packets = \RedPackets::findByIds($red_packet_ids);

        foreach ($red_packets as $red_packet) {

            $distance_start_at = $red_packet->getDistanceStartTime($user);
            $red_packet->distance_start_at = $distance_start_at;
            $red_packet->is_grabbed = $red_packet->isGrabbed($user);
        }

        return new \PaginationModel($red_packets, $total, $page, $per_page);
    }

    function isGrabbed($user)
    {

        $cache = \Users::getUserDb();
        $red_user_list_key = $this->generateRedPacketUserListKey();
        return $cache->zscore($red_user_list_key, $user->id) > 0;
    }

    // 可以领取的时间
    function getDistanceStartTime($user)
    {

        if ($this->red_packet_type != RED_PACKET_TYPE_STAY_AT_ROOM) {
            return 0;
        }

        //获取用户进房间的时间
        $time = $this->room->getUserEnterRoomTime($user->id);
        //如果用户进房间的时间小于红包的创建时间，则需要以红包创建时间为节点等待3分钟，否则以用户进房间的时间为节点等待3分钟
        $distance_start_at = $time + 3 * 60 - time() > 0 ? $time + 3 * 60 - time() : 0;
        if ($time <= $this->created_at) {
            $distance_start_at = $this->created_at + 3 * 60 - time() > 0 ? $this->created_at + 3 * 60 - time() : 0;
        }

        return $distance_start_at;
    }

    function getSubPacketDiamond()
    {

        $cache = \RedPackets::getHotWriteCache();
        $key = "red_packets_shuffle_diamond_" . $this->id;
        $diamond = $cache->rpop($key);
        if (!$diamond) {
            return 0;
        }

        return intval($diamond);
    }

    function subPackets()
    {

        $cache = \RedPackets::getHotWriteCache();

        $balance_diamond = $this->balance_diamond;
        $balance_num = $this->balance_num;

        $min_diamond = 1;
        if ($this->red_packet_type == RED_PACKET_TYPE_NEARBY) {
            $min_diamond = 50;
        }
        if ($this->red_packet_type == RED_PACKET_TYPE_FOLLOW || $this->red_packet_type == RED_PACKET_TYPE_STAY_AT_ROOM) {
            $min_diamond = 5;
        }

        $avg_diamond = ceil($this->diamond / $this->num);
        $max_diamond = ceil($this->diamond * 0.3);
        if ($max_diamond < $avg_diamond * 2) {
            $max_diamond += $avg_diamond;
        }

        if ($max_diamond > $avg_diamond * 4) {
            $max_diamond = $avg_diamond * 4;
        }

        $min_avg = $avg_diamond - ceil(($avg_diamond - $min_diamond) / 2);
        $max_avg = $avg_diamond + ceil(($max_diamond - $avg_diamond) / 2);

        info($this->id, '5个分包点', $min_diamond, $min_avg, $avg_diamond, $max_avg, $max_diamond);

        $total_get_diamond = 0;
        $get_diamonds = [];
        for ($i = 0; $i < $this->num; $i++) {

            $usable_balance_diamond = $balance_diamond - ($balance_num - 1) * $min_diamond;
            if ($usable_balance_diamond < $min_diamond) {
                $usable_balance_diamond = $min_diamond;
            }
            $user_rate = mt_rand(1, 100);
            if ($user_rate < 65) {
                $get_diamond = mt_rand($min_avg, $max_avg);
            } else {

                if (mt_rand(1, 100) < 90) {
                    $get_diamond = mt_rand($min_diamond, $min_avg);
                } else {
                    $get_diamond = mt_rand($max_avg, $max_diamond);
                }
            }

            // 防止超出
            if ($get_diamond >= $usable_balance_diamond && $balance_num > 1) {
                if ($balance_num <= 3) {
                    $get_diamond = ceil($usable_balance_diamond * 0.25);
                }
            }

            if ($total_get_diamond + $get_diamond >= $this->diamond || $get_diamond < $min_diamond || $usable_balance_diamond < $get_diamond) {
                $get_diamond = $min_diamond;
            }

            if ($balance_num == 1) {
                $get_diamond = $balance_diamond;
            }

            $get_diamonds[] = $get_diamond;
            $total_get_diamond += $get_diamond;

            $balance_diamond = $balance_diamond - $get_diamond;
            $balance_num = $balance_num - 1;

            info($get_diamond, 'total', $total_get_diamond, 'balance', $balance_diamond, $balance_num, 'ke', $usable_balance_diamond);
        }

        shuffle($get_diamonds);

        info($this->id, $get_diamonds);

        foreach ($get_diamonds as $diamond) {
            $cache->rpush("red_packets_shuffle_diamond_" . $this->id, $diamond);
        }

    }

    function grabRedPacket($user)
    {

        $lock_key = 'grab_red_packet_' . $this->id;
        $lock = tryLock($lock_key, 2000);
        if (!$lock) {
            return 0;
        }

        $red_racket = RedPackets::findFirstById($this->id);
        $get_diamond = $red_racket->getRedPacketDiamond($user->id);

        unlock($lock);

        if ($get_diamond) {

            $opts = ['remark' => '红包获取钻石' . $get_diamond, 'mobile' => $user->mobile, 'target_id' => $this->id];
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_RED_PACKET_INCOME, $get_diamond, $opts);

            $opts = [
                'type' => 'update',
                'content' => '恭喜' . $user->nickname . '抢到了' . $get_diamond . '个钻石'
            ];

            self::delay()->asyncSendRedPacketMessageToUsers($user->id, $this->room_id, $opts);
        }

        return $get_diamond;
    }

    function getRedPacketDiamond($user_id)
    {

        if ($this->status == STATUS_OFF || $this->balance_diamond < 1) {
            return 0;
        }

        $cache = \Users::getUserDb();
        $user_room_key = self::generateUserRoomRedPacketsKey($this->room_id, $user_id);
        $user_red_key = self::generateUserRedPacketsKey($user_id);
        $user_diamond_key = $this->generateRedPacketUserDiamondKey();
        $user_num = $cache->zcard($user_diamond_key);
        if ($user_num >= $this->num || $this->balance_diamond <= 0) {
            return 0;
        }

        $get_diamond = $this->getSubPacketDiamond();
        if (!$get_diamond) {
            return 0;
        }

        $this->balance_diamond = $this->balance_diamond - $get_diamond;
        $this->balance_num = $this->balance_num - 1;

        if ($this->balance_diamond < 0 || $this->balance_num < 0) {
            info('Exce', $this->id, $this->user_id, 'get', $get_diamond, '总', $this->balance_diamond, $this->balance_num);

            $this->balance_diamond = 0;
            $this->balance_num = 0;
        }

        if ($this->balance_diamond <= 0) {
            $this->status = STATUS_OFF;
        }
        $this->save();

        info($this->id, $this->user_id, 'get', $get_diamond, '总', $this->balance_diamond, $this->balance_num);

        $red_user_list_key = $this->generateRedPacketUserListKey();
        $user_diamond_key = $this->generateRedPacketUserDiamondKey();
        $cache->zadd($user_diamond_key, $get_diamond, $user_id);
        $cache->zadd($red_user_list_key, time(), $user_id);
        $cache->zadd($user_room_key, $get_diamond, $this->id);
        $cache->zadd($user_red_key, time(), $this->id);

        return intval($get_diamond);
    }

    function getRedPacketDiamond2()
    {

        $balance_diamond = $this->balance_diamond;
        $balance_num = $this->balance_num;
        $avg_diamond = ceil($this->diamond / $this->num);
        $min_diamond = 1;
        $max_diamond = ceil($this->diamond * 0.3);

        if ($this->red_packet_type == RED_PACKET_TYPE_NEARBY) {
            $min_diamond = 50;
        }
        if ($this->red_packet_type == RED_PACKET_TYPE_FOLLOW || $this->red_packet_type == RED_PACKET_TYPE_STAY_AT_ROOM) {
            $min_diamond = 5;
        }

        $get_diamond = 0;
        if ($balance_num == 1) {
            $get_diamond = $balance_diamond;
            $usable_balance_diamond = $balance_diamond;
        } else {

            $usable_balance_diamond = $balance_diamond - ($balance_num - 1) * $min_diamond * 2;
            if ($balance_num * 2 > $this->num) {
                $usable_balance_diamond2 = ceil($balance_diamond * mt_rand(40, 60) / 100);
                if ($usable_balance_diamond2 < $usable_balance_diamond) {
                    $usable_balance_diamond = $usable_balance_diamond2;
                }
            }

            $user_rate = mt_rand(1, 100);
            if ($user_rate < mt_rand(60, 80)) {
                if ($avg_diamond - ceil($this->diamond * 0.05) < $min_diamond) {
                    $get_diamond = mt_rand($min_diamond, $avg_diamond + ceil($this->diamond * 0.1));
                } else {
                    $get_diamond = mt_rand($avg_diamond - ceil($this->diamond * 0.05), $avg_diamond + ceil($this->diamond * 0.1));
                }
            } else {
                if (mt_rand(1, 100) < 80) {
                    $get_diamond = mt_rand($min_diamond, ceil($this->diamond * 0.1));
                } else {
                    $get_diamond = mt_rand(ceil($this->diamond * 0.15), $max_diamond);
                }
            }
        }

        if ($get_diamond > $max_diamond) {
            $get_diamond = ceil($usable_balance_diamond * 0.1);
        }

        // 防止超出
        if ($get_diamond >= $usable_balance_diamond && $balance_num > 1) {
            $get_diamond = $min_diamond;
            if ($balance_num <= 3) {
                $get_diamond = ceil($usable_balance_diamond * 0.25);
            }
        }

        return $get_diamond;
    }

    static function UserGetRedPacketIds($room_id, $user_id)
    {
        $cache = \Users::getUserDb();
        $key = self::generateUserRoomRedPacketsKey($room_id, $user_id);
        $user_get_red_packet_ids = $cache->zrange($key, 0, -1);
        return $user_get_red_packet_ids;
    }

    static function asyncSendRedPacketMessageToUsers($user_id, $room_id, $opts)
    {

        $user = Users::findFirstById($user_id);
        $room = Rooms::findFirstById($room_id);

        self::sendRedPacketMessageToUsers($user, $room, $opts);
    }

    static function sendRedPacketMessageToUsers($user, $room, $opts)
    {

        info($user->id, 'uid', $user->uid, $room->id, $opts);

        $type = fetch($opts, 'type');
        $content = fetch($opts, 'content');
        $red_packet_type = fetch($opts, 'red_packet_type');

        //红包socket
        self::pushRedPacketNumToUser($user, $room, $type);

        //红包公屏socket
        self::pushRedPacketTopTopicMessage($room, $content);

        //首页下沉通知
        if ($type == 'create' && $red_packet_type == RED_PACKET_TYPE_NEARBY && fetch($opts, 'diamond') >= 10000) {
            self:: pushRedPacketSinkMessage($user, $room, $opts);
        }
    }

    //红包的公屏
    static function pushRedPacketTopTopicMessage($room, $content)
    {
        $content_type = 'red_packet';
        $system_user = \Users::getSysTemUser();
        $room->pushTopTopicMessage($system_user, $content, $content_type);
    }

    //红包个数
    static function pushRedPacketNumToUser($user, $room, $type)
    {
        $url = self::generateRedPacketUrl($room->id);

        if ($type == 'update') {
            $underway_red_packet_num = $room->getNotDrawRedPacketNum($user);
            info($room->id, $user->id, 'uid', $user->uid, $underway_red_packet_num);
            $room->pushRedPacketNumToUser($user, $underway_red_packet_num, $url);
        } else {

            $users = $room->findTotalRealUsers();
            foreach ($users as $other_user) {
                $underway_red_packet_num = $room->getNotDrawRedPacketNum($other_user);
                info($user->id, 'uid', $user->uid, $room->id, $other_user->id, $underway_red_packet_num);
                $room->pushRedPacketNumToUser($other_user, $underway_red_packet_num, $url);
            }
        }

    }

    //下沉式
    static function pushRedPacketSinkMessage($current_user, $room, $opts)
    {

        $content = fetch($opts, 'content');
        $sex = fetch($opts, 'sex');

        $client_url = 'app://rooms/detail?id=' . $room->id;
        $users = $current_user->nearby(1, 200);

        foreach ($users as $user) {

            if ($user->current_room_id == $room->id) {
                continue;
            }

            if ($sex != USER_SEX_COMMON && $user->sex != $sex) {
                continue;
            }

            $body = ['action' => 'sink_notice', 'title' => '快来抢红包啦！！', 'content' => $content, 'client_url' => $client_url];
            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            if (!$intranet_ip || !$receiver_fd) {
                continue;
            }

            $result = \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);
            info('cur', $current_user->id, $current_user->uid, $user->id, $user->uid, $receiver_fd, '推送结果=>', $result, '结构=>', $body);
        }
    }

}