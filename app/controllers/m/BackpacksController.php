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
        $room_id = $user->current_room_id;


        // 前三排行
        $boom_histories = \BoomHistories::historiesTopList($user->id, 3);
        $boom_histories = $boom_histories->toJson('boom', 'toSimpleJson')['boom'];

        // 没爆礼物不抽奖
        $expire = \Rooms::getBoomGiftExpireAt($room_id);

        if (isBlank($expire)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '未开始爆礼物', ['target' => $boom_histories]);
        }

        // 抽奖物品保存至爆礼物结束时间
        $expire = $expire - time();
        $expire = $expire > 180 ? 180 : ($expire < 0 ? 1 : $expire);

        // 爆出的礼物从缓存拿到
        $cache = \Backpacks::getHotWriteCache();
        $user_sign_key = \Backpacks::generateBoomUserSignKey($user->id, $room_id);
        //$user_sign_key = $this->generateUserSignKey($user->id, $room_id);
        $user_sign = $cache->get($user_sign_key);

        // 领取后缓存值为1
        if ($user_sign == 1) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已领取！', ['target' => $boom_histories]);
        }

        // 未领取不抽奖
        if ($cache->exists($user_sign_key) && $user_sign != 1) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已抽奖，请先领取！', ['target' => $boom_histories]);
        }


        $record_key = \Rooms::generateBoomRecordKey($room_id);
        $amount = $cache->zscore($record_key, $user->id);

        $rate = mt_rand(1, 100);

//        switch ($rate) {
//            case $rate >= 1 && $rate <= 5:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 1;
//                    break;
//                }
//            case $rate > 5 && $rate <= 13:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 3;
//                    break;
//                }
//            case $rate > 13 && $rate <= 23:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 8;
//                    break;
//                }
//            case $rate > 23 && $rate <= 30:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 11;
//                    break;
//                }
//            case $rate > 30 && $rate <= 36:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 18;
//                    break;
//                }
//            case $rate > 36 && $rate <= 41:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 28;
//                    break;
//                }
//
//            case $rate > 41 && $rate <= 44:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 39;
//                    break;
//                }
//
//            case $rate > 44 && $rate <= 47:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 50;
//                    break;
//                }
//
//            case $rate > 47 && $rate <= 49:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 77;
//                    break;
//                }
//            case $rate > 49 && $rate <= 50:
//                {
//                    $type = BACKPACK_DIAMOND_TYPE;
//                    $num = 99;
//                    break;
//                }
//            case $rate > 50 && $rate <= 56:
//                {
//                    $type = BACKPACK_GOLD_TYPE;
//                    $num = 9;
//                    break;
//                }
//            case $rate > 56 && $rate <= 64:
//                {
//                    $type = BACKPACK_GOLD_TYPE;
//                    $num = 26;
//                    break;
//                }
//            case $rate > 64 && $rate <= 75:
//                {
//                    $type = BACKPACK_GOLD_TYPE;
//                    $num = 55;
//                    break;
//                }
//
//            case $rate > 75 && $rate <= 83:
//                {
//                    $type = BACKPACK_GOLD_TYPE;
//                    $num = 77;
//                    break;
//                }
//            case $rate > 83 && $rate <= 90:
//                {
//                    $type = BACKPACK_GOLD_TYPE;
//                    $num = 128;
//                    break;
//                }
//            case $rate > 90 && $rate <= 95:
//                {
//                    $type = BACKPACK_GOLD_TYPE;
//                    $num = 166;
//                    break;
//                }
//            case $rate > 95 && $rate <= 98:
//                {
//                    $type = BACKPACK_GOLD_TYPE;
//                    $num = 288;
//                    break;
//                }
//            case $rate > 98 && $rate <= 100:
//                {
//                    $type = BACKPACK_GOLD_TYPE;
//                    $num = 77;
//                    break;
//                }
//
//        }

        $type = BACKPACK_DIAMOND_TYPE;
        $num = mt_rand(1, 1000);
        $amount = $cache->get("room_boom_diamond_num_room_id_" . $room_id);

        info("boom_record", $amount, $room_id, $num, $this->currentUser()->id);

        $total_amount = mt_rand(40000, 50000);
        if ($amount > $total_amount) {
            $num = mt_rand(1, 10);
        }

        if($total_amount > 50000){
            $num = 1;
        }

        $cache->incrby("room_boom_diamond_num_room_id_" . $room_id, $num);

        // 1 随机类型
        //$type = array_rand(array_flip(self::$boom_type));

        // 2 爆礼品
//        if ($type == BACKPACK_GIFT_TYPE) {
//            $target = \Gifts::findFirstById(16);
//        } else {
//            $target = \Backpacks::getBoomDiamondOrGold($type);
//        }

        $target = \Backpacks::getBoomDiamondOrGold($type, $num);

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
    public
    function historyAction()
    {
        $boom_histories = \BoomHistories::historiesTopList();
        $boom_histories = $boom_histories->toJson('boom_histories', 'toSimpleJson');
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $boom_histories);
    }


    /**
     * @desc 爆礼物写入背包
     * @return bool
     */
    public
    function createAction()
    {
        $user = $this->currentUser();
        $room_id = $user->current_room_id;

        // 拿缓存
        $cache = \Backpacks::getHotWriteCache();
        $user_sign_key = \Backpacks::generateBoomUserSignKey($user->id, $room_id);
        //$user_sign_key = $this->generateUserSignKey($user->id, $room_id);
        $expire = $cache->ttl($user_sign_key);
        $user_sign = $cache->get($user_sign_key);

        // 超三分钟未领取礼物
        if (isBlank($user_sign))
            return $this->renderJSON(ERROR_CODE_FAIL, '爆礼物3分钟内未领取！');

        // 抽奖的奖品
        $json = json_decode($user_sign, true);
        $type = $json['type'];
        $prizes = $json['target'];

        if (isBlank($prizes)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '加入背包错误');
        }

        // 执行写入
        foreach ($prizes as $prize) {

            $res = \Backpacks::doCreate($user->id, $prize['id'], $prize['number'], $type);
            list($code, $reason, $prize_list) = $res;

            if ($code == ERROR_CODE_FAIL) {
                return $this->renderJSON($code, $reason);
            }
        }

        $cache->setex($user_sign_key, $expire, 1);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['backpack' => $prize_list]);
    }


    /**
     * 执行写入背包
     * @param $target_id
     * @param $number
     * @param $type
     * @return bool
     */
    protected
    function doCreate($target_id, $number, $type)
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
    protected
    function generateUserSignKey($user_id, $room_id)
    {
        return 'boom_target_room_' . $room_id . '_user_' . $user_id;
    }


    /**
     * 爆礼物获得钻石写入账户
     * @param $user_id
     * @param $number
     * @return bool
     */
    protected
    function boomGetDiamond($user_id, $number)
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
    protected
    function boomGetGold($user_id, $number)
    {
        $opts['remark'] = '爆礼物获得' . $number . '金币';
        \GoldHistories::changeBalance($user_id, GOLD_TYPE_IN_BOOM, $number, $opts);
        return true;
    }


    /**
     * @param $user_id
     * @return mixed
     */
    public
    function getCurrentRoomId($user_id)
    {
        // 获取当前房间ID
        $user_info = \Users::findFirstById($user_id);
        $user_info = $user_info->toJson();
        $room_id = $user_info['current_room_id'];
        return $room_id;
    }
}