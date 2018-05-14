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
        $start = $this->params('start', false);

        // 用户信息
        $user = $this->currentUser();

        // 获取当前房间ID
        $room_id = $this->getCurrentRoomId($user->id);

        // cache
        $cache = \Backpacks::getHotWriteCache();
        $cache_name = $this->getCacheName($user->id, $room_id);
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
     * @return bool
     */
    public function prizeAction()
    {
        $user = $this->currentUser();
        if (isDevelopmentEnv()) {
            $user = (object)array('id' => 1);
        }

        // 获取当前房间ID
        $room_id = $this->getCurrentRoomId($user->id);

        // cache
        $cache = \Backpacks::getHotWriteCache();
        $cache_room_name = \Backpacks::getBoomRoomCacheName($room_id);
        $cache_name = $this->getCacheName($user->id, $room_id);

        // 房间爆礼物进行中
        if (!$cache->exists($cache_room_name)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '房间爆礼物活动已结束！');
        }

        // 用户未抽奖
        if ($cache->exists($cache_name)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已抽奖，请先领取！');
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
        $cache->set($cache_name, json_encode($json), 180);

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
        $room_id = $this->getCurrentRoomId($user->id);

        // 拿缓存
        $cache = \Backpacks::getHotWriteCache();
        $cache_name = $this->getCacheName($user->id, $room_id);

        $json = $cache->get($cache_name);

        // 超三分钟未领取礼物
        if (empty($json))
            return $this->renderJSON(ERROR_CODE_FAIL, '三分钟内未领取！');

        // json 解析
        $json = json_decode($json, true);
        $type = $json['type'];
        $target = $json['target'];

        // 执行写入
        foreach ($target as $value) {
            $this->doCreate($value['id'], $value['number'], $type);
        }

        $cache->del($cache_name);
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

        // 钻石、金币 类型
        $arr = array(
            BACKPACK_DIAMOND_TYPE => 'boomGetDiamond',
            BACKPACK_GOLD_TYPE => 'boomGetGold'
        );
        $function = $arr[$type];

        // 记录日志
        (new \BoomHistories())->createBoomHistories($user->id, $target_id, $type, $number);

        // 处理爆礼物
        if ($type == BACKPACK_GIFT_TYPE && (!\Backpacks::createTarget($user->id, $target_id, $number, $type))) {

            return $this->renderJSON(ERROR_CODE_FAIL, '加入背包失败-2');

        } else
            $this->$function($user->id, $number);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['backpack' => $list]);
    }


    /**
     * 爆礼物缓存名称
     * @param $user_id
     * @param $room_id
     * @return string
     */
    protected function getCacheName($user_id, $room_id)
    {
        return 'boom_target_room:' . $room_id . '_user:' . $user_id;
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