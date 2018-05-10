<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:43
 */
namespace api;

class BackpacksController extends BaseController
{
    /**
     * @desc 背包列表
     * @return bool
     */
    public function indexAction()
    {
        $type = $this->params('type', 1);
        $opt = [ 'type' => $type ];

        $list = \Backpacks::findListByUserId($this->currentUser(), $opt);
        $list = $list->toJson('backpacks', 'toSimpleJson');

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $list);
    }


    /**
     * @desc 返回爆礼物活动入口
     * @return bool
     */
    public function BoomAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['boom_url' => 'url://m/backpacks']);
    }
}