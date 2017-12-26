<?php

namespace admin;

class WeixinTemplateMessagesController extends BaseController
{
    public function indexAction()
    {
        $cond = $this->getConditions('weixin_template_message');
        $cond['order'] = 'id desc';
        $product_channel_id = intval($this->params('weixin_template_message[product_channel_id_eq]'));
        $status = $this->params('weixin_template_message[status_eq]');
        if ('' !== $status) {
            $status = intval($status);
        }
        $weixin_template_messages = \WeixinTemplateMessages::find($cond);

        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channels = $product_channels;
        $this->view->weixin_template_messages = $weixin_template_messages;
        $this->view->product_channel_id = $product_channel_id;
        $this->view->status = $status;
    }

    public function newAction()
    {
        $weixin_template_message = new \WeixinTemplateMessages();
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->weixin_template_message = $weixin_template_message;
        $weixin_template_message->send_at = time() + 300;
        $this->view->product_channels = $product_channels;
    }

    public function deleteAction()
    {
        $id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($id);
        $weixin_template_message->operator_id = $this->currentOperator()->id;
        $weixin_template_message->send_status = SEND_STATUS_STOP;
        \OperatingRecords::logBeforeDelete($this->currentOperator(), $weixin_template_message);
        if ($weixin_template_message->save()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['redirect_url' => '/admin/weixin_template_messages']);
        }
    }

    public function editAction()
    {
        $id = $this->params('id');
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($id);
        $this->view->weixin_template_message = $weixin_template_message;
        $this->view->product_channels = $product_channels;
    }

    public function updateAction()
    {
        $id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($id);
        $this->assign($weixin_template_message, 'weixin_template_message');
        $weixin_template_message->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_template_message);
        if ($weixin_template_message->save()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['weixin_template_message' => $weixin_template_message->toJson()]);
        }

    }

    public function createAction()
    {
        $weixin_template_message = new \WeixinTemplateMessages();
        $this->assign($weixin_template_message, 'weixin_template_message');
        $weixin_template_message->operator_id = $this->currentOperator()->id;
        if ($weixin_template_message->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $weixin_template_message);
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['weixin_template_message' => $weixin_template_message->toJson()]);
        }
    }

    public function messageContentAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $conds['order'] = 'rank desc,id desc';

        $push_messages = \PushMessages::findPagination($conds, $page, $per_page);
        $weixin_template_message_id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($weixin_template_message_id);

        foreach ($push_messages as $push_message) {
            if ($push_message->id == $weixin_template_message->push_message_id) {
                $push_message->selected = true;
            } else {
                $push_message->selected = false;
            }
        }

        $this->view->push_messages = $push_messages;
        $this->view->weixin_template_message_id = $weixin_template_message_id;
    }

    public function addMessageContentAction()
    {
        $push_message_id = $this->params('id');
        $weixin_template_message_id = $this->params('weixin_template_message_id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($weixin_template_message_id);

        $weixin_template_message->push_message_id = intval($push_message_id);
        $weixin_template_message->operator_id = $this->currentOperator()->id;
        $weixin_template_message->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $weixin_template_message);

        $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    public function messageContentListAction()
    {
        $weixin_template_message_id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($weixin_template_message_id);

        $push_messages = [];
        if ($weixin_template_message->push_message_id) {
            $push_messages = \PushMessages::findByIds([$weixin_template_message->push_message_id]);
        }

        $this->view->push_messages = $push_messages;
        $this->view->weixin_template_message_id = $weixin_template_message_id;
    }

    public function supportProvinceAction()
    {
        $id = $this->params('id');
        $provinces = \Provinces::find(['order' => 'id asc']);
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($id);
        $support_province_ids = explode(',', $weixin_template_message->province_ids);
        $this->view->provinces = $provinces;
        $this->view->support_province_ids = $support_province_ids;
        $this->view->weixin_template_message_id = $id;
    }

    public function updateSupportProvinceAction()
    {
        $id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($id);
        $province_ids = $this->params('province_ids', []);

        $new_province_ids = implode(',', $province_ids);
        $old_province_ids = $weixin_template_message->province_ids;

        if ($new_province_ids != $old_province_ids) {
            debug('更新支持省份', $new_province_ids, $old_province_ids);
            $weixin_template_message->province_ids = $new_province_ids;
            $weixin_template_message->operator_id = $this->currentOperator()->id;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_template_message);
            if ($weixin_template_message->save()) {
                $this->renderJSON(ERROR_CODE_SUCCESS, '', ['weixin_template_message' => $weixin_template_message->toJson()]);
            }
        }
    }


    public function messageContentSend2Action()
    {
        $weixin_template_message_id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($weixin_template_message_id);
        if ($weixin_template_message->status != STATUS_ON) {
            return $this->renderJSON(ERROR_CODE_FAIL, '状态无效');
        }

        if (!$weixin_template_message->push_message) {
            return $this->renderJSON(ERROR_CODE_FAIL, '选择消息');
        }

        if (!$weixin_template_message->offline_day) {
            return $this->renderJSON(ERROR_CODE_FAIL, '离线天数不能为空');
        }

        if (count(explode('-', $weixin_template_message->offline_day)) != 2) {
            return $this->renderJSON(ERROR_CODE_FAIL, '离线天数非法');
        }

        $weixin_template_message_key = 'weixin_template_message_id' . $weixin_template_message_id;
        $hot_cache = \WeixinTemplateMessages::getHotWriteCache();
        $send_result = $hot_cache->get($weixin_template_message_key);
        if ($send_result) {
            return $this->renderJSON(ERROR_CODE_FAIL, '正在发送中,请稍等');
        }

        $hot_cache->setex($weixin_template_message_key, 60 * 60, time());

        $command = "php -q " . APP_ROOT . "cli.php weixin_kf_send weixin_kf_template " . $weixin_template_message_id . "  > " . APP_ROOT . "log/weixin_template_message_{$weixin_template_message_id}.log & ";
        exec($command);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功');
    }

    public function messageContentSendAction()
    {
        $weixin_template_message_id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($weixin_template_message_id);
        if ($weixin_template_message->status != STATUS_ON) {
            return $this->renderJSON(ERROR_CODE_FAIL, '状态无效');
        }

        if (!$weixin_template_message->push_message) {
            return $this->renderJSON(ERROR_CODE_FAIL, '选择消息');
        }

        if (!$weixin_template_message->offline_day) {
            return $this->renderJSON(ERROR_CODE_FAIL, '离线天数不能为空');
        }

        if (count(explode('-', $weixin_template_message->offline_day)) != 2) {
            return $this->renderJSON(ERROR_CODE_FAIL, '离线天数非法');
        }


        $send_status = $weixin_template_message->send_status;
        if ($send_status == SEND_STATUS_SUBMIT) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已提交发送,请稍等');
        }

        if ($send_status == SEND_STATUS_PROGRESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, '正在发送中,请稍等');
        }

        if ($weixin_template_message->send_at < time() + 60) {
            return $this->renderJSON(ERROR_CODE_FAIL, '发送时间非法,至少延后1分钟');
        }

        $weixin_template_message->operator_id = $this->currentOperator()->id;
        $weixin_template_message->send_status = SEND_STATUS_SUBMIT;
        \OperatingRecords::logAfterCreate($this->currentOperator(), $weixin_template_message);
        $weixin_template_message->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功');
    }

    function supportPlatformsAction()
    {
        $support_platforms = [];
        $id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($id);
        $platforms = \WeixinTemplateMessages::$PLATFORMS;

        if ($weixin_template_message->platforms) {
            $support_platforms = explode(',', $weixin_template_message->platforms);
        }

        $this->view->platforms = $platforms;
        $this->view->support_platforms = $support_platforms;
        $this->view->weixin_template_message_id = $id;
    }

    function updateSupportPlatformsAction()
    {
        $id = $this->params('id');
        $weixin_template_message = \WeixinTemplateMessages::findFirstById($id);

        $new_platforms = '';

        $platforms = $this->params('platforms', '');
        if ($platforms) {
            $new_platforms = implode(',', array_filter($platforms));
        }
        $weixin_template_message->platforms = $new_platforms;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_template_message);
        if ($weixin_template_message->save()) {
            $this->renderJSON(ERROR_CODE_SUCCESS, '', ['weixin_template_message' => $weixin_template_message->toJson()]);
        }
    }
}