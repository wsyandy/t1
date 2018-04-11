<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 下午4:31
 */

namespace iapi;
class BannersController extends BaseController
{
    function indexAction()
    {
        $hot = intval($this->params('hot', 1));
        $new = intval($this->params('new', 1));
        $type = intval($this->params('type', 0));

        $current_user = $this->currentUser();

        $banners = \Banners::searchBannersByInternational($current_user, ['type' => $type]);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $banners->toJson('banners', 'toSimpleJson'));
    }

    function clickAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}