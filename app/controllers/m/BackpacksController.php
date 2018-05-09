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

    }


    /**
     * @desc 礼物抽奖（暂定随机礼物，后优化）
     */
    public function prizeAction()
    {
        $gift_id =  \Gifts::randomGift();
        return $gift_id;
    }


    /**
     * @desc 礼物写入背包
     */
    public function createAction()
    {
        $target_id = $this->params('target_id', \Gifts::randomGift());
        $type = $this->params('type', BACKPACK_GIFT_TYPE);
        $number = mt_rand(1, 5);

        // target id
        if (empty($target_id))
            $this->renderJSON(ERROR_CODE_FAIL, 'not target');

        // 加入背包的数据
        $joining = array(
            'target_id' => $target_id,
            'number' => $number
        );

        if (! \Backpacks::createTarget($this->currentUser(), $target_id, $number, $type)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '加入背包失败');
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['backpack'=>$joining]);
    }


}