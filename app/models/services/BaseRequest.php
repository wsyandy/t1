<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 上午11:37
 */

namespace services;

// 数据基本处理

class BaseRequest
{

    private $_frame;
    public $_socket;
    public $_fd;
    public $_json_arr;

    /*
object(Swoole\WebSocket\Frame)#60 (4) {
  ["fd"]=>
  int(1)
  ["finish"]=>
  bool(true)
  ["opcode"]=>
  int(1)
  ["data"]=>
  string(72) "{
    action:enter_room 进入房间
    user_id 进入房间的用户id
    nickname 进入房间的用户昵称
    sex 进入房间的用户性别
    avatar_url 进入房间的用户的头像
    avatar_small_url 进入房间的用户头像小图
    channel_name 房间频道
}"
}
    */

    public function __construct(\swoole_websocket_server $server, $frame)
    {
        $this->_socket = $server;
        $this->_frame = $frame;
        $this->_json_arr = json_decode($frame->data, true);
    }


    public function getAction()
    {
        if (isset($this->_json_arr['action'])) {
            return $this->_json_arr['action'];
        }

        return null;
    }

    // websocket连接资源描述符
    public function getFd()
    {
        //fd的值不可可能为0,返回0表示fd无效
        if ($this->_frame) {
            return $this->_frame->fd;
        }

        return 0;
    }

    // swoole服务
    public function getSocket()
    {
        if ($this->_socket) {
            return $this->_socket;
        }

        return null;
    }

    public function getUserId()
    {
        $user_id = 0;
        if (isset($this->_json_arr['user_id'])) {
            $user_id = $this->_json_arr['user_id'];
        }

        return $user_id;
    }

    // 当前用户的sid
    public function getSid()
    {
        $sid = '';

        if (isset($this->_json_arr['sid'])) {
            $sid = $this->_json_arr['sid'];
        }

        return $sid;
    }

    function checkSign()
    {
        $data = $this->_json_arr;

        $sign = fetch($data, 'sign');

        if (!$sign) {

            info("sign_error", $data);

            return false;
        }

        unset($data['sign']);

        if ($data) {

            ksort($data);

            $sign_data = json_encode($data, JSON_UNESCAPED_UNICODE);

            if ($sign != md5($sign_data)) {
                info("sign_error", $data, $sign_data, md5($sign_data), $sign, $this->getSid());

                return false;
            }

            return true;
        }
    }

    // 推送消息
    public function pushMessage($push_data)
    {
        $receiver_fd = fetch($push_data, 'fd');
        $body = fetch($push_data, 'body');

        info($receiver_fd, $push_data);

        if ($receiver_fd) {

            if (!$this->_socket->exist($receiver_fd)) {
                info($receiver_fd, $push_data, "Exce fd not exist");
                return;
            }

            $this->_socket->push($receiver_fd, json_encode($body, JSON_UNESCAPED_UNICODE));
        }
    }

}