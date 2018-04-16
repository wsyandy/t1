<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 下午5:04
 */

namespace iapi;

class GiftsController extends BaseController
{

    function indexAction()
    {
        $gift_type = $this->params('gift_type', 1);

        $opts = ['gift_type' => $gift_type, 'abroad' => 1];

        $gifts = \Gifts::findValidList($this->currentUser(), $opts);

        $user_diamond_info = array(
            'i_gold' => intval($this->currentUser()->i_gold),
            'pay_url' => 'url://im/products'
        );

        if ($this->currentUser()->isNativePay()) {
            $products = \Products::findDiamondListByUser($this->currentUser(), 'toApiJson');
            $user_diamond_info['products'] = $products;
        }
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
            return $this->renderJSON(ERROR_CODE_FAIL, t('礼物不存在',$this->currentUser()->lang));
        }

        $notify_type = $src == 'room' ? 'bc' : 'ptp';

        $user_id = $this->params('user_id');

        if (!$user_id) {
            if ($gift->isCar()) {
                $user_id = $this->currentUser()->id;
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, t('用户不存在',$this->currentUser()->lang));
            }
        }

        if ($this->currentUser()->canGiveGift($gift, $gift_num)) {
            $give_result = \GiftOrders::giveToByInternational($this->currentUserId(), $user_id, $gift, $gift_num);

            if ($give_result) {

                $opts = ['gift' => $gift, 'gift_num' => $gift_num, 'sender' => $this->currentUser(), 'user_id' => $user_id];
                $notify_data = \ImNotify::generateNotifyData('gifts', 'give', $notify_type, $opts);

                $current_user = $this->currentUser(true);
                $res = array_merge($notify_data, ['i_gold' => $current_user->i_gold]);

                $error_reason = t('购买成功',$this->currentUser()->lang);

                if ($user_id != $this->currentUser()->id) {
                    $error_reason = t('赠送成功',$this->currentUser()->lang);
                }

                if ($renew) {
                    $error_reason = t('续费成功',$this->currentUser()->lang);
                }

                return $this->renderJSON(ERROR_CODE_SUCCESS, $error_reason, $res);
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, t('购买失败',$this->currentUser()->lang));
            }
        }
        return $this->renderJSON(ERROR_CODE_NEED_PAY, t('余额不足',$this->currentUser()->lang));
    }

    function checkParams()
    {
        if (isBlank($this->params('gift_id'))) {
            return [false, t('礼物错误',$this->currentUser()->lang)];
        }

        return [true, ''];
    }

    function setCarGiftAction()
    {
        $gift_id = $this->params('gift_id');

        $gift = \Gifts::findById($this->params('gift_id'));

        if (!$gift) {
            return $this->renderJSON(ERROR_CODE_FAIL, t('参数错误',$this->currentUser()->lang));
        }

        $user_gift = \UserGifts::findFirstBy(['gift_id' => $gift_id, 'user_id' => $this->currentUser()->id, 'gift_type' => GIFT_TYPE_CAR]);

        if ($user_gift->isExpired()) {
            return $this->renderJSON(ERROR_CODE_FAIL, t('座驾已过期',$this->currentUser()->lang));
        }

        if (STATUS_ON != $user_gift->status) {

            $user_gift->status = STATUS_ON;

            if ($user_gift->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, t('设置成功',$this->currentUser()->lang), $user_gift->toSimpleJson());
            }

            return $this->renderJSON(ERROR_CODE_FAIL, t('设置失败',$this->currentUser()->lang));
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, t('设置成功',$this->currentUser()->lang), $user_gift->toSimpleJson());
    }
}