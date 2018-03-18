<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 15:02
 */

namespace m;

class ProductsController extends BaseController
{
    function indexAction()
    {
        $payment_channels = \PaymentChannels::search($this->currentUser());
        $products = \Products::search($this->currentUser());

        $selected_product = $products[0];
        $selected_payment_channel = $payment_channels[0];
        $this->view->selected_product = $selected_product;
        $this->view->selected_payment_channel = $selected_payment_channel;
        $this->view->products = $products;
        $this->view->user = $this->currentUser();
        $this->view->payment_channels = $payment_channels;
        $this->view->product_channel = $this->currentProductChannel();
        $this->view->title = '充值';
    }
}