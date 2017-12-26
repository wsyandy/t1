<?php

namespace admin;

class SmsChannelsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $sms_channels = \SmsChannels::findPagination(array('order' => 'rank desc'), $page);
        $this->view->sms_channels = $sms_channels;
    }

    function newAction()
    {
        $sms_channel = new \SmsChannels();
        $this->view->sms_channel = $sms_channel;
        $this->view->gatewayNames = \smsgateway\Base::getGatewayNames();
    }

    function createAction()
    {
        $sms_channel = new \SmsChannels();
        $this->assign($sms_channel, 'sms_channel');
        $sms_channel->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $sms_channel);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('sms_channel' => $sms_channel->toJson()));
    }

    function editAction()
    {
        $sms_channel = \SmsChannels::findFirstById($this->params('id'));
        $this->view->sms_channel = $sms_channel;
        $this->view->gatewayNames = \smsgateway\Base::getGatewayNames();
    }

    function updateAction()
    {
        $sms_channel = \SmsChannels::findFirstById($this->params('id'));
        $this->assign($sms_channel, 'sms_channel');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $sms_channel);
        $sms_channel->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('sms_channel' => $sms_channel->toJson()));
    }

    function productChannelsAction()
    {
        $id = $this->params('id');
        $sms_channel = \SmsChannels::findFirstById($id);

        $product_channel_ids = $sms_channel->productChannelIdsHash();
        $all_product_channels = \ProductChannels::find(array('order' => ' id desc'));
        $this->view->all_product_channels = $all_product_channels;
        $this->view->product_channel_ids = $product_channel_ids;
        $this->view->sms_channel = $sms_channel;
    }

    function updateProductChannelsAction()
    {
        $id = $this->params('id');

        $product_channel_ids = $this->params('product_channel_ids');
        $sms_channel = \SmsChannels::findFirstById($id);
        $sms_channel->product_channel_ids = implode(',', array_filter($product_channel_ids));
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $sms_channel);

        $sms_channel->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('sms_channel' => $sms_channel->toJson()));
    }
}