<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/19
 * Time: 下午8:57
 */


class WebsocketTask extends Phalcon\CLI\Task
{

    function startAction()
    {
        $log_dir = $this->config->application->log;
        checkDirExists("{$log_dir}/pids/websocket/");
        if (file_exists("{$log_dir}/pids/websocket/server.pid")) {
            $pid = file_get_contents("{$log_dir}/pids/websocket/server.pid");
            $pid = intval(trim($pid));

            if ($pid > 0) {
                echoLine("process already start pid:", $pid);
                return;
            }
        }

        $swoole_services = new \services\SwooleServices();
        $swoole_services->startService();
    }

    function stopAction()
    {
        //停止服务 清空链接数
        \services\SwooleUtils::clearConnectionNum();

        $log_dir = $this->config->application->log;
        checkDirExists("{$log_dir}/pids/websocket/");
        if (file_exists("{$log_dir}/pids/websocket/server.pid")) {
            $pid = file_get_contents("{$log_dir}/pids/websocket/server.pid");
            $pid = intval(trim($pid));
            if (!$pid || @pcntl_getpriority($pid) === false) {
                info('websocket process not exited!');
                file_put_contents("{$log_dir}/pids/websocket/server.pid", '');
                return true;
            }

            $result = posix_kill($pid, SIGTERM);
            if ($result) {
                file_put_contents("{$log_dir}/pids/websocket/server.pid", '');
                info('websocket process exited!');
                info("###stop websocket###");

                return true;
            } else {
                info('can not kill websocket process!');
                return false;
            }
        }

        return true;
    }

    function reloadAction()
    {

        $log_dir = $this->config->application->log;

        checkDirExists("{$log_dir}/pids/websocket/");

        if (file_exists("{$log_dir}/pids/websocket/server.pid")) {

            $pid = file_get_contents("{$log_dir}/pids/websocket/server.pid");
            $pid = intval(trim($pid));

            if (!$pid || @pcntl_getpriority($pid) === false) {
                info('websocket process not exited!');
                return true;
            }

            $result = posix_kill($pid, SIGUSR1);

            if ($result) {
                info('websocket process reloaded!');
                return true;
            } else {
                info('can not reload websocket process!');
                return false;
            }
        }

        return true;
    }
}