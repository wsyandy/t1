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

    static function bindFd($user_id, $fd)
    {
        $user = Users::findFirstById($user_id);

        if (!$user) {
            info("user not exists", $user_id);
            return null;
        }

        $user_db = Users::getHotWriteCache();
        $key = "swoole_websocket_fd_user_id_" . $user->id;
        $user_db->set($key, $fd);
    }

    static function getFd($user_id)
    {
        $user = Users::findFirstById($user_id);

        if (!$user) {
            info("user not exists", $user_id);
            return null;
        }

        $user_db = Users::getHotWriteCache();
        $key = "swoole_websocket_fd_user_id_" . $user->id;
        $fd = $user_db->get($key);

        return $fd;
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
        if (!$server->exist($request->fd)) {
            info($request->fd, "Exce not exist");
            return;
        }
        $sid = self::params($request, 'sid');
        debug($request->fd, "connect", $sid);
        $server->push($request->fd, "hello " . $request->fd);
    }

    function onMessage($server, $frame)
    {
        debug($frame->fd, "send message", $frame);

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
    }

    function onRequest($request, $response)
    {

    }
}