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
        $sid = $this->params('sid');
        $code = $this->params('code');
        $start = $this->params('start', false);

        // 用户信息
        $user = $this->currentUser();
        if (isDevelopmentEnv()) {
            $user = (object)['id' => 1];
        }

        // cache
        $cache = \Backpacks::getHotWriteCache();
        $cache_name = $this->getCacheName($user->id);
        if ($cache->exists($cache_name)) {
            $start = false;
        }

        $this->view->title = '爆礼物';
        $this->view->start = $start;
        $this->view->sid = $sid;
        $this->view->code = $code;
    }


    /**
     * @desc 礼物抽奖（暂定随机礼物，后优化）
     */
    public function prizeAction()
    {
        // 用户信息
        $user = $this->currentUser();
        if (isDevelopmentEnv()) {
            $user = (object)['id' => 1];
        }

        // cache
        $cache = \Backpacks::getHotWriteCache();
        $cache_name = $this->getCacheName($user->id);

        // 用户抽奖状态
        if ($cache->exists($cache_name)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已抽奖，请先领取！');
        }

        // 随机爆类型
        $list = [BACKPACK_GIFT_TYPE, BACKPACK_DIAMOND_TYPE, BACKPACK_GOLD_TYPE];
        $type = array_rand($list);
        $type = $list[$type];

        // 爆礼品
        if ($type == BACKPACK_GIFT_TYPE) {

            $target = \Gifts::randomGift();
        } else
            $target = \Backpacks::boomValue($type);

        // 倒计时3分钟领取
        $cache->set($this->getCacheName($user->id), json_encode($target), 180);

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
        $type = $this->params('type', BACKPACK_GIFT_TYPE);
        $user = $this->currentUser();
        if (isDevelopmentEnv()) {
            $user = (object)['id' => 1];
        }

        // 缓存3分钟
        $cache = \Backpacks::getHotWriteCache();
        $cache_name = $this->getCacheName($user->id);
        $target = $cache->get($cache_name);

        if (empty($target)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '三分钟超时！');
        }

        // 写礼物
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



    protected function getCacheName($user_id)
    {
        return 'boom_target_user:'.$user_id;
    }


}