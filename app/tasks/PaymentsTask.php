<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 23/01/2018
 * Time: 17:09
 */

require 'CommonParam.php';

class PaymentsTask extends \Phalcon\Cli\Task
{
    use CommonParam;

    function testAppleResultAction()
    {
        $url = 'http://www.chance_php.com/api/payments/apple_result';
        $user = \Users::findLast();
        $product = \Products::findLast();
        $data = 'aa';
        $body = array_merge($this->commonBody(),
            array(
                'sid' => $user->sid,
                'product_id' => $product->id,
                'data' => $data
            )
        );

        $res = httpPost($url, $body);
        var_dump($res);
    }
}