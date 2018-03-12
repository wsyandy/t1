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
        $show = true;

        if (isProduction()) {
            $show = false;
        }

        $root = $this->getRoot();
        //声网登录密码
        $detail_json['menu_config'][] = ['show' => $show, 'title' => '家族', 'url' => '/m/unions', 'icon' => $root . 'menu_union.png'];
        $detail_json['menu_config'][] = ['show' => $show, 'title' => '游戏', 'url' => '/m/games', 'icon' => $root . 'menu_game.png'];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $detail_json);
    }
}