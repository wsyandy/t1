<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/23
 * Time: 下午2:39
 */

class WebsocketSever extends BaseModel
{
    static $_only_cache = true;

    static function getWebsocketConfig()
    {
        $websocket_config = self::di('config')->websocket_server;
        return $websocket_config;
    }

    static function getHost()
    {
        $websocket_config = self::getWebsocketConfig();
        $host = $websocket_config->host;

        return $host;
    }

    static function getPort()
    {
        $websocket_config = self::getWebsocketConfig();
        $port = $websocket_config->port;

        return $port;
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

    static function start()
    {
        $host = self::getHost();
        $port = self::getPort();

        $swoole_server = new swoole_websocket_server($host, $port);
        $swoole_server->set(
            [
                'worker_num' => 2, //设置多少合适
                'max_request' => 20, //设置多少合适
                'dispatch_model' => 3,
                'daemonize' => true,
                'log_file' => APP_ROOT . 'log/swoole_websocket_server.log',
                'pid_file' => APP_ROOT . 'log/swoole_websocket_server_pid.pid',
                'reload_async' => true,
                'heartbeat_check_interval' => 10, //10秒检测一次
                'heartbeat_idle_time' => 20 //20秒未向服务器发送任何数据包,此链接强制关闭
            ]
        );

        $websocket = new WebsocketSever();
        $swoole_server->on('start', [$websocket, 'onStart']);
        $swoole_server->on('open', [$websocket, 'onOpen']);
        $swoole_server->on('message', [$websocket, 'onMessage']);
        $swoole_server->on('close', [$websocket, 'onClose']);

        $swoole_server->on('request', function ($request, $response) use ($swoole_server) {
            $act = $request->get['act'];
            debug($act);
            switch ($act) {
                case 'reload':
                    $swoole_server->reload();
                    break;
                case 'shutdown':
                    $swoole_server->shutdown();
                    break;
                case 'exit':
                    exit;
                    break;
            }

            $response->header("X-Server", "Swoole");
            $msg = 'hello swoole !';
            $response->end($msg);
        });

        echo "[------------- start -------------]\n";
        $swoole_server->start();
    }

    static function reload()
    {
        $host = self::getHost();
        $port = self::getPort();

        $url = "{$host}:{$port}?act=reload";
        $resp = httpGet($url);
        debug($resp->body);
    }

    static function shutdown()
    {
        $host = self::getHost();
        $port = self::getPort();

        $url = "{$host}:{$port}?act=shutdown";
        $resp = httpGet($url);
        debug($resp->body);
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

        $online_token = $fd . 'f' . md5(uniqid() . $fd);

        $sid = self::params($request, 'sid');

        $hot_cache = self::getHotWriteCache();
        $online_key = "socket_push_online_token_" . $fd;
        $fd_key = "socket_push_fd_" . $online_token;
        $user_online_key = "socket_user_online_user_id" . intval($sid);
        $fd_user_id_key = "socket_fd_user_id" . $online_token;

        $hot_cache->set($online_key, $online_token);
        $hot_cache->set($fd_key, $fd);
        $hot_cache->set($user_online_key, $online_token);
        $hot_cache->set($fd_user_id_key, intval($sid));

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

        $hot_cache->del($online_key);
        $hot_cache->del($fd_key);
        $hot_cache->del($user_online_key);
        $hot_cache->del($fd_user_id_key);

        $user = Users::findFirstById($user_id);
        $room_seat = [];

        if ($user) {

            $current_room = Rooms::findRoomByOnlineToken($online_token);

            $hot_cache->del("room_seat_token_" . $online_token);
            $hot_cache->del("room_token_" . $online_token);

            //用户有新的连接 老的连接不推送
            if ($user->online_token != $online_token) {
                info($online_token, $user->online_token, $user->id);
                return;
            }

            if ($current_room) {

                $channel_name = $current_room->channel_name;
                $current_room->exitRoom($user);

                $current_room_seat = RoomSeats::findRoomSeatByOnlineToken($online_token);

                if ($current_room_seat) {
                    $room_seat = $current_room_seat->toJson();
                }

                $key = 'room_user_list_' . $current_room->id;
                $user_ids = $hot_cache->zrange($key, 0, -1);

                if (count($user_ids) > 0) {
                    $receiver_id = $user_ids[0];
                    $receiver_fd = intval($hot_cache->get("socket_user_online_user_id" . $receiver_id));

                    $data = ['action' => 'logout', 'user_id' => $user_id, 'room_seat' => $room_seat, 'channel_name' => $channel_name];

                    debug($user_id, $receiver_id, $data);

                    if ($receiver_fd) {
                        $server->push($receiver_fd, json_encode($data, JSON_UNESCAPED_UNICODE));
                    }
                }
            }
        }
    }

    function onRequest($request, $response)
    {

    }
}