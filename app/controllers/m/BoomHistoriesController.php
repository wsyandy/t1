<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:38
 */

namespace m;

class BoomHistoriesController extends BaseController
{

    static $boom_type = [BOOM_HISTORY_GIFT_TYPE, BOOM_HISTORY_DIAMOND_TYPE, BOOM_HISTORY_GOLD_TYPE];

    function indexAction()
    {
        $sid = $this->params('sid');
        $code = $this->params('code');

        $this->view->title = '爆礼物';
        $this->view->sid = $sid;
        $this->view->code = $code;
    }

    function prizeAction()
    {
        $user = $this->currentUser();
        $room_id = $user->current_room_id;
        // 前三排行
        $boom_histories = \BoomHistories::historiesTopList($user->id, 3);
        $boom_histories = $boom_histories->toJson('boom_histories', 'toSimpleJson');

        if (!$room_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误', $boom_histories);
        }

        $room = \Rooms::findFirstById($room_id);

        // 没爆礼物不抽奖
        $expire_at = \Rooms::getBoomGiftExpireAt($room_id);

        if (isBlank($expire_at)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '未开始爆礼物', $boom_histories);
        }

        // 抽奖物品保存至爆礼物结束时间
        $expire = $expire_at - time();
        $expire = $expire > 180 ? 180 : ($expire < 0 ? 1 : $expire);

        // 爆出的礼物从缓存拿到
        $cache = \Backpacks::getHotWriteCache();
        $user_sign_key = \Backpacks::generateBoomUserSignKey($user->id, $room_id);
        $user_sign = $cache->get($user_sign_key);

        // 领取后缓存值为1
        if ($user_sign == 1) {
            return $this->renderJSON(ERROR_CODE_FAIL, '已领取！', $boom_histories);
        }

        $target_id = 0;
        //用户贡献值 控制概率
        $record_key = \Rooms::generateBoomRecordKey($room_id);
        $amount = $cache->zscore($record_key, $user->id);
        $boom_user_id = $room->getBoomUserId();
        $type = BOOM_HISTORY_GIFT_TYPE;
        $number = 1;
        $boom_num = $room->getBoomNum();

        if ($boom_user_id == $user->id) {

            $gift_id = \BoomHistories::randomBoomUserGiftId();
            $target_id = $gift_id;

            info($user->id, $target_id, $boom_user_id);

        } elseif ($amount > 0) {

            $rank = $cache->zrrank($record_key, $user->id);

            if ($rank && $rank >= 0 && $rank < 3) {
                $gift_id = \BoomHistories::randomContributionUserGiftIdByRank($rank);

                if (!$gift_id) {
                    $data = \BoomHistories::randomBoomGiftIdByBoomNum($room, $boom_num);
                }

            } else {
                $data = \BoomHistories::randomBoomGiftIdByBoomNum($room, $boom_num);
            }

            if (!$data && !$gift_id) {
                $gift_id = 28;

                if (isDevelopmentEnv()) {
                    $gift_id = 54;
                }

                $target_id = $gift_id;
                $type = BOOM_HISTORY_GIFT_TYPE;
                $number = 1;
            } elseif ($gift_id) {
                $target_id = $gift_id;
                info($user->id, $rank, $target_id);
            } elseif ($data) {
                $type = fetch($data, 'type');
                $target_id = fetch($data, 'target_id');
                $number = fetch($data, 'number');
            } else {
                $gift_id = 28;

                if (isDevelopmentEnv()) {
                    $gift_id = 54;
                }

                $target_id = $gift_id;
                $type = BOOM_HISTORY_GIFT_TYPE;
                $number = 1;
            }

        } else {
            $data = \BoomHistories::randomBoomGiftIdByBoomNum($room, 60);

            if (!$data) {
                $gift_id = 28;

                if (isDevelopmentEnv()) {
                    $gift_id = 54;
                }
                
                $target_id = $gift_id;
                $type = BOOM_HISTORY_GIFT_TYPE;
                $number = 1;
            } else {
                $type = fetch($data, 'type');
                $target_id = fetch($data, 'target_id');
                $number = fetch($data, 'number');
            }
        }

        info("boom_record", "用户id:", $this->currentUser()->id, "贡献值:", $amount, "房间id:", $room_id, "个数", $number);

        $res = \BoomHistories::createBoomHistory($user, ['target_id' => $target_id, 'type' => $type, 'number' => $number, 'room_id' => $room_id]);

        list($code, $reason, $boom_history) = $res;

        if ($code == ERROR_CODE_FAIL) {
            return $this->renderJSON($code, $reason);
        }

        $cache->setex($user_sign_key, $expire, 1);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['boom_histories' => [$boom_history->toSimpleJson()]]);
    }

    /**
     * @desc 历史记录
     * @return bool
     */
    function historyAction()
    {
        $boom_histories = \BoomHistories::historiesTopList();
        $boom_histories = $boom_histories->toJson('boom_histories', 'toSimpleJson');
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $boom_histories);
    }
}