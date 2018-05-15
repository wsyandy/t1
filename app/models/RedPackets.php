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
        'red_packet_type' => ['null' => '不能为空'],
        'balance_diamond' => ['null' => '不能为空'],
        'balance_num' => ['null' => '不能为空']
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

        //generateUnderwayRedPacketListKey
        $cache->zadd($send_red_packet_list_key, time(), $this->id);
        $cache->zadd($underway_red_packet_list_key, time(), $this->id);


    }

    function afterUpdate()
    {
        if ($this->hasChanged('status') && $this->status == STATUS_OFF) {
            $cache = \Users::getUserDb();
            $underway_red_packet_list_key = self::generateUnderwayRedPacketListKey($this->current_room_id);
            $cache->zrem($underway_red_packet_list_key, time(), $this->id);
        }
    }

    //生成房间红包列表key
    static function generateRedPacketListKey($current_room_id)
    {
        return 'send_red_packet_list_for_' . $current_room_id;
    }

    //正在进行中的红包
    static function generateUnderwayRedPacketListKey($current_room_id)
    {
        return 'underway_red_packet_list_for_' . $current_room_id;
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
            //红包socket
            $url = self::generateRedPacketUrl($send_red_packet_history->current_room_id);
            $room->pushRedPacketMessage($send_red_packet_history->num, $url);

            //红包公屏socket
            $content = $user->nickname . '发了个大红包，快来抢啊！！！';
            $room->pushTopTopicMessage($user, $content);

            $opts = [
                'user_id' => $send_red_packet_history->user_id,
                'balance_diamond' => $send_red_packet_history->diamond,
                'balance_num' => $send_red_packet_history->num,
                'id' => $send_red_packet_history->id
            ];

            self::delay(24 * 60 * 60)->asyncFinishRedPacket($send_red_packet_history->id);

            self::saveRedPacketForRoom($opts);
            return $send_red_packet_history;
        }
        return null;
    }

    static function asyncFinishRedPacket($red_packet_id)
    {
        $red_packet = \RedPackets::findFirstById($red_packet_id);
        list($balance_diamond, $balance_num) = self::getBalance($red_packet_id);
        if ($balance_diamond > 0) {
            $opts = ['remark' => '红包余额返还钻石' . $balance_diamond, 'mobile' => $red_packet->user->mobile];
            \AccountHistories::changeBalance($red_packet->user_id, ACCOUNT_TYPE_RED_PACKET_RESTORATION, $balance_diamond, $opts);
        }
    }

    static function generateRedPacketUrl($room_id)
    {
        return 'url://m/red_packet_histories/red_packets_list?room_id=' . $room_id;
    }

    static function findRedPacketList($current_room_id, $page, $per_page)
    {
        $underway_red_packet_list_key = self::generateUnderwayRedPacketListKey($current_room_id);
        $cache_db = \Users::getUserDb();

        $total = $cache_db->zcard($underway_red_packet_list_key);
        $offset = ($page - 1) * $per_page;
        $red_packet_ids = $cache_db->zrevrange($underway_red_packet_list_key, $offset, $offset + $per_page - 1);
        $red_packets = \RedPackets::findByIds($red_packet_ids);

        return new \PaginationModel($red_packets, $total, $page, $per_page);
    }

    function toSimpleJson()
    {
        $distance_start_at = $this->created_at + 3 * 60 - time();
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_nickname' => $this->user->nickname,
            'diamond' => $this->diamond,
            'num' => $this->num,
            'status_text' => $this->status_text,
            'created_at_text' => $this->created_at_text,
            'distance_start_at' => $distance_start_at,
            'user_avatar_url' => $this->user->avatar_url,
            'red_packet_type' => $this->red_packet_type,
            'sex' => $this->sex
        ];
    }

    function toBasicJson()
    {
        list($balance_diamond, $balance_num) = self::getBalance($this->id);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_avatar_url' => $this->user->avatar_url,
            'user_nickname' => $this->user->nickname,
            'diamond' => $this->diamond,
            'num' => $this->num,
            'balance_diamond' => $balance_diamond,
            'balance_num' => $balance_num
        ];
    }

    static function getBalance($red_packet_id)
    {
        $cache = \Users::getUserDb();
        $red_packet_key = self::generateRedPacketInfoKey($red_packet_id);
        $balance_diamond = $cache->hget($red_packet_key, 'balance_diamond');
        $balance_num = $cache->hget($red_packet_key, 'balance_num');
        return [$balance_diamond, $balance_num];

    }

    static function grabRedPacket($current_room_id, $user, $red_packet_id)
    {
        $get_diamond = \RedPackets::getRedPacketDiamond($current_room_id, $user->id, $red_packet_id);

        if ($get_diamond) {
            $content = '恭喜' . $user->nickname . '抢到了' . $get_diamond . '个钻石';
            $room = \Rooms::findFirstById($current_room_id);
            $room->pushTopTopicMessage($user, $content);

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
        //红包内容
        $red_packet_key = self::generateRedPacketInfoKey($red_packet_id);

        //房间内对应抢到红包的用户的红包ID的集合
        $key = self::generateRedPacketForRoomKey($current_room_id, $user_id);

        //对应的抢到这个红包的用户ID集合
        $user_key = self::generateRedPacketInRoomForUserKey($current_room_id, $red_packet_id);

        $balance_diamond = $cache->hget($red_packet_key, 'balance_diamond');
        $balance_num = $cache->hget($red_packet_key, 'balance_num');
        if ($balance_diamond && $balance_num) {
            $usable_balance_diamond = $balance_diamond - ($balance_num - 1);
            $get_diamond = mt_rand(1, $usable_balance_diamond);
            $body = ['balance_num' => $balance_num - 1, 'balance_diamond' => $balance_diamond - $get_diamond];
            $cache->hmset($red_packet_key, $body);

            $cache->zadd($key, $get_diamond, $red_packet_id);

            $cache->zadd($user_key, $get_diamond, $user_id);

            return $get_diamond;
        }

        return null;

    }

    static function generateRedPacketInRoomForUserKey($current_room_id, $red_packet_id)
    {
        return 'get_red_packet_in_room_' . $current_room_id . '_for_red_packet_' . $red_packet_id;
    }


    static function saveRedPacketForRoom($opts)
    {
        $user_id = fetch($opts, 'user_id');
        $balance_diamond = fetch($opts, 'balance_diamond');
        $balance_num = fetch($opts, 'balance_num');
        $id = fetch($opts, 'id');

        $cache = \Users::getUserDb();

        //初始化红包数据
        $red_packet_key = self::generateRedPacketInfoKey($id);
        $body = ['id' => $id, 'balance_num' => $balance_num, 'balance_diamond' => $balance_diamond, 'user_id' => $user_id];
        $cache->hmset($red_packet_key, $body);
        info('初始化红包数据', $cache->hgetall($red_packet_key), $red_packet_key);

    }

    static function generateRedPacketForRoomKey($room_id, $user_id)
    {
        return 'get_red_packet_in_room_' . $room_id . '_for_user_' . $user_id;
    }

    static function generateRedPacketInfoKey($id)
    {
        return 'red_packet_info_' . $id;
    }


    static function checkRedPacketInfoForRoom($red_packet_id)
    {
        $cache = \Users::getUserDb();
        $red_packet_key = self::generateRedPacketInfoKey($red_packet_id);
        info('监测红包数据', $cache->hgetall($red_packet_key), $red_packet_key);
        $balance_diamond = $cache->hget($red_packet_key, 'balance_diamond');
        $balance_num = $cache->hget($red_packet_key, 'balance_num');
        return [$balance_diamond, $balance_num];
    }

    static function UserGetRedPacketIds($room_id, $user_id)
    {
        $cache = \Users::getUserDb();
        $key = self::generateRedPacketForRoomKey($room_id, $user_id);
        $user_get_red_packet_ids = $cache->zrange($key, 0, -1);
        return $user_get_red_packet_ids;
    }

}