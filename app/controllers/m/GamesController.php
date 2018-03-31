<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/12
 * Time: 下午3:20
 */

namespace m;

class GamesController extends BaseController
{
    function indexAction()
    {
        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $hot_cache = \Rooms::getHotWriteCache();
        $room_key = "game_room_" . $room_id;
        $hot_cache->zadd($room_key, time(), $this->currentUser()->id);
        $room_host_id = $hot_cache->zrange($room_key, 0, 0);
        $room_host_id = current($room_host_id);

        $this->view->current_user = $this->currentUser();
        $this->view->room_host_id = $room_host_id;
    }

    // 提交入场费
    function feeAction()
    {

    }

    function waitAction()
    {

    }

    function enterAction()
    {
        $body = [];
        $body['user_id'] = $this->currentUser()->id;
        $body['source'] = $this->currentProductChannel()->code;
        $body['nickname'] = $this->currentUser()->nickname;
        $body['avatar_url'] = $this->currentUser()->avatar_url;
        $body['sex'] = $this->currentUser()->sex;
        $body['room_id'] = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $body['nonce_str'] = randStr(20);

        $str = paramsToStr($body);

        $url = 'https://tyt.momoyuedu.cn/?' . $str;
        info($url);

        $this->renderJSON(ERROR_CODE_SUCCESS, '', ['url' => $url]);
    }


}