<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/23
 * Time: 下午2:39
 */

class PushSever extends BaseModel
{
    static $_only_cache = true;
    private $websocket_client_ip;
    private $websocket_client_port;
    private $websocket_server_ip;
    private $websocket_server_port;

    function __construct()
    {
        parent::__construct();

        $this->websocket_client_ip = env('websocket_client_ip', '0.0.0.0'); //监听客户端
        $this->websocket_client_port = env('websocket_client_port', 9509); //监听客户端
        $this->websocket_server_ip = env('websocket_server_ip', '0.0.0.0'); //监听服务端
        $this->websocket_server_port = env('websocket_server_ip', 9508); //监听服务端
    }

    static function params($request, $field, $default = null)
    {
        if (isBlank($field)) {
            return '';
        }

        $val = '';

        $get = $request->get;

        if ($get) {

            debug($get);
            $val = fetch($get, $field);

            if ($val) {
                return $val;
            }

            return $default;
        }

        $post = $request->post;

        if ($post) {

            debug($post);

            $val = fetch($post, $val);

            if ($val) {
                return $val;
            }

            return $default;
        }

        return $val;
    }

    function start()
    {
        $swoole_server = new swoole_websocket_server($this->websocket_client_ip, $this->websocket_client_port);
        $swoole_server->addListener($this->websocket_server_ip, $this->websocket_server_port, SWOOLE_TCP);
        $swoole_server->set(
            [
                'worker_num' => 2, //设置多少合适
                'max_request' => 20, //设置多少合适
                'dispatch_model' => 3,
                'daemonize' => true,
                'log_file' => APP_ROOT . 'log/websocket_server.log',
                'pid_file' => APP_ROOT . 'log/websocket_server_pid.pid',
                'reload_async' => true,
                'heartbeat_check_interval' => 10, //10秒检测一次
                'heartbeat_idle_time' => 20 //20秒未向服务器发送任何数据包,此链接强制关闭
            ]
        );

        $swoole_server->on('start', [$this, 'onStart']);
        $swoole_server->on('open', [$this, 'onOpen']);
        $swoole_server->on('message', [$this, 'onMessage']);
        $swoole_server->on('close', [$this, 'onClose']);
        echo "[------------- start -------------]\n";
        $swoole_server->start();
    }

    function reload()
    {
        $url = "{$this->websocket_client_ip}:{$this->websocket_client_port}?act=reload";
        $resp = httpGet($url);
        debug($resp->body);
    }

    function shutdown()
    {
        $url = "{$this->websocket_client_ip}:{$this->websocket_client_port}?act=shutdown";
        $resp = httpGet($url);
        debug($resp->body);
    }

    function getInsideIp()
    {
        $ips = swoole_get_local_ip();

        if (count($ips) < 1) {
            info("inside ip is null");
            return '';
        }

        $eth0 = fetch($ips, 'enth0', '');

        return $eth0;
    }

    function onStart($server)
    {
        info("start");
    }

    function onOpen($server, $request)
    {
        $fd = $request->fd;

        if (!$server->exist($fd)) {
            info($request->fd, "Exce not exist");
            return;
        }

        debug("fd_info", $server->connection_info($fd));
        $online_token = $fd . 'f' . md5(uniqid() . $fd);

        $sid = self::params($request, 'sid');
        $user_id = intval($sid);

        $user = Users::findFirstById($user_id);

        if (!$user) {
            $data = ['online_token' => $online_token, 'action' => 'create_token', 'error_code' => ERROR_CODE_FAIL, 'error_reason' => '用户不存在'];
            $server->push($request->fd, json_encode($data, JSON_UNESCAPED_UNICODE));
            return;
        }

        $hot_cache = self::getHotWriteCache();
        $online_key = "socket_push_online_token_" . $fd;
        $fd_key = "socket_push_fd_" . $online_token;
        $user_online_key = "socket_user_online_user_id" . intval($sid);
        $fd_user_id_key = "socket_fd_user_id" . $online_token;

        $hot_cache->set($online_key, $online_token);
        $hot_cache->set($fd_key, $fd);
        $hot_cache->set($user_online_key, $online_token);
        $hot_cache->set($fd_user_id_key, $user_id);

        $ip = $this->getInsideIp();

        if ($ip) {
            $fd_inside_ip_key = "socket_fd_inside_ip_" . $online_token;
            $hot_cache->set($fd_inside_ip_key, $user_id);
            info($fd_inside_ip_key, $ip);
        }

        if ($user->current_room) {
            $user->current_room->bindOnlineToken($user);
        }

        debug($request->fd, "connect", $sid, $online_token);

        $data = ['online_token' => $online_token, 'action' => 'create_token'];
        $server->push($request->fd, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    function onMessage($server, $frame)
    {
        debug($frame->fd, "send message");

        if (!$server->exist($frame->fd)) {
            info($frame->fd, "Exce not exist");
            return;
        }

        $data = $frame->data;


        if (!$data) {
            debug("数据为空");
        }

        debug($data);

        $data = json_decode($data, true);
        $sign = fetch($data, 'sign');
        $sid = fetch($data, 'sid');

        debug($sign, $sid);
        if (!$sign) {
            info("sign_error", $data);
        }

        unset($data['sign']);

        debug($data, $sid);

        if ($data) {

            ksort($data);

            $temp = [];

            foreach ($data as $k => $v) {
                $temp[] = $k . "=" . $v;
            }

            $str = implode("&", $temp);

            if ($sign != md5($str)) {
                info("sign_error", $data, $str, md5($str), $sign, $sid);
            }

            debug($data, $sid);
        }

        debug($frame->fd, $sid);
        //解析数据
        $server->push($frame->fd, $frame->data);
    }

    function onClose($server, $fd, $from_id)
    {
        debug($fd, $from_id, "connect close");

        $hot_cache = self::getHotWriteCache();
        $online_key = "socket_push_online_token_" . $fd;
        $online_token = $hot_cache->get($online_key);
        $fd_key = "socket_push_fd_" . $online_token;
        $fd_user_id_key = "socket_fd_user_id" . $online_token;
        $user_id = $hot_cache->get($fd_user_id_key);
        $user_online_key = "socket_user_online_user_id" . $user_id;
        $fd_inside_ip_key = "socket_fd_inside_ip_" . $online_token;

        $hot_cache->del($online_key);
        $hot_cache->del($fd_key);
        $hot_cache->del($fd_user_id_key);
        $hot_cache->del($fd_inside_ip_key);

        $user = Users::findFirstById($user_id);
        $room_seat = null;

        if ($user) {

            $current_room = Rooms::findRoomByOnlineToken($online_token);
            $current_room_seat = RoomSeats::findRoomSeatByOnlineToken($online_token);

            $hot_cache->del("room_seat_token_" . $online_token);
            $hot_cache->del("room_token_" . $online_token);

            //用户有新的连接 老的连接不推送
            if ($user->online_token != $online_token) {
                info($online_token, $user->online_token, $user->id);
                return;
            }

            if ($current_room) {

                if ($current_room_seat) {
                    debug($current_room_seat->id);
                    $current_room_seat->down($user);
                    $room_seat = $current_room_seat->toOnlineJson();
                }

                $channel_name = $current_room->channel_name;
                $current_room->exitRoom($user);

                $key = 'room_user_list_' . $current_room->id;
                $user_ids = $hot_cache->zrange($key, 0, -1);

                if (count($user_ids) > 0) {
                    $receiver_id = $user_ids[0];

                    debug($user_ids);

                    $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_id));

                    $data = ['action' => 'exit_room', 'user_id' => $user_id, 'room_seat' => $room_seat, 'channel_name' => $channel_name];

                    debug($user_id, $receiver_id, $receiver_fd, $data);

                    //判断fd是否存在
                    if ($receiver_fd) {

                        if (!$server->exist($fd)) {
                            debug("fd 不存在", $fd);
                            return;
                        }

                        $server->push($receiver_fd, json_encode($data, JSON_UNESCAPED_UNICODE));
                    }

                    //重新连接 用户的key不一样
                    $hot_cache->del($user_online_key);
                } else {
                    info("no users", $key, $user_id);
                }

            }

            //如果有电话进行中
            if ($user->isCalling()) {
                debug($user_id, $data);
                $voice_call = VoiceCalls::getVoiceCallByUserId($user_id);

                if ($voice_call) {
                    $call_sender_id = $voice_call->sender_id;
                    $call_receiver_id = $voice_call->receiver_id;
                    $voice_call->changeStatus(CALL_STATUS_HANG_UP);
                    $receiver_id = $user_id == $call_sender_id ? $call_receiver_id : $call_sender_id;
                    $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_id));
                    $data = ['action' => 'hang_up', 'user_id' => $user_id, 'receiver_id' => $receiver_id, 'channel_name' => $voice_call->call_no];
                    debug($user_id, $receiver_id, $receiver_fd, $data);

                    $server->push($receiver_fd, json_encode($data, JSON_UNESCAPED_UNICODE));
                }
            }
        }
    }

    function onRequest($request, $response)
    {

    }
}