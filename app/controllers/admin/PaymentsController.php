<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/5
 * Time: 上午10:52
 */

namespace admin;

class PaymentsController extends BaseController
{
    function indexAction()
    {
        $conds = array('order' => 'id desc');
        $page = 1;
        $per_page = 30;
        if (isPresent($this->params('user_id'))) {
            $conds = array(
                "conditions" => "user_id = :user_id:",
                "bind" => array(
                    "user_id" => $this->params('user_id')
                ),
                "order" => "id desc"
            );
        }

        $payments = \Payments::findPagination($conds, $page, $per_page);
        $this->view->payments = $payments;
    }

    function detailAction()
    {

    }
}