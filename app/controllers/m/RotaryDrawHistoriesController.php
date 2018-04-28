<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/4/28
 * Time: 下午4:35
 */

namespace m;


class RotaryDrawHistoriesController extends BaseController
{
    function indexAction()
    {
        $data = \RotaryDrawHistories::getData();

    }

    // 转盘抽奖
    function rotaryAction()
    {
        if ($this->request->isAjax()) {

            $amount = 10;
            $remark = '抽奖消费' . $amount . '钻石';
            $opts['remark'] = $remark;
            $user = $this->currentUser();
            if ($user->diamond < $amount) {
                return $this->renderJSON(ERROR_CODE_FAIL, '钻石不足');
            }

            $target = \AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_BUY_GIFT, $amount, $opts);

            $random = mt_rand(1, 100);
            $type = 'gold';

            $res = \RotaryDrawHistories::createHistory($this->currentUser(), []);

            $key = 'lucky_draw_num_activity_id_' . $activity_id; //减去用户抽取次数
            $day_user_key = 'lucky_draw_activity_id_' . $activity_id . '_user' . date("Y-m-d"); //记录每天抽奖的人数
            $day_num_key = 'lucky_draw_activity_id_' . $activity_id . '_num' . date("Y-m-d"); //记录每天抽奖的次数

            $db = \Users::getUserDb();
            $lucky_draw_num = $db->zincrby($key, -1, $this->currentUser()->id);
            $db->zadd($day_user_key, time(), $this->currentUser()->id);
            $db->incrby($day_num_key, 1);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['lucky_draw_num' => $lucky_draw_num, 'type' => $type]);
        }

    }

}