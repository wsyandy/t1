<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 上午11:37
 */

namespace services;

/*
 * 改动此文件代码需要shutdown服务重启后才生效
 */
class SwooleServices extends \BaseModel
{
    static $_only_cache = true;
    public $local_server_ip;
    public $local_server_port;
    public $side_server_ip;
    public $side_server_port;
    public $server;
    public $request_dispatcher; //请求拦截处理对象

    function __construct()
    {
        parent::__construct();
        $this->initService();
    }

    function initService()
    {
        $this->side_server_ip = self::config('websocket_side_server_ip'); //监听客户端
        $this->side_server_port = self::config('websocket_side_server_port'); //监听客户端
        $this->local_server_ip = self::config('websocket_local_server_ip'); //监听服务端
        $this->local_server_port = self::config('websocket_local_server_port'); //监听服务端

        $this->server = new \swoole_websocket_server($this->side_server_ip, $this->side_server_port);
        $this->server->addListener($this->local_server_ip, $this->local_server_port, SWOOLE_SOCK_TCP);
        $this->server->set(
            [
                'worker_num' => self::config('websocket_worker_num'), //cpu的1~4倍
                'max_request' => self::config('websocket_max_request'), //设置多少合适
                'dispatch_model' => 2,
                'daemonize' => true,
                'log_file' => APP_ROOT . 'log/websocket_server.log',
                'pid_file' => APP_ROOT . 'log/pids/websocket/server.pid',
                'reload_async' => true,
                'reactor_num' => self::config('websocket_reactor_num'),
                'heartbeat_check_interval' => 10, //10秒检测一次
                'task_worker_num' => self::config('websocket_task_worker_num'),
                'heartbeat_idle_time' => 30, //20秒未向服务器发送任何数据包,此链接强制关闭
                //'task_worker_num' => 8
            ]
        );

        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('open', [$this, 'onOpen']);
        $this->server->on('message', [$this, 'onMessage']);
        $this->server->on('close', [$this, 'onClose']);
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('workerStop', [$this, 'onWorkerStop']);
        $this->server->on('task', [$this, 'onTask']); // 在task_worker进程内被调用。worker进程可以使用swoole_server_task函数向task_worker进程投递新的任务。
        $this->server->on('finish', [$this, 'onFinish']); // 当worker进程投递的任务在task_worker中完成时，task进程会通过return将任务处理的结果发送给worker进程。
        $this->server->on('request', [$this, 'onRequest']); // http服务接收数据
    }

    function startService()
    {
        $this->server->start();
    }

    function onStart($server)
    {
        SwooleEvents::onStartEvent($this, $server);
    }

    function onWorkerStart($server, $worker_id)
    {
        SwooleEvents::onWorkerStartEvent($this, $server, $worker_id);
    }

    function onWorkerStop($server, $worker_id)
    {
        SwooleEvents::onWorkerStopEvent($this, $server, $worker_id);
    }

    function onOpen($server, $request)
    {
        SwooleEvents::onOpenEvent($this, $server, $request);
    }

    function onMessage($server, $frame)
    {
        SwooleEvents::onMessageEvent($this, $server, $frame);
    }

    function onClose($server, $fd, $from_id)
    {
        SwooleEvents::onCloseEvent($this, $server, $fd, $from_id);
    }

    function onTask($server, $task_id, $from_id, $data)
    {
        SwooleEvents::onTaskEvent($this, $server, $task_id, $from_id, $data);
    }

    function onFinish($server, $task_id, $data)
    {
        SwooleEvents::onFinishEvent($this, $server, $task_id, $data);
    }

    function onRequest($request, $response)
    {
        SwooleEvents::onRequestEvent($this, $request, $response);
    }
}