<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/20
 * Time: 下午4:56
 */

namespace api;

class WebsocketController extends BaseController
{
    function endPointAction()
    {
        $websocket_end_point = \services\SwooleUtils::getWebsocketEndPoint();

        $ping_interval = 15;
        if (isDevelopmentEnv()) {
            $ping_interval = 3;
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['end_point' => $websocket_end_point, 'ping_interval' => $ping_interval]);
    }
}