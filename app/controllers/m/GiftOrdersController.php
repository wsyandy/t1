<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/3
 * Time: 下午5:04
 */

namespace m;

class GiftOrdersController extends BaseController
{

    //用户收到的礼物
    function indexAction()
    {
        if ($this->request->isAjax()) {

            $conds = ['conditions' => 'user_id = ' . $this->currentUserId() . ' and status=' . GIFT_ORDER_STATUS_SUCCESS . ' and gift_type = ' . GIFT_TYPE_COMMON
                , 'order' => 'created_at desc'];
            $page = $this->params('page', 1);
            $per_page = $this->params('per_page', 20);
            $gift_orders = \GiftOrders::findPagination($conds, $page, $per_page);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $gift_orders->toJson('gift_orders', 'toDetailJson'));
        }

        $code = $this->currentProductChannel()->code;
        $coin_name = '';
        if ($code == 'yuewan') {
            $coin_name = 'Hi';
        } else if ($code == 'ruanyuyin') {
            $coin_name = 'R';
        }

        $this->view->coin_name = $coin_name;
        $this->view->sid = $this->currentUser()->sid;
        $this->view->code = $code;
        $this->view->hi_coins = $this->currentUser()->getHiCoinText();
        $this->view->title = "我的礼物";
    }

    //用户送出的礼物
    function listAction()
    {
        if ($this->request->isAjax()) {
            $conds = ['conditions' => 'sender_id = ' . $this->currentUserId() . ' and status=' . GIFT_ORDER_STATUS_SUCCESS . ' and gift_type = ' . GIFT_TYPE_COMMON,
                'order' => 'created_at desc'];
            $page = $this->params('page', 1);
            $per_page = $this->params('per_page', 20);

            $gift_orders = \GiftOrders::findPagination($conds, $page, $per_page);

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $gift_orders->toJson('gift_orders', 'toDetailJson'));
        }
    }
}