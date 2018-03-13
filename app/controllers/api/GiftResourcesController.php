<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 下午5:04
 */

namespace api;

class GiftResourcesController extends BaseController
{

    function indexAction()
    {
        $gift_resource = \GiftResources::findFirstBy(['status' => STATUS_ON], 'id desc');

        return $this->renderJSON(ERROR_CODE_SUCCESS, '',
            ['resource_file_url' => $gift_resource->resourceFileUrl(), 'resource_code' => $gift_resource->resource_code]);
    }
}