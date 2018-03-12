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
        $show_union = false;

        if (isProduction()) {
            $show_union = true;
        }

        //声网登录密码
        $detail_json['menu_config'] = ['show_union' => $show_union];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $detail_json);
    }
}