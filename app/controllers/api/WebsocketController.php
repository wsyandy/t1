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
        $websocket_end_point = '116.62.103.161:9509';
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['end_point' => $websocket_end_point]);
    }
}