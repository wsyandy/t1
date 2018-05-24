<?php

class RedPackets extends BaseModel
{
    static $_only_cache = true;

    /**
     * @type string
     */
    private $_id;

    /**
     * @type Users
     */
    private $_user;
    /**
     * @type Rooms
     */
    private $_room;

    /**
     * @type integer
     */
    private $_diamond;

    /**
     * @type integer
     */
    private $_user_id;

    /**
     * @type integer
     */
    private $_num;

    /**
     * @type integer
     */
    private $_status;

    /**
     * @type integer
     */
    private $_created_at;

    /**
     * @type integer
     */
    private $_room_id;

    /**
     * @type string
     */
    private $_red_packet_type;

    /**
     * @type integer
     */
    private $_nearby_distance;

    /**
     * @type integer
     */
    private $_balance_diamond;
    /**
     * @type integer
     */
    private $_balance_num;

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

    // RED_PACKET_TYPE_NEARBY => '附近的人才能领取'
    static $RED_PACKET_TYPE = [RED_PACKET_TYPE_ALL => '都可以领取', RED_PACKET_TYPE_ATTENTION => '关注房主才能领取', RED_PACKET_TYPE_STAY_AT_ROOM => '在房间满3分钟才能领取'];
    static $STATUS = [STATUS_ON => '进行中', STATUS_OFF => '结束'];

    function beforeCreate()
    {
        $this->id = $this->generateId();
    }


    function afterCreate()
    {
        $cache = \Users::getUserDb();
        //当前房间对应红包id集合
        $send_red_packet_list_key = self::generateRedPacketListKey($this->room_id);
        //当前正在进行中的红包id集合
        $underway_red_packet_list_key = self::generateUnderwayRedPacketListKey($this->room_id);
        info('添加', $underway_red_packet_list_key);

        //当前用户发过的红包id集合
        $user_send_red_packets_key = self::generateUserSendRedPacketsKey($this->user_id);

        //generateUnderwayRedPacketListKey
        $cache->zadd($send_red_packet_list_key, time(), $this->id);
        $cache->zadd($underway_red_packet_list_key, time(), $this->id);
        $cache->zadd($user_send_red_packets_key, time(), $this->id);
    }

    function afterUpdate()
    {
        if ($this->hasChanged('status') && $this->status == STATUS_OFF) {
            info('红包已经结束回收，删除对应进行中的红包id', $this->id, $this->status);
            $cache = \Users::getUserDb();
            $underway_red_packet_list_key = self::generateUnderwayRedPacketListKey($this->room_id);
            $cache->zrem($underway_red_packet_list_key, $this->id);

            self::pushRedPacketMessageForUser($this->room, $this->user, 'bc');
        }
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_nickname' => $this->user->nickname,
            'diamond' => $this->diamond,
            'num' => $this->num,
            'status_text' => $this->status_text,
            'created_at_text' => $this->created_at_text,
            'user_avatar_url' => $this->user->avatar_url,
            'red_packet_type' => $this->red_packet_type,
            'sex' => $this->sex,
            'distance_start_at' => $this->distance_start_at
        ];
    }

    function toBasicJson()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_avatar_url' => $this->user->avatar_url,
            'user_nickname' => $this->user->nickname,
            'diamond' => $this->diamond,
            'num' => $this->num,
            'balance_diamond' => $this->balance_diamond,
            'balance_num' => $this->balance_num,
            'current_room_id' => $this->room_id
        ];
    }


    //生成房间红包列表key
    static function generateRedPacketListKey($current_room_id)
    {
        return 'send_red_packet_list_for_' . $current_room_id;
    }

    //正在进行中的红包的key
    static function generateUnderwayRedPacketListKey($current_room_id)
    {
        return 'underway_red_packet_list_for_' . $current_room_id;
    }

    //用户发过的红包的key
    static function generateUserSendRedPacketsKey($user_id)
    {
        return 'user_send_red_packets_' . $user_id;
    }

    static function getCacheEndPoint()
    {
        $config = self::di('config');
        $endpoints = explode(',', $config->user_db_endpoints);
        return $endpoints[0];
    }

    function generateId()
    {
        return 'red_packet_for_user_' . strval($this->user_id) . '_' . time() . mt_rand(1000, 10000);
    }

    static function createReadPacket($user, $room, $opts)
    {
        $send_red_packet_history = new \RedPackets();
        foreach (['user_id', 'diamond', 'num', 'status', 'room_id', 'red_packet_type', 'sex', 'nearby_distance', 'balance_num', 'balance_diamond'] as $column) {
            $send_red_packet_history->$column = fetch($opts, $column);
        }
        if ($send_red_packet_history->create()) {
            $opts = [
                'type' => 'create',
                'content' => $user->nickname . '在房间内发红包，手快有，手慢无，赶紧去抢吧',
                'notify_type' => 'bc'
            ];
            self::sendSocketForRedPacket($user, $room, $opts);
            $time = 24 * 60 * 60;
            if (isDevelopmentEnv()) {
                $time = 5 * 60;
            }

            self::delay($time)->asyncFinishRedPacket($send_red_packet_history->id);

            if ($send_red_packet_history->diamond >= 1000) {
                $user->has_red_packet = STATUS_ON;
                $user->update();

                $room->has_red_packet = STATUS_ON;
                $room->update();
            }
            return $send_red_packet_history;
        }
        return null;
    }

    static function asyncFinishRedPacket($red_packet_id)
    {
        $red_packet = \RedPackets::findFirstById($red_packet_id);

        if (isPresent($red_packet) && $red_packet->status != STATUS_OFF) {
            if ($red_packet->balance_diamond > 0) {
                $opts = ['remark' => '红包余额返还钻石' . $red_packet->balance_diamond, 'mobile' => $red_packet->user->mobile, 'target_id' => $red_packet->id];
                \AccountHistories::changeBalance($red_packet->user_id, ACCOUNT_TYPE_RED_PACKET_RESTORATION, $red_packet->balance_diamond, $opts);
            }

            info('回收红包', $red_packet->balance_diamond);
            $red_packet->balance_diamond = 0;
            $red_packet->balance_num = 0;
            $red_packet->status = STATUS_OFF;
            $red_packet->update();
        }
    }

    static function generateRedPacketUrl($room_id)
    {
        return 'url://m/red_packets/red_packets_list?room_id=' . $room_id;
    }

    static function findRedPacketList($room, $page, $per_page, $user)
    {
        $underway_red_packet_list_key = self::generateUnderwayRedPacketListKey($room->id);
        $cache_db = \Users::getUserDb();

        $total = $cache_db->zcard($underway_red_packet_list_key);
        $offset = ($page - 1) * $per_page;
        $red_packet_ids = $cache_db->zrevrange($underway_red_packet_list_key, $offset, $offset + $per_page - 1);
        $red_packets = \RedPackets::findByIds($red_packet_ids);
//        $screen_red_packets = self::getScreenRedPackets($red_packets, $user);
        foreach ($red_packets as $red_packet) {
            $distance_start_at = $red_packet->getDistanceStartTime($room, $user->id);
            $red_packet->distance_start_at = $distance_start_at;
        }

        return new \PaginationModel($red_packets, $total, $page, $per_page);
    }

    static function getScreenRedPackets($red_packets, $user)
    {
        $screen_red_packets = [];
        foreach ($red_packets as $red_packet) {
            //这里以后还要加距离限制
            if ($red_packet->red_packet_type != 'nearby' || ($red_packet->red_packet_type == 'nearby' && ($red_packet->sex == USER_SEX_COMMON || $red_packet->sex == $user->sex))) {
                $screen_red_packets[] = $red_packet;
            }
        }
        return $screen_red_packets;
    }

    static function grabRedPacket($user, $room, $red_packet_id)
    {
        $get_diamond = \RedPackets::getRedPacketDiamond($room->id, $user->id, $red_packet_id);
        if ($get_diamond) {
            $opts = [
                'type' => 'update',
                'content' => '恭喜' . $user->nickname . '抢到了' . $get_diamond . '个钻石',
                'notify_type' => 'ptp'
            ];
            self::sendSocketForRedPacket($user, $room, $opts);

            return [ERROR_CODE_SUCCESS, $get_diamond];
        }

        return [ERROR_CODE_SUCCESS, null];
    }

    static function getRedPacketDiamond($current_room_id, $user_id, $red_packet_id)
    {
        $cache = \Users::getUserDb();
        //房间内对应抢到红包的用户的红包ID的集合
        $key = self::generateRedPacketForRoomKey($current_room_id, $user_id);

        //对应的抢到这个红包的用户ID集合
        $user_key = self::generateRedPacketInRoomForUserKey($current_room_id, $red_packet_id);

        $red_packet = \RedPackets::findFirstById($red_packet_id);
        $balance_diamond = $red_packet->balance_diamond;
        $balance_num = $red_packet->balance_num;

        if ($balance_diamond && $balance_num && $red_packet->status == STATUS_ON) {
            $usable_balance_diamond = $balance_diamond - ($balance_num - 1);
            if ($usable_balance_diamond > ceil($red_packet->diamond * 0.5)) {
                $get_diamond = mt_rand(1, ceil($red_packet->diamond * 0.4));
            } else {
                $get_diamond = mt_rand(1, $usable_balance_diamond);
            }

            if ($balance_num == 1) {
                $get_diamond = $balance_diamond;
            }
            $red_packet->balance_diamond = $balance_diamond - $get_diamond;
            $red_packet->balance_num = $balance_num - 1;
            $red_packet->update();
            if ($red_packet->balance_num <= 0) {
                $red_packet->status = STATUS_OFF;
                $red_packet->update();

                //红包抢完公屏socket，红包过时回收的话只发送红包socket，所以两者放在afterUpdate统一处理
                $content = $red_packet->user->nickname . '发的红包已抢完';
                self::pushRedPacketTopTopicMessage($red_packet->room, $content);
            }

            $cache->zadd($key, $get_diamond, $red_packet_id);
            $cache->zadd($user_key, time(), $user_id);

            return $get_diamond;
        }

        return null;

    }

    static function generateRedPacketInRoomForUserKey($current_room_id, $red_packet_id)
    {
        return 'get_red_packet_in_room_' . $current_room_id . '_for_red_packet_' . $red_packet_id;
    }


    static function generateRedPacketForRoomKey($room_id, $user_id)
    {
        return 'get_red_packet_in_room_' . $room_id . '_for_user_' . $user_id;
    }

    static function UserGetRedPacketIds($room_id, $user_id)
    {
        $cache = \Users::getUserDb();
        $key = self::generateRedPacketForRoomKey($room_id, $user_id);
        $user_get_red_packet_ids = $cache->zrange($key, 0, -1);
        return $user_get_red_packet_ids;
    }

    static function sendSocketForRedPacket($user, $room, $opts)
    {
        $type = fetch($opts, 'type');
        $content = fetch($opts, 'content');
        $notify_type = fetch($opts, 'notify_type');

        //红包socket
        self::pushRedPacketMessageForUser($room, $user, $notify_type);

        //红包公屏socket
        self::pushRedPacketTopTopicMessage($room, $content);

        //首页下沉通知
        if (isDevelopmentEnv() && $type == 'create') {
            self:: pushRedPacketSinkMessage($room->id, $content);
        }
    }

    function getDistanceStartTime($room, $user_id)
    {
        //获取用户进房间的时间
        $time = $room->getTimeForUserInRoom($user_id);

        //如果用户进房间的时间小于红包的创建时间，则需要以红包创建时间为节点等待3分钟，否则以用户进房间的时间为节点等待3分钟
        $distance_start_at = $time + 3 * 60 - time() > 0 ? $time + 3 * 60 - time() : 0;
        if ($time <= $this->created_at) {
            $distance_start_at = $this->created_at + 3 * 60 - time() > 0 ? $this->created_at + 3 * 60 - time() : 0;
        }

        return $distance_start_at;
    }

    //红包的公屏socket
    static function pushRedPacketTopTopicMessage($room, $content)
    {
        $content_type = 'red_packet';
        $system_user = \Users::getSysTemUser();
        $room->pushTopTopicMessage($system_user, $content, $content_type);
    }

    //红包socket
    static function pushRedPacketMessageForUser($room, $user, $notify_type)
    {
        $url = self::generateRedPacketUrl($room->id);
        $underway_red_packet_num = count($room->getNotDrawRedPacket($user));
        $room->pushRedPacketMessage($user, $underway_red_packet_num, $url, $notify_type);
    }

    //下沉式
    static function pushRedPacketSinkMessage($room_id, $content)
    {
        //这里还没有做是否符合附近人的条件
        $cond = ['conditions' => 'user_status!=:user_status: and last_at>:last_at:',
            'bind' => ['user_status' => USER_TYPE_SILENT, 'last_at' => time() - 2 * 60 * 60]
        ];
        $client_url = 'app://rooms/detail?id=' . $room_id;
        $users = \Users::find($cond);
        foreach ($users as $user) {
            $body = ['action' => 'sink_notice', 'title' => '快来抢红包啦！！', 'content' => $content, 'client_url' => $client_url];
            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            $result = \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);
            info('推送结果=>', $result, '结构=>', $body);
        }
    }

}