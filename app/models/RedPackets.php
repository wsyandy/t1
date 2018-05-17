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
    private $_current_room_id;

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
        'current_room_id' => ['null' => '不能为空'],
        'red_packet_type' => ['null' => '不能为空']
    ];

    static $RED_PACKET_TYPE = [RED_PACKET_TYPE_ALL => '都可以领取', RED_PACKET_TYPE_ATTENTION => '关注房主才能领取', RED_PACKET_TYPE_STAY_AT_ROOM => '在房间满3分钟才能领取', RED_PACKET_TYPE_NEARBY => '附近的人才能领取'];
    static $STATUS = [STATUS_ON => '进行中', STATUS_OFF => '结束'];

    function beforeCreate()
    {
        $this->id = $this->generateId();
    }


    function afterCreate()
    {
        $cache = \Users::getUserDb();
        //当前房间对应红包id集合
        $send_red_packet_list_key = self::generateRedPacketListKey($this->current_room_id);
        //当前正在进行中的红包id集合
        $underway_red_packet_list_key = self::generateUnderwayRedPacketListKey($this->current_room_id);

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
            $cache = \Users::getUserDb();
            $underway_red_packet_list_key = self::generateUnderwayRedPacketListKey($this->current_room_id);
            $cache->zrem($underway_red_packet_list_key, time(), $this->id);
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
            'sex' => $this->sex
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
            'current_room_id' => $this->current_room_id
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
        foreach (['user_id', 'diamond', 'num', 'status', 'current_room_id', 'red_packet_type', 'sex', 'nearby_distance', 'balance_num', 'balance_diamond'] as $column) {
            $send_red_packet_history->$column = fetch($opts, $column);
        }
        if ($send_red_packet_history->create()) {

            self::sendSocketForRedpacket($send_red_packet_history, $user, $room);
            $time = 24 * 60 * 60;
            if (isDevelopmentEnv()) {
                $time = 5 * 60;
            }

            self::delay($time)->asyncFinishRedPacket($send_red_packet_history->id);

            if ($send_red_packet_history->diamond >= 1000) {
                $user->has_red_packet = STATUS_ON;
                $user->update();

                $room = $user->current_room;
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
        $balance_diamond = $red_packet->balance_diamond;

        if ($balance_diamond > 0) {
            $opts = ['remark' => '红包余额返还钻石' . $balance_diamond, 'mobile' => $red_packet->user->mobile, 'target_id' => $red_packet->id];
            \AccountHistories::changeBalance($red_packet->user_id, ACCOUNT_TYPE_RED_PACKET_RESTORATION, $balance_diamond, $opts);
        }

        if (isPresent($red_packet) && $red_packet->status != STATUS_OFF) {
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

    static function findRedPacketList($current_room_id, $page, $per_page, $user)
    {
        $underway_red_packet_list_key = self::generateUnderwayRedPacketListKey($current_room_id);
        $cache_db = \Users::getUserDb();

        $total = $cache_db->zcard($underway_red_packet_list_key);
        $offset = ($page - 1) * $per_page;
        $red_packet_ids = $cache_db->zrevrange($underway_red_packet_list_key, $offset, $offset + $per_page - 1);
        $red_packets = \RedPackets::findByIds($red_packet_ids);
//        $screen_red_packets = self::getScreenRedPackets($red_packets, $user);

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
        $get_diamond = \RedPackets::getRedPacketDiamond($user->current_room_id, $user->id, $red_packet_id);

        if ($get_diamond) {
            $content = '恭喜' . $user->nickname . '抢到了' . $get_diamond . '个钻石';
            $content_type = 'red_packet';
            $system_user = \Users::getSysTemUser();
            $room->pushTopTopicMessage($system_user, $content, $content_type);

            //红包socket
            $url = self::generateRedPacketUrl($user->current_room_id);
            $underway_red_packet = $room->getNotDrawRedPacket($user);
            $room->pushRedPacketMessage(count($underway_red_packet), $url);

            return [ERROR_CODE_SUCCESS, $get_diamond];
        }

        $red_packet = self::findFirstById($red_packet_id);
        $red_packet->status = STATUS_OFF;
        $red_packet->update();

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

        if ($balance_diamond && $balance_num) {
            $usable_balance_diamond = $balance_diamond - ($balance_num - 1);
            $get_diamond = mt_rand(1, $usable_balance_diamond);
            $red_packet->balance_diamond = $balance_diamond - $get_diamond;
            $red_packet->balance_num = $balance_num - 1;
            $red_packet->update();

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

    static function sendSocketForRedpacket($send_red_packet_history, $user, $room)
    {
        //红包socket
        $url = self::generateRedPacketUrl($send_red_packet_history->current_room_id);
        $underway_red_packet = $room->getNotDrawRedPacket($user);
        $room->pushRedPacketMessage(count($underway_red_packet), $url);

        //红包公屏socket
        $content = $user->nickname . '在房间内发红包，手快有，手慢无，赶紧去抢吧';
        $content_type = 'red_packet';
        $system_user = \Users::getSysTemUser();
        $room->pushTopTopicMessage($system_user, $content, $content_type);

        //首页下沉通知
        if (isDevelopmentEnv()) {
            //这里还没有做是否符合附近人的条件
            $cond = ['conditions' => 'user_status!=:user_status: and last_at>:last_at:',
                'bind' => ['user_status' => USER_TYPE_SILENT, 'last_at' => time() - 2 * 60 * 60]
            ];
            $client_url = 'app://rooms/detail?id=' . $room->id;
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

}