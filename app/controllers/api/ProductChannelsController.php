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
        $side_menu = $this->params('side_menu');
        $product_menu = $this->params('product_menu');

        $root = $this->getRoot();
        //声网登录密码
        $product_channel_id = $this->currentProductChannelId();
        if (isDevelopmentEnv()) {
            $detail_json['menu_config'][] = ['show' => true, 'title' => '推荐', 'url' => '/m/users/recommend', 'icon' => $root . 'images/menu_recommend.png'];
            $detail_json['menu_config'][] = ['show' => true, 'title' => '测试分享协议', 'url' => '/m/shares/test', 'icon' => $root . 'images/test.png'];
            $detail_json['menu_config'][] = ['show' => true, 'title' => '测一测', 'url' => '/m/users/voice', 'icon' => $root . 'images/test.png'];
            $detail_json['menu_config'][] = ['show' => true, 'title' => '测试推广系统', 'url' => '/m/distribute', 'icon' => $root . 'images/test.png'];
        } else {
            $detail_json['menu_config'][] = ['show' => false, 'title' => '游戏', 'url' => '/m/games', 'icon' => $root . 'images/menu_game.png'];
            if ($product_channel_id == 1) {
                $detail_json['menu_config'][] = ['show' => true, 'title' => '测一测', 'url' => '/m/users/voice', 'icon' => $root . 'images/test.png'];
            }
        }

        $detail_json['menu_config'][] = ['show' => true, 'title' => '活动', 'url' => '/m/activities', 'icon' => $root . 'images/menu_activity.png'];
        $detail_json['menu_config'][] = ['show' => true, 'title' => '家族', 'url' => '/m/unions', 'icon' => $root . 'images/menu_union.png'];

        $cond = [
            'conditions' => " status = :status: and product_channel_id = :product_channel_id:",
            'bind' => ['status' => STATUS_ON, 'product_channel_id' => $this->currentProductChannelId()],
            'order' => 'rank desc,id desc'
        ];

        if (isDevelopmentEnv() && !$side_menu) {
            $detail_json['menu_config'] = '';
        }

        if ($product_menu) {

            $product_menus = \ProductMenus::find($cond);

            $product_menus_json = [];

            foreach ($product_menus as $product_menu) {
                $product_menus_json[] = ['name' => $product_menu->name, 'type' => $product_menu->type];
            }

            $detail_json['product_menus'] = $product_menus_json;
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $detail_json);
    }
}