<?php

namespace admin;

class PartnerUrlsController extends BaseController
{
    function indexAction()
    {

        $cond = $this->getConditions('partner_url');
        $cond['order'] = 'id desc';
        $page = $this->params('page');
        $partners = $this->currentOperator()->getPartners();

        $partner_urls = \PartnerUrls::findPagination($cond, $page);

        $this->view->partner_urls = $partner_urls;
        $this->view->url = '';
        $this->view->product_channel_id = '';
        $this->view->partner_url_id = '';
        $this->view->partner_id = '';
        $this->view->partners = $partners;

        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc']);

    }

    function newAction()
    {
        $partner_url = new \PartnerUrls();
        $this->view->partner_url = $partner_url;
    }

    function createAction()
    {
        $name = $this->params('partner_url[name]');
        $domain = $this->params('partner_url[domain]');
        if (!$name || !$domain) {
            return $this->renderJSON(ERROR_CODE_FAIL, '请填写完整信息');

        }
        $partner_url = new \PartnerUrls();
        $this->assign($partner_url, 'partner_url');
        $partner_url->operator_id = $this->currentOperator()->id;
        $partner_url->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $partner_url);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['partner_url' => $partner_url->toJson, 'error_url' => '/admin/partner_urls']);
    }

    function editAction()
    {
        $partner_url_id = $this->params('id');
        $partner_url = \PartnerUrls::findFirstById($partner_url_id);
        $partner_url->operator_id = $this->currentOperator()->id;

        $this->view->partner_url = $partner_url;
    }

    function updateAction()
    {
        $partner_url_id = $this->params('id');
        $partner_url = \PartnerUrls::findFirstById($partner_url_id);
        $partner_url->operator_id = $this->currentOperator()->id;
        $this->assign($partner_url, 'partner_url');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $partner_url);

        if ($partner_url->save()) {
            info($partner_url->toJson());
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('partner_url' => $partner_url->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function generateUrlAction()
    {
        $product_channel_id = $this->params('product_channel_id');
        $partner_url_id = $this->params('partner_url_id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $partner_id = $this->params('partner_id');
        $fr = '';
        if ($partner_id) {
            $partner = \Partners::findFirstById($partner_id);
            $fr = $partner->fr;
        }

        $partner_url = \PartnerUrls::findFirstById($partner_url_id);
        $code = $product_channel->code;
        $platform = $partner_url->platform;
        $type = $partner_url->type;

        $partner_param = \PartnerUrls::$PARTNER_PARMS[$platform . '_' . $type];
        if ($platform == 'android') {
            $partner_param = sprintf($partner_param, $code);
        } else {
            $partner_param = sprintf($partner_param, $code, $fr);
        }

        if ($partner_param && $partner_url->domain) {
            $url = 'http://' . $partner_url->domain . $partner_param;
            $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['url' => $url]);
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '生成失败');
        }
        return;

    }
}
