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
        $product_menus = [
            ['name' => '推荐', 'type' => 'recommend'],
            ['name' => '娱乐', 'type' => 'amuse'],
            ['name' => '开黑', 'type' => 'gang_up'],
            ['name' => '娱乐', 'type' => 'amuse'],
            ['name' => '最新', 'type' => 'new'],
            ['name' => '关注', 'type' => 'follow'],
            ['name' => '附近', 'type' => 'nearby'],
        ];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['product_menus' => $product_menus]);
    }
}