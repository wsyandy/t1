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

        $cache = \Users::getUserDb();
        //当前正在进行中的红包id集合
        $underway_red_packet_list_key = self::getUnderwayRedPacketListKey($this->room_id);
        $cache->zadd($underway_red_packet_list_key, time(), $this->id);

        if ($this->diamond >= 10000) {
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
            if($this->balance_diamond){
                $content = $this->user->nickname . '发的红包已过期';
            }
            self::sendRedPacketMessageToUsers($this->user, $this->room, ['type' => 'finish', 'content' => $content]);
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

        $push_data = [
            'type' => 'create',
            'content' => $user->nickname . '在房间内发红包，手快有，手慢无，赶紧去抢吧'
        ];

        self::sendRedPacketMessageToUsers($user, $room, $push_data);

        return $red_packet;
    }

    static function asyncCheckRefund($red_packet_id)
    {

        $red_packet = \RedPackets::findFirstById($red_packet_id);
        if (!$red_packet || $red_packet->status == STATUS_OFF) {
            info('已领万', $red_packet_id);
            return;
        }

        $user = $red_packet->user;
        if ($red_packet->balance_diamond > 0) {
            $opts = ['remark' => '红包余额返还钻石' . $red_packet->balance_diamond, 'mobile' => $user->mobile, 'target_id' => $red_packet->id];
            \AccountHistories::changeBalance($user, ACCOUNT_TYPE_RED_PACKET_RESTORATION, $red_packet->balance_diamond, $opts);
        }

        info('回收红包', $red_packet->id, $red_packet->user_id, $red_packet->room_id, $red_packet->balance_diamond);

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

            if (isDevelopmentEnv()) {
                self::delay(300)->asyncCheckRefund($red_packet->id);
            }

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
        $time = $this->room->getTimeForUserInRoom($user->id);
        //如果用户进房间的时间小于红包的创建时间，则需要以红包创建时间为节点等待3分钟，否则以用户进房间的时间为节点等待3分钟
        $distance_start_at = $time + 3 * 60 - time() > 0 ? $time + 3 * 60 - time() : 0;
        if ($time <= $this->created_at) {
            $distance_start_at = $this->created_at + 3 * 60 - time() > 0 ? $this->created_at + 3 * 60 - time() : 0;
        }

        return $distance_start_at;
    }

    function grabRedPacket($user)
    {

        $lock_key = 'grab_red_packet_' . $this->id;
        $lock = tryLock($lock_key);
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

            self::sendRedPacketMessageToUsers($user, $this->room, $opts);
        }

        return $get_diamond;
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

            if ($this->balance_diamond <= 0) {
                $this->status = STATUS_OFF;
            }

            $this->save();

            $red_user_list_key = $this->generateRedPacketUserListKey();
            $cache->zadd($red_user_list_key, time(), $user_id);
            $cache->zadd($user_room_key, $get_diamond, $this->id);
            $cache->zadd($user_red_key, time(), $this->id);

            return $get_diamond;
        }

        return 0;

    }

    static function UserGetRedPacketIds($room_id, $user_id)
    {
        $cache = \Users::getUserDb();
        $key = self::generateUserRoomRedPacketsKey($room_id, $user_id);
        $user_get_red_packet_ids = $cache->zrange($key, 0, -1);
        return $user_get_red_packet_ids;
    }

    static function sendRedPacketMessageToUsers($user, $room, $opts)
    {

        $type = fetch($opts, 'type');
        $content = fetch($opts, 'content');

        //红包socket
        self::pushRedPacketNumToUser($user, $room, $type);

        //红包公屏socket
        self::pushRedPacketTopTopicMessage($room, $content);

        //首页下沉通知
        if (isDevelopmentEnv() && $type == 'create') {
            self:: pushRedPacketSinkMessage($room, $content);
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
            info($room->id, $user->id, $underway_red_packet_num);
            $room->pushRedPacketNumToUser($user, $underway_red_packet_num, $url);
        } else {

            $users = $room->findTotalRealUsers();
            foreach ($users as $other_user) {
                $underway_red_packet_num = $room->getNotDrawRedPacketNum($other_user);
                info($room->id, $other_user->id, $underway_red_packet_num);
                $room->pushRedPacketNumToUser($other_user, $underway_red_packet_num, $url);
            }
        }

    }

    //下沉式
    static function pushRedPacketSinkMessage($room, $content)
    {
        //这里还没有做是否符合附近人的条件
        $cond = ['conditions' => 'user_status!=:user_status: and last_at>:last_at:',
            'bind' => ['user_status' => USER_TYPE_SILENT, 'last_at' => time() - 30 * 60],
            'order' => 'last_at desc'
        ];

        $client_url = 'app://rooms/detail?id=' . $room->id;
        $users = \Users::findPagination($cond, 1, 500);

        foreach ($users as $user) {

            $body = ['action' => 'sink_notice', 'title' => '快来抢红包啦！！', 'content' => $content, 'client_url' => $client_url];

            $intranet_ip = $user->getIntranetIp();
            $receiver_fd = $user->getUserFd();

            if (!$intranet_ip || !$receiver_fd) {
                continue;
            }

            $result = \services\SwooleUtils::send('push', $intranet_ip, \Users::config('websocket_local_server_port'), ['body' => $body, 'fd' => $receiver_fd]);
            info('推送结果=>', $result, '结构=>', $body);
        }
    }

}