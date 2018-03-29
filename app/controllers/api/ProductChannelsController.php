<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/3
 * Time: 下午3:15
 */

namespace api;


class ProductChannelsController extends BaseController
{
    function aboutAction()
    {
        $product_channel = $this->currentProductChannel();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $product_channel->toAboutJson());
    }

    function detailAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $this->currentProductChannel()->toDetailJson());
    }

    function bootConfigAction()
    {
        $root = $this->getRoot();
        //声网登录密码
        
        if(isDevelopmentEnv()){
            $detail_json['menu_config'][] = ['show' => true, 'title' => '游戏', 'url' => '/m/games', 'icon' => $root . 'images/menu_game.png'];
        }else{
            $detail_json['menu_config'][] = ['show' => false, 'title' => '游戏', 'url' => '/m/games', 'icon' => $root . 'images/menu_game.png'];
        }
        $detail_json['menu_config'][] = ['show' => true, 'title' => '家族', 'url' => '/m/unions', 'icon' => $root . 'images/menu_union.png'];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $detail_json);
    }
}