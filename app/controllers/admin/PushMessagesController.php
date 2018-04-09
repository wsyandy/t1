<?php

namespace admin;

class PushMessagesController extends BaseController
{
    public function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $conds['order'] = 'offline_time desc, rank desc, id desc';
        $push_messages = \PushMessages::findPagination($conds, $page, $per_page);
        $this->view->push_messages = $push_messages;
    }

    function newAction()
    {
        $push_message = new \PushMessages();
        $push_message->rank = 100;
        $this->view->push_message = $push_message;
        $products = \Products::find(['order' => 'id desc']);
        $new_products = [0 => '请选择'];
        foreach ($products as $product) {
            $new_products[$product->id] = $product->name;
        }

        $this->view->products = $new_products;
    }

    function createAction()
    {
        $push_message = new \PushMessages();
        $this->assign($push_message, 'push_message');
        if ($push_message->url && $push_message->product_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, 'url和产品不能同时有值');
        }

        if ($push_message->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $push_message);

            $push_message->tracker_no = substr($push_message->id . 'd' . md5($push_message->id . '$' . time()), 0, 20);
            $push_message->update();
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['push_message' => $push_message->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    function editAction()
    {
        $push_message_id = $this->params('id');
        $push_message = \PushMessages::findFirstById($push_message_id);
        $this->view->push_message = $push_message;

        $products = \Products::find(['order' => 'id desc']);
        $new_products = [0 => '请选择'];
        foreach ($products as $product) {
            $new_products[$product->id] = $product->name;
        }

        $this->view->products = $new_products;
    }

    function updateAction()
    {
        $push_message_id = $this->params('id');
        $push_message = \PushMessages::findFirstById($push_message_id);
        $this->assign($push_message, 'push_message');

        if ($push_message->url && $push_message->product_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, 'url和产品不能同时有值');
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $push_message);

        if ($push_message->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['push_message' => $push_message->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    function deleteAction()
    {
        $id = $this->params('id');
        $push_message = \PushMessages::findFirstById($id);
        \OperatingRecords::logBeforeDelete($this->currentOperator(), $push_message);

        if ($push_message->delete()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功', ['push_message' => $push_message->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '删除失败');
        }
    }

    function testPushAction()
    {

        $id = $this->params('id');
        $push_message = \PushMessages::findFirstById($id);

        if ($this->params('form')) {
            $this->view->push_message = $push_message;
            return;
        }

        $opts = $this->params('push_message');
        $receiver = null;
        if (isset($opts['device_id']) && $opts['device_id']) {
            $receiver = \Devices::findFirstById($opts['device_id']);
        }
        if (isset($opts['user_id']) && $opts['user_id']) {
            $receiver = \Users::findFirstById($opts['user_id']);
        }

        if ($receiver) {
            if ($receiver->pushMessage($push_message)) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
            }
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '接收者不存在');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '条件非法或AppidError');
    }


    public function platformsAction()
    {

        $push_message = \PushMessages::findFirstById($this->params('id'));
        $platforms = \PushMessages::$PLATFORMS;
        $all_select_platforms = explode(',', $push_message->platforms);
        $this->view->push_message = $push_message;
        $this->view->platforms = $platforms;
        $this->view->all_select_platforms = $all_select_platforms;

    }

    public function updatePlatformsAction()
    {

        $push_message = \PushMessages::findFirstById($this->params('id'));
        $platforms = $this->params('platforms', '');
        if ($platforms) {
            $platforms = implode(',', array_filter($platforms));
        }
        debug("STRING: ", $platforms);
        $push_message->platforms = $platforms;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $push_message);
        if ($push_message->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['push_message' => $push_message->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '修改失败');
        }
    }


    public function productChannelIdsAction()
    {

        $id = $this->params('id');
        $push_message = \PushMessages::findFirstById($id);
        $product_channels = \ProductChannels::find(['order' => 'id desc']);

        $select_product_channel_ids = [];
        $product_channel_ids = $push_message->product_channel_ids;
        if ($product_channel_ids) {
            $select_product_channel_ids = explode(',', $product_channel_ids);
            $select_product_channel_ids = array_filter($select_product_channel_ids);
            debug($select_product_channel_ids);
        }

        $this->view->product_channels = $product_channels;
        $this->view->select_product_channel_ids = $select_product_channel_ids;
        $this->view->push_message = $push_message;

    }

    public function updateProductChannelIdsAction()
    {

        $id = $this->params('id');
        $product_channel_ids = $this->params('product_channel_ids');
        $push_message = \PushMessages::findFirstById($id);
        if (!$push_message) {
            $this->renderJSON(ERROR_CODE_FAIL, '产品存在');
            return;
        }

        if ($product_channel_ids) {
            $product_channel_ids = implode(',', array_filter($product_channel_ids));
            $push_message->product_channel_ids = ',' . $product_channel_ids . ',';
        } else {
            $push_message->product_channel_ids = '';
        }

        $push_message->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $push_message);
        $push_message->update();

        $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功', ['push_message' => $push_message->toJson()]);
    }


}