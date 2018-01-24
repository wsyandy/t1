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
    private static $intranet_ip = "intranet_ip";

    function __construct()
    {
        parent::__construct();

        $this->websocket_client_ip = env('websocket_client_ip', '0.0.0.0'); //监听客户端
        $this->websocket_client_port = env('websocket_client_port', 9509); //监听客户端
        $this->websocket_server_ip = env('websocket_server_ip', '0.0.0.0'); //监听服务端
        $this->websocket_server_port = env('websocket_server_ip', 9508); //监听服务端
    }

    static function getJobQueueCache()
    {
        $job_queue = self::config('job_queue');
        $endpoint = $job_queue->endpoint;
        $cache = XRedis::getInstance($endpoint);
        return $cache;
    }

    static function getIntranetIp()
    {
        $cache = self::getJobQueueCache();
        $ip = $cache->get(self::$intranet_ip);

        if ($ip) {
            debug($ip);
            return $ip;
        }

        $ips = swoole_get_local_ip();
        $ips = array_values($ips);

        debug($ips);

        if (count($ips) < 1) {
            info("intranet ip is null");
            return '';
        }

        $ip = $ips[0];
        self::saveIntranetIp($ip);
        return $ip;
    }

    static function saveIntranetIp($ip)
    {
        $cache = self::getJobQueueCache();
        $cache->set(self::$intranet_ip, $ip);
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

    //服务器内部通信
    function send($action, $opts = [])
    {
        debug($action, $opts);
        $ip = fetch($opts, 'ip', self::getIntranetIp());
        $ip = self::getIntranetIp();
        debug($this->websocket_server_port, $ip);
        $client = new \WebSocket\Client("ws://{$ip}:$this->websocket_server_port");
        $payload = ['action' => $action, 'message' => $opts];
        $data = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $client->send($data);
        $client->close();
    }

    function reload($server, $opt = [])
    {
        $server->reload();
    }

    function shutdown($server, $opt = [])
    {
        $server->shutdown();
    }

    function onStart($server)
    {
        info("start");
    }

    //服务器客户端通信
    function push($server, $opts = [])
    {
        debug($opts);
        $fd = fetch($opts, 'fd');
        $body = fetch($opts, 'body');
        $server->push($fd, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    function onOpen($server, $request)
    {
        $fd = $request->fd;

        if (!$server->exist($fd)) {
            info($request->fd, "Exce not exist");
            return;
        }

        $online_token = $fd . 'f' . md5(uniqid() . $fd);

        $sid = self::params($request, 'sid');
        info($fd, $online_token, $sid);
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

        $ip = self::getIntranetIp();

        if ($ip) {
            $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
            $hot_cache->set($fd_intranet_ip_key, $ip);
            info($fd_intranet_ip_key, $ip);
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
        $fd = $frame->fd;

        debug($frame->data);
        if (!$server->exist($fd)) {
            info($frame->fd, "Exce not exist");
            return;
        }

        $data = $frame->data;

        if (!$data) {
            info("data is null", $fd);
        }

        $data = json_decode($data, true);
        debug($data);
        $connect_info = $server->connection_info($fd);
        $server_port = fetch($connect_info, 'server_port');

        if ($this->websocket_server_port == $server_port) {
            $action = fetch($data, 'action');
            $message = fetch($data, 'message', []);
            info("server_to_server", $data, $action, $message);
            $this->$action($server, $message);
        } else {
            $sign = fetch($data, 'sign');
            $sid = fetch($data, 'sid');

            if (!$sign) {
                info("sign_error", $data);
            }

            unset($data['sign']);

            if ($data) {

                ksort($data);

                $sign_data = json_encode($data, JSON_UNESCAPED_UNICODE);

                if ($sign != md5($sign_data)) {
                    info("sign_error", $data, $sign_data, md5($sign_data), $sign, $sid);
                }
            }

            info($fd, $sid, $data);

            if (isDevelopmentEnv()) {
                //解析数据
                $hot_cache = self::getHotReadCache();
                $online_key = "socket_push_online_token_" . $fd;
                $online_token = $hot_cache->get($online_key);
                $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
                $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
                $payload = ['body' => $data, 'fd' => $fd, 'ip' => $intranet_ip];
                debug($payload);
                //$this->send('push', $payload);
                $server->push($frame->fd, $frame->data);
            }
        }
    }

    function onClose($server, $fd, $from_id)
    {
        $hot_cache = self::getHotWriteCache();
        $online_key = "socket_push_online_token_" . $fd;
        $online_token = $hot_cache->get($online_key);
        $fd_key = "socket_push_fd_" . $online_token;
        $fd_user_id_key = "socket_fd_user_id" . $online_token;
        $user_id = $hot_cache->get($fd_user_id_key);
        $user_online_key = "socket_user_online_user_id" . $user_id;
        $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
        $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
        $hot_cache->del($online_key);
        $hot_cache->del($fd_key);
        $hot_cache->del($fd_user_id_key);
        $hot_cache->del($fd_intranet_ip_key);

        $user = Users::findFirstById($user_id);
        $room_seat = null;

        if ($user) {

            debug($fd, $user->sid, "connect close");

            $current_room = Rooms::findRoomByOnlineToken($online_token);
            $current_room_seat = RoomSeats::findRoomSeatByOnlineToken($online_token);

            $hot_cache->del("room_seat_token_" . $online_token);
            $hot_cache->del("room_token_" . $online_token);

            //用户有新的连接 老的连接不推送
            if ($user->online_token != $online_token) {
                info("online_token change", $online_token, $user->online_token, $user->sid);
                return;
            }

            if (!$intranet_ip) {
                info("intranet_ip is null", $user->sid, $online_token);
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

                    $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_id));

                    $data = ['action' => 'exit_room', 'user_id' => $user_id, 'room_seat' => $room_seat, 'channel_name' => $channel_name];

                    info("exit_room_exce", $user->sid, $user_ids, $receiver_id, $receiver_fd, $data);

                    //判断fd是否存在
                    if ($receiver_fd) {

                        if (!$server->exist($fd)) {
                            info("fd 不存在", $fd);
                            return;
                        }

                        $payload = ['body' => $data, 'fd' => $fd, 'ip' => $intranet_ip];
                        //$this->send('push', $payload);
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
                $voice_call = VoiceCalls::getVoiceCallByUserId($user_id);

                if ($voice_call) {
                    $call_sender_id = $voice_call->sender_id;
                    $call_receiver_id = $voice_call->receiver_id;
                    $voice_call->changeStatus(CALL_STATUS_HANG_UP);
                    $receiver_id = $user_id == $call_sender_id ? $call_receiver_id : $call_sender_id;
                    $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_id));
                    $data = ['action' => 'hang_up', 'user_id' => $user_id, 'receiver_id' => $receiver_id, 'channel_name' => $voice_call->call_no];
                    info("calling_hang_up_exce", $user->sid, $receiver_id, $receiver_fd, $data);
                    $payload = ['body' => $data, 'fd' => $fd, 'ip' => $intranet_ip];
                    //$this->send('push', $payload);
                    $server->push($receiver_fd, json_encode($data, JSON_UNESCAPED_UNICODE));
                }
            }
        }
    }

    function isLocalIp($intranet_ip)
    {
        $ip = self::getIntranetIp();
        return $intranet_ip == $ip;
    }
}