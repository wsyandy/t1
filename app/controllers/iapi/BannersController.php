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
        $type = intval($this->params('type', 1));

        $current_user = $this->currentUser();

        $all_banners_json = \Banners::searchBanners($current_user, ['hot' => $hot, 'new' => $new, 'type' => $type]);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $all_banners_json);
    }

    function clickAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}