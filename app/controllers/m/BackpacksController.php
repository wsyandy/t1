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
        $arr = [BACKPACK_GIFT_TYPE, BACKPACK_DIAMOND_TYPE, BACKPACK_GOLD_TYPE];
        $one = array_rand($arr);
        if ($arr[$one] == BACKPACK_GIFT_TYPE) {
            $target = \Gifts::randomGift();
        } elseif ($arr[$one] == BACKPACK_DIAMOND_TYPE) {
            $target[] = [
                'id' => 0,
                'name' => '钻石',
                'image_url' => \Backpacks::getDiamondImage(),
                'number' => mt_rand(10, 1000)
            ];
        } elseif ($arr[$one] == BACKPACK_GOLD_TYPE) {
            $target[] = [
                'id' => 0,
                'name' => '金币',
                'image_url' => \Backpacks::getGoldImage(),
                'number' => mt_rand(500, 2000)
            ];
        }

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
            $this->prepare($value['id'], $value['number']);
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS);
    }


    /**
     * 执行礼物写入背包
     * @param $target_id
     * @param $number
     * @return bool
     */
    protected function prepare($target_id, $number, $type = BACKPACK_GIFT_TYPE)
    {

        // target id
        if (empty($target_id) && ($type != BACKPACK_DIAMOND_TYPE || $type != BACKPACK_GOLD_TYPE))
            $this->renderJSON(ERROR_CODE_FAIL, 'not target');
        else $target_id = 0;

        // 加入背包的数据
        $joining = array(
            'target_id' => $target_id,
            'type' => $type,
            'number' => $number
        );

        $user = $this->currentUser();

        if ($type == BACKPACK_DIAMOND_TYPE) {

            $opts['remark'] = '爆礼物抽中'.$number.'钻石';
            \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_IN_BOOM, $number, $opts);
        } elseif ($type == BACKPACK_GOLD_TYPE) {

            $opts['remark'] = '爆礼物抽中'.$number.'金币';
            \GoldHistories::changeBalance($user->id, GOLD_TYPE_IN_BOOM, $number, $opts);


        } else {
            if (!\Backpacks::createTarget($user, $target_id, $number, $type)) {
                return $this->renderJSON(ERROR_CODE_FAIL, '加入背包失败');
            }
        }


        // 记录爆礼物日志
        (new \BoomHistories())->createBoom($user, $joining);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['backpack' => $joining]);

    }



}