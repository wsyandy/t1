<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 下午4:31
 */
namespace api;
class BannersController extends BaseController
{
    function indexAction()
    {
        $hot = intval($this->params('hot', 1));
        $new = intval($this->params('new', 1));
        debug($hot, $new);

        $current_user = $this->currentUser();

        $hot_banners = \Banners::searchBanners($current_user, $hot, 0);
        $latest_banners = \Banners::searchBanners($current_user, 0, $new);

        $opts = ['hot_banners' => $hot_banners, 'latest_banners' => $latest_banners];

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $opts);

    }

    function clickAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}