<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:43
 */

namespace api;

class BackpacksController extends BaseController
{
    /**
     * @desc 背包列表
     * @return bool
     */
    public function indexAction()
    {
        $type = $this->params('type', 1);
        $opt = ['type' => $type];

        $list = \Backpacks::findListByUserId($this->currentUser(), $opt);
        $list = $list->toJson('backpacks', 'toSimpleJson');

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $list);
    }


    /**
     * 背包送礼物
     * @return bool
     */
    public function sendGiftAction()
    {
        $gift_num = $this->params('gift_num', 1);
        $backpack_id = $this->params('id');
        $user_id = $this->params('user_id');
        $src = $this->params('src', 'room');
        $notify_type = $src == 'room' ? 'bc' : 'ptp';

        if (!$user_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $backpack = \Backpacks::findFirstById($backpack_id);

        if (!$backpack) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $res = $backpack->sendGift($this->currentUser(), $user_id, $gift_num, ['notify_type' => $notify_type]);
        list($error_code, $error_reason, $opts) = $res;

        return $this->renderJSON($error_code, $error_reason, $opts);
    }
}