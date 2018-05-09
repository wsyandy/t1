<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:38
 */
namespace m;

class BackpacksController extends BaseController
{
    public function indexAction()
    {
        $this->view->title = '爆礼物';
        if (false) {
            $this->response->redirect('/backpacks/desc');
        }
    }


    public function descAction()
    {
        $this->view->title = '爆礼物';
    }


    /**
     * @desc 礼物抽奖（暂定随机礼物，后优化）
     */
    public function prizeAction()
    {
        $target = \Gifts::randomGift();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['target' => $target]);
    }


    /**
     * @desc 领取历史记录
     * @return bool
     */
    public function historyAction()
    {
        $list = \BoomHistories::topList();
        $list = $list->toJson('boom', 'toSimpleJson');
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $list);
    }


    /**
     * @desc 礼物写入背包
     */
    public function createAction()
    {
        $target = $this->params('target');

        if (!is_array($target)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '加入背包失败');
        }
        foreach ($target as $value) {
            $this->prepare($value['id'], $value['name']);
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS);
    }


    protected function prepare($target_id, $number)
    {
        $type = BACKPACK_GIFT_TYPE;

        // target id
        if (empty($target_id))
            $this->renderJSON(ERROR_CODE_FAIL, 'not target');

        // 加入背包的数据
        $joining = array(
            'target_id' => $target_id,
            'type' => $type,
            'number' => $number
        );

        if (!\Backpacks::createTarget($this->currentUser(), $target_id, $number, $type)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '加入背包失败');
        }

        // 记录爆礼物日志
        (new \BoomHistories())->createBoom($this->currentUser(), $joining);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['backpack' => $joining]);

    }



}