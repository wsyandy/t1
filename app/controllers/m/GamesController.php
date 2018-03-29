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
        $body = [];
        $body['user_id'] = $this->currentUser()->id;
        $body['source'] = $this->currentProductChannel()->code;
        $body['nickname'] = $this->currentUser()->nickname;
        $body['avatar_url'] = $this->currentUser()->avatar_url;
        $body['sex'] = $this->currentUser()->sex;
        $body['room_id'] = $this->currentUser()->current_room_id > 0 ? $this->currentUser()->current_room_id : $this->currentUser()->room_id;
        $body['nonce_str'] = randStr(20);
        $body['return_url'] = $this->getRoot().'m/games?code='.$this->currentProductChannel()->code.'&sid='.$this->currentUser()->sid;


        $str = paramsToStr($body);

        $url = 'https://tyt.momoyuedu.cn/?' . $str;
        info($url);

        $this->view->url = $url;
    }

    function enterAction()
    {
        //$this->response->redirect($url);
    }


}