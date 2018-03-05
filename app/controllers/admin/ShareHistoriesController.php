<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/27
 * Time: 下午3:34
 */
namespace admin;
class ShareHistoriesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $cond = $this->getConditions('share_history');

        $share_histories = \ShareHistories::findPagination($cond,$page,$per_page);
        $this->view->share_histories = $share_histories;
    }
}