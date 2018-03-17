<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午5:18
 */

namespace wx;

class ProductChannelsController extends BaseController
{
    function strategiesAction()
    {
        $this->view->title = '玩转Hi';
    }

    function serviceAction()
    {
        $this->view->product_channel = $this->currentProductChannel();
        $this->view->title = '客服中心';
    }
}
