<?php

namespace admin;

class WeixinKefuMessagesController extends BaseController
{
    public function indexAction()
    {
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $cond = $this->getConditions('weixin_kefu_message');
        $cond['order'] = 'id desc';
        $product_channel_id = intval($this->params('weixin_kefu_message[product_channel_id_eq]'));
        $status = $this->params('weixin_kefu_message[status_eq]');
        if ('' !== $status) {
            $status = intval($status);
        }
        $weixin_kefu_messages = \WeixinKefuMessages::find($cond);

        $this->view->product_channels = $product_channels;
        $this->view->weixin_kefu_messages = $weixin_kefu_messages;
        $this->view->product_channel_id = $product_channel_id;
        $this->view->status = $status;
    }

    public function newAction()
    {
        $weixin_kefu_message = new \WeixinKefuMessages();
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $provinces = \Provinces::find(['order' => 'id asc']);
        $provinces_array = [];
        $provinces_array[0] = '请选择';
        foreach ($provinces as $province) {
            $provinces_array[$province->id] = $province->name;
        }
        $this->view->provinces = $provinces_array;
        $this->view->product_channels = $product_channels;
        $this->view->weixin_kefu_message = $weixin_kefu_message;
    }

    public function createAction()
    {
        $weixin_kefu_message = new \WeixinKefuMessages();
        $this->assign($weixin_kefu_message, 'weixin_kefu_message');
        $weixin_kefu_message->operator_id = $this->currentOperator()->id;
        if ($weixin_kefu_message->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $weixin_kefu_message);
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['weixin_kefu_message' => $weixin_kefu_message->toJson()]);
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    public function updateAction()
    {
        $id = $this->params('id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($id);
        $this->assign($weixin_kefu_message, 'weixin_kefu_message');
        $weixin_kefu_message->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_kefu_message);
        if ($weixin_kefu_message->save()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['weixin_kefu_message' => $weixin_kefu_message->toJson()]);
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    public function editAction()
    {
        $id = $this->params('id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($id);
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $provinces = \Provinces::find(['order' => 'id asc']);
        $provinces_array = [];
        $provinces_array[0] = '请选择';
        foreach ($provinces as $province) {
            $provinces_array[$province->id] = $province->name;
        }

        $this->view->provinces = $provinces_array;
        $this->view->product_channels = $product_channels;
        $this->view->weixin_kefu_message = $weixin_kefu_message;
    }

    // 终止任务
    public function deleteAction()
    {
        $id = $this->params('id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($id);
        $weixin_kefu_message->operator_id = $this->currentOperator()->id;
        $weixin_kefu_message->send_status = SEND_STATUS_STOP;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_kefu_message);
        if ($weixin_kefu_message->save()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['redirect_url' => '/admin/weixin_kefu_messages']);
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    public function messageContentAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $conds['order'] = 'rank desc,id desc';

        $push_messages = \PushMessages::findPagination($conds, $page, $per_page);

        $weixin_kefu_message_id = $this->params('id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        $push_message_ids = explode(',', $weixin_kefu_message->push_message_ids);
        foreach ($push_messages as $push_message) {
            if (in_array($push_message->id, $push_message_ids)) {
                $push_message->selected = true;
            } else {
                $push_message->selected = false;
            }
        }

        $this->view->push_messages = $push_messages;
        $this->view->weixin_kefu_message_id = $weixin_kefu_message_id;
    }

    public function addMessageContentAction()
    {
        $push_message_id = $this->params('id');
        $weixin_kefu_message_id = $this->params('weixin_kefu_message_id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        $push_message_ids = explode(',', $weixin_kefu_message->push_message_ids);

        if (count($push_message_ids) >= 8) {
            $this->renderJSON(ERROR_CODE_FAIL, '信息已超过8条');
            return;
        }

        if (!in_array($push_message_id, $push_message_ids)) {
            $push_message_ids[] = $push_message_id;
            $weixin_kefu_message->push_message_ids = trim(implode(',', $push_message_ids), ',');
            $weixin_kefu_message->operator_id = $this->currentOperator()->id;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_kefu_message);
            $weixin_kefu_message->save();
        }

        $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    public function messageContentListAction()
    {
        $weixin_kefu_message_id = $this->params('id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        $push_message_ids = explode(',', $weixin_kefu_message->push_message_ids);
        $push_messages = \PushMessages::findByIds($push_message_ids);
        $this->view->push_messages = $push_messages;
        $this->view->weixin_kefu_message_id = $weixin_kefu_message_id;
    }

    public function deleteMessageContentAction()
    {
        $push_message_id = $this->params('id');
        $weixin_kefu_message_id = $this->params('weixin_kefu_message_id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        $push_message_ids = explode(',', $weixin_kefu_message->push_message_ids);
        $key = array_search($push_message_id, $push_message_ids);
        debug($push_message_id, $push_message_ids);
        if (false !== $key) {
            unset($push_message_ids[$key]);
            $weixin_kefu_message->push_message_ids = trim(implode(',', $push_message_ids), ',');
            $weixin_kefu_message->operator_id = $this->currentOperator()->id;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_kefu_message);
            $weixin_kefu_message->save();
        }

        $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    public function supportProvinceAction()
    {
        $id = $this->params('id');
        $provinces = \Provinces::find(['order' => 'id asc']);
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($id);
        $support_province_ids = explode(',', $weixin_kefu_message->province_ids);
        $this->view->provinces = $provinces;
        $this->view->support_province_ids = $support_province_ids;
        $this->view->weixin_kefu_message_id = $id;
    }

    public function updateSupportProvinceAction()
    {
        $id = $this->params('id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($id);
        $province_ids = $this->params('province_ids', []);

        $new_province_ids = implode(',', $province_ids);
        $old_province_ids = $weixin_kefu_message->province_ids;

        if ($new_province_ids != $old_province_ids) {
            debug('更新支持省份', $new_province_ids, $old_province_ids);
            $weixin_kefu_message->province_ids = $new_province_ids;
            $weixin_kefu_message->operator_id = $this->currentOperator()->id;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_kefu_message);
            if ($weixin_kefu_message->save()) {
                $this->renderJSON(ERROR_CODE_SUCCESS, '', ['weixin_kefu_message' => $weixin_kefu_message->toJson()]);
            }
        }

    }

    public function messageContentSend2Action()
    {
        $weixin_kefu_message_id = $this->params('id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        if ($weixin_kefu_message->status != STATUS_ON) {
            return $this->renderJSON(ERROR_CODE_FAIL, '状态无效');
        }

        if (!$weixin_kefu_message->push_message_ids) {
            return $this->renderJSON(ERROR_CODE_FAIL, '选择消息');
        }

        $weixin_kefu_message_key = 'weixin_kefu_message_' . $weixin_kefu_message_id;
        $hot_cache = \WeixinKefuMessages::getHotWriteCache();
        $send_result = $hot_cache->get($weixin_kefu_message_key);
        if ($send_result) {
            return $this->renderJSON(ERROR_CODE_FAIL, '正在发送中,请稍等');
        }

        $hot_cache->setex($weixin_kefu_message_key, 60 * 60, time());

        $command = "php -q " . APP_ROOT . "cli.php weixin_kf_send weixin_kf_custom " . $weixin_kefu_message_id . "  > " . APP_ROOT . "log/weixin_kefu_message_{$weixin_kefu_message_id}.log & ";
        exec($command);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功');
    }

    public function messageContentSendAction()
    {
        $weixin_kefu_message_id = $this->params('id');
        $weixin_kefu_message = \WeixinKefuMessages::findFirstById($weixin_kefu_message_id);
        if ($weixin_kefu_message->status != STATUS_ON) {
            return $this->renderJSON(ERROR_CODE_FAIL, '状态无效');
        }

        if (!$weixin_kefu_message->push_message_ids) {
            return $this->renderJSON(ERROR_CODE_FAIL, '选择消息');
        }

        $send_status = $weixin_kefu_message->send_status;
        if ($send_status == SEND_STATUS_SUBMIT) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已提交发送,请稍等');
        }

        if ($send_status == SEND_STATUS_PROGRESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, '正在发送中,请稍等');
        }

        $weixin_kefu_message->operator_id = $this->currentOperator()->id;
        $weixin_kefu_message->send_status = SEND_STATUS_SUBMIT;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_kefu_message);
        $weixin_kefu_message->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功');
    }

}