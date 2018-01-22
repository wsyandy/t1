<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 22/01/2018
 * Time: 15:11
 */
namespace api;

class ProductsController extends BaseController
{
    function indexAction()
    {
        $products = \Products::findDiamondListByUser($this->currentUser(), 'toApiJson');

        $resp = array('diamond' => $this->currentUser()->diamond);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', array_merge($resp,array(
            'products' => $products)));
    }
}