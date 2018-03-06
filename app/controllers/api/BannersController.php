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
        $hot = intval($this->params('hot', 0));
        $new = intval($this->params('new', 0));

        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 10);

        $current_user = $this->currentUser();

        $banners = \Banners::searchBanners($current_user, $page, $per_page, $hot, $new);

        if (count($banners)) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $banners->toJson('banners', 'toSimpleJson'));
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', '');
    }

    function clickAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}