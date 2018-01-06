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
        $products = \Products::findDiamondListByUser($this->currentUser());
        $payment_channels = \PaymentChannels::selectByUser($this->currentUser());

        $selected_product = $products[0];
        $selected_payment_channel = $payment_channels[0];
        $this->view->selected_product = $selected_product;
        $this->view->selected_payment_channel = $selected_payment_channel;
        $this->view->products = $products;
        $this->view->user = $this->currentUser();
        $this->view->payment_channels = $payment_channels;
    }
}