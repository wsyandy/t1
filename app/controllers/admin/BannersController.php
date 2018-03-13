<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 下午3:03
 */
namespace admin;
class BannersController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('banner');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $banners = \Banners::findPagination($conds, $page);
        $this->view->banners = $banners;

        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channels = $product_channels;
    }

    function newAction()
    {
        $banner = new \Banners();
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channels = $product_channels;
        $this->view->banner = $banner;
    }

    function createAction()
    {
        $banner = new \Banners();
        $this->assign($banner, 'banner');
        $banner->operator_id = $this->currentOperator()->id;
        $banner->material_ids = trim(preg_replace('/，/', ',', $banner->material_ids), ',');

        list($error_code, $error_reason) = $banner->checkFields();
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }
        $banner->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $banner);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['banner' => $banner->to_json]);
    }

    function editAction()
    {
        $id = $this->params('id');
        $banner = \Banners::findFirstById($id);
        $product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->banner = $banner;
        $this->view->product_channels = $product_channels;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $banner = \Banners::findFirstById($id);
        $this->assign($banner, 'banner');
        $banner->operator_id = $this->currentOperator()->id;
        $banner->material_ids = trim(preg_replace('/，/', ',', $banner->material_ids), ',');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $banner);
        list($error_code, $error_reason) = $banner->checkFields();
        if ($error_code != ERROR_CODE_SUCCESS) {
            return $this->renderJSON(ERROR_CODE_FAIL, $error_reason);
        }
        $banner->save();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['banner' => $banner->to_json]);
    }

    function platformsAction()
    {
        $banner = \Banners::findFirstById($this->params('id'));
        $platforms = \Banners::$PLATFORMS;
        $all_select_platforms = explode(',', $banner->platforms);
        $this->view->banner = $banner;
        $this->view->platforms = $platforms;
        $this->view->all_select_platforms = $all_select_platforms;
    }

    function updatePlatformsAction()
    {
        $banner = \Banners::findFirstById($this->params('id'));
        $platforms = $this->params('platforms', ['*']);
        if (in_array('*', $platforms)) {
            $platforms = ['*'];
        }
        $banner->platforms = implode(',', $platforms);
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $banner);
        $banner->update();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['banner' => $banner->to_json]);
    }

    function productChannelsAction()
    {
        $product_channel_banner_ids = [];
        $id = $this->params('id');
        $banner = \Banners::findFirstById($id);

        $all_product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->all_product_channels = $all_product_channels;

        $product_channel_banners = \ProductChannelBanners::findByBannerId($id);
        foreach ($product_channel_banners as $product_channel_banner) {
            $product_channel_banner_ids[] = $product_channel_banner->product_channel_id;

        }
        $this->view->product_channel_banner_ids = $product_channel_banner_ids;
        $this->view->id = $id;
        $this->view->banner = $banner;
    }

    function updateProductChannelsAction()
    {
        $product_channel_ids = $this->params('product_channel_ids');
        $id = $this->params('id');
        if (!is_array($product_channel_ids)) {
            $product_channel_ids = [];
        }

        $banner = \Banners::findFirstById($id);

        if (!$banner) {
            return;
        }

        $product_channel_banners = \ProductChannelBanners::findByBannerId($id);
        foreach ($product_channel_banners as $product_channel_banner) {
            if (!in_array($product_channel_banner->product_channel_id, $product_channel_ids)) {
                debug('delete', $product_channel_banner->product_channel_id);
                \OperatingRecords::logBeforeDelete($this->currentOperator(), $product_channel_banner);
                $product_channel_banner->delete();
            }
        }

        if ($product_channel_ids) {
            foreach ($product_channel_ids as $product_channel_id) {
                $product_channel_banner = \ProductChannelBanners::findFirst(
                    ['conditions' => 'product_channel_id=:product_channel_id: and banner_id=:banner_id:',
                        'bind' => ['product_channel_id' => $product_channel_id, 'banner_id' => $id]
                    ]);
                if ($product_channel_banner) {
                    debug('continue', $product_channel_id);
                    continue;
                }

                $product_channel_banner = new \ProductChannelBanners();
                $product_channel_banner->product_channel_id = $product_channel_id;
                $product_channel_banner->banner_id = $id;
                $product_channel_banner->save();
                debug('create', $product_channel_id);
                \OperatingRecords::logAfterCreate($this->currentOperator(), $product_channel_banner);
            }
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['banner' => $banner->to_json]);
    }
}