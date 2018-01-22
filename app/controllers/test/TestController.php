<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/22
 * Time: 下午8:14
 */

namespace test;

class TestController extends \ApplicationController
{
    function indexAction()
    {
        $user = \Users::findFirstById(1000);
        debug($user->id);
    }
}