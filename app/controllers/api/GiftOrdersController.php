<?php

namespace api;

class GiftOrdersController extends BaseController
{
    //根据传递的type判断返回数据，显示用户收到或送出的礼物
    function indexAction()
    {
        $type = $this->params('type', 'receive');
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 20);
        $opts = [];

        $conds = ['conditions' => 'status=' . GIFT_ORDER_STATUS_SUCCESS . ' and gift_type = ' . GIFT_TYPE_COMMON
            , 'order' => 'created_at desc'];
        if ($type == 'receive') {
            $conds['conditions'] .= ' and user_id = ' . $this->currentUserId();
            if ($page == 1) {
                $opts['hi_coins'] = $this->currentUser()->getHiCoinText();
            }

        } else {
            $conds['conditions'] .= ' and sender_id = ' . $this->currentUserId();
        }

        $gift_orders = \GiftOrders::findPagination($conds, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', array_merge($opts, $gift_orders->toJson('gift_orders', 'toDetailJson')));
    }
}