<?php

namespace admin;

class WeixinMenuTemplatesController extends BaseController
{

    public function indexAction()
    {
        // 创建多个菜单模板
        // weixin_menus类里加一个product_channel_ids
        // 应用到所有产品时, 才覆盖, 对应产品渠道的 菜单;
        $page = $this->params('page');
        $per_page = $this->params('per_page', 50);

        $weixin_menu_templates = \WeixinMenuTemplates::findPagination(['order' => 'id desc'], $page, $per_page);
        $this->view->weixin_menu_templates = $weixin_menu_templates;
    }

    public function newAction()
    {
        $weixin_menu_template = new \WeixinMenuTemplates();
        $this->view->weixin_menu_template = $weixin_menu_template;
    }

    public function createAction()
    {
        $weixin_menu_template = new \WeixinMenuTemplates();
        $this->assign($weixin_menu_template, 'weixin_menu_template');

        if ($weixin_menu_template->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $weixin_menu_template);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_menu_template' => $weixin_menu_template->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    public function editAction()
    {
        $weixin_menu_template_id = $this->params('id');
        $weixin_menu_template = \WeixinMenuTemplates::findFirstById($weixin_menu_template_id);
        $this->view->weixin_menu_template = $weixin_menu_template;
    }

    public function updateAction()
    {
        $weixin_menu_template_id = $this->params('id');
        $weixin_menu_template = \WeixinMenuTemplates::findFirstById($weixin_menu_template_id);
        $this->assign($weixin_menu_template, 'weixin_menu_template');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $weixin_menu_template);

        if ($weixin_menu_template->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_menu_template' => $weixin_menu_template->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    public function deleteAction()
    {
        $weixin_menu_template_id = $this->params('id');
        $weixin_menu_template = \WeixinMenuTemplates::findFirstById($weixin_menu_template_id);
        \OperatingRecords::logBeforeDelete($this->currentOperator(), $weixin_menu_template);

        if ($weixin_menu_template->delete()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', ['weixin_menu_template' => $weixin_menu_template->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '操作失败');
        }
    }

    public function productChannelListAction()
    {
        $weixin_menu_template_id = $this->params('weixin_menu_template_id');
        $weixin_menu_template = \WeixinMenuTemplates::findFirstById($weixin_menu_template_id);
        $product_channel_ids = $weixin_menu_template->product_channel_ids;

        $product_channel_ids = explode(',', $product_channel_ids);

        $product_channels = \ProductChannels::findByIds($product_channel_ids);

        $this->view->product_channels = $product_channels;
    }
}