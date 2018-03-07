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

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

        $current_user = $this->currentUser();

//        $banners = \Banners::searchBanners($current_user, $page, $per_page, $hot, $new);

        $hot_banners = \Banners::searchBanners($current_user, $page, $per_page, $hot, 0);
        $new_banners = \Banners::searchBanners($current_user, $page, $per_page, 0, $new);
        $hot_banners_json = [];
        $new_banners_json = [];
        if (count($hot_banners)) {
            $hot_banners_json = $hot_banners->toJson('hot_banners', 'toSimpleJson');
        }

        if (count($new_banners)) {
            $new_banners_json = $new_banners->toJson('new_banners', 'toSimpleJson');
        }

        $opts = array_merge($hot_banners_json, $new_banners_json);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $opts);

    }

    function clickAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}