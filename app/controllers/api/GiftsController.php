<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 下午5:04
 */

namespace api;

class GiftsController extends BaseController
{

    function indexAction()
    {
        $gift_type = $this->params('gift_type', 1);

        $opts = ['gift_type' => $gift_type];

        $gifts = \Gifts::findValidList($this->currentUser(), $opts);

        $user_diamond_info = [
            'diamond' => intval($this->currentUser()->diamond),
            'gold' => intval($this->currentUser()->gold),
            'pay_url' => 'url://m/products'
        ];

//        if ($this->currentUser()->isNativePay()) {
//            $products = \Products::findDiamondListByUser($this->currentUser(), 'toApiJson');
//            $user_diamond_info['products'] = $products;
//        }

        return $this->renderJSON(
            ERROR_CODE_SUCCESS, '',
            array_merge($user_diamond_info, $gifts->toJson('gifts', 'toSimpleJson'))
        );
    }

    function createAction()
    {
        list($result, $reason) = $this->checkParams();

        if (!$result) {
            return $this->renderJSON(ERROR_CODE_FAIL, $reason);
        }

        $gift_num = $this->params('gift_num', 1);
        $renew = $this->params('renew', 0);
        $src = $this->params('src', 'room');
        $gift = \Gifts::findById($this->params('gift_id'));

        if (isBlank($gift) || $gift->isInvalid()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '礼物不存在');
        }

        $notify_type = $src == 'room' ? 'bc' : 'ptp';

        $user_id = $this->params('user_id');

        if (!$user_id) {
            if ($gift->isCar()) {
                $user_id = $this->currentUser()->id;
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
            }
        }

        $receiver_ids = explode(',', $user_id);

        if (count($receiver_ids) < 1) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }

        if (in_array($this->currentUserId(), $receiver_ids) && !$gift->isCar()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '不能给自己送礼物');
        }

        $total_gift_num = count($receiver_ids) * $gift_num;

        if ($gift->isDiamondPayType()) {

            $gift_amount = $total_gift_num * $gift->amount;
            $check_result = $this->currentUser()->canSendToUser($receiver_ids, $gift_amount);

            if (!$check_result) {
                return $this->renderJSON(ERROR_CODE_FAIL, '非常抱歉，您已经超过今日对外送出的额度');
            }
        }

        if ($this->currentUser()->canGiveGift($gift, $total_gift_num)) {

            $give_result = \GiftOrders::sendGift($this->currentUser(), $receiver_ids, $gift, $gift_num);

            if ($give_result) {

                $notify_data = \ImNotify::generateNotifyData(
                    'gifts',
                    'give',
                    $notify_type,
                    [
                        'gift' => $gift,
                        'gift_num' => $gift_num,
                        'sender' => $this->currentUser(),
                        'user_id' => $receiver_ids[0]
                    ]
                );

                $receiver_ids = explode(",", $receiver_ids);
                $receiver_num = count($receiver_ids);
                $total_gift_num = $receiver_num * $gift_num;
                $total_amount = intval($gift->amount) * $total_gift_num;


                $current_user = $this->currentUser(true);


                $res = array_merge($notify_data, ['diamond' => $current_user->diamond, 'gold' => $current_user->gold, 'total_amount' => $total_amount, 'pay_type' => $gift->pay_type]);

                $error_reason = "购买成功";

                if (!in_array($this->currentUser()->id, $receiver_ids)) {
                    $error_reason = "赠送成功";
                }

                if ($renew) {
                    $error_reason = "续费成功";
                }

                return $this->renderJSON(ERROR_CODE_SUCCESS, $error_reason, $res);
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '购买失败');
            }
        }

        return $this->renderJSON(ERROR_CODE_NEED_PAY, '余额不足');
    }

    function checkParams()
    {
        if (isBlank($this->params('gift_id'))) {
            return [false, '礼物错误'];
        }

        return [true, ''];
    }

    //设置座驾礼物
    function setCarGiftAction()
    {
        $gift_id = $this->params('gift_id');

        $gift = \Gifts::findById($this->params('gift_id'));

        if (!$gift) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $user_gift = \UserGifts::findFirstBy(['gift_id' => $gift_id, 'user_id' => $this->currentUser()->id, 'gift_type' => GIFT_TYPE_CAR]);

        if ($user_gift->isExpired()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '座驾已过期');
        }

        if (STATUS_ON != $user_gift->status) {

            $user_gift->status = STATUS_ON;

            if ($user_gift->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '设置成功', $user_gift->toSimpleJson());
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '设置失败');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '设置成功', $user_gift->toSimpleJson());
    }

    //取消座驾礼物
    function cancelCarGiftAction()
    {
        $gift_id = $this->params('gift_id');

        $gift = \Gifts::findById($this->params('gift_id'));

        if (!$gift) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $user_gift = \UserGifts::findFirstBy(['gift_id' => $gift_id, 'user_id' => $this->currentUser()->id, 'gift_type' => GIFT_TYPE_CAR]);

        if (STATUS_OFF != $user_gift->status) {

            $user_gift->status = STATUS_OFF;

            if ($user_gift->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '设置成功', $user_gift->toSimpleJson());
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '设置失败');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '设置成功', $user_gift->toSimpleJson());
    }
}