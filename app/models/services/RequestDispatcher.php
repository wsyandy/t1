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
        info("Action:", $request->getAction(), 'fd', $request->getFd(), 'sid', $request->getSid(), 'data', $request->getFrameData());

        if (!$request->checkSign()) {
            return $this->requestError('sign error1');
        }

        if (!$request->getAction()) {
            return $this->requestError();
        }

        if ($swoole_service->local_server_port == SwooleUtils::getServerPort($request->_socket, $request->getFd())) {
            info("server_to_server", $request->_json_arr);

            $action = fetch($request->_json_arr, 'action');
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

        // 测试心跳包
        if (isDevelopmentEnv() && $request->getAction() == 'ping') {
            //解析数据
            $online_token = SwooleUtils::getOnlineTokenByFd($request->getFd());
            $intranet_ip = SwooleUtils::getIntranetIpdByOnlineToken($online_token);
            debug('fd', $request->getFd(), $intranet_ip, 'token', $online_token);
            $payload = ['body' => $request->_json_arr, 'fd' => $request->getFd()];
            SwooleUtils::delay()->send('push', $intranet_ip, $swoole_service->local_server_port, $payload);
        }

        return 'success';
    }

}