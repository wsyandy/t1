<?php

namespace admin;


class ProductChannelsController extends BaseController
{

    function indexAction()
    {
        $page = $this->params('page');

        $cond = $this->getConditions('product_channel');
        $cond['order'] = 'id desc';
        $product_channels = \ProductChannels::findPagination($cond, $page);
        $this->view->product_channels = $product_channels;

        $this->view->all_product_channels = \ProductChannels::find(['order' => 'id asc', 'columns' => 'id,name']);
    }

    function newAction()
    {
        $product_channel = new \ProductChannels();
        $this->view->product_channel = $product_channel;
    }

    function createAction()
    {
        $product_channel = new \ProductChannels();
        $this->assign($product_channel, 'product_channel');
        $old_product_channel = \ProductChannels::findFirstByCodeHotCache($product_channel->code);
        if ($old_product_channel) {
            return $this->renderJSON(ERROR_CODE_FAIL, 'code重复');
        }

        if ($product_channel->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $product_channel);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('product_channel' => $product_channel->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    function editAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);

        $this->view->product_channel = $product_channel;
    }

    function updateAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $this->assign($product_channel, 'product_channel');

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $product_channel);
        if ($product_channel->save()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('product_channel' => $product_channel->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }
    }

    //h5配置
    function touchConfigAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $this->view->product_channel = $product_channel;
        $this->view->touch_themes = \ProductChannels::getTouchThemes();
    }

    //更新H5配置
    function updateTouchConfigAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $old_touch_domain = $product_channel->touch_domain;
        $this->assign($product_channel, 'product_channel');

        $touch_domain = $product_channel->touch_domain;
        $old_product_channel = \ProductChannels::findFirstByTouchDomain($touch_domain);
        if ($touch_domain && $old_touch_domain != $touch_domain && $old_product_channel) {
            $this->renderJSON(ERROR_CODE_FAIL, '域名已被' . $old_product_channel->name . '使用');
            return;
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $product_channel);
        if ($product_channel->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('product_channel' => $product_channel->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL);
        }
    }

    //web配置
    function webConfigAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $this->view->product_channel = $product_channel;
        $this->view->web_themes = \ProductChannels::getWebThemes();
    }

    //更新web配置
    function updateWebConfigAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $old_web_domain = $product_channel->web_domain;
        $this->assign($product_channel, 'product_channel');

        $web_domain = $product_channel->web_domain;
        $old_product_channel = \ProductChannels::findFirstByWebDomain($web_domain);
        if ($web_domain && $old_web_domain != $web_domain && $old_product_channel) {
            $this->renderJSON(ERROR_CODE_FAIL, '域名已被' . $old_product_channel->name . '使用');
            return;
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $product_channel);
        if ($product_channel->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('product_channel' => $product_channel->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL);
        }
    }

    //微信配置
    function weixinConfigAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $this->view->product_channel = $product_channel;
        $this->view->weixin_themes = \ProductChannels::getWeixinThemes();
    }

    //更新微信配置
    function updateWeixinConfigAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $this->assign($product_channel, 'product_channel');

        $weixin_domain = $product_channel->weixin_domain;
        $old_product_channel = \ProductChannels::findFirstByWeixinDomain($weixin_domain);
        if ($weixin_domain && $old_product_channel && $product_channel->id != $old_product_channel->id) {
            $this->renderJSON(ERROR_CODE_FAIL, '域名已被' . $old_product_channel->name . '使用');
            return;
        }

        if ($product_channel->weixin_appid) {
            $old_product_channel = \ProductChannels::findFirstByWeixinAppid($product_channel->weixin_appid);
            if ($old_product_channel && $product_channel->id != $old_product_channel->id) {
                $this->renderJSON(ERROR_CODE_FAIL, '微信账户已被' . $old_product_channel->name . '使用');
                return;
            }
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $product_channel);
        if ($product_channel->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('product_channel' => $product_channel->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL);
        }
    }

    //微信菜单项
    function weixinMenuAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);

        $weixin_menu_templates = [];
        $all_templates = \WeixinMenuTemplates::findForeach();
        $weixin_menu_templates[0] = '请选择';
        foreach ($all_templates as $template) {
            $weixin_menu_templates[$template->id] = $template->name;
        }
        $this->view->product_channel = $product_channel;
        $this->view->weixin_menu_templates = $weixin_menu_templates;
    }

    //修改微信菜单项
    function updateWeixinMenuAction()
    {
        $product_channel_id = $this->params('id');
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        $old_weixin_menu_template_id = $product_channel->weixin_menu_template_id;

        $this->assign($product_channel, 'product_channel');

        $weixin_menu_template_id = $product_channel->weixin_menu_template_id;
        $weixin_menu_template = \WeixinMenuTemplates::findFirstById($weixin_menu_template_id);

        if (\WeixinMenus::createMenu($weixin_menu_template_id, $product_channel)) {
            $weixin_menu_template->addProductChannelId($product_channel_id);

            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $product_channel);
            $product_channel->save();

            // 删除老的
            if ($old_weixin_menu_template_id && $old_weixin_menu_template_id != $weixin_menu_template_id) {
                $old_weixin_menu_template = \WeixinMenuTemplates::findFirstById($old_weixin_menu_template_id);
                $old_weixin_menu_template->removeProductChannelId($product_channel_id);
            }

            $this->renderJSON(ERROR_CODE_SUCCESS, '创建菜单成功');
            return;
        }

        info('生成菜单失败', $product_channel_id, $weixin_menu_template_id);

        $this->renderJSON(ERROR_CODE_FAIL, '创建菜单失败,请稍后重试');
        return;
    }

    function paymentChannelAction()
    {
        $product_channel_id = $this->params('product_channel_id');
        $payment_channel_product_channels = \PaymentChannelProductChannels::findByProductChannelId($product_channel_id);

        $payment_channel_ids = [];
        foreach ($payment_channel_product_channels as $payment_channel_product_channel) {
            debug($payment_channel_product_channel->payment_channel_id);
            $payment_channel_ids[] = $payment_channel_product_channel->payment_channel_id;
        }

        debug($payment_channel_ids);
        $payment_channels = \PaymentChannels::findByIds($payment_channel_ids);
        debug($payment_channels);
        $this->view->payment_channels = $payment_channels;
    }

    function pushAction()
    {
        $product_channel = \ProductChannels::findFirstById($this->params('id'));
        $this->view->product_channel = $product_channel;
    }


    function copyAction()
    {
        if ($this->request->isPost()) {
            $dest_product_channel_id = $this->params('dest_product_channel_id');
            $src_product_channel_id = $this->params('src_product_channel_id');
            $product_channel = \ProductChannels::findFirstById($src_product_channel_id);
            if ($product_channel) {
                $hot_cache = \ProductChannels::getHotWriteCache();
                $key = 'product_channel_copy_to_' . $dest_product_channel_id;
                if ($hot_cache->get($key)) {
                    info("正在复制", $key);
                    return false;
                }
                $hot_cache->setex($key, 60 * 10, $product_channel->id);

                \ProductChannels::delay()->asyncCopyTo($product_channel->id, $dest_product_channel_id);
            }
            return false;
        } else {
            $this->view->src_product_channel_id = $this->params('id');
            $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        }
    }

    function generateWeixinQrcodeAction()
    {
        $product_channel_id = $this->params('id');
        $this->view->product_channel_id = $product_channel_id;
    }

    function getLimitQrcodeUrlAction()
    {
        $product_channel_id = $this->params('product_channel_id');
        $fr = trim($this->params('fr', 'app'));
        $product_channel = \ProductChannels::findFirstById($product_channel_id);
        if ($product_channel) {
            $img_url = $product_channel->getLimitQrcodeUrl($fr);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['img_url' => $img_url]);
        }
        return $this->renderJSON(ERROR_CODE_FAIL, '产品渠道不存在');
    }

    function getuiGlobalPushAction()
    {
         $product_channel = \ProductChannels::findById($this->params('id'));
         if ($this->request->isPost()) {
             $result = \GeTuiMessages::testGlobalPush(
                 $product_channel,
                 $this->params('platform'),
                 $this->params('title'),
                 $this->params('body')
             );
             if ($result) {
                 return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');
             } else {
                 return $this->renderJSON(ERROR_CODE_FAIL, '发送失败');
             }
         }
         $this->view->product_channel = $product_channel;
    }

    function agoraAction()
    {
        $product_channel = \ProductChannels::findFirstById($this->params('id'));
        $this->view->product_channel = $product_channel;
    }
}