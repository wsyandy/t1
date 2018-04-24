<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/21
 * Time: 下午2:23
 */

namespace api;

class ProductMenusController extends BaseController
{
    function indexAction()
    {
        $cond = [
            'conditions' => " status = :status: and product_channel_id = :product_channel_id:",
            'bind' => ['status' => STATUS_ON, 'product_channel_id' => $this->currentProductChannelId()],
            'order' => 'rank desc,id desc'
        ];

        $product_menus = \ProductMenus::find($cond);

        $product_menus_json = [];

        foreach ($product_menus as $product_menu) {
            $product_menus_json[] = ['name' => $product_menu->name, 'type' => $product_menu->type];
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['product_menus' => $product_menus_json]);
    }
}