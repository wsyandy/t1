<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 上午11:37
 */

namespace services;

class RequestDispatcher
{

    protected function requestError($str = "Request cannot be processed!")
    {
        return $str;
    }

    //  开始处理请求
    public function startAction(\services\SwooleServices $swoole_service, \services\BaseRequest $request)
    {
        return $this->logicHandler($swoole_service, $request);
    }

    // 请求处理
    public function logicHandler(\services\SwooleServices $swoole_service, \services\BaseRequest $request)
    {
        $action = $request->getAction();
        $fd = $request->getFd();
        $sid = $request->getSid();

        info("Action:", $action, 'fd', $fd, 'sid', $sid, 'data', $request->_json_arr);

        if (!$request->checkSign()) {
            return $this->requestError('sign error1');
        }

        if (!$action) {
            return $this->requestError();
        }

        if ($swoole_service->local_server_port == SwooleUtils::getServerPort($request->_socket, $fd)) {
            info("server_to_server", $request->_json_arr);

            $payload = fetch($request->_json_arr, 'payload');

            try {
                if ('push' == $action) {
                    $request->pushMessage($payload);
                }
            } catch (\Exception $e) {
                info("Exce", $action, $payload, $e->getMessage());
                return 'fail';
            }

            return 'success';
        }

        if (in_array($action, ['room_signal_status_report', 'room_channel_status_report'])) {
            $field = "current" . "_" . preg_replace('/_report/', "", $action);
            $user = \Users::findFirstById(intval($sid));
            $status = fetch($request->_json_arr, 'status');

            debug($field, $request->_json_arr, $status, $sid);

            if ($user && $status) {
                $user->updateRoomProfile([$field => $status]);
            }
        }

        // 测试心跳包
        if (isDevelopmentEnv() && $action == 'ping') {
            //解析数据
            $online_token = SwooleUtils::getOnlineTokenByFd($fd);
            $intranet_ip = SwooleUtils::getIntranetIpdByOnlineToken($online_token);
            debug('fd', $fd, $intranet_ip, 'token', $online_token);
            $payload = ['body' => $request->_json_arr, 'fd' => $fd];
            SwooleUtils::delay()->send('push', $intranet_ip, $swoole_service->local_server_port, $payload);
        }

        return 'success';
    }

}