<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 15/01/2018
 * Time: 15:36
 */

namespace m;

class UsersController extends BaseController
{
    function indexAction()
    {

    }

    function accountAction()
    {
        $products = \Products::findDiamondListByUser($this->currentUser());
        $payment_channels = \PaymentChannels::selectByUser($this->currentUser());

        $selected_product = $products[0];
        $selected_payment_channel = $payment_channels[0];
        $this->view->selected_product = $selected_product;
        $this->view->selected_payment_channel = $selected_payment_channel;
        $this->view->products = $products;
        $this->view->user = $this->currentUser();
        $this->view->payment_channels = $payment_channels;
        $this->view->product_channel = $this->currentProductChannel();
        $this->view->title = '我的账户';
    }

    function levelIntroduceAction()
    {
        $this->view->title = '荣耀等级';

        $current_user = $this->currentUser();

        $level = $current_user->level;

        $segment = $current_user->segment;

        $segment_text = $current_user->segment_text;

        $need_experience = $current_user->next_level_experience - $current_user->experience;

        $this->view->code = $this->params('code');
        $this->view->sid = $this->params('sid');

        $this->view->level = $level;
        $this->view->segment = $segment;
        $this->view->segment_text = $segment_text;
        $this->view->need_experience = $need_experience;
    }

    function levelDetailAction()
    {
        $this->view->title = '荣耀等级';
    }
}