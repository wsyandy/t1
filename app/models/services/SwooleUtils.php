<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 下午12:03
 */

namespace services;

class SwooleUtils extends \BaseModel
{
    static $_only_cache = true;
    private static $connection_list = 'websocket_connection_list'; // 本地ip的连接数

    static function remoteIp($request)
    {
        $server = $request->server;
        return fetch($server, 'remote_addr');
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
    
    // 本机ip
    static function getIntranetIp()
    {
        $ips = swoole_get_local_ip();
        info($ips);
        
        $ips = array_values($ips);
        if (count($ips) < 1) {
            info("intranet ip is null");
            return '';
        }

        $ip = $ips[0];
        return $ip;
    }

    static function increaseConnectNum($num, $ip)
    {
        if (!$ip) {
            return;
        }

        $hot_cache = SwooleUtils::getHotWriteCache();
        $total_num = $hot_cache->zincrby(SwooleUtils::$connection_list, $num, $ip);

        info($ip, 'total_num', $total_num, 'num', $num);
    }

    static function clearConnectionNum()
    {
        $hot_cache = SwooleUtils::getHotReadCache();
        return $hot_cache->del(SwooleUtils::$connection_list);
    }

    static function getConnectionNum()
    {
        $hot_cache = SwooleUtils::getHotReadCache();
        $local_ip = SwooleUtils::getIntranetIp();
        return $hot_cache->zscore(SwooleUtils::$connection_list, $local_ip);
    }

    static function getWebsocketEndPoint()
    {
        return SwooleUtils::config('websocket_client_endpoint');
    }

    static function getOnlineTokenByFd($fd)
    {
        $hot_cache = SwooleUtils::getHotWriteCache();
        $ip = SwooleUtils::getIntranetIp();
        $fd_token_key = "socket_push_online_token_" . $fd;
        $fd_ip_token_key = "socket_push_online_token_" . $fd.'_'.$ip;
        $token = $hot_cache->get($fd_ip_token_key);
        if($token){
            return $token;
        }

        return $hot_cache->get($fd_token_key);
    }

    static function getUserIdByOnlineToken($online_token)
    {
        $hot_cache = SwooleUtils::getHotWriteCache();
        $fd_user_id_key = "socket_fd_user_id" . $online_token;

        return $hot_cache->get($fd_user_id_key);
    }

    static function getIntranetIpdByOnlineToken($online_token)
    {
        $hot_cache = SwooleUtils::getHotWriteCache();
        $fd_intranet_ip_key = "socket_fd_intranet_ip_" . $online_token;

        return $hot_cache->get($fd_intranet_ip_key);
    }

    static function getServerPort($server, $fd)
    {
        $connect_info = $server->connection_info($fd);
        $server_port = fetch($connect_info, 'server_port');
        return $server_port;
    }


    //服务器内部通信
    static function send($action, $ip, $port, $payload = [])
    {
        if (!$ip || !$port) {
            info("Exce", $action, $ip, $port, $payload);
            return false;
        }

        info($port, $ip, $action, $payload);

        try {
            $client = new SwooleClient($ip, $port, 3);
            if (!$client->connect()) {
                info("Exce connect fail", $ip, $port, $payload);

                if (isProduction()) {
                    return false;
                }
            }
            $payload = ['action' => $action, 'payload' => $payload];
            $payload['sign'] = SwooleUtils::generateSign($payload);
            $data = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $client->send($data);
            $client->close();
            return true;
        } catch (\Exception $e) {
            info("Exce", $action, $ip, $payload, $e->getMessage());
        }

        return false;
    }

    static function generateSign($data)
    {
        $sign = '';

        if ($data) {

            ksort($data);

            $sign = md5(json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        return $sign;
    }

    static function pushMessage($push_data)
    {
        debug($push_data);
        return;
        $receiver_fd = fetch($push_data, 'fd');
        $body = fetch($push_data, 'body');

        info($receiver_fd, $push_data);

        if ($receiver_fd) {

            if (!$socket->exist($receiver_fd)) {
                info($receiver_fd, $push_data, "Exce fd not exist");
                return;
            }

            $socket->push($receiver_fd, json_encode($body, JSON_UNESCAPED_UNICODE));
        }
    }
}