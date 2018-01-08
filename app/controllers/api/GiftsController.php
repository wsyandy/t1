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
        $gifts = \Gifts::findValidList();
        $user_diamond_info = array(
            'diamond' => intval($this->currentUser()->diamond),
            'pay_url' => 'url://m/products'
        );
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
       $src = $this->params('src', 'room');
       $gift = \Gifts::findById($this->params('gift_id'));
       if (isBlank($gift) || $gift->invalid()) {
           return  $this->renderJSON(ERROR_CODE_FAIL, '礼物不存在');
       }
       $notify_type = $src == 'room' ? 'bc' : 'ptp';
       $user_id = $this->params('user_id');
       if ($this->currentUser()->canGiveGift($gift, $gift_num)) {
           $give_result = \GiftOrders::giveTo($this->currentUserId(), $user_id, $gift, $gift_num);
           if ($give_result) {
               $notify_data = \ImNotify::generateNotifyData(
                   'gifts',
                   'give',
                   $notify_type,
                   array(
                       'gift' => $gift,
                       'gift_num' => $gift_num,
                       'sender' => $this->currentUser(),
                       'user_id' => $user_id
                   )
               );
               return $this->renderJSON(ERROR_CODE_SUCCESS, '赠送成功', array('notify_data' => $notify_data));
           } else {
               return $this->renderJSON(ERROR_CODE_FAIL, '赠送失败');
           }
       }
        return $this->renderJSON(ERROR_CODE_NEED_PAY, '余额不足');
    }

    function checkParams()
    {
        if (isBlank($this->params('gift_id'))) {
            return [false, '礼物错误'];
        }
        if (isBlank($this->params('user_id'))) {
            return [false, '用户不存在'];
        }
        return [true, ''];
    }
}