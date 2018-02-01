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
    private $websocket_listen_client_ip;
    private $websocket_listen_client_port;
    private $websocket_listen_server_ip;
    private $websocket_listen_server_port;
    private static $intranet_ip_key = "intranet_ip";
    private $connection_list = 'websocket_connection_list';
    private $server;

    function __construct()
    {
        parent::__construct();

        $this->websocket_listen_client_ip = self::config('websocket_listen_client_ip'); //监听客户端
        $this->websocket_listen_client_port = self::config('websocket_listen_client_port'); //监听客户端
        $this->websocket_listen_server_ip = self::config('websocket_listen_server_ip'); //监听服务端
        $this->websocket_listen_server_port = self::config('websocket_listen_server_port'); //监听服务端
        $this->websocket_worker_num = self::config('websocket_worker_num'); //监听服务端
        $this->websocket_max_request = self::config('websocket_max_request'); //监听服务端
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
        $ip = $cache->get(self::$intranet_ip_key);

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
        $cache->set(self::$intranet_ip_key, $ip);
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
        try {
            $swoole_server = new swoole_websocket_server($this->websocket_listen_client_ip, $this->websocket_listen_client_port);
            $swoole_server->addListener($this->websocket_listen_server_ip, $this->websocket_listen_server_port, SWOOLE_SOCK_TCP);
            $swoole_server->set(
                [
                    'worker_num' => $this->websocket_worker_num, //cpu的1~4倍
                    'max_request' => $this->websocket_max_request, //设置多少合适
                    'dispatch_model' => 2,
                    'daemonize' => true,
                    'log_file' => APP_ROOT . 'log/websocket_server.log',
                    'pid_file' => APP_ROOT . 'log/pids/websocket/server.pid',
                    'reload_async' => true,
                    'heartbeat_check_interval' => 10, //10秒检测一次
                    'heartbeat_idle_time' => 20, //20秒未向服务器发送任何数据包,此链接强制关闭
                    //'task_worker_num' => 8
                ]
            );

            $swoole_server->on('start', [$this, 'onStart']);
            $swoole_server->on('open', [$this, 'onOpen']);
            $swoole_server->on('message', [$this, 'onMessage']);
            $swoole_server->on('close', [$this, 'onClose']);
            $swoole_server->on('request', function ($request, $response) use ($swoole_server) {
                $post = $request->post;
                $action = fetch($post, 'action');

                if ('push' == $action) {
                    $payload = fetch($post, 'payload');
                    $receiver_fd = fetch($payload, 'fd');
                    $body = fetch($payload, 'body');
                    info($receiver_fd, $body);
                    if ($receiver_fd) {
                        if (!$swoole_server->exist($receiver_fd)) {
                            info($receiver_fd, "Exce not exist");
                            return;
                        }
                        $swoole_server->push($receiver_fd, json_encode($body, JSON_UNESCAPED_UNICODE));
                    }
                }
            });
            $swoole_server->start();
            echo "[------------- start -------------]\n";
        } catch (\Exception $e) {
            info("Exce", $e->getMessage());
        }
    }

    //服务器内部通信
    function send1($action, $ip, $payload = [])
    {
        info($this->websocket_listen_server_port, $ip, $action, $payload);

        try {
            //$client = new \WebSocket\Client("$protocol://{$ip}:$this->websocket_listen_server_port");
            $client = new PushClient($ip, $this->websocket_listen_server_port, 1);
            if (!$client->connect()) {
                info("Exce connect fail");
                return false;
            }
            $payload = ['action' => $action, 'payload' => $payload];
            $data = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $client->send($data);
            $client->close();
            return true;
        } catch (\Exception $e) {
            info("Exce", $action, $ip, $payload, $e->getMessage());
        }

        return false;
    }

    function send($action, $ip, $payload = [])
    {
        info($this->websocket_listen_server_port, $ip, $action, $payload);

        try {
            $payload = ['action' => $action, 'payload' => $payload];
            httpPost($ip . ":" . $this->websocket_listen_server_port, $payload);
            return true;
        } catch (\Exception $e) {
            info("Exce", $action, $ip, $payload, $e->getMessage());
        }

        return false;
    }


    function onStart($server)
    {
        info("start");
    }

    function onOpen($server, $request)
    {
        $fd = $request->fd;
        $connect_info = $server->connection_info($fd);
        $server_port = fetch($connect_info, 'server_port');

        if ($this->websocket_listen_server_port == $server_port) {
            info($fd, "server_to_server onOpen");
            $server->push($request->fd, json_encode(['welcome'], JSON_UNESCAPED_UNICODE));
            return;
        }

        if (!$server->exist($fd)) {
            info($request->fd, "Exce not exist");
            return;
        }

        $online_token = $fd . 'f' . md5(uniqid() . $fd);

        $ip = self::getIntranetIp();
        $sid = self::params($request, 'sid');
        info($sid, $fd, $online_token, $ip);
        $user_id = intval($sid);
        $user = Users::findFirstById($user_id);

        $hot_cache = self::getHotWriteCache();
        $hot_cache->zincrby($this->connection_list, 1, $ip);
        if (!$user) {
            $data = ['online_token' => $online_token, 'action' => 'create_token', 'error_code' => ERROR_CODE_FAIL, 'error_reason' => '用户不存在'];
            $server->push($request->fd, json_encode($data, JSON_UNESCAPED_UNICODE));
            return;
        }

        $online_key = "socket_push_online_token_" . $fd;
        $fd_key = "socket_push_fd_" . $online_token;
        $user_online_key = "socket_user_online_user_id" . intval($sid);
        $fd_user_id_key = "socket_fd_user_id" . $online_token;

        $hot_cache->set($online_key, $online_token);
        $hot_cache->set($fd_key, $fd);
        $hot_cache->set($user_online_key, $online_token);
        $hot_cache->set($fd_user_id_key, $user_id);

        if ($ip) {
            $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
            $hot_cache->set($fd_intranet_ip_key, $ip);
        }

        if ($user->current_room) {
            $user->current_room->bindOnlineToken($user);
        }

        if ($user->current_room_seat) {
            $user->current_room_seat->bindOnlineToken($user);
        }

        $data = ['online_token' => $online_token, 'action' => 'create_token'];
        $server->push($request->fd, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    function onMessage($server, $frame)
    {
        $fd = $frame->fd;

        if (!$server->exist($fd)) {
            info($frame->fd, "Exce not exist");
            return;
        }

        $data = $frame->data;

        if (!$data) {
            info("data is null", $fd);
        }

        $connect_info = $server->connection_info($fd);
        $server_port = fetch($connect_info, 'server_port');
        $data = json_decode($data, true);

        if ($this->websocket_listen_server_port == $server_port) {

            info("server_to_server", $data);
            $action = fetch($data, 'action');
            $payload = fetch($data, 'payload');

            try {
                if ('shutdown' == $action) {
                    $server->shutdown();
                } elseif ('push' == $action) {
                    $receiver_fd = fetch($payload, 'fd');
                    $body = fetch($payload, 'body');
                    info($receiver_fd, $body);
                    if ($receiver_fd) {
                        if (!$server->exist($receiver_fd)) {
                            info($receiver_fd, "Exce not exist");
                            return;
                        }
                        $server->push($receiver_fd, json_encode($body, JSON_UNESCAPED_UNICODE));
                    }
                }
            } catch (\Exception $e) {
                info("Exce", $action, $payload, $e->getMessage());
            }
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

            info($sid, $fd, $data);

            if (isDevelopmentEnv()) {
                //解析数据
                $hot_cache = self::getHotReadCache();
                $online_key = "socket_push_online_token_" . $fd;
                $online_token = $hot_cache->get($online_key);
                $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;
                $intranet_ip = $hot_cache->get($fd_intranet_ip_key);
                debug($intranet_ip, $online_token);
                $payload = ['body' => $data, 'fd' => $fd];
                $this->send('push', $intranet_ip, $payload);
                //$res = $server->push($frame->fd, $frame->data);
            }
        }
    }

    function onClose($server, $fd, $from_id)
    {
        $hot_cache = self::getHotWriteCache();
        $online_key = "socket_push_online_token_" . $fd;
        $online_token = $hot_cache->get($online_key);

        $connect_info = $server->connection_info($fd);

        $server_port = fetch($connect_info, 'server_port');
        if ($this->websocket_listen_server_port == $server_port) {
            info($fd, "server_to_server onClose");
            return;
        }

        if (!$online_token) {
            info("fd非法", $fd, $from_id);
            return;
        }

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

        $local_ip = self::getIntranetIp();
        if ($local_ip) {
            $hot_cache->zincrby($this->connection_list, -1, $local_ip);
        }

        if ($user) {

            info($user->sid, $fd, $local_ip, "connect close");

            $current_room = Rooms::findRoomByOnlineToken($online_token);
            $current_room_seat = RoomSeats::findRoomSeatByOnlineToken($online_token);

            $hot_cache->del("room_seat_token_" . $online_token);
            $hot_cache->del("room_token_" . $online_token);

            //用户有新的连接 老的连接不推送
            if ($user->online_token != $online_token) {
                info("online_token change", $user->sid, $online_token, $user->online_token);
                return;
            }

            if (!$intranet_ip) {
                info("intranet_ip is null", $user->sid, $online_token);
                return;
            }

            if ($current_room) {

                //并发退出房间
                $exce_exit_room_key = "exce_exit_room_id{$current_room->id}";
                $exce_exit_room_lock = tryLock($exce_exit_room_key, 1000);

                if ($current_room_seat) {
                    $current_room_seat->down($user);
                    $room_seat = $current_room_seat->toOnlineJson();
                }

                $current_room->exitRoom($user);
                $this->pushExitRoomInfo($server, $user, $current_room, $room_seat, $intranet_ip);
                //重新连接 用户的key不一样
                $hot_cache->del($user_online_key);
                unlock($exce_exit_room_lock);
            } else {
                info("room not exists", $user->sid, $online_token);
            }

            //如果有电话进行中
            if ($user->isCalling()) {
                $this->pushHangupInfo($server, $user, $intranet_ip);
            }
        }
    }

//    public function onTask($server, $task_id, $from_id, $data)
//    {
//        debug("任务ID: {$task_id} WorkID: {$from_id}", $data);
//
//        $total = 0;
//        $start_at = microtime(true);
//        $json = json_decode($data, true);
//        $action = fetch($json, 'action');
//        $message = fetch($json, 'message');
//        $fd = fetch($message, 'fd');
//        $body = fetch($message, 'body');
//        $server->push($fd, json_encode($body, JSON_UNESCAPED_UNICODE));
//        $use_time = sprintf('%0.03f秒', microtime(true) - $start_at, $fd);
//        return "这是任务ID{$task_id}处理结果:" . $total . ' 用时:' . $use_time;
//    }
//
//    public function onFinish($server, $task_id, $data)
//    {
//        debug("{$task_id}处理结果: {$data}");
//    }

    function pushExitRoomInfo($server, $user, $current_room, $room_seat, $intranet_ip)
    {
        $hot_cache = self::getHotWriteCache();
        $key = 'room_user_list_' . $current_room->id;
        $user_ids = $hot_cache->zrevrange($key, 0, 10);
        $channel_name = $current_room->channel_name;

        foreach ($user_ids as $receiver_id) {

            $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_id));

            $data = ['action' => 'exit_room', 'user_id' => $user->id, 'room_seat' => $room_seat, 'channel_name' => $channel_name];

            //判断fd是否存在
            if ($receiver_fd) {

                if (!$server->exist($receiver_fd)) {
                    info("fd 不存在", $user->sid, $receiver_fd);
                    return;
                }

                $payload = ['body' => $data, 'fd' => $receiver_fd];
                //$res = $this->send('push', $intranet_ip, $payload);
                $res = $server->push($receiver_fd, json_encode($data, JSON_UNESCAPED_UNICODE));

                if ($res) {
                    info("exit_room_exce", $user->sid, $user_ids, $receiver_id, $receiver_fd, $data);
                    break;
                }
            }
        }
    }

    function pushHangupInfo($server, $user, $intranet_ip)
    {
        $hot_cache = self::getHotReadCache();
        $user_id = $user->id;
        $voice_call = VoiceCalls::getVoiceCallByUserId($user_id);

        if ($voice_call) {
            $call_sender_id = $voice_call->sender_id;
            $call_receiver_id = $voice_call->receiver_id;
            $voice_call->changeStatus(CALL_STATUS_HANG_UP);
            $receiver_id = $user->id == $call_sender_id ? $call_receiver_id : $call_sender_id;
            $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_id));
            $data = ['action' => 'hang_up', 'user_id' => $user_id, 'receiver_id' => $receiver_id, 'channel_name' => $voice_call->call_no];
            info("calling_hang_up_exce", $user->sid, $receiver_id, $receiver_fd, $data, $intranet_ip);
            //$payload = ['body' => $data, 'fd' => $fd, 'ip' => $intranet_ip];
            //$this->send('push', $payload);
            $server->push($receiver_fd, json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }

    function isLocalIp($intranet_ip)
    {
        $ip = self::getIntranetIp();
        return $intranet_ip == $ip;
    }

    function getConnectionNum()
    {
        $hot_cache = self::getHotReadCache();
        $local_ip = self::getIntranetIp();
        return $hot_cache->zscore($this->connection_list, $local_ip);
    }

    function clearConnectionNum()
    {
        $hot_cache = self::getHotReadCache();
        return $hot_cache->del($this->connection_list);
    }

    static function getWebsocketEndPoint()
    {
        return self::config('websocket_client_endpoint');
    }
}