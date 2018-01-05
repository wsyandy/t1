<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 17:11
 */

trait UserAbilities
{
    function canGiveGift($gift, $gift_num)
    {
        $total_amount = intval($gift->amount) * $gift_num;
        return intval($this->diamond) >= $total_amount;
    }
}