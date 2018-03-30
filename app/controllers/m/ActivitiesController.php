<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/30
 * Time: 上午11:46
 */
namespace m;
class ActivitiesController extends BaseController
{
    function weekChartActivityAction()
    {
        $this->view->title = "周榜专属奖励";
    }
}