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
            $device_id = $user->device_id;
            $device = \Devices::findFirstById($device_id);
            if ($device) {
                $version = $device->version_name;
            }
        }

        if (!$version) {
            $version = $this->params("ver");
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
    function privacyAgreementAction()
    {
        $this->view->product_channel = $this->currentProductChannel();
    }
}
