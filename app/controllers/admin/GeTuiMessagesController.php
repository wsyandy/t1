<?php

namespace admin;

class GeTuiMessagesController extends BaseController
{
    public function indexAction()
    {
        $cond = $this->getConditions('ge_tui_message');
        $cond['order'] = 'id desc';
        $product_channel_id = intval($this->params('ge_tui_message[product_channel_id_eq]'));
        $status = $this->params('ge_tui_message[status_eq]');
        if ('' !== $status) {
            $status = intval($status);
        }
        $ge_tui_messages = \GeTuiMessages::find($cond);

        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channels = $product_channels;
        $this->view->ge_tui_messages = $ge_tui_messages;
        $this->view->product_channel_id = $product_channel_id;
        $this->view->status = $status;
    }

    public function newAction()
    {
        $ge_tui_message = new \GeTuiMessages();
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $ge_tui_message->send_at = time() + 300;
        $this->view->ge_tui_message = $ge_tui_message;
        $this->view->product_channels = $product_channels;
    }

    public function deleteAction()
    {
        $id = $this->params('id');
        $ge_tui_message = \GeTuiMessages::findFirstById($id);
        $ge_tui_message->operator_id = $this->currentOperator()->id;
        $ge_tui_message->send_status = SEND_STATUS_STOP;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $ge_tui_message);
        if ($ge_tui_message->save()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['redirect_url' => '/admin/ge_tui_messages']);
        }
    }

    public function editAction()
    {
        $id = $this->params('id');
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $ge_tui_message = \GeTuiMessages::findFirstById($id);
        $this->view->ge_tui_message = $ge_tui_message;
        $this->view->product_channels = $product_channels;
    }

    public function updateAction()
    {
        $id = $this->params('id');
        $ge_tui_message = \GeTuiMessages::findFirstById($id);
        $this->assign($ge_tui_message, 'ge_tui_message');
        $ge_tui_message->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $ge_tui_message);
        if ($ge_tui_message->save()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['ge_tui_message' => $ge_tui_message->toJson()]);
        }

    }

    public function createAction()
    {
        $ge_tui_message = new \GeTuiMessages();
        $this->assign($ge_tui_message, 'ge_tui_message');
        $ge_tui_message->operator_id = $this->currentOperator()->id;
        if ($ge_tui_message->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $ge_tui_message);
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['ge_tui_message' => $ge_tui_message->toJson()]);
        }
    }

    public function messageContentAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $conds['order'] = 'rank desc,id desc';

        $push_messages = \PushMessages::findPagination($conds, $page, $per_page);
        $ge_tui_message_id = $this->params('id');
        $ge_tui_message = \GeTuiMessages::findFirstById($ge_tui_message_id);

        foreach ($push_messages as $push_message) {
            if ($push_message->id == $ge_tui_message->push_message_id) {
                $push_message->selected = true;
            } else {
                $push_message->selected = false;
            }
        }

        $this->view->push_messages = $push_messages;
        $this->view->ge_tui_message_id = $ge_tui_message_id;
    }

    public function addMessageContentAction()
    {
        $push_message_id = $this->params('id');
        $ge_tui_message_id = $this->params('ge_tui_message_id');
        $ge_tui_message = \GeTuiMessages::findFirstById($ge_tui_message_id);

        $ge_tui_message->push_message_id = intval($push_message_id);
        $ge_tui_message->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $ge_tui_message);
        $ge_tui_message->save();

        $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    public function messageContentListAction()
    {
        $ge_tui_message_id = $this->params('id');
        $ge_tui_message = \GeTuiMessages::findFirstById($ge_tui_message_id);

        $push_messages = [];
        if ($ge_tui_message->push_message_id) {
            $push_messages = \PushMessages::findByIds([$ge_tui_message->push_message_id]);
        }

        $this->view->push_messages = $push_messages;
        $this->view->ge_tui_message_id = $ge_tui_message_id;
    }

    public function supportProvinceAction()
    {
        $id = $this->params('id');
        $provinces = \Provinces::find(['order' => 'id asc']);
        $ge_tui_message = \GeTuiMessages::findFirstById($id);
        $support_province_ids = explode(',', $ge_tui_message->province_ids);
        $this->view->provinces = $provinces;
        $this->view->support_province_ids = $support_province_ids;
        $this->view->ge_tui_message_id = $id;
    }

    public function updateSupportProvinceAction()
    {
        $id = $this->params('id');
        $ge_tui_message = \GeTuiMessages::findFirstById($id);
        $province_ids = $this->params('province_ids', []);

        $new_province_ids = implode(',', $province_ids);
        $old_province_ids = $ge_tui_message->province_ids;

        if ($new_province_ids != $old_province_ids) {
            debug('更新支持省份', $new_province_ids, $old_province_ids);
            $ge_tui_message->province_ids = $new_province_ids;
            $ge_tui_message->operator_id = $this->currentOperator()->id;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $ge_tui_message);
            if ($ge_tui_message->save()) {
                $this->renderJSON(ERROR_CODE_SUCCESS, '', ['ge_tui_message' => $ge_tui_message->toJson()]);
            }
        }
    }

    public function messageContentSendAction()
    {
        $ge_tui_message_id = $this->params('id');
        $ge_tui_message = \GeTuiMessages::findFirstById($ge_tui_message_id);
        if ($ge_tui_message->status != STATUS_ON) {
            return $this->renderJSON(ERROR_CODE_FAIL, '状态无效');
        }

        if (!$ge_tui_message->push_message) {
            return $this->renderJSON(ERROR_CODE_FAIL, '选择消息');
        }

        if (!$ge_tui_message->offline_day) {
            return $this->renderJSON(ERROR_CODE_FAIL, '离线天数不能为空');
        }

        if (count(explode('-', $ge_tui_message->offline_day)) != 2) {
            return $this->renderJSON(ERROR_CODE_FAIL, '离线天数非法');
        }


        $send_status = $ge_tui_message->send_status;
        if ($send_status == SEND_STATUS_SUBMIT) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已提交发送,请稍等');
        }

        if ($send_status == SEND_STATUS_PROGRESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, '正在发送中,请稍等');
        }

        if ($ge_tui_message->send_at < time() + 60) {
            return $this->renderJSON(ERROR_CODE_FAIL, '发送时间非法');
        }

        $ge_tui_message->operator_id = $this->currentOperator()->id;
        $ge_tui_message->send_status = SEND_STATUS_SUBMIT;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $ge_tui_message);
        $ge_tui_message->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功');
    }

}