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
        $user = $this->currentUser();
        $version = '';
        if ($user) {
            $version = $user->version_code;
        }
        if (!$version && $this->currentDevice()) {
            $version = $this->currentDevice()->version_code;
        }

        $sid = $this->params('sid');
        $product_channel = $this->currentProductChannel();

        $this->view->product_channel = $product_channel;
        $this->view->sid = $sid;
        $this->view->version = $version;
//        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $product_channel->toAboutJson());
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
    function priAgreementAction()
    {
        $this->view->product_channel = $this->currentProductChannel();
    }
}
