<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午5:18
 */

namespace m;

class ProductChannelsController extends BaseController
{
    function regAgreementAction()
    {
    }

    function aboutAction()
    {
        $version = $this->params("ver");
        if(!$version){
            $version = $this->currentUser()->version_name;
        }
        $this->view->product_channel = $this->currentProductChannel();
        $this->view->version = $version;
    }

    function serviceAction()
    {
        $this->view->product_channel = $this->currentProductChannel();
    }

    // 用户协议
    function userAgreementAction()
    {
        $this->view->product_channel = $this->currentProductChannel();
    }

    //隐私条款
    function privacyAgreementAction()
    {
        $this->view->product_channel = $this->currentProductChannel();
    }

    function strategiesAction()
    {

    }
}
