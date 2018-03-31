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
        $room_info_key = "game_room_" . $room_id . '_info';
        $hot_cache->zadd($room_key, time(), $this->currentUser()->id);
        $num = $hot_cache->zcard($room_key);
        $room_host_id = $this->currentUser()->id;
        $pay_type = '';
        $amount = 0;
        if ($num == 1) {
            $hot_cache->hset($room_info_key, 'room_host_id', $room_host_id);
        } else {
            $info = $hot_cache->hmget($room_info_key);
            $room_host_id = fetch($info, 'room_host_id');
            $pay_type = fetch($info, 'pay_type');
            $amount = fetch($info, 'amount');
        }

        $this->view->current_user = $this->currentUser();
        $this->view->room_host_id = $room_host_id;
        $this->view->pay_type = $pay_type;
        $this->view->amount = $amount;
    }

    // 提交入场费
    function feeAction()
    {

        $room_id = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;

        $room_info_key = "game_room_" . $room_id . '_info';
        $hot_cache = \Rooms::getHotWriteCache();
        $info = $hot_cache->hmget($room_info_key);
        $room_host_id = fetch($info, 'room_host_id');
        $pay_type = fetch($info, 'pay_type');
        $amount = fetch($info, 'amount');

        if ($room_host_id == $this->currentUser()->id) {
            // free diamond gold
            $pay_type = $this->params('pay_type', '');
            $amount = $this->params('amount', 0);
            if(!$pay_type || $pay_type == 'free' && $amount != 0 || $pay_type != 'free' && $amount == 0){
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }

            $hot_cache->hset($room_info_key, 'pay_type', $pay_type);
            $hot_cache->hset($room_info_key, 'amount', $amount);
        }

        $this->renderJSON(ERROR_CODE_SUCCESS, '');
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