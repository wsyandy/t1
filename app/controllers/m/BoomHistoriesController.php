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
            return $this->renderJSON(ERROR_CODE_FAIL, '您已不在当前房间' . $room_id, $boom_histories);
        }

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

        //用户贡献值 控制概率
        $record_key = \Rooms::generateBoomRecordKey($room_id);
        $amount = $cache->zscore($record_key, $user->id);
        $rate = mt_rand(1, 100);

        $type = BOOM_HISTORY_DIAMOND_TYPE;
        $number = mt_rand(1, 500);

        $amount = $cache->get("room_boom_diamond_num_room_id_" . $room_id);

        info("boom_record", $amount, $room_id, $number, $this->currentUser()->id);

        $total_amount = mt_rand(5000, 10000);

        if ($amount > $total_amount) {
            $type = BOOM_HISTORY_GOLD_TYPE;
            $number = mt_rand(10, 5000);
        } else {
            $cache->incrby("room_boom_diamond_num_room_id_" . $room_id, $number);
        }

        $res = \BoomHistories::createBoomHistory($user, ['target_id' => 0, 'type' => $type, 'number' => $number, 'room_id' => $room_id]);

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