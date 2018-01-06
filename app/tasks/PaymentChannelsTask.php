<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 06/01/2018
 * Time: 13:50
 */

class PaymentChannelsTask extends \Phalcon\Cli\Task
{
    function testIndexAction()
    {
        $conditions = array("id" => 1);
        var_dump(count(\PaymentCHannels::findByConditions($conditions)));
    }
}