<?php
namespace admin;

class GdtConfigsController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('gdt_confing');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $gdt_configs = \GdtConfigs::findPagination($conds, $page);
        $platform = $this->params('platform');
        $partners = $this->currentOperator()->getPartners();
        $muid = $this->params('muid');
        $this->view->muid = $muid;
        $this->view->partner_id = '';
        $this->view->partners = $partners;
        $this->view->gdt_configs = $gdt_configs;
        $this->view->product_channel_id = '';
        $this->view->product_channels = \ProductChannels::find(['order' => ' id desc']);
        $this->view->platforms = \GdtConfigs::$PLATFORM;
        $this->view->platform = $platform;
    }

    function newAction()
    {
        $gdt_config = new \GdtConfigs();
        $gdt_config->operator_id = $this->currentOperator()->id;
        $this->view->gdt_config = $gdt_config;
    }

    function createAction()
    {
        $gdt_config = new \GdtConfigs();
        $gdt_config->operator_id = $this->currentOperator()->id;
        $this->assign($gdt_config, 'gdt_config');
        $old_gdt_config = \GdtConfigs::findFirstByAdvertiserId($gdt_config->advertiser_id);
        if ($old_gdt_config) {
            $this->renderJSON(ERROR_CODE_FAIL, '已存在广告组' . $gdt_config->advertiser_id);
            return;
        }

        $gdt_config->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $gdt_config);
        $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['gdt_config' => $gdt_config->toJson()]);
    }

    function editAction()
    {
        $gdt_config_id = $this->params('id');
        $gdt_config = \GdtConfigs::findFirstById($gdt_config_id);
        $gdt_config->operator_id = $this->currentOperator()->id;

        $this->view->gdt_config = $gdt_config;
    }

    function updateAction()
    {
        $gdt_config_id = $this->params('id');
        $gdt_config = \GdtConfigs::findFirstById($gdt_config_id);
        $gdt_config->operator_id = $this->currentOperator()->id;
        $this->assign($gdt_config, 'gdt_config');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $gdt_config);
        $gdt_config->save();
        $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['gdt_config' => $gdt_config->toJson()]);
    }

    function testGdtAction()
    {
        $platform = $this->params('platform');

        $product_channel_id = $this->params('product_channel_id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $code = $product_channel->code;

        $partner_id = $this->params('partner_id');
        $partner = \Partners::findFirstById($partner_id);
        $fr = $partner->fr;

        $attributes = [];
        $attributes['code'] = $code;
        $attributes['platform'] = $platform;
        $attributes['fr'] = $fr;
        if ($platform == 'android') {
            $imei = $this->params('imei');
            $attributes['imei'] = $imei;
        } else {
            $idfa = $this->params('idfa');
            $attributes['idfa'] = $idfa;
        }

        $res = \Partners::testGdt($attributes);

        info($attributes, $res);

        $this->renderJSON(ERROR_CODE_SUCCESS, $res);
    }

}