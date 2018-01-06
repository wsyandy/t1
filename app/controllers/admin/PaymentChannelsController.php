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
    }

    function updateAction()
    {
       $payment_channel = \PaymentChannels::findById($this->params('id'));
       $this->assign($payment_channel, 'payment_channel');
        if ($payment_channel->create()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',
                array('payment_channel' => $payment_channel->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function productChannelsAction()
    {
        $payment_channel_id = $this->params('payment_channel_id');
        $product_channels = \ProductChannels::validList();
        $this->view->payment_channel_id = $payment_channel_id;
        $this->view->product_channels = $product_channels;
    }
}