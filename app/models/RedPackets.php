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

    static $RED_PACKET_TYPE = [RED_PACKET_TYPE_ALL => '都可以领取', RED_PACKET_TYPE_FOLLOW => '关注房主才能领取', RED_PACKET_TYPE_STAY_AT_ROOM => '在房间满3分钟才能领取'];

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

        $cache = \Users::getUserDb();
        //当前正在进行中的红包id集合
        $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id);
        $cache->zadd($underway_red_packet_list_key, time(), $this->id);

        if ($this->diamond >= 1000) {
            $this->user->has_red_packet = STATUS_ON;
            $this->user->update();

            $this->room->has_red_packet = STATUS_ON;
            $this->room->update();
        }
    }

    function afterUpdate()
    {
        if ($this->hasChanged('status') && $this->status == STATUS_OFF) {

            $cache = \Users::getUserDb();
            $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id);
            $cache->zrem($underway_red_packet_list_key, $this->id);

            info('红包已经结束回收，删除对应进行中的红包id', $underway_red_packet_list_key, $this->id, $this->status);

            self::pushRedPacketMessageForUser($this->room, $this->user, 'bc');


            if ($this->diamond >= 1000) {
                $this->user->has_red_packet = STATUS_OFF;
                $this->user->update();
                if($this->room){
                    $this->room->has_red_packet = STATUS_OFF;
                    $this->room->update();
                }
            }
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
            'user_avatar_url' => $this->user->avatar_small_url,
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
    static function getUnderwayRedPacketListKey($current_room_id)
    {
        return 'room_underway_red_packet_list_' . $current_room_id;
    }

    function generateRedPacketUserListKey()
    {
        return 'red_packet_user_list_' . $this->id;
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

        return $red_packet;
    }

    static function asyncCheckRefund($red_packet_id)
    {

        $red_packet = \RedPackets::findFirstById($red_packet_id);
        if (!$red_packet || $red_packet->status == STATUS_ON) {
            return;
        }

        $user = $red_packet->user;
        if ($red_packet->balance_diamond > 0) {
            $opts = ['remark' => '红包余额返还钻石' . $red_packet->balance_diamond, 'mobile' => $user->mobile, 'target_id' => $red_packet->id];
            \AccountHistories::changeBalance($user, ACCOUNT_TYPE_RED_PACKET_RESTORATION, $red_packet->balance_diamond, $opts);
        }

        info('回收红包', $red_packet->id, $red_packet->user_id, $red_packet->room_id, $red_packet->balance_diamond);

        $red_packet->balance_diamond = 0;
        $red_packet->balance_num = 0;
        $red_packet->status = STATUS_OFF;
        $red_packet->update();
    }

    // 房间里的红包
    static function findRedPacketList($user, $room, $page, $per_page = 20)
    {

        $cache_db = \Users::getUserDb();
        $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($room->id);
        $total = $cache_db->zcard($underway_red_packet_list_key);
        $offset = ($page - 1) * $per_page;
        $red_packet_ids = $cache_db->zrevrange($underway_red_packet_list_key, $offset, $offset + $per_page - 1);
        $red_packets = \RedPackets::findByIds($red_packet_ids);

        foreach ($red_packets as $red_packet) {
            $distance_start_at = $red_packet->getDistanceStartTime($user);
            $red_packet->distance_start_at = $distance_start_at;
        }

        return new \PaginationModel($red_packets, $total, $page, $per_page);
    }

    // 可以领取的时间
    function getDistanceStartTime($user)
    {

        if($this->red_packet_type != RED_PACKET_TYPE_STAY_AT_ROOM){
            return 0;
        }

        //获取用户进房间的时间
        $time = $this->room->getTimeForUserInRoom($user->id);
        //如果用户进房间的时间小于红包的创建时间，则需要以红包创建时间为节点等待3分钟，否则以用户进房间的时间为节点等待3分钟
        $distance_start_at = $time + 3 * 60 - time() > 0 ? $time + 3 * 60 - time() : 0;
        if ($time <= $this->created_at) {
            $distance_start_at = $this->created_at + 3 * 60 - time() > 0 ? $this->created_at + 3 * 60 - time() : 0;
        }

        return $distance_start_at;
    }

    function grabRedPacket($user, $room)
    {

        $get_diamond = $this->getRedPacketDiamond($user->id);

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

    function getRedPacketDiamond($user_id)
    {
        $cache = \Users::getUserDb();
        $user_room_key = self::generateUserRoomRedPacketsKey($this->room_id, $user_id);
        $user_red_key = self::generateUserRedPacketsKey($user_id);

        $balance_diamond = $this->balance_diamond;
        $balance_num = $this->balance_num;

        if ($balance_diamond && $balance_num && $this->status == STATUS_ON) {
            $usable_balance_diamond = $balance_diamond - ($balance_num - 1);
            if ($usable_balance_diamond > ceil($this->diamond * 0.5)) {
                $get_diamond = mt_rand(1, ceil($this->diamond * 0.4));
            } else {
                $get_diamond = mt_rand(1, $usable_balance_diamond);
            }

            if ($balance_num == 1) {
                $get_diamond = $balance_diamond;
            }
            $this->balance_diamond = $balance_diamond - $get_diamond;
            $this->balance_num = $balance_num - 1;
            $this->update();
            if ($this->balance_num <= 0) {
                $this->status = STATUS_OFF;
                $this->update();

                //红包抢完公屏socket，红包过时回收的话只发送红包socket，所以两者放在afterUpdate统一处理
                $content = $this->user->nickname . '发的红包已抢完';
                self::pushRedPacketTopTopicMessage($this->room, $content);
            }

            $red_user_list_key = $this->generateRedPacketUserListKey();
            $cache->zadd($red_user_list_key, time(), $user_id);
            $cache->zadd($user_room_key, $get_diamond, $this->id);
            $cache->zadd($user_red_key, time(), $this->id);

            return $get_diamond;
        }

        return null;

    }

    static function UserGetRedPacketIds($room_id, $user_id)
    {
        $cache = \Users::getUserDb();
        $key = self::generateUserRoomRedPacketsKey($room_id, $user_id);
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
        $underway_red_packet_num = $room->getNotDrawRedPacketNum($user);
        $room->pushRedPacketMessage($user, $underway_red_packet_num, $url, $notify_type);
    }

    //下沉式
    static function pushRedPacketSinkMessage($room_id, $content)
    {
        //这里还没有做是否符合附近人的条件
        $cond = ['conditions' => 'user_status!=:user_status: and last_at>:last_at:',
            'bind' => ['user_status' => USER_TYPE_SILENT, 'last_at' => time() - 30 * 60]
        ];

        $client_url = 'app://rooms/detail?id=' . $room_id;
        $users = \Users::find($cond);

        foreach ($users as $user) {

            $body = ['action' => 'sink_notice', 'title' => '快来抢红包啦！！', 'content' => $content, 'client_url' => $client_url];

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            if(!$intranet_ip || !$receiver_fd){
                continue;
            }

            $result = \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);
            info('推送结果=>', $result, '结构=>', $body);
        }
    }

}