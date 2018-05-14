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

    static $VALIDATES = [
        'id' => ['null' => '不能为空'],
        'user_id' => ['null' => '不能为空'],
        'num' => ['null' => '不能为空'],
        'diamond' => ['null' => '不能为空'],
        'status' => ['null' => '不能为空'],
        'current_room_id' => ['null' => '不能为空'],
        'red_packet_type' => ['null' => '不能为空']
    ];

    static $RED_PACKET_STATUS = [STATUS_ON => '进行中', STATUS_OFF => '结束'];
    static $RED_PACKET_TYPE = ['all' => '都可以领取', 'attention' => '关注房主才能领取', 'stay_at_room' => '在房间满3分钟才能领取', 'nearby' => '附近的人才能领取'];
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    function beforeCreate()
    {
        $this->id = $this->generateId();
    }


    function afterCreate()
    {
        $cache = \Users::getUserDb();
        $send_red_packet_list_key = $this->generateRedPacketListKey($this->current_room_id);
        $cache->zadd($send_red_packet_list_key, time(), $this->id);

    }

    //生成房间红包列表key
    static function generateRedPacketListKey($current_room_id)
    {
        return 'send_red_packet_list_for_' . $current_room_id;
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

    static function createReadPacket($room, $opts)
    {
        info('全部参数', $opts);
        $send_red_packet_history = new \RedPackets();
        foreach (['user_id', 'diamond', 'num', 'status', 'current_room_id', 'red_packet_type', 'sex', 'nearby_distance'] as $column) {
            $send_red_packet_history->$column = fetch($opts, $column);
        }
        if ($send_red_packet_history->create()) {
            $url = self::generateRedPacketUrl($send_red_packet_history->user_id);
            $room->pushRedPacketMessage($send_red_packet_history->num, $url);
            return $send_red_packet_history;
        }
        return null;
    }

    static function generateRedPacketUrl($user_id)
    {
        return 'url://m/games';
    }

    static function findRedPacketList($current_room_id, $page, $per_page)
    {
        $key = self::generateRedPacketListKey($current_room_id);
        $cache_db = \Users::getUserDb();

        $total = $cache_db->zcard($key);
        $offset = ($page - 1) * $per_page;
        $red_packet_ids = $cache_db->zrevrange($key, $offset, $offset + $per_page - 1);
        $red_packets = \RedPackets::findByIds($red_packet_ids);

        return new \PaginationModel($red_packets, $total, $page, $per_page);
    }

    function toSimpleJson()
    {
        $start_at = date('Y-m-d H:i:s', $this->created_at + 3 * 60);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_nickanme' => $this->user->nickname,
            'diamond' => $this->diamond,
            'num' => $this->num,
            'status_text' => $this->status_text,
            'created_at_text' => $this->created_at_text,
            'start_at_text' => $start_at
        ];
    }

}