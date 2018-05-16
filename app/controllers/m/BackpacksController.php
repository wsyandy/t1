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

    static $boom_type = [BACKPACK_GIFT_TYPE, BACKPACK_DIAMOND_TYPE, BACKPACK_GOLD_TYPE];


    /**
     * @desc 首页html
     */
    public function indexAction()
    {
        $sid = $this->params('sid');
        $code = $this->params('code');

        $this->view->title = '爆礼物';
        $this->view->sid = $sid;
        $this->view->code = $code;
    }


    /**
     * @desc 礼物抽奖（暂定随机礼物，后优化）
     * @return bool
     */
    public function prizeAction()
    {
        $user = $this->currentUser();
        if (isDevelopmentEnv()) {
            $user = (object)array('id' => 1);
        }

        $room_id = $user->current_room_id;

        // 前三排行
        $boom_histories = \BoomHistories::historiesTopList(3);
        $boom_histories = $boom_histories->toJson('boom', 'toSimpleJson')['boom'];

        // 没爆礼物不抽奖
        $expire = \Backpacks::getExpireAt($room_id);
        if (empty($expire)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '未开始爆礼物', ['target' => $boom_histories]);
        }

        // 抽奖物品保存至爆礼物结束时间
        $expire = $expire - time();
        $expire = $expire > 180 ? 180 : ($expire < 0 ? 1 : $expire);

        // 爆出的礼物从缓存拿到
        $cache = \Backpacks::getHotWriteCache();
        $user_sign_key = $this->generateUserSignKey($user->id, $room_id);
        $user_sign = $cache->get($user_sign_key);

        // 领取后缓存值为1
        if ($user_sign == 1) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已领取！', ['target' => $boom_histories]);
        }

        // 未领取不抽奖
        if ($cache->exists($user_sign_key) && $user_sign!=1) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已抽奖，请先领取！', ['target' => $boom_histories]);
        }

        // 1 随机类型
        $type = array_rand(array_flip(self::$boom_type));

        // 2 爆礼品
        if ($type == BACKPACK_GIFT_TYPE)
            $target = \Gifts::getNGift();
        else
            $target = \Backpacks::getBoomDiamondOrGold($type);

        // 缓存数据体
        $json = array(
            'type' => $type,
            'target' => $target
        );

        // 领取时间三分钟
        $cache->setex($user_sign_key, $expire, json_encode($json));

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['target' => $target]);
    }


    /**
     * @desc 历史记录
     * @return bool
     */
    public function historyAction()
    {
        $list = \BoomHistories::historiesTopList();
        $list = $list->toJson('boom', 'toSimpleJson');
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $list);
    }


    /**
     * @desc 爆礼物写入背包
     * @return bool
     */
    public function createAction()
    {
        $user = $this->currentUser();
        if (isDevelopmentEnv()) {
            $user = (object)array('id' => 1);
        }
        
        $room_id = $this->getCurrentRoomId($user->id);

        // 拿缓存
        $cache = \Backpacks::getHotWriteCache();
        $user_sign_key = $this->generateUserSignKey($user->id, $room_id);
        $expire = $cache->ttl($user_sign_key);
        $user_sign = $cache->get($user_sign_key);

        // 超三分钟未领取礼物
        if (empty($user_sign))
            return $this->renderJSON(ERROR_CODE_FAIL, '爆礼物3分钟内未领取！');

        // 抽奖的奖品
        $json = json_decode($user_sign, true);
        $type = $json['type'];
        $prizes = $json['target'];

        if (empty($prizes)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '加入背包错误');
        }

        // 执行写入
        foreach ($prizes as $prize) {
            $this->doCreate($prize['id'], $prize['number'], $type);
        }

        $cache->setex($user_sign_key, $expire, 1);
        return $this->renderJSON(ERROR_CODE_SUCCESS);
    }


    /**
     * 执行写入背包
     * @param $target_id
     * @param $number
     * @param $type
     * @return bool
     */
    protected function doCreate($target_id, $number, $type)
    {
        if ($type == BACKPACK_GIFT_TYPE && empty($target_id)) {

            return $this->renderJSON(ERROR_CODE_FAIL, '加入背包失败-1');

        } elseif ($type != BACKPACK_GIFT_TYPE)
            $target_id = 0;

        $user = $this->currentUser();

        // 爆出的数据
        $list = array(
            'target_id' => $target_id,
            'type' => $type,
            'number' => $number
        );

        // 记录日志
        (new \BoomHistories())->createBoomHistories($user->id, $target_id, $type, $number);

        // 爆礼物类型
        if ($type == BACKPACK_GIFT_TYPE && (!\Backpacks::createTarget($user->id, $target_id, $number, $type))) {

            return $this->renderJSON(ERROR_CODE_FAIL, '加入背包失败-2');

        }

        if ($type == BACKPACK_DIAMOND_TYPE) {
            $this->boomGetDiamond($user->id, $number);
        } elseif ($type == BACKPACK_GOLD_TYPE) {
            $this->boomGetGold($user->id, $number);
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['backpack' => $list]);
    }


    /**
     * 爆礼物缓存名称
     * @param $user_id
     * @param $room_id
     * @return string
     */
    protected function generateUserSignKey($user_id, $room_id)
    {
        return 'boom_target_room_' . $room_id . '_user_' . $user_id;
    }


    /**
     * 爆礼物获得钻石写入账户
     * @param $user_id
     * @param $number
     * @return bool
     */
    protected function boomGetDiamond($user_id, $number)
    {
        $opts['remark'] = '爆礼物获得' . $number . '钻石';
        \AccountHistories::changeBalance($user_id, ACCOUNT_TYPE_IN_BOOM, $number, $opts);
        return true;
    }


    /**
     * 爆礼物获得金币写入账户
     * @param $user_id
     * @param $number
     * @return bool
     */
    protected function boomGetGold($user_id, $number)
    {
        $opts['remark'] = '爆礼物获得' . $number . '金币';
        \GoldHistories::changeBalance($user_id, GOLD_TYPE_IN_BOOM, $number, $opts);
        return true;
    }


    /**
     * @param $user_id
     * @return mixed
     */
    public function getCurrentRoomId($user_id)
    {
        // 获取当前房间ID
        $user_info = \Users::findFirstById($user_id);
        $user_info = $user_info->toJson();
        $room_id = $user_info['current_room_id'];
        return $room_id;
    }
}