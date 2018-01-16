<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 10:50
 */
namespace admin;

class PaymentChannelsController extends BaseController
{
    function indexAction()
    {
        $payment_channels = \PaymentChannels::find();
        $this->view->payment_channels = $payment_channels;
    }

    function newAction()
    {
        $payment_channel = new \PaymentChannels();
        $this->view->payment_channel = $payment_channel;
        $this->view->clazz_names = \PaymentChannels::getGatewayClasses();
    }

    function createAction()
    {
        $payment_channel = new \PaymentChannels();
        $this->assign($payment_channel, 'payment_channel');
        if ($payment_channel->create()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',
                array('payment_channel' => $payment_channel->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function editAction()
    {
        $payment_channel = \PaymentChannels::findById($this->params('id'));
        $this->view->payment_channel = $payment_channel;
        $this->view->clazz_names = \PaymentChannels::getGatewayClasses();
    }

    function updateAction()
    {
       $payment_channel = \PaymentChannels::findById($this->params('id'));
       $this->assign($payment_channel, 'payment_channel');
        if ($payment_channel->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',
                array('payment_channel' => $payment_channel->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function productChannelsAction()
    {
        $payment_channel_id = $this->params('payment_channel_id');
        if (isBlank($payment_channel_id)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
        if ($this->request->isPost()) {
            \PaymentChannelProductChannels::fresh($payment_channel_id, $this->params('product_channel_ids'));
            return $this->response->redirect('/admin/payment_channels');
        }
        $product_channels = \ProductChannels::validList();
        $payment_channel = \PaymentChannels::findById($payment_channel_id);
        $checked = array();
        foreach ($product_channels as $product_channel) {
            $checked[$product_channel->id] = $payment_channel->supportProductChannel($product_channel);
        }
        $this->view->payment_channel_id = $payment_channel_id;
        $this->view->product_channels = $product_channels;
        $this->view->checked = $checked;
    }
}