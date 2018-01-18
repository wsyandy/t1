<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/23
 * Time: 下午2:39
 */

class SwooleWebsocketSever extends BaseModel
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

            $val = fetch($get, $field);

            if ($val) {
                return $val;
            }

            return $default;
        }

        $post = $request->post;

        if ($post) {

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

        $swoole_server->on('start', function ($swoole_server) {
            info("start");
        });

//        $swoole_server->on('connect', function ($swoole_server, $fd) {
//
//            info($fd, "Exce not exist");
//
////            $swoole_server->push($fd, "hello " . $fd);
//        });

//        $swoole_server->on('receive', function ($swoole_server, $fd, $from_id, $data) {
////            $swoole_server->send($fd, 'Swoole: ' . $data, $from_id);
//            info($fd, $from_id, $data, "Exce not exist");
//        });

        $swoole_server->on('WorkerStart', function ($swoole_server, $worker_id) {
//            $swoole_server->send($fd, 'Swoole: ' . $data, $from_id);
            info($worker_id, "Exce not exist");
        });

        //web_socket使用
        $swoole_server->on('open', function ($swoole_server, $request) {

            if (!$swoole_server->exist($request->fd)) {
                info($request->fd, "Exce not exist");
                return;
            }

            $user_id = self::params($request, 'id');

            debug($request->fd, "connect", $user_id);

//            if ($user_id) {
//                self::bindFd($user_id, $request->fd);
//            }
            $swoole_server->push($request->fd, "hello " . $request->fd);
        });

        $swoole_server->on('message', function ($swoole_server, $request) {
            debug($request->fd, "send message", $request->data);

            if (!$swoole_server->exist($request->fd)) {
                info($request->fd, "Exce not exist");
                return;
            }

            //解析数据
            $swoole_server->push($request->fd, $request->data);
        });

        $swoole_server->on('close', function ($swoole_server, $fd) {
            debug($fd, "connect close");
            //已关闭不能继续操作
            //$swoole_server->push($fd, "you already closed");
        });

//        $swoole_server->on('request', function ($request, $response) use ($swoole_server) {
//            $act = $request->get['act'];
//            debug($act);
//            switch ($act) {
//                case 'reload':
//                    $swoole_server->reload();
//                    break;
//                case 'shutdown':
//                    $swoole_server->shutdown();
//                    break;
//                case 'exit':
//                    exit;
//                    break;
//            }
//
//            $response->header("X-Server", "Swoole");
//            $msg = 'hello swoole !';
//            $response->end($msg);
//        });

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
}